// Cache of the teaser output (et_teaser and the widget) and the default CSS of et_teaser.
const { test, expect, visit } = require( '../helpers' );

const UNAVAILABLE = 'Der Veranstaltungskalender ist derzeit nicht verfügbar.';
const teaser = { module: 'teaser', tpl: '1' };
const widget = { module: 'teaser', tpl: '2' };

test.describe( 'teaser and widget cache', () => {
	test( 'the second request of the same page does not ask the calendar host again', async ( { page, site } ) => {
		await visit( page, '/et-teaser/' );
		await expect( page.locator( '.et_teaser .et_content_row' ) ).toHaveCount( 5 );
		expect( await site.requests( page, teaser ) ).toHaveLength( 1 );
		expect( await site.requests( page, widget ) ).toHaveLength( 1 );

		await visit( page, '/et-teaser/' );
		// The cached output is the same.
		await expect( page.locator( '.et_teaser .et_content_row' ) ).toHaveCount( 5 );
		await expect( page.locator( '.et_sidebar .et_content_row' ) ).toHaveCount( 3 );
		expect( await site.requests( page, teaser ) ).toHaveLength( 1 );
		expect( await site.requests( page, widget ) ).toHaveLength( 1 );
	} );

	test( 'another organiser (vid) and another page are not served from the cache', async ( { page, site } ) => {
		await visit( page, '/et-teaser/' );
		await visit( page, '/et-teaser-other/' );

		const teaserRequests = await site.requests( page, teaser );
		expect( teaserRequests.map( ( request ) => request.query.vid ) ).toEqual( [ 'all', '952' ] );
		// The widget is the same on both pages, but the cache is kept per page (the links depend on the page).
		expect( await site.requests( page, widget ) ).toHaveLength( 2 );

		await visit( page, '/et-teaser-other/' );
		expect( await site.requests( page, teaser ) ).toHaveLength( 2 );
		expect( await site.requests( page, widget ) ).toHaveLength( 2 );
	} );

	for ( const fail of [ 'error', 'http500' ] ) {
		test( `an error of the calendar host is not cached (${ fail })`, async ( { page, site } ) => {
			await site.reset( { fail } );
			await visit( page, '/et-teaser/' );
			await expect( page.locator( '.et_teaser' ) ).toHaveText( UNAVAILABLE );
			await expect( page.locator( '.et_sidebar' ) ).toHaveText( UNAVAILABLE );

			await site.config( { fail: '' } );
			await visit( page, '/et-teaser/' );
			await expect( page.locator( '.et_teaser .et_content_row' ) ).toHaveCount( 5 );
			await expect( page.locator( '.et_sidebar .et_content_row' ) ).toHaveCount( 3 );
			expect( await site.requests( page, teaser ) ).toHaveLength( 2 );
			expect( await site.requests( page, widget ) ).toHaveLength( 2 );
		} );
	}

	test( 'the filter evangtermine_cache_ttl with 0 switches the cache off', async ( { page, site } ) => {
		await site.reset( { ttl: 0 } );
		await visit( page, '/et-teaser/' );
		await visit( page, '/et-teaser/' );

		expect( await site.requests( page, teaser ) ).toHaveLength( 2 );
		expect( await site.requests( page, widget ) ).toHaveLength( 2 );
	} );

	for ( const [ name, url, module ] of [
		[ 'et_veranstalter (list)', '/et-veranstalter/', 'veranstaltungen-php' ],
		[ 'et_veranstalter (detail view)', '/et-veranstalter/?etID=1001', 'detail-php' ],
		[ 'et_panels', '/et-panels/', 'panels' ],
	] ) {
		test( `${ name } is never cached`, async ( { page, site } ) => {
			await visit( page, url );
			await visit( page, url );
			expect( await site.requests( page, { module } ) ).toHaveLength( 2 );
		} );
	}

	test( 'et_veranstalter shows a message when the calendar host fails', async ( { page, site } ) => {
		await site.reset( { fail: 'http500' } );
		await visit( page, '/et-veranstalter/' );
		await expect( page.locator( '.et_veranstalter' ) ).toHaveText( UNAVAILABLE );
	} );
} );

test.describe( 'default CSS of et_teaser', () => {
	test( 'without a custom CSS file et_teaser gets the styles of the calendar host, the widget does not', async ( { page, site } ) => {
		await visit( page, '/et-teaser/' );

		// The plugin asks for the styles by leaving out "css=nocss".
		const teaserRequests = await site.requests( page, teaser );
		expect( teaserRequests[ 0 ].query.css ).toBeUndefined();
		await expect( page.locator( '.et_teaser style' ) ).toHaveCount( 1 );
		expect( await page.locator( '.et_teaser style' ).textContent() ).toContain( '#et_container' );

		const widgetRequests = await site.requests( page, widget );
		expect( widgetRequests[ 0 ].query.css ).toBe( 'nocss' );
		await expect( page.locator( '.et_sidebar style' ) ).toHaveCount( 0 );
		await expect( page.locator( 'link[href="nocss"]' ) ).toHaveCount( 0 );
	} );

	test( 'with a custom CSS file et_teaser has no styles of the calendar host', async ( { page, site } ) => {
		await site.reset( { options: { css: 'https://example.org/my.css' } } );
		await visit( page, '/et-teaser/' );

		const teaserRequests = await site.requests( page, teaser );
		expect( teaserRequests[ 0 ].query.css ).toBe( 'nocss' );
		await expect( page.locator( '.et_teaser .et_content_row' ) ).toHaveCount( 5 );
		await expect( page.locator( '.et_teaser style' ) ).toHaveCount( 0 );
		await expect( page.locator( 'link[href="nocss"]' ) ).toHaveCount( 0 );
		await expect( page.locator( 'head link#evangtermine-custom-css' ) ).toHaveAttribute( 'href', 'https://example.org/my.css' );
	} );
} );
