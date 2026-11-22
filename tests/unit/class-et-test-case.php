<?php
/**
 * Base class for the unit tests: sets up Brain Monkey and stand-ins for the WordPress functions the plugin uses.
 *
 * @package evangtermine
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

abstract class Et_Test_Case extends TestCase {

	/**
	 * Option values returned by the mocked get_option().
	 *
	 * @var array
	 */
	protected $options = array();

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->options = array();

		Functions\stubTranslationFunctions();
		Functions\when( 'get_option' )->alias(
			function ( $name, $default = false ) {
				return array_key_exists( $name, $this->options ) ? $this->options[ $name ] : $default;
			}
		);
		// Simplified stand-ins: enough to show that the plugin calls them and what it does with the result.
		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return trim( preg_replace( '/\s+/', ' ', strip_tags( (string) $value ) ) );
			}
		);
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'esc_attr' )->alias(
			function ( $value ) {
				return htmlspecialchars( (string) $value, ENT_QUOTES );
			}
		);
		Functions\when( 'plugins_url' )->alias(
			function ( $path ) {
				return 'https://example.test/wp-content/plugins/evangtermine/' . ltrim( $path, '/' );
			}
		);
		Functions\when( 'get_permalink' )->justReturn( 'https://example.test/termine/' );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Builds a query object with the given options.
	 *
	 * @param array       $options Option values (get_option).
	 * @param string|null $tpl     Template (teaser/widget), null for the event list.
	 * @return Evangtermine_Query
	 */
	protected function make_query( array $options = array(), $tpl = null ) {
		$this->options = $options;
		return new Evangtermine_Query( $tpl );
	}

	/**
	 * Calls a private method.
	 *
	 * @param object $object Object.
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 * @return mixed
	 */
	protected function call_private( $object, $method, array $args = array() ) {
		$reflection = new ReflectionMethod( $object, $method );
		if ( PHP_VERSION_ID < 80100 ) {
			$reflection->setAccessible( true );
		}
		return $reflection->invokeArgs( $object, $args );
	}
}
