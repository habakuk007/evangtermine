// Optional checks against a real calendar host (default: www.evangelische-termine.de).
// Not part of the default run and not run in CI on pull requests: npm run test:e2e:live
//
// Environment variables (all optional):
//   ET_E2E_LIVE_HOST    calendar host for the checks (default www.evangelische-termine.de)
//   ET_E2E_LIVE_VID     organiser with the filter form (default 1474)
//   ET_E2E_LIVE_REGION  region for the lists (default 407)
//   ET_E2E_LIVE_ALT_HOST  host of the "other host" check (default termine.ekir.de)
//
// The data on the real host changes, so these tests check only the structure of the output.
const { test, expect, visit } = require( '../helpers' );

const UNAVAILABLE = 'The event calendar is currently unavailable.';

const HOST = process.env.ET_E2E_LIVE_HOST || 'www.evangelische-termine.de';
const VID = process.env.ET_E2E_LIVE_VID || '1474';
const REGION = process.env.ET_E2E_LIVE_REGION || '407';
const ALT_HOST = process.env.ET_E2E_LIVE_ALT_HOST || 'termine.ekir.de';
const IS_DEFAULT_HOST = 'www.evangelische-termine.de' === HOST;

test.describe( 'live calendar host', () => {
	// The real host can be slow, searches take 5 to 8 seconds.
	test.setTimeout( 120000 );

	// The option "ethost" points the plugin to the configured host; "vid" is the organiser of the page without own
	// values. The region of the list pages is passed as a request parameter (the shortcode has vid=all).
	test.use( { setup: { mode: 'live', options: { ethost: HOST, vid: VID } } } );

	test( 'et_veranstalter with the organiser 1474 shows the filter form', async ( { page, site } ) => {
		await visit( page, '/et-veranstalter-1474/' );

		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		await expect( list.locator( '#et_filter_container form' ) ).toBeVisible();
		await expect( list.locator( '#et_content_container' ) ).toHaveCount( 1 );

		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].scheme ).toBe( 'https' );
		expect( requests[ 0 ].host ).toBe( HOST );
		expect( requests[ 0 ].query.vid ).toBe( VID );
	} );

	test( 'et_veranstalter with a region shows events, page 2 and a detail view', async ( { page } ) => {
		await visit( page, `/et-veranstalter/?region=${ REGION }` );
		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		await expect( list.locator( '.et_content_row' ).first() ).toBeVisible();
		const firstTitle = await list.locator( '.et_link_title' ).first().textContent();

		await list.locator( 'a[title="page 2"]' ).first().click();
		await expect( page ).toHaveURL( /pageID=2/ );
		await expect( list.locator( '.et_content_row' ).first() ).toBeVisible();

		await visit( page, `/et-veranstalter/?region=${ REGION }` );
		await list.locator( '.et_link_title' ).first().click();
		await expect( page ).toHaveURL( /etID=\d+/ );
		await expect( list.locator( '#et_detail_title' ) ).toHaveText( firstTitle.trim() );
	} );

	test( 'et_veranstalter search', async ( { page } ) => {
		await visit( page, `/et-veranstalter/?region=${ REGION }&et_q=Gottesdienst&action=search` );
		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		await expect( list.locator( '#et_totalItems' ) ).toHaveText( /^\d+$/ );
	} );

	// The teaser pages have a fixed region 407 in their shortcodes, which only fits the default host.
	test( 'et_teaser, et_panels and the widget show events', async ( { page } ) => {
		test.skip( ! IS_DEFAULT_HOST, 'The shortcodes of the test pages have region 407 (default host only).' );
		await visit( page, '/et-teaser/' );
		await expect( page.locator( '.et_teaser .et_content_row' ).first() ).toBeVisible();
		await expect( page.locator( '.et_sidebar .et_content_row' ).first() ).toBeVisible();

		await visit( page, '/et-panels/' );
		await expect( page.locator( '.et_panel .thumbnail' ).first() ).toBeVisible();
	} );

	// The option "ethost" really decides which server the plugin asks (the mock cannot check this, see README).
	test( `the plugin queries the host from the option (${ ALT_HOST })`, async ( { page, site } ) => {
		await site.reset( { mode: 'live', options: { ethost: ALT_HOST, vid: 'all' } } );
		await visit( page, '/et-veranstalter-1474/' );

		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].host ).toBe( ALT_HOST );
		expect( requests[ 0 ].scheme ).toBe( 'https' );

		// Structure only: the host may run an adapted version of the software and has other data.
		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		await expect( list.locator( '#et_content_container' ) ).toHaveCount( 1 );
		// Not checked: an adapted host puts its own stylesheet link into the markup (termine.ekir.de: publicintegration.css).
	} );


	// All four endpoints of an adapted host (issue #87): list, detail, teaser and panels are fetched from ALT_HOST.
	test( `${ ALT_HOST } serves list, detail, teaser and panels`, async ( { page, site } ) => {
		await site.reset( { mode: 'live', options: { ethost: ALT_HOST, vid: 'all' } } );

		await visit( page, '/et-veranstalter/?region=' + REGION );
		const list = page.locator( '.et_veranstalter' );
		await expect( list ).not.toContainText( UNAVAILABLE );
		const link = list.locator( '.et_link_title' ).first();
		// A host may have no events in this region: then only the list request is checked.
		if ( await link.count() ) {
			await link.click();
			await expect( page ).toHaveURL( /etID=\d+/ );
			await expect( list.locator( '#et_detail_title' ) ).toBeVisible();
			const detail = await site.requests( page, { module: 'detail-php' } );
			expect( detail ).toHaveLength( 1 );
			expect( detail[ 0 ].host ).toBe( ALT_HOST );
		}

		// The shortcodes of these pages have region 407; the host answers, even with an empty list.
		await visit( page, '/et-teaser/' );
		await expect( page.locator( '.et_teaser' ) ).not.toContainText( UNAVAILABLE );
		const teaser = await site.requests( page, { module: 'teaser' } );
		expect( teaser.length ).toBeGreaterThan( 0 );
		expect( teaser.every( ( entry ) => ALT_HOST === entry.host ) ).toBe( true );

		await visit( page, '/et-panels/' );
		await expect( page.locator( '.et_panel, .et_panels' ).first() ).toBeAttached();
		const panels = await site.requests( page, { module: 'panels' } );
		expect( panels ).toHaveLength( 1 );
		expect( panels[ 0 ].host ).toBe( ALT_HOST );
	} );
} );
