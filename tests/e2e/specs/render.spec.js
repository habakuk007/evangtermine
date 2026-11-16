// The four shortcodes and the widget render the calendar and show no PHP messages.
const { test, expect, visit, PHP_MESSAGE_IN_PAGE } = require( '../helpers' );

const HOST = 'www.evangelische-termine.de';

test.describe( 'shortcodes and widget render', () => {
	test( 'et_veranstalter shows the list', async ( { page, site } ) => {
		await visit( page, '/et-veranstalter/' );

		const list = page.locator( '.et_veranstalter' );
		await expect( list.locator( '.et_content_row' ) ).toHaveCount( 5 );
		await expect( list.locator( '.et_link_title' ).first() ).toHaveText( 'Offener Kreis' );
		await expect( list.locator( '#et_filter_container form' ) ).toBeVisible();
		// The plugin removes the headline of the calendar and the broken "nocss" stylesheet link.
		await expect( list.locator( '#et_headline' ) ).toHaveCount( 0 );
		await expect( page.locator( 'link[href="nocss"]' ) ).toHaveCount( 0 );

		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].query ).toMatchObject( { vid: 'all', region: '407', itemsPerPage: '5' } );
	} );

	test( 'et_veranstalter uses the options when the shortcode has no attributes', async ( { page, site } ) => {
		await visit( page, '/et-veranstalter-1474/' );

		// The organiser 1474 has no events.
		await expect( page.locator( '.et_veranstalter .monthbar' ) ).toHaveText( 'Ihre Suche ergab leider keine Ergebnisse.' );
		const requests = await site.requests( page, { module: 'veranstaltungen-php' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].query.vid ).toBe( '1474' );
	} );

	test( 'et_veranstalter loads jQuery and the calendar script from WordPress, not from the calendar host', async ( { page } ) => {
		const errors = [];
		page.on( 'pageerror', ( error ) => errors.push( error.message ) );
		await visit( page, '/et-veranstalter/' );

		// The inline script of the calendar turns the date field into a date picker. This needs jQuery UI and "dateoptions".
		await expect( page.locator( '#date.hasDatepicker' ) ).toHaveCount( 1 );
		expect( errors ).toEqual( [] );
		expect( page.externalRequests.filter( ( url ) => /\.js(\?|$)/.test( url ) ) ).toEqual( [] );
	} );

	test( 'et_teaser shows the teaser', async ( { page, site } ) => {
		await visit( page, '/et-teaser/' );

		await expect( page.locator( '.et_teaser .et_content_row' ) ).toHaveCount( 5 );
		await expect( page.locator( '.et_teaser .et_link_title' ).first() ).toHaveText( 'Offener Kreis' );
		expect( await site.requests( page, { module: 'teaser', tpl: '1' } ) ).toHaveLength( 1 );
	} );

	test( 'et_panels shows the panels and loads Bootstrap and Font Awesome from the plugin', async ( { page, site } ) => {
		await visit( page, '/et-panels/' );

		await expect( page.locator( '.et_panel .thumbnail' ) ).toHaveCount( 6 );
		expect( await site.requests( page, { module: 'panels' } ) ).toHaveLength( 1 );

		const assets = await page.locator( '.et_panel link[rel="stylesheet"], .et_panel script[src]' ).evaluateAll(
			( nodes ) => nodes.map( ( node ) => node.href || node.src )
		);
		expect( assets.filter( ( url ) => url.includes( '/assets/styles/bootstrap3.3.7.min.css' ) ) ).toHaveLength( 1 );
		expect( assets.filter( ( url ) => url.includes( '/assets/font-awesome-4.7.0/css/font-awesome.min.css' ) ) ).toHaveLength( 1 );
		expect( assets.filter( ( url ) => url.includes( '/assets/scripts/bootstrap3.3.7.min.js' ) ) ).toHaveLength( 1 );
		// Everything comes from this WordPress site (plugin folder or wp-includes), nothing from the calendar host.
		for ( const url of assets ) {
			expect( url ).toMatch( /\/wp-(content\/plugins|includes)\// );
		}
	} );

	test( 'et_minical prints the container and the data for the script', async ( { page } ) => {
		const errors = [];
		page.on( 'pageerror', ( error ) => errors.push( error.message ) );
		await visit( page, '/et-minical/' );

		await expect( page.locator( '#et_minicalendar' ) ).toHaveCount( 1 );
		const data = await page.evaluate( () => window.etMinicalData );
		expect( data.baseUrl ).toBe( `https://${ HOST }` );
		expect( data.parameters ).toMatchObject( { vid: 'all', region: '407' } );
		expect( errors ).toEqual( [] );
	} );

	test( 'the widget shows its title and the teaser in the sidebar', async ( { page, site } ) => {
		await visit( page, '/et-veranstalter-1474/' );

		const widget = page.locator( '.widget-area .et_sidebar' );
		await expect( widget.locator( '.et_content_row' ) ).toHaveCount( 3 );
		await expect( page.locator( '.widget-area' ) ).toContainText( 'Nächste Veranstaltungen' );

		const requests = await site.requests( page, { module: 'teaser', tpl: '2' } );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].query ).toMatchObject( { vid: 'all', region: '407', itemsPerPage: '3', css: 'nocss' } );
		// The title of the widget is not sent to the calendar host.
		expect( requests[ 0 ].query.title ).toBeUndefined();
	} );

	test( 'teaser and list work on the same page', async ( { page } ) => {
		await visit( page, '/et-all/' );

		await expect( page.locator( '.et_teaser .et_content_row' ) ).toHaveCount( 5 );
		await expect( page.locator( '.et_veranstalter .et_content_row' ) ).toHaveCount( 5 );
	} );
} );

test.describe( 'widget settings', () => {
	test( 'the form shows the saved values, escaped', async ( { page, request, site } ) => {
		const instance = {
			vid: 'all" autofocus="',
			region: '407',
			itemsPerPage: '3',
			additionalParameters: 'foo=1&bar=2',
		};
		const response = await request.post( '/?et_e2e=widget-form', { data: instance } );
		expect( response.ok() ).toBeTruthy();
		const html = await response.text();
		expect( html ).not.toMatch( PHP_MESSAGE_IN_PAGE );

		await page.setContent( html );
		const field = ( name ) => page.locator( `[name="widget-etwidget[2][${ name }]"]` );
		await expect( field( 'vid' ) ).toHaveValue( 'all" autofocus="' );
		await expect( field( 'vid' ) ).not.toHaveAttribute( 'autofocus' );
		await expect( field( 'region' ) ).toHaveValue( '407' );
		await expect( field( 'itemsPerPage' ) ).toHaveValue( '3' );
		await expect( field( 'additionalParameters' ) ).toHaveValue( 'foo=1&bar=2' );
		await expect( field( 'highlight' ) ).toHaveValue( 'all' );
		expect( await site.phpMessages() ).toEqual( [] );
	} );

	test( 'the form shows the saved title', async ( { page, request } ) => {
		// Known bug in the plugin (not fixed here): EtWidget::form() always replaces the saved title
		// with "Nächste Veranstaltungen". Remove test.fail() when the bug is fixed.
		test.fail();

		const response = await request.post( '/?et_e2e=widget-form', { data: { title: 'Termine "heute" <b>fett</b>' } } );
		await page.setContent( await response.text() );
		await expect( page.locator( '[name="widget-etwidget[2][title]"]' ) ).toHaveValue( 'Termine "heute" <b>fett</b>', { timeout: 2000 } );
	} );

	test( 'the form of a new widget shows no PHP messages', async ( { request } ) => {
		const response = await request.post( '/?et_e2e=widget-form', { data: {} } );
		expect( response.ok() ).toBeTruthy();
		expect( await response.text() ).not.toMatch( PHP_MESSAGE_IN_PAGE );
	} );

	test( 'saving keeps only known fields and cleans the values', async ( { site } ) => {
		const saved = await site.call( 'widget-update', {
			new: {
				title: 'Termine <script>alert(1)</script>',
				vid: 'all',
				highlight: 'bogus',
				dest: 'intern',
				additionalParameters: 'foo=1&bar="><script>',
				tpl: '9',
				unknown: 'x',
			},
			old: {},
		} );

		expect( saved.title ).toBe( 'Termine' );
		expect( saved.vid ).toBe( 'all' );
		expect( saved.highlight ).toBe( 'all' );
		expect( saved.dest ).toBe( 'intern' );
		expect( saved.additionalParameters ).toBe( 'foo=1&bar=script' );
		expect( String( saved.tpl ) ).toBe( '2' );
		expect( saved.unknown ).toBeUndefined();
	} );

	test( 'saving with missing fields shows no PHP messages', async ( { site } ) => {
		const saved = await site.call( 'widget-update', { new: {}, old: {} } );
		expect( saved ).toMatchObject( { highlight: 'all', auswahl: '', dest: 'extern', until: 'yes' } );
	} );
} );
