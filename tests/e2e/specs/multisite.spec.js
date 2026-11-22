// Deleting the plugin in a multisite removes its data on every site of the network (uninstall.php, issue #79).
// Runs only in the project "multisite" (npm run test:e2e:multisite): the test site must be a network.
const { test, expect } = require( '../helpers' );

const OPTIONS = [ 'css', 'encoding', 'et_e2e_unrelated', 'ethost', 'etprotocol', 'region', 'until', 'vid', 'widget_etwidget' ];

test.describe( 'uninstall in a multisite', () => {
	test( 'deleting the plugin removes the data of all sites and keeps other data', async ( { site } ) => {
		const result = await site.call( 'uninstall-multisite' );
		expect( result.error ).toBeUndefined();
		expect( result.multisite, 'the test site is not a multisite' ).toBe( true );
		expect( result.newId ).not.toBe( result.mainId );

		for ( const id of [ result.mainId, result.newId ] ) {
			const before = result.before[ id ];
			expect( [ ...before.options ].sort(), `site ${ id } before` ).toEqual( OPTIONS );
			expect( before.cache.length ).toBeGreaterThan( 0 );
			expect( before.unrelated.length ).toBeGreaterThan( 0 );

			const after = result.after[ id ];
			expect( after.options, `site ${ id } after` ).toEqual( [ 'et_e2e_unrelated' ] );
			expect( after.cache ).toEqual( [] );
			expect( after.unrelated.length ).toBeGreaterThan( 0 );
		}
	} );
} );
