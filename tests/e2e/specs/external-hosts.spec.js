// Regression guard (issue #33, finding 4): which hosts do the shortcode pages contact from the browser?
//
// The browser may only contact the hosts in ALLOWLIST. Every entry names the follow-up PR that removes it. When a PR
// removes a host from the output, it also removes the entry here, so the guard gets stricter step by step.
// A new external host (a new CDN, font, script, tracking pixel, ...) makes these tests fail.
const { test, expect, visit } = require( '../helpers' );

const ALLOWLIST = [
	// Images of the detail view that the plugin points to the calendar host (/Upload/ rewrite). The default stylesheet
	// and the placeholder image are bundled since #33. Removed with the image decision (4.0, #121).
	'www.evangelische-termine.de',
	// Event images in panels and minical output (disclosed). Removed by PR E (#33).
	'et-live.obsv3.de-global-1.spacenet.cloud',
	// Not the plugin: WordPress core. Playground logs the administrator in on every new browser session, and the admin
	// bar shows the Gravatar of that user. It is never removed, but it only matters for the logged-in test site.
	'secure.gravatar.com',
];

// Every page of the test site that shows calendar output.
const PAGES = [
	'/et-veranstalter/',
	'/et-teaser/',
	'/et-panels/',
	'/et-minical/',
	'/et-all/',
	'/et-veranstalter/?etID=1001', // detail view
];

// Values of src/href/srcset that start a request, read from the markup (protocol-relative URLs stay as written).
// Navigation links (a[href], for example the iCal and invitation links of the detail view) are out of scope on purpose:
// they only contact a host when the visitor clicks them.
async function referencedUrls( page ) {
	return page.evaluate( () => {
		const selectors = [
			[ 'link[rel~="stylesheet"][href]', 'href' ],
			[ 'script[src]', 'src' ],
			[ 'img[src]', 'src' ],
			[ 'img[srcset], source[srcset]', 'srcset' ],
			[ 'iframe[src]', 'src' ],
		];
		const found = [];
		for ( const [ selector, attribute ] of selectors ) {
			for ( const node of document.querySelectorAll( selector ) ) {
				const raw = node.getAttribute( attribute );
				const values = 'srcset' === attribute ? raw.split( ',' ).map( ( part ) => part.trim().split( /\s+/ )[ 0 ] ) : [ raw ];
				for ( const value of values ) {
					// A malformed value (or a data:/javascript: URL) cannot start a request to another host: skip it
					// instead of failing the whole test with an unclear stack.
					try {
						const url = new URL( value, document.baseURI );
						if ( /^https?:$/.test( url.protocol ) ) {
							found.push( { selector, raw: value, url: url.href } );
						}
					} catch ( error ) {
						// Not a valid URL: ignore.
					}
				}
			}
		}
		return found;
	} );
}

// One place for the allowlist semantics: an http(s) URL on another origin than the test site and not on the allowlist.
function isOutside( href, own ) {
	const url = new URL( href );
	return /^https?:$/.test( url.protocol ) && url.origin !== own && ! ALLOWLIST.includes( url.hostname );
}

test.describe( 'external hosts guard', () => {
	for ( const path of PAGES ) {
		test( `${ path }: only allowed hosts are requested and referenced`, async ( { page, baseURL } ) => {
			await visit( page, path );
			await page.waitForLoadState( 'networkidle' );

			const own = new URL( baseURL ).origin;

			// (a) Requests that the browser tried to send to other hosts (the fixture "page" blocks and records them).
			expect( page.externalRequests.filter( ( href ) => isOutside( href, own ) ), 'requests to hosts outside the allowlist' ).toEqual( [] );
			expect( page.externalRequests.filter( ( url ) => /\.js(\?|$)/.test( url ) ), 'external JavaScript' ).toEqual( [] );

			// (b) Markup that would start a request later (lazy images, scripts that did not run, ...).
			const referenced = await referencedUrls( page );
			const outside = referenced.filter( ( entry ) => isOutside( entry.url, own ) );
			expect( outside, 'src/href/srcset outside the allowlist' ).toEqual( [] );
			const externalScripts = referenced.filter( ( entry ) => 'script[src]' === entry.selector && new URL( entry.url ).origin !== own );
			expect( externalScripts, 'external script[src]' ).toEqual( [] );
		} );
	}

	test( 'the default stylesheet and the placeholder image come from the plugin, not from the calendar host', async ( { page, baseURL } ) => {
		const own = new URL( baseURL ).origin;

		await visit( page, '/et-veranstalter/' );
		let referenced = await referencedUrls( page );
		const sheets = referenced.filter( ( entry ) => 'link[rel~="stylesheet"][href]' === entry.selector );
		expect( sheets.filter( ( entry ) => new URL( entry.url ).origin !== own ), 'external stylesheets' ).toEqual( [] );
		expect( sheets.some( ( entry ) => /\/assets\/styles\/publicintegration\.css/.test( entry.url ) ), 'bundled publicintegration.css' ).toBe( true );

		await visit( page, '/et-panels/' );
		referenced = await referencedUrls( page );
		const placeholders = referenced.filter( ( entry ) => /placeholder\.png/.test( entry.url ) );
		expect( placeholders.length, 'placeholder image in the panels' ).toBeGreaterThan( 0 );
		expect( placeholders.filter( ( entry ) => new URL( entry.url ).origin !== own ), 'external placeholder image' ).toEqual( [] );
	} );

	test( 'with a custom CSS file the default stylesheet is not loaded at all', async ( { page, site } ) => {
		await site.reset( { options: { css: 'https://example.org/my.css' } } );

		await visit( page, '/et-veranstalter/' );
		const referenced = await referencedUrls( page );
		expect( referenced.filter( ( entry ) => /publicintegration\.css/.test( entry.url ) ), 'publicintegration.css' ).toEqual( [] );
	} );
} );
