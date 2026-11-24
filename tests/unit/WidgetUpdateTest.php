<?php
/**
 * Tests for EtWidget::update().
 *
 * @package evangtermine
 */

class WidgetUpdateTest extends Et_Test_Case {

	private function update( $new, $old = array() ) {
		$widget = ( new ReflectionClass( 'EtWidget' ) )->newInstanceWithoutConstructor();
		return $widget->update( $new, $old );
	}

	public function test_defaults_for_an_empty_form() {
		$this->assertSame(
			array(
				'highlight'            => 'all',
				'auswahl'              => '',
				'dest'                 => 'extern',
				'until'                => 'yes',
				'additionalParameters' => '',
				'tpl'                  => ET_TEMPLATE_TEASER_WIDGET,
			),
			$this->update( array() )
		);
	}

	public function test_text_fields_are_sanitized() {
		$result = $this->update(
			array(
				'title'        => '  <b>Termine</b>  ',
				'vid'          => '952, 988',
				'itemsPerPage' => '<i>5</i>',
			)
		);
		$this->assertSame( 'Termine', $result['title'] );
		$this->assertSame( '952, 988', $result['vid'] );
		$this->assertSame( '5', $result['itemsPerPage'] );
	}

	public function test_unknown_fields_are_dropped() {
		$result = $this->update( array( 'evil' => 'x', 'vid' => '1' ), array( 'other' => 'y' ) );
		$this->assertArrayNotHasKey( 'evil', $result );
		$this->assertArrayNotHasKey( 'other', $result );
		$this->assertSame( '1', $result['vid'] );
	}

	public function test_non_scalar_values_are_ignored() {
		$result = $this->update( array( 'vid' => array( '1' ), 'title' => new stdClass() ) );
		$this->assertArrayNotHasKey( 'vid', $result );
		$this->assertArrayNotHasKey( 'title', $result );
	}

	public function test_old_values_are_used_when_the_new_ones_are_missing() {
		$result = $this->update( array( 'vid' => '2' ), array( 'vid' => '1', 'region' => '<i>7</i>', 'dest' => 'all' ) );
		$this->assertSame( '2', $result['vid'] );
		$this->assertSame( '7', $result['region'] );
		$this->assertSame( 'all', $result['dest'] );
	}

	public function test_an_empty_new_value_replaces_the_old_one() {
		$result = $this->update( array( 'title' => '' ), array( 'title' => 'Old' ) );
		$this->assertSame( '', $result['title'] );
	}

	public function test_select_fields_accept_only_allowed_values() {
		$result = $this->update(
			array(
				'highlight' => 'high',
				'auswahl'   => 'eb',
				'dest'      => 'intern',
				'until'     => 'no',
			)
		);
		$this->assertSame( 'high', $result['highlight'] );
		$this->assertSame( 'eb', $result['auswahl'] );
		$this->assertSame( 'intern', $result['dest'] );
		$this->assertSame( 'no', $result['until'] );

		$result = $this->update(
			array(
				'highlight' => 'bogus',
				'dest'      => array( 'all' ),
				'until'     => 'NO',
			),
			array( 'highlight' => 'high' )
		);
		$this->assertSame( 'high', $result['highlight'], 'an invalid new value falls back to the old one' );
		$this->assertSame( 'extern', $result['dest'] );
		$this->assertSame( 'yes', $result['until'] );
	}

	public function test_additional_parameters_keep_only_url_safe_characters() {
		$result = $this->update( array( 'additionalParameters' => 'a=1&b=<x> "2"' ) );
		$this->assertSame( 'a=1&b=x2', $result['additionalParameters'] );

		$result = $this->update( array(), array( 'additionalParameters' => 'c=3;d' ) );
		$this->assertSame( 'c=3d', $result['additionalParameters'] );
	}

	public function test_non_array_arguments_are_treated_as_empty() {
		$result = $this->update( 'nonsense', null );
		$this->assertSame( ET_TEMPLATE_TEASER_WIDGET, $result['tpl'] );
		$this->assertSame( 'all', $result['highlight'] );
	}

	public function test_tpl_cannot_be_overridden() {
		$result = $this->update( array( 'tpl' => '1' ) );
		$this->assertSame( ET_TEMPLATE_TEASER_WIDGET, $result['tpl'] );
	}
}
