// Deleting the plugin removes its data (uninstall.php, issue #79).
//
// The helper calls uninstall_plugin() of WordPress, which runs uninstall.php exactly as "Delete" on the plugins page
// does. The plugin folder is mounted from the host in the test site, so the files are never really deleted.
const { test, expect } = require( '../helpers' );

test.describe( 'uninstall', () => {
	test( 'deleting the plugin removes the options, the widget settings and the cache', async ( { site } ) => {
		const { before, after } = await site.call( 'uninstall' );

		// The test data was really there ...
		expect( before.options ).toEqual(
			[ 'css', 'encoding', 'et_e2e_unrelated', 'ethost', 'etprotocol', 'region', 'until', 'vid', 'widget_etwidget' ]
		);
		expect( before.cache.length ).toBeGreaterThan( 0 );
		expect( before.unrelated.length ).toBeGreaterThan( 0 );

		// ... and is gone, but data of other plugins is not touched.
		expect( after.options ).toEqual( [ 'et_e2e_unrelated' ] );
		expect( after.cache ).toEqual( [] );
		expect( after.unrelated.length ).toBeGreaterThan( 0 );
	} );
} );
