<?php
/**
 * @package evangtermine
 * @version 3.5.0
 */
/*
 * Plugin Name: Evangelische Termine
 * Plugin URI: https://github.com/habakuk007/evangtermine
 * Description: Shows the events of the German Protestant event calendar "Evangelische Termine" (www.evangelische-termine.de) with shortcodes and a widget.
 * Author: habakuk
 * Version: 3.5.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author URI: https://profiles.wordpress.org/habakuk/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define ('ET_VERSION', '3.5.0');

define('ET_DEBUG', false);

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
		
		// No load_plugin_textdomain(): since WordPress 4.6 translations of plugins hosted on
		// WordPress.org (text domain = slug) are loaded automatically from the language packs.

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
		
		// shortcode für das Panelsmodul einbinden
		add_shortcode('et_panels', array('Evangtermine_ShortcodePanels', 'etPanels'));
		
		// Proxy für die Abrufe des Mini-Kalenders (der Browser der Besucher ruft die Evangelischen Termine nicht direkt ab)
		add_action ('rest_api_init', array('Evangtermine_MinicalProxy', 'registerRoutes'));

		// shortcode für den Mini-Kalender
		add_shortcode('et_minical', array('Evangtermine_ShortcodeMinical', 'etMinical'));
		
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