// Shared helpers for the end-to-end tests.
const { test: base, expect } = require( '@playwright/test' );

// PHP messages as WordPress prints them into the page when WP_DEBUG_DISPLAY is on.
const PHP_MESSAGE_IN_PAGE = /<b>(Warning|Notice|Deprecated|Fatal error|Parse error)<\/b>:|Uncaught \w*(Error|Exception)|WordPress database error/;
// PHP messages in wp-content/debug.log.
const PHP_MESSAGE_IN_LOG = /PHP (Warning|Notice|Deprecated|Fatal error|Parse error)/;

function phpMessagesInLog( debugLog ) {
	return debugLog.split( '\n' ).filter( ( line ) => PHP_MESSAGE_IN_LOG.test( line ) );
}

function filterRequests( requests, filter ) {
	return requests.filter( ( entry ) =>
		Object.entries( filter ).every( ( [ key, value ] ) =>
			'module' === key ? entry.module === value : entry.query[ key ] === value
		)
	);
}

/**
 * Talks to the test helper inside WordPress (tests/e2e/mock/et-e2e-mock.php).
 *
 * A request to WordPress Playground is slow (it starts WordPress every time), so the helper prints its state
 * (requests to the calendar host, PHP messages) at the end of every page. The tests read it from the page.
 */
class Site {
	constructor( request ) {
		this.request = request;
	}

	async call( action, data ) {
		const response = await this.request.post( `/?et_e2e=${ action }`, { data: data || {} } );
		expect( response.ok(), `control action "${ action }" failed with status ${ response.status() }` ).toBeTruthy();
		return response.json();
	}

	/**
	 * Start state: mock on, empty request log, empty cache, default options, empty debug.log.
	 *
	 * @param {Object} setup Settings of the mock ({ mode, fail, ttl, session }) and plugin options
	 *                       ({ options: { vid, region, until, css, encoding, etprotocol, ethost } }).
	 *                       The options are written directly into the database.
	 */
	reset( setup ) {
		return this.call( 'reset', setup );
	}

	/** Change settings of the mock without a reset: { mode, fail, ttl, session }. */
	config( config ) {
		return this.call( 'config', config );
	}

	info() {
		return this.call( 'info' );
	}

	/**
	 * Requests that the plugin sent to the calendar host since the last reset, read from the current page.
	 * Also checks that the page and debug.log have no PHP messages.
	 * Optional filter: { module: 'teaser', tpl: '1' } (all other keys are compared with the query parameters).
	 */
	async requests( page, filter = {} ) {
		const state = await expectNoPhpMessages( page );
		return filterRequests( state.requests, filter );
	}

	/** Lines with PHP messages in wp-content/debug.log since the last reset (for tests without a page). */
	async phpMessages() {
		const state = await this.call( 'state' );
		return phpMessagesInLog( state.debugLog );
	}
}

const test = base.extend( {
	// Settings for the reset before the test, see Site.reset(). Set with test.use( { setup: { ... } } ).
	setup: [ {}, { option: true } ],

	// Every test starts from the same state, also the tests that do not use "site" (auto: true).
	site: [
		async ( { request, setup }, use ) => {
			const site = new Site( request );
			await site.reset( setup );
			await use( site );
		},
		{ auto: true },
	],

	// The browser must not load anything from other hosts: the tests have to work without the calendar host.
	// The blocked addresses are collected in page.externalRequests.
	page: async ( { page, baseURL }, use ) => {
		const origin = new URL( baseURL ).origin;
		page.externalRequests = [];
		await page.route(
			( url ) => /^https?:$/.test( url.protocol ) && url.origin !== origin,
			( route ) => {
				page.externalRequests.push( route.request().url() );
				return route.abort();
			}
		);
		await use( page );
	},
} );

/**
 * Checks that the current page shows no PHP message and that debug.log has none. Returns the state of the helper.
 */
async function expectNoPhpMessages( page ) {
	const html = await page.content();
	const match = html.match( PHP_MESSAGE_IN_PAGE );
	const excerpt = match ? html.substr( Math.max( 0, match.index - 100 ), 500 ) : '';
	expect( excerpt, 'PHP message in the page' ).toBe( '' );

	const json = await page.locator( '#et-e2e-state' ).textContent();
	const state = JSON.parse( json );
	expect( phpMessagesInLog( state.debugLog ), 'PHP messages in wp-content/debug.log' ).toEqual( [] );
	return state;
}

/**
 * Opens a page of the test site and checks that it shows no PHP message.
 */
async function visit( page, url ) {
	const response = await page.goto( url );
	expect( response.status(), `HTTP status of ${ url }` ).toBe( 200 );
	await expectNoPhpMessages( page );
	return response;
}

module.exports = { test, expect, visit, expectNoPhpMessages, PHP_MESSAGE_IN_PAGE };
