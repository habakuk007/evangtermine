// et_veranstalter: list, pagination, search, filters, reset and detail view.
//
// The tests check only what a visitor sees and what the plugin asks the calendar host. They run two times:
// without a PHP session (WordPress does not start one) and with an active PHP session (another plugin may start one).
const { test, expect, visit } = require( '../helpers' );

const PAGE = '/et-veranstalter/';

/** The last request for the event list that the plugin sent to the calendar host. */
async function lastListRequest( site, page ) {
	const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
	expect( requests.length ).toBeGreaterThan( 0 );
	return requests[ requests.length - 1 ];
}

for ( const session of [ false, true ] ) {
	test.describe( `et_veranstalter ${ session ? 'with' : 'without' } a PHP session`, () => {
		test.use( { setup: { session } } );

		test( 'the PHP session is ' + ( session ? 'active' : 'not active' ), async ( { site } ) => {
			expect( ( await site.info() ).session ).toBe( session );
		} );

		test( 'pagination: page 2 and back', async ( { page, site } ) => {
			await visit( page, PAGE );
			const list = page.locator( '.et_veranstalter' );
			await expect( list.locator( '.et_link_title' ).first() ).toHaveText( 'Offener Kreis' );

			await list.locator( 'a[title="page 2"]' ).first().click();
			await expect( list.locator( '.et_link_title' ).first() ).toHaveText( 'Seniorennachmittag' );
			await expect( list.locator( '.et_content_row' ) ).toHaveCount( 3 );
			expect( new URL( page.url() ).pathname ).toBe( PAGE );
			expect( ( await lastListRequest( site, page ) ).query ).toMatchObject( { pageID: '2', vid: 'all', region: '407', itemsPerPage: '5' } );

			await list.locator( 'a[title="page 1"]' ).first().click();
			await expect( list.locator( '.et_link_title' ).first() ).toHaveText( 'Offener Kreis' );
			expect( ( await lastListRequest( site, page ) ).query.pageID ).toBe( '1' );
		} );

		test( 'search sends the term to the calendar host', async ( { page, site } ) => {
			await visit( page, PAGE );
			const list = page.locator( '.et_veranstalter' );

			await list.locator( '#et_q' ).fill( 'Offener Kreis' );
			await list.locator( '#Suche' ).click();

			await expect( list.locator( '.et_link_title' ).nth( 1 ) ).toHaveText( 'Offener Familientreff' );
			await expect( list.locator( '#et_totalItems' ) ).toHaveText( '7' );
			const request = await lastListRequest( site, page );
			expect( request.query.q ).toBe( 'Offener Kreis' );
			// Spaces are sent as "+", the way the calendar host expects it.
			expect( request.url ).toContain( 'q=Offener+Kreis' );
		} );

		test( 'search without a result', async ( { page } ) => {
			await visit( page, `${ PAGE }?et_q=nichts&action=search` );
			await expect( page.locator( '.et_veranstalter .monthbar' ) ).toHaveText( 'Ihre Suche ergab leider keine Ergebnisse.' );
		} );

		test( 'a filter is sent to the calendar host and kept on page 2', async ( { page, site } ) => {
			await visit( page, PAGE );
			const list = page.locator( '.et_veranstalter' );

			await list.locator( '#eventtype' ).selectOption( '1' );
			await list.locator( '#Suche' ).click();
			await expect( list.locator( '.et_content_row' ) ).toHaveCount( 5 );
			expect( ( await lastListRequest( site, page ) ).query.eventtype ).toBe( '1' );

			await list.locator( 'a[title="page 2"]' ).first().click();
			await expect( list.locator( '.et_link_title' ).first() ).toHaveText( 'Seniorennachmittag' );
			expect( ( await lastListRequest( site, page ) ).query ).toMatchObject( { eventtype: '1', pageID: '2' } );
		} );

		test( 'reset goes back to the values of the shortcode', async ( { page, site } ) => {
			await visit( page, `${ PAGE }?et_q=Offener&eventtype=1&vid=9002&pageID=2&action=search` );
			const list = page.locator( '.et_veranstalter' );
			expect( ( await lastListRequest( site, page ) ).query ).toMatchObject( { q: 'Offener', eventtype: '1', vid: '9002' } );

			await list.locator( '#reset' ).click();

			await expect( list.locator( '.et_link_title' ).first() ).toHaveText( 'Offener Kreis' );
			await expect( list.locator( '#et_totalItems' ) ).toHaveText( '8' );
			const request = await lastListRequest( site, page );
			expect( request.query ).toMatchObject( { vid: 'all', region: '407', eventtype: '', q: '', itemsPerPage: '5' } );
			expect( request.query.pageID ).toBeUndefined();

			// After the reset a plain visit of the page still shows the unfiltered list.
			await visit( page, PAGE );
			await expect( list.locator( '#et_totalItems' ) ).toHaveText( '8' );
			expect( ( await lastListRequest( site, page ) ).query ).toMatchObject( { vid: 'all', eventtype: '', q: '' } );
		} );

		test( 'detail view of an event', async ( { page, site } ) => {
			await visit( page, PAGE );
			const list = page.locator( '.et_veranstalter' );

			await list.locator( '.et_link_title' ).first().click();
			await expect( page ).toHaveURL( /[?&]etID=1001(&|$)/ );
			expect( new URL( page.url() ).pathname ).toBe( PAGE );

			await expect( list.locator( '#et_detail_title' ) ).toHaveText( 'Offener Kreis' );
			await expect( list.locator( '#et_detail_date' ) ).toContainText( '12.11.2026' );
			// Images of the calendar get the address of the calendar host.
			await expect( list.locator( '#et_place_image img' ) ).toHaveAttribute( 'src', 'https://www.evangelische-termine.de/Upload/place/e2e-fixture.png' );

			const requests = await site.requests( page, { module: 'detail-php' } );
			expect( requests ).toHaveLength( 1 );
			expect( requests[ 0 ].query.ID ).toBe( '1001' );
			expect( requests[ 0 ].query.vid ).toBe( 'all' );
		} );

		test( 'detail view with an unknown event ID shows the message of the calendar host', async ( { page } ) => {
			await visit( page, `${ PAGE }?etID=999999` );
			await expect( page.locator( '.et_veranstalter .warning' ) ).toContainText( 'Termin-ID fehlerhaft' );
		} );
	} );
}

// Known bugs in the plugin. They are not fixed here: each test is marked with test.fail() and passes as long as
// the bug exists. When a bug is fixed, the test "passes unexpectedly": then remove test.fail().
test.describe( 'et_veranstalter: known bugs', () => {
	test( 'the search term is kept on page 2', async ( { page, site } ) => {
		// The pagination links carry "q=<term>", but the plugin reads only "et_q". Page 2 shows the unfiltered list.
		test.fail();

		await visit( page, `${ PAGE }?et_q=Offener&action=search` );
		const list = page.locator( '.et_veranstalter' );
		await expect( list.locator( '#et_totalItems' ) ).toHaveText( '7' );

		await list.locator( 'a[title="page 2"]' ).first().click();
		await expect( page ).toHaveURL( /pageID=2/ );
		await expect( list.locator( '.et_content_row' ).first() ).toBeVisible();
		expect( ( await lastListRequest( site, page ) ).query ).toMatchObject( { q: 'Offener', pageID: '2' } );
	} );

	test( 'reset also clears a search term that is still in the search field', async ( { page, site } ) => {
		// With "reset=1&et_q=term" the plugin still searches for the term.
		test.fail();

		await visit( page, PAGE );
		const list = page.locator( '.et_veranstalter' );
		await list.locator( '#et_q' ).fill( 'Offener' );
		await list.locator( '#reset' ).click();
		await expect( page ).toHaveURL( /reset=1/ );
		await expect( list.locator( '.et_content_row' ).first() ).toBeVisible();
		expect( ( await lastListRequest( site, page ) ).query.q ).toBe( '' );
	} );

	test( 'the <h1> of the detail view becomes an <h2>', async ( { page } ) => {
		// EtQuery::filterStream() replaces only "<h1>" without attributes. The calendar host sends
		// <h1 id="et_detail_title">, so the output is <h1 id="et_detail_title">...</h2>.
		test.fail();

		await visit( page, `${ PAGE }?etID=1001` );
		await expect( page.locator( '.et_veranstalter #et_detail_title' ) ).toHaveText( 'Offener Kreis' );
		await expect( page.locator( '.et_veranstalter h2#et_detail_title' ) ).toHaveCount( 1, { timeout: 2000 } );
	} );
} );
