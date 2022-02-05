<?php
class PluginOptionsPage {
	
	/**
	 * Optionspage auf der Pluginseite aufrufbar
	 *
	 * @since 1.8
	 */
	public static function etPluginActions($links) {
		$links [] = '<a href="' . esc_url ( get_admin_url ( null, 'options-general.php?page=options.php' ) ) . '">Einstellungen</a>';
		return $links;
	}
}
?>