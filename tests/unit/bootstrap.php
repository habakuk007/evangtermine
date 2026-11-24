<?php
/**
 * PHPUnit bootstrap for the unit tests (issue #99).
 *
 * No WordPress is loaded. WordPress functions are mocked per test with Brain Monkey; the plugin classes are
 * loaded directly (not through evangtermine.php, which registers hooks).
 *
 * @package evangtermine
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// The plugin files exit when ABSPATH is not defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}
define( 'ET_VERSION', '0.0.0-test' );
define( 'ET_BASE_PATH', dirname( __DIR__, 2 ) );

require_once ET_BASE_PATH . '/application/configs/defaults.php';
require_once __DIR__ . '/stubs/class-wp-widget.php';
require_once ET_BASE_PATH . '/application/models/class.evangtermine_query.php';
require_once ET_BASE_PATH . '/application/controllers/class.etwidget.php';
require_once __DIR__ . '/class-et-test-case.php';
