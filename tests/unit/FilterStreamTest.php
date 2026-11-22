<?php
/**
 * Tests for the replacements in Evangtermine_Query::filterStream() (private, called through reflection).
 *
 * @package evangtermine
 */

use Brain\Monkey\Functions;

class FilterStreamTest extends Et_Test_Case {

	private function filter( $html, array $options = array(), $load_css = true ) {
		$query = $this->make_query( array_merge( array( 'vid' => '1' ), $options ) );
		$query->setLoadCSS( $load_css );
		return $this->call_private( $query, 'filterStream', array( $html ) );
	}

	public function test_nocss_links_are_removed() {
		$html = 'a<link rel="stylesheet" type="text/css" href="nocss"  />b<link href="nocss" media="screen, projection" rel="stylesheet" type="text/css" />c';
		$this->assertSame( 'abc', $this->filter( $html ) );
	}

	/**
	 * @dataProvider bundled_assets
	 */
	public function test_bundles_are_loaded_from_the_plugin( $attribute, $remote, $local ) {
		foreach ( array( '', 'https://www.evangelische-termine.de', 'http://www.evangelische-termine.de', '//www.evangelische-termine.de' ) as $prefix ) {
			foreach ( array( '', '?v=123' ) as $version ) {
				$html = '<x ' . $attribute . '="' . $prefix . '/bundles/vket/' . $remote . $version . '">';
				$this->assertSame(
					'<x ' . $attribute . '="https://example.test/wp-content/plugins/evangtermine/assets/' . $local . '">',
					$this->filter( $html ),
					$html
				);
			}
		}
	}

	public static function bundled_assets() {
		return array(
			'bootstrap'       => array( 'href', 'css/panels/bootstrap3.3.7.min.css', 'styles/bootstrap3.3.7.min.css' ),
			'bootstrap theme' => array( 'href', 'css/panels/bootstrap-theme3.3.7.min.css', 'styles/bootstrap-theme3.3.7.min.css' ),
			'font awesome'    => array( 'href', 'css/font-awesome.min.css', 'font-awesome-4.7.0/css/font-awesome.min.css' ),
			'jquery ui css'   => array( 'href', 'css/jquery-ui.min.css', 'ui/1.12.1/themes/base/jquery-ui.css' ),
		);
	}

	public function test_bootstrap_javascript_is_removed() {
		$html = 'a<script src="/bundles/vket/css/panels/bootstrap3.3.7.min.js?v=1"></script>b';
		$this->assertSame( 'ab', $this->filter( $html ) );
	}

	public function test_remote_css_is_removed_only_when_own_css_is_used() {
		$tag = '<link rel="stylesheet" type="text/css" href="https://www.evangelische-termine.de/bundles/vket/css/publicintegration.css"  />';
		$this->assertSame( $tag, $this->filter( $tag, array(), true ) );
		$this->assertSame( '', $this->filter( $tag, array(), false ) );
	}

	public function test_uploads_get_the_calendar_host() {
		$this->assertSame(
			'<img src="https://www.evangelische-termine.de/Upload/a.jpg">',
			$this->filter( '<img src="/Upload/a.jpg">' )
		);
		$this->assertSame(
			'<img src="http://cal.example/Upload/a.jpg">',
			$this->filter(
				'<img src="/Upload/a.jpg">',
				array(
					'etprotocol' => 'http://',
					'ethost'     => 'cal.example',
				)
			)
		);
	}

	public function test_links_are_rewritten_to_the_permalink_and_escaped() {
		Functions\when( 'get_permalink' )->justReturn( 'https://example.test/termine/?p=1&x="y"' );
		$out = $this->filter( '<a href="http://_HOST_/?page=2">' );
		$this->assertStringStartsWith( '<a href="https://example.test/termine/?p=1&amp;x=&quot;y&quot;?', $out );
		$this->assertStringNotContainsString( '_HOST_', $out );
		$this->assertStringNotContainsString( '"y"', $out );
	}

	public function test_h1_becomes_h2_with_attributes() {
		$this->assertSame(
			'<h2 id="et_detail_title">T</h2><h2>U</h2>',
			$this->filter( '<H1 id="et_detail_title">T</h1 ><h1>U</h1>' )
		);
	}

	public function test_headline_block_is_removed() {
		$this->assertSame( 'x', $this->filter( '<div id="et_headline"><h1>Veranstaltungen</h1></div>x' ) );
	}

	public function test_obsolete_elements_and_colon_fix() {
		$this->assertSame(
			'<img src="a"><b>T: <span class="et_content_subtitle">S</span></b>',
			$this->filter( '<img language="javascript" align="absmiddle" src="a"><a name="veranstaltungen" ></a><b>T : <span class="et_content_subtitle">S</span></b>' )
		);
	}

	public function test_http_icons_of_the_host_are_upgraded_with_https() {
		$this->assertSame(
			'<img src="https://www.evangelische-termine.de/i.png">',
			$this->filter( '<img src="http://www.evangelische-termine.de/i.png">' )
		);
	}

	public function test_http_icons_stay_with_http_protocol() {
		$html = '<img src="http://cal.example/i.png">';
		$this->assertSame(
			$html,
			$this->filter(
				$html,
				array(
					'etprotocol' => 'http://',
					'ethost'     => 'cal.example',
				)
			)
		);
	}
}
