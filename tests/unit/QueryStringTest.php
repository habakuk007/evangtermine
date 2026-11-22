<?php
/**
 * Tests for Evangtermine_Query::getQuerystring(), cleanParameterName() and isSafeHost().
 *
 * @package evangtermine
 */

class QueryStringTest extends Et_Test_Case {

	public function test_querystring_contains_defaults_and_options() {
		$query = $this->make_query( array( 'vid' => '952,988', 'region' => '7' ) );
		parse_str( $query->getQuerystring(), $params );

		$this->assertSame( '952,988', $params['vid'] );
		$this->assertSame( '7', $params['region'] );
		$this->assertSame( 'nocss', $params['css'] );
		$this->assertSame( 'utf8', $params['encoding'] );
		$this->assertSame( '20', $params['itemsPerPage'] );
		$this->assertSame( 'yes', $params['until'] );
		$this->assertArrayNotHasKey( 'tpl', $params );
	}

	public function test_teaser_template_is_added() {
		$query = $this->make_query( array(), ET_TEMPLATE_TEASER_WIDGET );
		parse_str( $query->getQuerystring(), $params );
		$this->assertSame( ET_TEMPLATE_TEASER_WIDGET, $params['tpl'] );
	}

	public function test_content_teaser_without_own_css_keeps_the_remote_css() {
		$query = $this->make_query( array(), ET_TEMPLATE_TEASER_SHORTCODE );
		parse_str( $query->getQuerystring(), $params );
		$this->assertArrayNotHasKey( 'css', $params );
	}

	public function test_content_teaser_with_own_css_sends_nocss() {
		$query = $this->make_query( array( 'css' => 'https://example.test/my.css' ), ET_TEMPLATE_TEASER_SHORTCODE );
		parse_str( $query->getQuerystring(), $params );
		$this->assertSame( 'nocss', $params['css'] );
	}

	public function test_values_are_url_encoded_and_space_becomes_plus() {
		$query = $this->make_query();
		$query->setEtParameter( 'q', 'Konzert & Kaffee=ä' );
		$this->assertStringContainsString( 'q=Konzert+%26+Kaffee%3D%C3%A4', $query->getQuerystring() );
	}

	public function test_et_q_is_renamed_to_q() {
		$query = $this->make_query();
		$query->setEtParameter( 'et_q', 'suche' );
		$this->assertSame( 'suche', $query->getEtParameter( 'q' ) );
		$this->assertSame( '', $query->getEtParameter( 'et_q' ) );
	}

	public function test_title_and_non_scalar_values_are_skipped() {
		$query = $this->make_query();
		$query->setEtParameters(
			array(
				'title'  => 'Widget title',
				'nested' => array( 'a' => 'b' ),
				'object' => new stdClass(),
			)
		);
		$string = $query->getQuerystring();
		$this->assertStringNotContainsString( 'title', $string );
		$this->assertStringNotContainsString( 'nested', $string );
		$this->assertStringNotContainsString( 'object', $string );
	}

	public function test_parameter_names_are_cleaned() {
		$query = $this->make_query();
		$query->setEtParameters(
			array(
				'foo&evil=1#' => 'x',
				'<>'          => 'dropped',
			)
		);
		parse_str( $query->getQuerystring(), $params );
		$this->assertSame( 'x', $params['fooevil1'] ?? null );
		$this->assertArrayNotHasKey( 'evil', $params );
		$this->assertStringNotContainsString( 'dropped', $query->getQuerystring() );
	}

	public function test_additional_parameters_keep_only_url_safe_characters() {
		$query = $this->make_query();
		$query->setEtParameters( array( 'additionalParameters' => '&&foo=1&bar=a b<script>"\'' ) );
		$string = $query->getQuerystring();
		$this->assertStringEndsWith( '&foo=1&bar=abscript', $string );
		$this->assertStringNotContainsString( '&&', $string );
		$this->assertStringNotContainsString( '<', $string );
		$this->assertStringNotContainsString( ' ', $string );
	}

	public function test_empty_additional_parameters_add_nothing() {
		$query = $this->make_query();
		$query->setEtParameters( array( 'additionalParameters' => '<>"' ) );
		$this->assertStringNotContainsString( 'additionalParameters', $query->getQuerystring() );
		$this->assertStringEndsNotWith( '&', $query->getQuerystring() );
	}

	public function test_itemsperpage_is_normalised() {
		$query = $this->make_query();
		$query->setEtParameters( array( 'itemsperpage' => '5' ) );
		parse_str( $query->getQuerystring(), $params );
		$this->assertSame( '5', $params['itemsPerPage'] );
		$this->assertArrayNotHasKey( 'itemsperpage', $params );
	}

	public function test_empty_values_are_not_set() {
		$query = $this->make_query();
		$query->setEtParameter( 'place', '' );
		$query->setEtParameter( '', 'x' );
		$this->assertSame( 'all', $query->getEtParameter( 'place' ) );
	}

	/**
	 * @dataProvider parameter_names
	 */
	public function test_clean_parameter_name( $input, $expected ) {
		$this->assertSame( $expected, Evangtermine_Query::cleanParameterName( $input ) );
	}

	public static function parameter_names() {
		return array(
			'plain'              => array( 'itemsPerPage', 'itemsPerPage' ),
			'allowed specials'   => array( 'a_b.c-d1', 'a_b.c-d1' ),
			'brackets and space' => array( 'a[0] b', 'a0b' ),
			'markup'             => array( '<script>x</script>', 'scriptxscript' ),
			'null'               => array( null, '' ),
			'int'                => array( 42, '42' ),
			'only specials'      => array( '&=#', '' ),
		);
	}

	/**
	 * @dataProvider hosts
	 */
	public function test_is_safe_host( $host, $expected ) {
		$this->assertSame( $expected, Evangtermine_Query::isSafeHost( $host ) );
	}

	public static function hosts() {
		return array(
			'public ip'         => array( '8.8.8.8', true ),
			'public ip, port'   => array( '8.8.8.8:8443', true ),
			'empty'             => array( '', false ),
			'blank'             => array( '   ', false ),
			'localhost'         => array( 'localhost', false ),
			'localhost upper'   => array( 'LOCALHOST', false ),
			'localhost, port'   => array( 'localhost:8080', false ),
			'sub.localhost'     => array( 'et.localhost', false ),
			'loopback'          => array( '127.0.0.1', false ),
			'private 10/8'      => array( '10.0.0.5', false ),
			'private 192.168'   => array( '192.168.1.1', false ),
			'private 172.16'    => array( '172.16.0.1', false ),
			'link-local, cloud' => array( '169.254.169.254', false ),
			'unspecified'       => array( '0.0.0.0', false ),
		);
	}
}
