// Security: XSS probes, cookies, protocol and host of the request to the calendar host.
const { test, expect, visit } = require( '../helpers' );

const PAGE = '/et-veranstalter/';
const HOST = 'www.evangelische-termine.de';

/** Fails the test if the page opens a dialog (alert) or sets window.__xss. */
function watchForXss( page ) {
	const dialogs = [];
	page.on( 'dialog', async ( dialog ) => {
		dialogs.push( dialog.message() );
		await dialog.dismiss();
	} );
	return async () => {
		expect( dialogs, 'JavaScript dialogs' ).toEqual( [] );
		expect( await page.evaluate( () => typeof window.__xss ), 'window.__xss' ).toBe( 'undefined' );
		// No element with an injected event handler or script.
		await expect( page.locator( '.et_veranstalter [onmouseover], .et_veranstalter [onerror], .et_veranstalter [onload]' ) ).toHaveCount( 0 );
	};
}

/** The pagination links must still be links to page 2 of the same WordPress page. */
async function expectPagerLinksIntact( page ) {
	const links = page.locator( '.et_veranstalter a[title="page 2"]' );
	await expect( links ).toHaveCount( 2 );
	for ( const href of await links.evaluateAll( ( nodes ) => nodes.map( ( node ) => node.href ) ) ) {
		const url = new URL( href );
		expect( url.origin ).toBe( new URL( page.url() ).origin );
		expect( url.pathname ).toBe( PAGE );
		expect( url.searchParams.get( 'pageID' ) ).toBe( '2' );
	}
	// The link text is only the page number: nothing leaked out of the href attribute.
	await expect( links.first() ).toHaveText( '2' );
}

test.describe( 'XSS probes on et_veranstalter', () => {
	test( 'script in the search term (et_q)', async ( { page, site } ) => {
		const assertNoXss = watchForXss( page );
		await visit( page, `${ PAGE }?et_q=${ encodeURIComponent( '"><script>window.__xss=1</script>' ) }` );

		await assertNoXss();
		await expectPagerLinksIntact( page );
		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].url ).not.toMatch( /[<>"']/ );
	} );

	test( 'attribute injection in the search term (et_q)', async ( { page, site } ) => {
		const assertNoXss = watchForXss( page );
		const probe = 'x" onmouseover="window.__xss=1" y="';
		await visit( page, `${ PAGE }?et_q=${ encodeURIComponent( probe ) }` );

		await assertNoXss();
		await expectPagerLinksIntact( page );
		// The term reaches the calendar host unchanged, but URL-encoded.
		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests[ 0 ].query.q ).toBe( probe );
		expect( requests[ 0 ].url ).not.toMatch( /[<>"' ]/ );
	} );

	test( 'HTML in the name and value of an unknown parameter', async ( { page, site } ) => {
		const assertNoXss = watchForXss( page );
		const name = encodeURIComponent( 'x"y' );
		const value = encodeURIComponent( '<img src=x onerror=window.__xss=1>' );
		await visit( page, `${ PAGE }?${ name }=${ value }&foo=${ encodeURIComponent( 'a b"c' ) }` );

		await assertNoXss();
		await expectPagerLinksIntact( page );
		await expect( page.locator( '.et_veranstalter img[src="x"]' ) ).toHaveCount( 0 );

		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].url ).not.toMatch( /[<>"' ]/ );
		// Unknown parameters are passed on with a cleaned name and an encoded value.
		expect( requests[ 0 ].query.foo ).toBe( 'a b"c' );
		expect( Object.keys( requests[ 0 ].query ) ).toContain( 'xy' );
	} );

	test( 'HTML in the event ID (etID)', async ( { page, site } ) => {
		const assertNoXss = watchForXss( page );
		await visit( page, `${ PAGE }?etID=${ encodeURIComponent( '1"><svg onload=window.__xss=1>' ) }` );

		await assertNoXss();
		await expect( page.locator( '.et_veranstalter svg' ) ).toHaveCount( 0 );
		// Only the number reaches the calendar host.
		const requests = await site.requests( page, { module: 'detail-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].query.ID ).toBe( '1' );
		expect( requests[ 0 ].url ).not.toMatch( /[<>"' ]/ );
	} );

	test( 'arrays as parameters are ignored', async ( { page, site } ) => {
		await visit( page, `${ PAGE }?et_q[]=a&etID[]=5&foo[]=b&pageID[]=2` );

		await expect( page.locator( '.et_veranstalter .et_content_row' ) ).toHaveCount( 5 );
		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].query ).toMatchObject( { q: '' } );
		expect( requests[ 0 ].query.foo ).toBeUndefined();
		expect( requests[ 0 ].query.pageID ).toBeUndefined();
	} );
} );

test.describe( 'request to the calendar host', () => {
	test( 'cookies of the visitor are not forwarded', async ( { page, context, baseURL, site } ) => {
		await context.addCookies( [
			{ name: 'et_secret', value: 's3cret-cookie', url: baseURL },
			{ name: 'et_q', value: 'cookieprobe', url: baseURL },
			{ name: 'pageID', value: '2', url: baseURL },
			{ name: 'vid', value: '4711', url: baseURL },
		] );
		await visit( page, '/et-all/' );
		await expect( page.locator( '.et_veranstalter .et_link_title' ).first() ).toHaveText( 'Offener Kreis' );

		// List, teaser and widget.
		const requests = await site.requests( page );
		expect( requests ).toHaveLength( 3 );
		for ( const request of requests ) {
			expect( request.cookies ).toBe( 0 );
			expect( Object.keys( request.headers ).map( ( name ) => name.toLowerCase() ) ).toEqual( [ 'referer' ] );
			expect( request.url ).not.toContain( 's3cret-cookie' );
			expect( request.url ).not.toContain( 'cookieprobe' );
			expect( request.url ).not.toContain( '4711' );
			expect( request.query.pageID ).toBeUndefined();
			expect( request.query.vid ).toBe( 'all' );
		}
	} );

	test( 'the default protocol is https and unsafe targets are rejected by WordPress', async ( { page, site } ) => {
		await visit( page, '/et-all/' );

		const requests = await site.requests( page );
		expect( requests ).toHaveLength( 3 );
		for ( const request of requests ) {
			expect( request.scheme ).toBe( 'https' );
			expect( request.host ).toBe( HOST );
			expect( request.reject_unsafe_urls ).toBe( true );
			expect( request.user_agent ).toMatch( /^evangtermine\/[\d.]+ / );
		}
	} );

	test( 'the protocol option http:// is used when it is set', async ( { page, site } ) => {
		await site.reset( { options: { etprotocol: 'http://' } } );
		await visit( page, PAGE );

		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests[ 0 ].scheme ).toBe( 'http' );
	} );

	for ( const host of [ 'localhost', '169.254.169.254', '10.0.0.1', 'intern.localhost:8080' ] ) {
		test( `an unsafe host in the database (${ host }) is not requested`, async ( { page, site } ) => {
			// Written directly into the database, as if the option was saved before the host check existed.
			await site.reset( { options: { ethost: host } } );
			await visit( page, PAGE );

			await expect( page.locator( '.et_veranstalter' ) ).toHaveText( 'Der Veranstaltungskalender ist derzeit nicht verfügbar.' );
			expect( await site.requests( page ) ).toEqual( [] );
		} );
	}
} );

test.describe( 'et_minical', () => {
	test( "an apostrophe in a shortcode attribute does not break the inline script", async ( { page } ) => {
		const errors = [];
		page.on( 'pageerror', ( error ) => errors.push( error.message ) );
		await visit( page, '/et-minical-quote/' );

		await expect( page.locator( '#et_minicalendar' ) ).toHaveCount( 1 );
		const data = await page.evaluate( () => window.etMinicalData );
		expect( data.parameters.place ).toBe( "St. Mary's" );
		expect( data.baseUrl ).toBe( `https://${ HOST }` );
		expect( errors ).toEqual( [] );
	} );
} );
