<?php
/**
 * Tests for the evangtermine_sanitize_*() functions in application/forms/options.php.
 *
 * @package evangtermine
 */

use Brain\Monkey\Functions;

class OptionsSanitizeTest extends Et_Test_Case {

	protected function setUp(): void {
		parent::setUp();
		// options.php registers its hooks when it is loaded; add_action() is provided by Brain Monkey.
		require_once ET_BASE_PATH . '/application/forms/options.php';
	}

	/**
	 * @dataProvider choices
	 */
	public function test_choice_functions( $function, $input, $expected ) {
		$this->assertSame( $expected, $function( $input ) );
	}

	public static function choices() {
		return array(
			'until yes'           => array( 'evangtermine_sanitize_until', 'yes', 'yes' ),
			'until no'            => array( 'evangtermine_sanitize_until', 'no', 'no' ),
			'until invalid'       => array( 'evangtermine_sanitize_until', 'maybe', 'yes' ),
			'until case matters'  => array( 'evangtermine_sanitize_until', 'NO', 'yes' ),
			'until array'         => array( 'evangtermine_sanitize_until', array( 'no' ), 'yes' ),
			'until null'          => array( 'evangtermine_sanitize_until', null, 'yes' ),
			'encoding latin1'     => array( 'evangtermine_sanitize_encoding', 'latin1', 'latin1' ),
			'encoding invalid'    => array( 'evangtermine_sanitize_encoding', 'utf-8', 'utf8' ),
			'protocol http'       => array( 'evangtermine_sanitize_etprotocol', 'http://', 'http://' ),
			'protocol https'      => array( 'evangtermine_sanitize_etprotocol', 'https://', 'https://' ),
			'protocol empty'      => array( 'evangtermine_sanitize_etprotocol', '', '' ),
			'protocol javascript' => array( 'evangtermine_sanitize_etprotocol', 'javascript:', '' ),
			'protocol ftp'        => array( 'evangtermine_sanitize_etprotocol', 'ftp://', '' ),
		);
	}

	public function test_choice_compares_as_string_and_rejects_booleans() {
		$this->assertSame( '1', evangtermine_sanitize_choice( 1, array( '1' ), 'd' ) );
		$this->assertSame( 'd', evangtermine_sanitize_choice( 2, array( '1' ), 'd' ) );
		// true is scalar and becomes '1', which is not allowed here.
		$this->assertSame( 'd', evangtermine_sanitize_choice( true, array( 'a' ), 'd' ) );
	}

	/**
	 * @dataProvider css_values
	 */
	public function test_sanitize_css( $input, $expected ) {
		$this->assertSame( $expected, evangtermine_sanitize_css( $input ) );
	}

	public static function css_values() {
		return array(
			'empty'  => array( '', '' ),
			'blank'  => array( '   ', '' ),
			'nocss'  => array( ' nocss ', 'nocss' ),
			'url'    => array( ' https://example.test/a.css ', 'https://example.test/a.css' ),
			'markup' => array( '<b>https://example.test/a.css</b>', 'https://example.test/a.css' ),
		);
	}

	public function test_ethost_empty_is_allowed() {
		$this->assertSame( '', evangtermine_sanitize_ethost( '  ' ) );
	}

	public function test_ethost_valid_values_are_lowercased() {
		Functions\when( 'wp_http_validate_url' )->justReturn( true );
		$this->assertSame( 'www.evangelische-termine.de', evangtermine_sanitize_ethost( ' WWW.Evangelische-Termine.de ' ) );
		$this->assertSame( '8.8.8.8:8443', evangtermine_sanitize_ethost( '8.8.8.8:8443' ) );
	}

	/**
	 * @dataProvider invalid_hosts
	 */
	public function test_ethost_invalid_values_keep_the_previous_value( $input, $code ) {
		$this->options = array( 'ethost' => 'old.example' );
		Functions\when( 'wp_http_validate_url' )->justReturn( true );
		Functions\expect( 'add_settings_error' )->once()->with( 'ethost', $code, \Mockery::type( 'string' ) );
		$this->assertSame( 'old.example', evangtermine_sanitize_ethost( $input ) );
	}

	public static function invalid_hosts() {
		return array(
			'with protocol' => array( 'https://cal.example', 'et_ethost_invalid' ),
			'with path'     => array( 'cal.example/path', 'et_ethost_invalid' ),
			'with space'    => array( 'cal example', 'et_ethost_invalid' ),
			'leading dot'   => array( '.cal.example', 'et_ethost_invalid' ),
			'localhost'     => array( 'localhost', 'et_ethost_unsafe' ),
			'loopback ip'   => array( '127.0.0.1', 'et_ethost_unsafe' ),
			'private ip'    => array( '192.168.0.10:8080', 'et_ethost_unsafe' ),
			'metadata ip'   => array( '169.254.169.254', 'et_ethost_unsafe' ),
		);
	}

	public function test_ethost_rejected_by_wordpress_url_validation() {
		$this->options = array( 'ethost' => 'old.example' );
		Functions\when( 'wp_http_validate_url' )->justReturn( false );
		Functions\expect( 'add_settings_error' )->once()->with( 'ethost', 'et_ethost_unsafe', \Mockery::type( 'string' ) );
		$this->assertSame( 'old.example', evangtermine_sanitize_ethost( '8.8.8.8:25' ) );
	}
}
