<?php
class IncludeCSS {

	/**
	 * Eigene CSS-Datei aus den Einstellungen einbinden (Hook wp_enqueue_scripts)
	 *
	 * @since 1.0.0
	 */
	public static function etIncludeCSS() {
		$css = get_option( 'css' );
		if ( $css && 'nocss' !== $css ) {
			// TODO evtl. media="all", damit auch für den Druck ein ordentliches CSS erstellt werden kann
			// Ohne Versionsparameter: die Adresse der Datei bleibt genau so, wie sie in den Einstellungen steht.
			wp_enqueue_style( 'evangtermine-custom', esc_url_raw( $css ), array(), null, 'screen, projection' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
	}
}
