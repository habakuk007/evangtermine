<?php
/**
 * @package evangtermine
 * @version 3.4
 */
/*
 * Plugin Name: Evangelische Termine
 * Description: Shows the events of the German Protestant event calendar "Evangelische Termine" (www.evangelische-termine.de) with shortcodes and a widget.
 * Author: habakuk
 * Version: 3.4
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author URI: https://profiles.wordpress.org/habakuk/
 * License: GPLv2 or later
 * Text Domain: evangtermine
 * Domain Path: /languages
 */

/*
 * Originally developed by Norbert Räbiger (WordPress.org: regibaer).
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

define ('ET_VERSION', '3.4');

define('ET_DEBUG', false);

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class evangtermine {
	
	public function __construct() {
		
		// Defaulteinstellungen laden
		require_once (__DIR__ . '/application/configs/defaults.php');
		
		// Autoloader aufrufen - über diesen werden die einzelnen Klassen registriert.
		if (!defined('ET_BASE_PATH')) {
			define('ET_BASE_PATH', __DIR__);
			require_once ET_BASE_PATH . '/autoloader.php';
			Evangtermine_Autoloader::Register();
		}
		
		// Load the bundled translations (languages/evangtermine-<locale>.mo)
		add_action ('init', function () {
			load_plugin_textdomain ('evangtermine', false, dirname (plugin_basename (__FILE__)) . '/languages');
		});

		// integrate the options menu if an admin ist logged in
		if (is_admin ()) {
			require_once (__DIR__ . '/application/forms/options.php');
		}
		// Options-Seite über das Backend-Menü Plugins aufrufen
		add_filter ('plugin_action_links_' . plugin_basename(__FILE__), array('Evangtermine_PluginOptionsPage', 'etPluginActions'));

		// Eigene CSS-Datei aus den Einstellungen einbinden (nach den Stylesheets des Themes)
		add_action ('wp_enqueue_scripts', array('Evangtermine_IncludeCSS', 'etIncludeCSS'), 20);

		// Skripte und Stylesheets des Mini-Kalenders registrieren
		add_action ('wp_enqueue_scripts', array('Evangtermine_ShortcodeMinical', 'registerAssets'));
		
		// shortcode für den Teaser einbinden
		add_shortcode('et_teaser', array('Evangtermine_ShortcodeTeaser', 'etTeaser'));
		
		// shortcode für das Veranstaltermodul einbinden
		add_shortcode('et_veranstalter', array('Evangtermine_ShortcodeVeranstalter', 'etVeranstalter'));
		
		// TODO shortcode für das Panelsmodul einbinden
		add_shortcode('et_panels', array('Evangtermine_ShortcodePanels', 'etPanels'));
		
		// shortcode für den Mini-Kalender
		add_shortcode('et_minical', array('Evangtermine_ShortcodeMinical', 'etMinical'));
		
		// shortcode für einen Teaser der die Daten über JSON abruft
		// add_shortcode('et_teaserjson', array('ShortcodeTeaserJson', 'etTeaserJson'));
		
		// TODO shortcode für das Modul mit der Übersichtskarte http://www.evangelische-termine.de/map?vid=952&itemsPerPage=100&lat=48.92971763063&lng=10.78857421875&zoom=7&ipm=808®ion=407&vid=all
		
		// Widget einbinden
		add_action( 'widgets_init', 'evangtermine_register_widget' );
	}
	
}

/**
 * Registers the widget. Declared at top level, not inside the constructor.
 *
 * @return void
 */
function evangtermine_register_widget() {
	register_widget( 'EtWidget' );
}

new evangtermine();
?>