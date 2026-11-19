// Regression guard (issue #33, finding 4): which hosts do the shortcode pages contact from the browser?
//
// The browser may only contact the hosts in ALLOWLIST. Every entry names the follow-up PR that removes it. When a PR
// removes a host from the output, it also removes the entry here, so the guard gets stricter step by step.
// A new external host (a new CDN, font, script, tracking pixel, ...) makes these tests fail.
const { test, expect, visit } = require( '../helpers' );

const ALLOWLIST = [
	// publicintegration.css and the placeholder image (/bundles/vket/images/placeholder.png), and images of the detail
	// view that the plugin points to the calendar host. Removed by PR C (#33).
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
					if ( value ) {
						found.push( { selector, raw: value, url: new URL( value, document.baseURI ).href } );
					}
				}
			}
		}
		return found;
	} );
}

function hostsOutside( urls ) {
	return urls
		.map( ( url ) => new URL( url ) )
		.filter( ( url ) => /^https?:$/.test( url.protocol ) && ! ALLOWLIST.includes( url.hostname ) )
		.map( ( url ) => url.href );
}

test.describe( 'external hosts guard', () => {
	for ( const path of PAGES ) {
		test( `${ path }: only allowed hosts are requested and referenced`, async ( { page, baseURL } ) => {
			await visit( page, path );
			await page.waitForLoadState( 'networkidle' );

			// (a) Requests that the browser tried to send to other hosts (the fixture "page" blocks and records them).
			expect( hostsOutside( page.externalRequests ), 'requests to hosts outside the allowlist' ).toEqual( [] );
			expect( page.externalRequests.filter( ( url ) => /\.js(\?|$)/.test( url ) ), 'external JavaScript' ).toEqual( [] );

			// (b) Markup that would start a request later (lazy images, scripts that did not run, ...).
			const own = new URL( baseURL ).origin;
			const referenced = await referencedUrls( page );
			const outside = referenced.filter( ( entry ) => {
				const url = new URL( entry.url );
				return /^https?:$/.test( url.protocol ) && url.origin !== own && ! ALLOWLIST.includes( url.hostname );
			} );
			expect( outside, 'src/href/srcset outside the allowlist' ).toEqual( [] );
			const externalScripts = referenced.filter( ( entry ) => 'script[src]' === entry.selector && new URL( entry.url ).origin !== own );
			expect( externalScripts, 'external script[src]' ).toEqual( [] );
		} );
	}
} );
