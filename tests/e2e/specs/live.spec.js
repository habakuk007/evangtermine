// Optional checks against the real calendar host www.evangelische-termine.de.
// Not part of the default run and not run in CI on pull requests: npm run test:e2e:live
//
// The data on the real host changes, so these tests check only the structure of the output.
const { test, expect, visit } = require( '../helpers' );

const UNAVAILABLE = 'Der Veranstaltungskalender ist derzeit nicht verfügbar.';

test.describe( 'live calendar host', () => {
	// The real host can be slow, searches take 5 to 8 seconds.
	test.setTimeout( 120000 );

	test.use( { setup: { mode: 'live' } } );

	test( 'et_veranstalter with the organiser 1474 shows the filter form', async ( { page, site } ) => {
		await visit( page, '/et-veranstalter-1474/' );

		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		await expect( list.locator( '#et_filter_container form' ) ).toBeVisible();
		await expect( list.locator( '#et_content_container' ) ).toHaveCount( 1 );

		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].scheme ).toBe( 'https' );
		expect( requests[ 0 ].query.vid ).toBe( '1474' );
	} );

	test( 'et_veranstalter with a region shows events, page 2 and a detail view', async ( { page } ) => {
		await visit( page, '/et-veranstalter/' );
		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		await expect( list.locator( '.et_content_row' ).first() ).toBeVisible();
		const firstTitle = await list.locator( '.et_link_title' ).first().textContent();

		await list.locator( 'a[title="page 2"]' ).first().click();
		await expect( page ).toHaveURL( /pageID=2/ );
		await expect( list.locator( '.et_content_row' ).first() ).toBeVisible();

		await visit( page, '/et-veranstalter/' );
		await list.locator( '.et_link_title' ).first().click();
		await expect( page ).toHaveURL( /etID=\d+/ );
		await expect( list.locator( 'h2#et_detail_title' ) ).toHaveText( firstTitle.trim() );
	} );

	test( 'et_veranstalter search', async ( { page } ) => {
		await visit( page, '/et-veranstalter/?et_q=Gottesdienst&action=search' );
		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		await expect( list.locator( '#et_totalItems' ) ).toHaveText( /^\d+$/ );
	} );

	test( 'et_teaser, et_panels and the widget show events', async ( { page } ) => {
		await visit( page, '/et-teaser/' );
		await expect( page.locator( '.et_teaser .et_content_row' ).first() ).toBeVisible();
		await expect( page.locator( '.et_sidebar .et_content_row' ).first() ).toBeVisible();

		await visit( page, '/et-panels/' );
		await expect( page.locator( '.et_panel .thumbnail' ).first() ).toBeVisible();
	} );
} );
