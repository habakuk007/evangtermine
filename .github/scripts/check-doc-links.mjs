// Checks the relative links in README.md and docs/**/*.md (CI job "Docs").
//
// For every Markdown link and image with a relative target it checks that the file exists
// and, if the link has a #anchor, that the target Markdown file has a heading with that anchor
// (GitHub's rules for the anchor of a heading). External links (http, https, mailto) are not
// checked here: the check needs no network. Links inside fenced code blocks and inline code are ignored.
//
// Usage: node .github/scripts/check-doc-links.mjs [file-or-folder ...]   (default: README.md docs)

import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { dirname, join, resolve, relative, sep } from 'node:path';

const root = process.cwd();
const targets = process.argv.length > 2 ? process.argv.slice( 2 ) : [ 'README.md', 'docs' ];

function collect( path, files ) {
	const stat = statSync( path );
	if ( stat.isDirectory() ) {
		for ( const entry of readdirSync( path ) ) {
			collect( join( path, entry ), files );
		}
	} else if ( path.endsWith( '.md' ) ) {
		files.push( resolve( path ) );
	}
	return files;
}

// Removes fenced code blocks and inline code, so that examples are not mistaken for links.
function stripCode( text ) {
	return text.replace( /^(```|~~~)[^\n]*\n[\s\S]*?^\1[^\n]*$/gm, '' ).replace( /`[^`\n]*`/g, '' );
}

// GitHub's anchor of a heading: lower case, without markup and punctuation, spaces become hyphens.
function slug( heading ) {
	return heading
		.replace( /\[([^\]]*)\]\([^)]*\)/g, '$1' )
		.replace( /[`*_]/g, '' )
		.trim()
		.toLowerCase()
		.replace( /[^\p{L}\p{N}\s_-]/gu, '' )
		.replace( /\s/g, '-' );
}

const anchorCache = new Map();
function anchorsOf( file ) {
	if ( ! anchorCache.has( file ) ) {
		const anchors = new Set();
		const counts = new Map();
		for ( const match of stripCode( readFileSync( file, 'utf8' ) ).matchAll( /^#{1,6}[ \t]+(.+?)[ \t#]*$/gm ) ) {
			const base = slug( match[ 1 ] );
			const n = counts.get( base ) || 0;
			counts.set( base, n + 1 );
			anchors.add( n === 0 ? base : `${ base }-${ n }` );
		}
		anchorCache.set( file, anchors );
	}
	return anchorCache.get( file );
}

const errors = [];
const files = targets.flatMap( ( target ) => collect( target, [] ) );

for ( const file of files ) {
	const text = stripCode( readFileSync( file, 'utf8' ) );
	const shown = relative( root, file ).split( sep ).join( '/' );
	// [text](target) and ![alt](target); the target ends at the first space or closing parenthesis.
	for ( const match of text.matchAll( /!?\[[^\]]*\]\(\s*<?([^)\s>]+)>?(?:\s+"[^"]*")?\s*\)/g ) ) {
		const target = match[ 1 ];
		if ( /^[a-z][a-z0-9+.-]*:/i.test( target ) || target.startsWith( '//' ) ) {
			continue; // http:, https:, mailto: ...
		}
		const [ pathPart, anchor ] = target.split( '#' );
		const targetFile = pathPart === '' ? file : resolve( dirname( file ), decodeURIComponent( pathPart ) );
		if ( ! existsSync( targetFile ) ) {
			errors.push( `${ shown }: broken link "${ target }" (file not found)` );
			continue;
		}
		if ( anchor && targetFile.endsWith( '.md' ) && ! anchorsOf( targetFile ).has( decodeURIComponent( anchor ).toLowerCase() ) ) {
			errors.push( `${ shown }: broken link "${ target }" (no heading with this anchor)` );
		}
	}
}

if ( errors.length > 0 ) {
	console.error( errors.join( '\n' ) );
	console.error( `\n${ errors.length } broken link(s) in ${ files.length } file(s).` );
	process.exit( 1 );
}
console.log( `Checked the relative links in ${ files.length } Markdown file(s): no broken link.` );
