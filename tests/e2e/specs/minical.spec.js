// [et_minical] and its same-origin proxy (issue #33, finding 2): the browser of a visitor only talks to the own site.
// The server fetches "json" and "detail-bt" from the calendar host (host and protocol from the settings).
const { test, expect, visit } = require( '../helpers' );

const HOST = 'www.evangelische-termine.de';
const ROUTE = '/?rest_route=/evangtermine/v1/minical/';

function url( route, params ) {
	return ROUTE + route + '&' + new URLSearchParams( params ).toString();
}

async function remoteRequests( site, module ) {
	const state = await site.call( 'state' );
	return state.requests.filter( ( entry ) => entry.module === module );
}

async function allRemoteRequests( site ) {
	return ( await site.call( 'state' ) ).requests;
}

test.describe( 'et_minical in the browser', () => {
	test( 'the calendar and the detail popup only use the own site', async ( { page, site, baseURL } ) => {
		const own = new URL( baseURL ).origin;
		const errors = [];
		page.on( 'pageerror', ( error ) => errors.push( error.message ) );
		const proxyRequests = [];
		page.on( 'request', ( request ) => {
			if ( request.url().startsWith( own ) && /minical/.test( request.url() ) ) {
				proxyRequests.push( request.url() );
			}
		} );

		await visit( page, '/et-minical/' );
		const days = page.locator( '#et_minicalendar .ui-datepicker-calendar td a' );
		await expect( days ).toHaveCount( 2 );

		// The data came through the proxy; the browser never tried to reach the calendar host.
		expect( proxyRequests.some( ( href ) => href.includes( 'minical/events' ) ) ).toBe( true );
		expect( page.externalRequests ).toEqual( [] );
		const requests = await remoteRequests( site, 'json' );
		expect( requests ).toHaveLength( 1 );
		expect( requests[ 0 ].host ).toBe( HOST );
		expect( requests[ 0 ].query ).toMatchObject( { vid: 'all', region: '407', d: '0' } );

		// The tooltip shows the events of the day; a click opens the detail view in the popup.
		await days.first().hover();
		const link = page.locator( '.ui-tooltip .et_minical_link' ).first();
		await expect( link ).toContainText( 'Mock-Event 1001' );
		await link.click();

		const iframe = page.locator( '#colorbox iframe' );
		await expect( iframe ).toHaveCount( 1 );
		const src = new URL( await iframe.getAttribute( 'src' ), own );
		expect( src.origin ).toBe( own );
		expect( src.href ).toContain( 'minical/detail' );
		expect( src.href ).toContain( 'ID=1001' );
		await expect( page.frameLocator( '#colorbox iframe' ).locator( '#et_detail_bt' ) ).toContainText( 'Mock-Detail' );

		// The detail page itself was fetched by the server. (Its stylesheet is not covered by the proxy and is blocked here.)
		expect( ( await remoteRequests( site, 'detail-bt' ) ).map( ( entry ) => entry.query.ID ) ).toEqual( [ '1001' ] );
		expect( page.externalRequests.filter( ( href ) => /\/(json|detail-bt)(\?|$)/.test( href ) ) ).toEqual( [] );
		expect( errors ).toEqual( [] );
	} );
} );

test.describe( 'minical proxy', () => {
	test( 'events: only allowlisted parameters are passed on, values are cleaned', async ( { request, site } ) => {
		const params = new URLSearchParams( {
			month: '11.26',
			d: '0',
			vid: '952',
			q: '<b>Konzert</b>\nabend',
			place: "St. Mary's",
			evil: '1',
			url: 'https://evil.example/x',
			ID: '5',
		} );
		params.append( 'region[]', '1' );
		params.append( 'itemsPerPage', 'y'.repeat( 500 ) );
		const response = await request.get( ROUTE + 'events&' + params.toString() );
		expect( response.status() ).toBe( 200 );
		expect( response.headers()[ 'content-type' ] ).toContain( 'application/json' );
		const data = await response.json();
		expect( data ).toHaveLength( 2 );
		expect( data[ 0 ].Veranstaltung._event_TITLE ).toBe( 'Mock-Event 1001' );

		const [ sent ] = await remoteRequests( site, 'json' );
		expect( sent.host ).toBe( HOST );
		expect( sent.query ).toEqual( {
			vid: '952',
			q: 'Konzert abend',
			place: "St. Mary's",
			itemsPerPage: 'y'.repeat( 200 ),
			d: '0',
			month: '11.26',
		} );
		expect( sent.url ).not.toContain( 'evil' );
	} );

	test( 'events: an invalid month or day is rejected without a request to the calendar host', async ( { request, site } ) => {
		for ( const query of [ '', 'month=abc', 'month=13.26', 'month=11.2026', 'month=11.26&d=x', 'month[]=11.26', 'month=11.26%0d%0aX:1' ] ) {
			const response = await request.get( ROUTE + 'events' + ( query ? '&' + query : '' ) );
			expect( response.status(), query ).toBe( 400 );
		}
		expect( await allRemoteRequests( site ) ).toEqual( [] );
	} );

	test( 'detail: returns the HTML of the event with a base address, in a sandbox', async ( { request, site } ) => {
		const response = await request.get( url( 'detail', { ID: '1001' } ) );
		expect( response.status() ).toBe( 200 );
		const headers = response.headers();
		expect( headers[ 'content-type' ] ).toContain( 'text/html' );
		expect( headers[ 'content-security-policy' ] ).toContain( 'sandbox' );
		expect( headers[ 'x-content-type-options' ] ).toBe( 'nosniff' );
		const body = await response.text();
		expect( body ).toContain( 'Mock-Detail der Veranstaltung' );
		expect( body ).toContain( `<base href="https://${ HOST }/">` );

		const [ sent ] = await remoteRequests( site, 'detail-bt' );
		expect( sent.query ).toEqual( { ID: '1001' } );
	} );

	test( 'detail: only a numeric ID is accepted', async ( { request, site } ) => {
		for ( const id of [ '', '0', 'abc', '12abc', '-1', '1e3', '1001/../x' ] ) {
			const response = await request.get( ROUTE + 'detail&ID=' + encodeURIComponent( id ) );
			expect( response.status(), id ).toBe( 400 );
		}
		expect( await allRemoteRequests( site ) ).toEqual( [] );
	} );

	test( 'the answers are cached for a short time', async ( { request, site } ) => {
		for ( let i = 0; i < 3; i++ ) {
			expect( ( await request.get( url( 'events', { month: '11.26', vid: '952' } ) ) ).status() ).toBe( 200 );
			expect( ( await request.get( url( 'detail', { ID: '1001' } ) ) ).status() ).toBe( 200 );
		}
		expect( await remoteRequests( site, 'json' ) ).toHaveLength( 1 );
		expect( await remoteRequests( site, 'detail-bt' ) ).toHaveLength( 1 );

		// Other parameters are another cache entry.
		await request.get( url( 'events', { month: '12.26', vid: '952' } ) );
		expect( await remoteRequests( site, 'json' ) ).toHaveLength( 2 );
	} );

	test.describe( 'without cache', () => {
		test.use( { setup: { ttl: 0 } } );

		test( 'every call asks the calendar host', async ( { request, site } ) => {
			await request.get( url( 'events', { month: '11.26' } ) );
			await request.get( url( 'events', { month: '11.26' } ) );
			expect( await remoteRequests( site, 'json' ) ).toHaveLength( 2 );
		} );
	} );

	test.describe( 'host from the settings', () => {
		test.use( { setup: { options: { ethost: 'calendar.example.org', etprotocol: 'http://' } } } );

		test( 'host and protocol of the settings are used', async ( { request, site } ) => {
			expect( ( await request.get( url( 'events', { month: '11.26' } ) ) ).status() ).toBe( 200 );
			expect( ( await request.get( url( 'detail', { ID: '1001' } ) ) ).status() ).toBe( 200 );
			const requests = await allRemoteRequests( site );
			expect( requests.map( ( entry ) => [ entry.scheme, entry.host, entry.module ] ) ).toEqual( [
				[ 'http', 'calendar.example.org', 'json' ],
				[ 'http', 'calendar.example.org', 'detail-bt' ],
			] );
			for ( const entry of requests ) {
				expect( entry.reject_unsafe_urls ).toBe( true );
				expect( entry.timeout ).toBeGreaterThan( 0 );
				expect( entry.timeout ).toBeLessThanOrEqual( 30 );
				expect( entry.user_agent ).toMatch( /^evangtermine\/[\d.]+ / );
				expect( entry.cookies ).toBe( 0 );
			}
		} );
	} );

	for ( const host of [ 'localhost', '169.254.169.254', '10.0.0.1', 'intern.localhost:8080' ] ) {
		test.describe( `unsafe host ${ host }`, () => {
			test.use( { setup: { options: { ethost: host } } } );

			test( 'is not requested', async ( { request, site } ) => {
				expect( ( await request.get( url( 'events', { month: '11.26' } ) ) ).status() ).toBe( 503 );
				expect( ( await request.get( url( 'detail', { ID: '1001' } ) ) ).status() ).toBe( 503 );
				expect( await allRemoteRequests( site ) ).toEqual( [] );
			} );
		} );
	}

	test.describe( 'errors of the calendar host', () => {
		test.use( { setup: { fail: 'http500' } } );

		test( 'are answered with 502 and not cached', async ( { request, site } ) => {
			expect( ( await request.get( url( 'events', { month: '11.26' } ) ) ).status() ).toBe( 502 );
			await site.config( { fail: '' } );
			expect( ( await request.get( url( 'events', { month: '11.26' } ) ) ).status() ).toBe( 200 );
		} );
	} );
} );
