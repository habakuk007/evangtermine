<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Evangtermine_PluginOptionsPage {
	
	/**
	 * Optionspage auf der Pluginseite aufrufbar
	 *
	 * @since 1.8
	 */
	public static function etPluginActions($links) {
		$links [] = '<a href="' . esc_url ( get_admin_url ( null, 'options-general.php?page=options.php' ) ) . '">' . esc_html__( 'Settings', 'evangtermine' ) . '</a>';
		$links [] = '<a href="' . esc_url( ET_DOCS_URL ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'evangtermine' ) . '</a>';
		return $links;
	}
}
?>