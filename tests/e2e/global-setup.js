// Runs once before all tests: waits until WordPress Playground has finished the blueprint and the plugin is active.
// It also saves the cookies of the automatic login of Playground. Without them every test would start with
// two extra (slow) redirects.
const { request } = require( '@playwright/test' );

module.exports = async ( config ) => {
	const baseURL = config.projects[ 0 ].use.baseURL;
	const context = await request.newContext( { baseURL } );
	const deadline = Date.now() + 120000;
	let info = null;

	while ( Date.now() < deadline ) {
		try {
			const response = await context.post( '/?et_e2e=info', { data: {} } );
			if ( response.ok() ) {
				info = await response.json();
				if ( info.plugin ) {
					break;
				}
			}
		} catch ( error ) {
			// Not ready yet.
		}
		await new Promise( ( resolve ) => setTimeout( resolve, 2000 ) );
	}
	if ( ! info || ! info.plugin ) {
		await context.dispose();
		throw new Error( `The test site at ${ baseURL } is not ready: the plugin or the test helper (tests/e2e/mock) is not loaded.` );
	}
	await context.storageState( { path: config.projects[ 0 ].use.storageState } );
	await context.dispose();
	console.log( `Test site: WordPress ${ info.wp }, PHP ${ info.php }, Evangelische Termine ${ info.plugin }` );
};
