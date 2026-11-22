<?php
/**
 * Minimal stand-in for WP_Widget so that EtWidget can be loaded without WordPress.
 *
 * @package evangtermine
 */

if ( ! class_exists( 'WP_Widget' ) ) {
	// phpcs:ignore Generic.Files.OneObjectStructurePerFile, PEAR.NamingConventions.ValidClassName.Invalid
	class WP_Widget {
	}
}
