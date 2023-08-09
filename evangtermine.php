<?php
/**
 * @package evangtermine
 * @version 3.3
 */
/*
 * Plugin Name: Evangelische Termine
 * Description: Dieses Plugin bindet die Evangelischen Termine (www.evangelische-termine.de) in Wordpress ein.
 * Author: regibaer
 * Version: 3.3
 * Tested up to: 6.3
 * Author URI: mailto:mail@oisisi.de
 * License: GPLv2
 */

/*
 * Copyright (C) 2017-2022 Norbert Räbiger (E-Mail: mail@oisisi.de)
 * This program is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

define ('ET_VERSION', '3.3');

define('DEBUG', false);

// Exit if accessed directly
defined('ABSPATH') or die('No script kiddies please!');

class evangtermine {
	
	public function __construct() {
		
		// Defaulteinstellungen laden
		require_once (__DIR__ . '/application/configs/defaults.php');
		
		// Autoloader aufrufen - über diesen werden die einzelnen Klassen registriert.
		if (!defined('BASE_PATH')) {
			define('BASE_PATH', __DIR__);
			require_once BASE_PATH . '/autoloader.php';
			AutoloaderET::Register();
		}
		
		// integrate the options menu if an admin ist logged in
		if (is_admin ()) {
			require_once (__DIR__ . '/application/forms/options.php');
		}
		// Options-Seite über das Backend-Menü Plugins aufrufen
		add_filter ('plugin_action_links_' . plugin_basename(__FILE__), array('PluginOptionsPage', 'etPluginActions'));

		// CSS der Evangelischen Termine im Kopfbereich der Ausgabeseite integrieren
		add_action ('wp_head', array('IncludeCSS', 'etIncludeCSS'));
		
		// shortcode für den Teaser einbinden
		add_shortcode('et_teaser', array('ShortcodeTeaser', 'etTeaser'));
		
		// shortcode für das Veranstaltermodul einbinden
		add_shortcode('et_veranstalter', array('ShortcodeVeranstalter', 'etVeranstalter'));
		
		// TODO shortcode für das Panelsmodul einbinden
		add_shortcode('et_panels', array('ShortcodePanels', 'etPanels'));
		
		// shortcode für den Mini-Kalender
		add_shortcode('et_minical', array('ShortcodeMinical', 'etMinical'));
		
		// shortcode für einen Teaser der die Daten über JSON abruft
		// add_shortcode('et_teaserjson', array('ShortcodeTeaserJson', 'etTeaserJson'));
		
		// TODO shortcode für das Modul mit der Übersichtskarte http://www.evangelische-termine.de/map?vid=952&itemsPerPage=100&lat=48.92971763063&lng=10.78857421875&zoom=7&ipm=808®ion=407&vid=all
		
		// Widget einbinden
		function EtWidget_init_widget () {
			return register_widget('EtWidget');
		}
		add_action ('widgets_init', 'EtWidget_init_widget');
	}
	
}

new evangtermine();
?>