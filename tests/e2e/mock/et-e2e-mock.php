<?php
/**
 * Test helper for the end-to-end tests (tests/e2e). It is loaded as a must-use plugin by the Playground blueprint
 * and is never part of a release (the folder "tests" is listed in .distignore).
 *
 * What it does:
 * - It answers the requests of the plugin to the calendar host with the HTML files in tests/e2e/fixtures
 *   (filter "pre_http_request"), so the tests do not need www.evangelische-termine.de.
 * - It records every request to the calendar host (URL, headers, cookies), so the tests can check what was sent.
 * - It offers a small control interface for the tests: /?et_e2e=<action>
 *
 * The mock is switched off by default ("live" mode: requests go to the real host). The tests switch it on.
 * Do not use this file on a real website: the control interface has no access check.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ET_E2E_CONFIG', 'et_e2e_config' );
define( 'ET_E2E_LOG', 'et_e2e_log' );
define( 'ET_E2E_FIXTURES', dirname( __DIR__ ) . '/fixtures/' );

/**
 * Current settings of the mock.
 *
 * - mode:    "live" (real host) or "mock" (fixtures)
 * - fail:    "" (normal answer), "error" (connection error) or "http500" (HTTP status 500)
 * - ttl:     null (default of the plugin) or a number of seconds for the filter "evangtermine_cache_ttl"
 * - session: true starts a PHP session on every request
 *
 * @return array
 */
function et_e2e_config() {
	$config = get_option( ET_E2E_CONFIG );
	return array_merge(
		array(
			'mode'    => 'live',
			'fail'    => '',
			'ttl'     => null,
			'session' => false,
		),
		is_array( $config ) ? $config : array()
	);
}

/**
 * Reads a fixture file.
 *
 * @param string $name File name without the extension.
 * @return string
 */
function et_e2e_fixture( $name ) {
	$file = ET_E2E_FIXTURES . $name . '.html';
	return is_readable( $file ) ? (string) file_get_contents( $file ) : '';
}

/**
 * Picks the answer of the calendar host for one request.
 *
 * @param string $module Requested module (veranstaltungen-php, detail-php, teaser, panels).
 * @param array  $query  Query parameters of the request.
 * @return string
 */
function et_e2e_response_body( $module, $query ) {
	$get = function ( $key ) use ( $query ) {
		return isset( $query[ $key ] ) && is_scalar( $query[ $key ] ) ? (string) $query[ $key ] : '';
	};

	if ( 'detail-php' === $module ) {
		return '1001' === $get( 'ID' ) ? et_e2e_fixture( 'detail' ) : et_e2e_fixture( 'detail-error' );
	}

	if ( 'teaser' === $module ) {
		if ( '2' === $get( 'tpl' ) ) {
			return et_e2e_fixture( 'teaser-tpl2' );
		}
		return 'nocss' === $get( 'css' ) ? et_e2e_fixture( 'teaser-tpl1-nocss' ) : et_e2e_fixture( 'teaser-tpl1-css' );
	}

	if ( 'panels' === $module ) {
		return et_e2e_fixture( 'panels' );
	}

	// veranstaltungen-php: the organiser 1474 has no events, a search has its own result, page 2 has other events.
	$search = $get( 'q' );
	if ( '1474' === $get( 'vid' ) || false !== strpos( $search, 'nichts' ) ) {
		$body = et_e2e_fixture( 'list-empty' );
	} elseif ( '' !== $search ) {
		$body = et_e2e_fixture( 'list-search' );
	} elseif ( (int) $get( 'pageID' ) > 1 ) {
		$body = et_e2e_fixture( 'list-page2' );
	} else {
		$body = et_e2e_fixture( 'list-page1' );
	}

	// Like the real host, the links repeat the search term.
	$suffix = '' !== $search ? '&amp;q=' . esc_attr( $search ) : '';
	return str_replace( '{{LINK_SUFFIX}}', $suffix, $body );
}

/**
 * Records the requests to the calendar host and answers them in "mock" mode.
 *
 * @param false|array|WP_Error $pre  Short-circuit value.
 * @param array                $args Request arguments.
 * @param string               $url  Request URL.
 * @return false|array|WP_Error
 */
function et_e2e_pre_http_request( $pre, $args, $url ) {
	$parts  = wp_parse_url( $url );
	$module = isset( $parts['path'] ) ? ltrim( $parts['path'], '/' ) : '';
	if ( ! in_array( $module, array( 'veranstaltungen-php', 'detail-php', 'teaser', 'panels' ), true ) ) {
		return $pre;
	}

	$query = array();
	if ( isset( $parts['query'] ) ) {
		parse_str( $parts['query'], $query );
	}

	$log   = get_option( ET_E2E_LOG );
	$log   = is_array( $log ) ? $log : array();
	$log[] = array(
		'url'                => $url,
		'scheme'             => isset( $parts['scheme'] ) ? $parts['scheme'] : '',
		'host'               => isset( $parts['host'] ) ? $parts['host'] : '',
		'module'             => $module,
		'query'              => $query,
		'headers'            => isset( $args['headers'] ) ? (array) $args['headers'] : array(),
		'cookies'            => isset( $args['cookies'] ) ? count( (array) $args['cookies'] ) : 0,
		'user_agent'         => isset( $args['user-agent'] ) ? (string) $args['user-agent'] : '',
		'timeout'            => isset( $args['timeout'] ) ? $args['timeout'] : null,
		'reject_unsafe_urls' => ! empty( $args['reject_unsafe_urls'] ),
	);
	update_option( ET_E2E_LOG, $log, false );

	$config = et_e2e_config();
	if ( 'mock' !== $config['mode'] ) {
		return $pre;
	}
	if ( 'error' === $config['fail'] ) {
		return new WP_Error( 'http_request_failed', 'E2E mock: simulated connection error' );
	}

	$status = 'http500' === $config['fail'] ? 500 : 200;
	return array(
		'headers'  => array( 'content-type' => 'text/html; charset=UTF-8' ),
		'body'     => 200 === $status ? et_e2e_response_body( $module, $query ) : 'Internal Server Error',
		'response' => array(
			'code'    => $status,
			'message' => 200 === $status ? 'OK' : 'Internal Server Error',
		),
		'cookies'  => array(),
		'filename' => null,
	);
}
add_filter( 'pre_http_request', 'et_e2e_pre_http_request', 10, 3 );

/**
 * Cache lifetime from the settings of the mock.
 *
 * @param int $ttl Lifetime in seconds.
 * @return int
 */
function et_e2e_cache_ttl( $ttl ) {
	$config = et_e2e_config();
	return null === $config['ttl'] ? $ttl : (int) $config['ttl'];
}
add_filter( 'evangtermine_cache_ttl', 'et_e2e_cache_ttl' );

/**
 * Starts a PHP session if the tests ask for it (to check that the plugin works with and without a session).
 */
function et_e2e_maybe_start_session() {
	$config = et_e2e_config();
	if ( $config['session'] && PHP_SESSION_NONE === session_status() && ! headers_sent() ) {
		// The default folder for session files does not exist in WordPress Playground.
		if ( ! is_dir( (string) session_save_path() ) ) {
			session_save_path( get_temp_dir() );
		}
		session_start();
	}
}
add_action( 'init', 'et_e2e_maybe_start_session', 1 );

/**
 * Deletes the cached teaser output of the plugin (transients "et_stream_*").
 */
function et_e2e_clear_cache() {
	global $wpdb;
	$names = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%transient%et%stream%'" );
	foreach ( $names as $name ) {
		delete_option( $name );
	}
	wp_cache_flush();
}

/**
 * Writes plugin options directly into the database (without the cleaning of the options page).
 *
 * @param array $options Option names and values.
 */
function et_e2e_set_options( $options ) {
	foreach ( $options as $name => $value ) {
		if ( in_array( $name, array( 'vid', 'region', 'until', 'css', 'encoding', 'etprotocol', 'ethost' ), true ) ) {
			update_option( $name, (string) $value );
		}
	}
}

/**
 * What happened since the last reset: the requests to the calendar host and the content of wp-content/debug.log.
 *
 * @return array
 */
function et_e2e_state() {
	$log  = get_option( ET_E2E_LOG );
	$file = WP_CONTENT_DIR . '/debug.log';
	return array(
		'requests' => is_array( $log ) ? $log : array(),
		'debugLog' => file_exists( $file ) ? (string) file_get_contents( $file ) : '',
	);
}

/**
 * Prints the state at the end of every page, so the tests can read it without another request.
 * Only after the tests have called "reset" at least once.
 */
function et_e2e_print_state() {
	if ( false === get_option( ET_E2E_CONFIG ) ) {
		return;
	}
	echo '<script type="application/json" id="et-e2e-state">' . wp_json_encode( et_e2e_state(), JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>';
}
add_action( 'wp_footer', 'et_e2e_print_state', 9999 );
add_action( 'admin_footer', 'et_e2e_print_state', 9999 );

/**
 * Sends a JSON answer and stops.
 *
 * @param mixed $data Data to send.
 */
function et_e2e_send( $data ) {
	nocache_headers();
	header( 'Content-Type: application/json; charset=UTF-8' );
	echo wp_json_encode( $data );
	exit;
}

/**
 * Control interface for the tests: /?et_e2e=<action>
 */
function et_e2e_control() {
	if ( ! isset( $_GET['et_e2e'] ) || ! is_string( $_GET['et_e2e'] ) ) {
		return;
	}
	$input = json_decode( (string) file_get_contents( 'php://input' ), true );
	$input = is_array( $input ) ? $input : array();

	switch ( $_GET['et_e2e'] ) {
		// Start state of every test: mock on, empty log, empty cache, default options, empty debug.log.
		case 'reset':
			$options = isset( $input['options'] ) && is_array( $input['options'] ) ? $input['options'] : array();
			unset( $input['options'] );
			update_option( ET_E2E_CONFIG, array_merge( array( 'mode' => 'mock' ), $input ), false );
			delete_option( ET_E2E_LOG );
			et_e2e_clear_cache();
			update_option( 'vid', '1474' );
			foreach ( array( 'region', 'until', 'css', 'encoding', 'etprotocol', 'ethost' ) as $name ) {
				delete_option( $name );
			}
			et_e2e_set_options( $options );
			if ( file_exists( WP_CONTENT_DIR . '/debug.log' ) ) {
				file_put_contents( WP_CONTENT_DIR . '/debug.log', '' );
			}
			et_e2e_send( et_e2e_config() );
			break;

		case 'config':
			update_option( ET_E2E_CONFIG, array_merge( et_e2e_config(), $input ), false );
			et_e2e_send( et_e2e_config() );
			break;

		case 'options':
			et_e2e_set_options( $input );
			et_e2e_send( true );
			break;

		case 'state':
			et_e2e_send( et_e2e_state() );
			break;

		case 'info':
			et_e2e_send(
				array(
					'php'     => PHP_VERSION,
					'wp'      => get_bloginfo( 'version' ),
					'plugin'  => defined( 'ET_VERSION' ) ? ET_VERSION : '',
					'session' => PHP_SESSION_ACTIVE === session_status(),
				)
			);
			break;

		// Settings form of the widget, as wp-admin shows it.
		case 'widget-form':
			$widget = new EtWidget();
			$widget->_set( 2 );
			nocache_headers();
			header( 'Content-Type: text/html; charset=UTF-8' );
			echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>EtWidget form</title></head><body><form id="et-widget-form">';
			$widget->form( $input );
			echo '</form></body></html>';
			exit;

		// Result of saving the widget settings: {"new": {...}, "old": {...}}.
		case 'widget-update':
			$widget = new EtWidget();
			et_e2e_send(
				$widget->update(
					isset( $input['new'] ) ? $input['new'] : array(),
					isset( $input['old'] ) ? $input['old'] : array()
				)
			);
			break;

		default:
			status_header( 400 );
			et_e2e_send( array( 'error' => 'unknown action' ) );
	}
}
add_action( 'wp_loaded', 'et_e2e_control' );
