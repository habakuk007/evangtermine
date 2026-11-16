// Options page (Settings > Evangelische Termine) and the output of the "css" option.
const { test, expect, visit } = require( '../helpers' );

const OPTIONS_PAGE = '/wp-admin/options-general.php?page=options.php';
const HOST = 'www.evangelische-termine.de';

async function save( page ) {
	await page.locator( '#submit' ).click();
	await expect( page.locator( '#setting-error-settings_updated, .notice-error, .settings-error' ).first() ).toBeVisible();
}

test.describe( 'options page', () => {
	test( 'shows all fields without PHP messages', async ( { page } ) => {
		await visit( page, OPTIONS_PAGE );

		for ( const name of [ 'vid', 'region', 'until', 'css', 'encoding', 'etprotocol', 'ethost' ] ) {
			await expect( page.locator( `form [name="${ name }"]` ) ).toHaveCount( 1 );
		}
		await expect( page.locator( 'input[name="vid"]' ) ).toHaveValue( '1474' );
	} );

	test( 'values with quotes and HTML are cleaned when saved and escaped when shown', async ( { page } ) => {
		await visit( page, OPTIONS_PAGE );
		await page.locator( 'input[name="vid"]' ).fill( '12" onfocus="window.__xss=1" autofocus="' );
		await page.locator( 'input[name="region"]' ).fill( '407"><script>window.__xss=1</script>' );
		await page.locator( 'input[name="css"]' ).fill( 'https://example.org/my.css?a=1&b="><script>window.__xss=1</script>' );
		await save( page );
		await visit( page, OPTIONS_PAGE );

		// The quote stays text inside the value attribute.
		await expect( page.locator( 'input[name="vid"]' ) ).toHaveValue( '12" onfocus="window.__xss=1" autofocus="' );
		await expect( page.locator( 'input[name="vid"]' ) ).not.toHaveAttribute( 'onfocus' );
		await expect( page.locator( 'input[name="region"]' ) ).toHaveValue( '407">' );
		const css = await page.locator( 'input[name="css"]' ).inputValue();
		expect( css ).toMatch( /^https:\/\/example\.org\/my\.css\?a=1&b=/ );
		expect( css ).not.toMatch( /[<>"]/ );
		expect( await page.evaluate( () => typeof window.__xss ) ).toBe( 'undefined' );
	} );

	test( 'select fields accept only their own values', async ( { page } ) => {
		await visit( page, OPTIONS_PAGE );
		// Add a value that the form does not offer.
		await page.locator( 'select[name="etprotocol"]' ).evaluate( ( select ) => {
			select.add( new Option( 'ftp://', 'ftp://' ) );
			select.value = 'ftp://';
		} );
		await page.locator( 'select[name="until"]' ).selectOption( 'no' );
		await save( page );
		await visit( page, OPTIONS_PAGE );

		await expect( page.locator( 'select[name="etprotocol"]' ) ).toHaveValue( '' );
		await expect( page.locator( 'select[name="until"]' ) ).toHaveValue( 'no' );
	} );

	test( 'a normal host name is accepted', async ( { page } ) => {
		await visit( page, OPTIONS_PAGE );
		await page.locator( 'input[name="ethost"]' ).fill( ` ${ HOST.toUpperCase() } ` );
		await save( page );

		await expect( page.locator( '.notice-error, .settings-error.error' ) ).toHaveCount( 0 );
		await expect( page.locator( 'input[name="ethost"]' ) ).toHaveValue( HOST );
	} );

	for ( const host of [ 'localhost', '169.254.169.254', 'http://x/', '10.0.0.1' ] ) {
		test( `the host "${ host }" is rejected and the old value is kept`, async ( { page, site } ) => {
			await site.reset( { options: { ethost: HOST } } );
			await visit( page, OPTIONS_PAGE );
			await expect( page.locator( 'input[name="ethost"]' ) ).toHaveValue( HOST );

			await page.locator( 'input[name="ethost"]' ).fill( host );
			await save( page );

			await expect( page.locator( '.notice-error, .settings-error.error' ).first() ).toContainText( 'Der bisherige Wert wurde beibehalten' );
			await expect( page.locator( 'input[name="ethost"]' ) ).toHaveValue( HOST );
		} );
	}
} );

test.describe( 'css option in the page head', () => {
	test( 'the stylesheet link is escaped', async ( { page, site } ) => {
		// Written directly into the database, without the cleaning of the options page.
		await site.reset( { options: { css: 'https://example.org/my.css?a=1&b="><script>window.__xss=1</script><link rel="x' } } );
		await visit( page, '/et-veranstalter-1474/' );

		expect( await page.evaluate( () => typeof window.__xss ) ).toBe( 'undefined' );
		const link = page.locator( 'head link#evangtermine-custom-css' );
		await expect( link ).toHaveCount( 1 );
		const href = await link.getAttribute( 'href' );
		expect( href ).toMatch( /^https:\/\/example\.org\/my\.css\?a=1&b=/ );
		expect( href ).not.toMatch( /[<>"]/ );
		await expect( page.locator( 'link[rel="x"]' ) ).toHaveCount( 0 );
	} );

	test( 'no stylesheet link without the option and with "nocss"', async ( { page, site } ) => {
		await visit( page, '/et-veranstalter-1474/' );
		await expect( page.locator( 'link#evangtermine-custom-css' ) ).toHaveCount( 0 );

		await site.reset( { options: { css: 'nocss' } } );
		await visit( page, '/et-veranstalter-1474/' );
		await expect( page.locator( 'link#evangtermine-custom-css' ) ).toHaveCount( 0 );
	} );
} );
