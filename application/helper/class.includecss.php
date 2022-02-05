<?php
class IncludeCSS {
	
	/**
	 * CSS der Evangelischen Termine im Head-Bereich einbinden
	 *
	 * @since 1.0.0
	 */
	public static function etIncludeCSS() {
		if (get_option ( 'css' ) && (get_option( 'css' ) != 'nocss')) {
			$css = get_option ( 'css' );
			// TODO evtl. media="all", damit auch für den Druck ein ordentliches CSS erstellt werden kann
			$output = '<link href="' . $css . '" media="screen, projection" rel ="stylesheet" type="text/css" />';
			echo $output;
		} 
	}
}
?>