<?php
/**
 * @package evangtermine
 * @version 1.8
 */
/*
Plugin Name: Evangelische Termine
Description: Dieses Plugin bindet die Evangelischen Termine (www.evangelische-termine.de) in Wordpress ein.
Author: regibaer
Version: 1.8
Tested up to: 4.3
Author URI: mailto:rae@de-zeit.de
License: GPLv2
*/

/*
Copyright (C) 2015 Norbert Räbiger (E-Mail: rae@de-zeit.de)
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program;
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
Boston, MA 02110-1301, USA.
*/

// Exit if accessed directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Defaultwerte festlegen
define( 'ET_OPTION_EVENTTYPE', '' );
define( 'ET_OPTION_HIGHLIGHT', 'all' );
define( 'ET_OPTION_UNTIL', 'yes' );
define( 'ET_OPTION_PEOPLE', '0' );
define( 'ET_OPTION_PERSON', 'all' );
define( 'ET_OPTION_PLACE', 'all' );
define( 'ET_OPTION_IPM', 'all' );
define( 'ET_OPTION_CHA', 'all' );
define( 'ET_OPTION_ITEMSPERPAGE', '20' );
define( 'ET_OPTION_DEST', 'extern' );
define( 'ET_DEFAULT_CHARSET', 'utf8' );
define( 'ET_TEMPLATE_TEASER_SHORTCODE', '1' );
define( 'ET_TEMPLATE_TEASER_WIDGET', '2');
define( 'ET_DEFAULT_PROTOCOL', 'http://' );
define( 'ET_DEFAULT_HOST', 'www.evangelische-termine.de' );

// Pluginpfad
define( 'EVANGTERMINE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Internationalisierung laden
load_plugin_textdomain('evangtermine', false, EVANGTERMINE_PLUGIN_PATH . '/assets/languages' );

// Benötigte Dateien laden
// Optionsmenü einbinden
if( is_admin() ) {
	require_once( EVANGTERMINE_PLUGIN_PATH . 'includes/options.php' );
}
require_once ( EVANGTERMINE_PLUGIN_PATH . 'includes/functions.php' );

//
// Ausgabe
// Die Funktionen in diesem Block werden für die Ausgabe der Evangelischen Termine auf der Website verwendet.
// Shortcodes/Makros: [et_veranstalter Parameter], [et_teaser Parameter]
// Widget: Evangelische Termine
//

/**
 * Shortcode für das Veranstalter-Modul
 *
 * Das Veranstalter-Modul der Evangelischen Termine wird mit einem Formular ausgeliefert, über das die
 * Veranstaltungen nach verschiedenen Kriterien gefilter werden können. Über den Shortcode [et_veranstalter @params]
 * kann das Modul in einem Beitrag oder in einer Seite integriert werden.
 *
 * @since 1.0.0
 *
 * @param Array $attr - übergibt die im Shortcode angegebenen Paramter als Array an die Funktion
 * @param String $content - übergibt den schon aufgebauten Content an die Funktion
 *
 * @return String 
 */
function et_veranstalter_shortcode( $attr, $content = null ) {
	$a = shortcode_atts( array(
			'vid' 		=> get_option( 'vid' ),
			'region'	=> get_option( 'region' ),
			'eventtype' => ET_OPTION_EVENTTYPE,
			'highlight'	=> ET_OPTION_HIGHLIGHT,
			'people'	=> ET_OPTION_PEOPLE,
			'place'		=> ET_OPTION_PLACE,
			'person'	=> ET_OPTION_PERSON,
			'ipm'		=> ET_OPTION_IPM,
			'cha'		=> ET_OPTION_CHA,
			'itemsperpage' => get_option( 'itemsPerPage' ) ? get_option ( 'itemsPerPage' ) : ET_OPTION_ITEMSPERPAGE,
			'dest'		=> ET_OPTION_DEST,
			'until'		=> get_option( 'until' ) ? get_option ( 'until' ) : ET_OPTION_UNTIL
	), $attr );
	$a[ 'itemsPerPage' ] = $a[ 'itemsperpage' ];
	unset( $a[ 'itemsperpage' ] );
	return $content . '

<!-- [et_veranstalter @attribute] -->
<div class="et_veranstalter">' . et_veranstalter( $a ) . '</div>
<!-- [et_veranstalter] -->';
}
add_shortcode( 'et_veranstalter', 'et_veranstalter_shortcode' );

/**
 * Shortcode für das Teaser-Modul
 *
 * Das Teaser-Modul der Evangelischen Termine. Über den Shortcode [et_teaser @params]
 * kann das Modul in einem Beitrag oder in einer Seite integriert werden.
 *
 * @since 1.0.0
 *
 * @param Array $attr - übergibt die im Shortcode angegebenen Paramter als Array an die Funktion
 * @param String $content - übergibt den schon aufgebauten Content an die Funktion
 *
 * @return String 
 */
function et_teaser_shortcode( $attr, $content = null ) {
	$a = shortcode_atts( array(
			'vid' 			=> get_option( 'vid' ),
			'region'		=> get_option( 'region' ),
			'eventtype'		=> ET_OPTION_EVENTTYPE,
			'highlight'		=> ET_OPTION_HIGHLIGHT,
			'people'		=> ET_OPTION_PEOPLE,
			'place'			=> ET_OPTION_PLACE,
			'person'		=> ET_OPTION_PERSON,
			'ipm'			=> ET_OPTION_IPM,
			'cha'			=> ET_OPTION_CHA,
			'itemsperpage'	=> get_option( 'itemsPerPage' ) ? get_option( 'itemsPerPage' ) : ET_OPTION_ITEMSPERPAGE,
			'dest'			=> ET_OPTION_DEST,
			'until'			=> get_option( 'until' ) ? get_option( 'until' ) : ET_OPTION_UNTIL,
			'tpl'			=> ET_TEMPLATE_TEASER_SHORTCODE
	), $attr );
	$a[ 'itemsPerPage' ] = $a[ 'itemsperpage' ];
	unset( $a[ 'itemsperpage' ] );
	return $content . '

<!-- [et_teaser @attribute] -->
<div class="et_teaser">' . et_teaser( $a ) . '</div>';
}
add_shortcode( 'et_teaser', 'et_teaser_shortcode' );

// Widget Evangelische Termine
class ET_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'ET_Widget',
			'Evangelische Termine',
			array ('description' =>  __('Zeigt eine Liste der nächsten Veranstaltungen an.'), )
		);
	}
	
	function form( $instance ) {
		$default_settings = array(
				'title'	=> 'Nächste Termine',
				'vid'	=> get_option( 'vid' ),
				'region' => get_option( 'region' ),
				'eventtype' => ET_OPTION_EVENTTYPE,
				'highlight' => ET_OPTION_HIGHLIGHT,
				'place' => ET_OPTION_PLACE,
				'ipm' => ET_OPTION_IPM,
				'cha' => ET_OPTION_CHA,
				'itemsPerPage' => get_option( 'itemsPerPage' ) ? get_option( 'itemsPerPage' ) : ET_OPTION_ITEMSPERPAGE,
				'dest' => ET_OPTION_DEST,
				'until' => get_option( 'until' ) ? get_option( 'until' ) : ET_OPTION_UNTIL,
				'tpl'	=> ET_TEMPLATE_TEASER_WIDGET
		);
		$instance = wp_parse_args( (array) $instance, $default_settings );
		
?>
	<p>Titel: 
		<input name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
	</p>
	<p>Veranstalter-ID (kommaseparierte Liste, keine Leerzeichen): 
		<input name="<?php echo $this->get_field_name( 'vid' ); ?>" type="text" value="<?php echo esc_attr( $instance['vid'] ); ?>" />
	</p>
	<p>Dekanats-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'region' ); ?>" type="text" value="<?php echo esc_attr( $instance['region'] ); ?>" />
	</p>
	<p>Eventtype-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'eventtype' ); ?>" type="text" value="<?php echo esc_attr( $instance['eventtype' ] ); ?>" />
	</p>
	<p>Highlight (all/high):
		<select name="<?php echo $this->get_field_name( 'highlight' ); ?>">
			<?php
			$items = array( 'all', 'high' );
			foreach ($items as $item) {
			 	 $selected = ( $instance['highlight'] == $item ) ? 'selected="selected"' : '';
			 	 echo '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
			} 
			?>
		</select>
	</p>
	<p>Veranstaltungsort-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'place' ); ?>" type="text" value="<?php echo esc_attr( $instance['place'] ); ?>" />
	</p>
	<p>Veranstaltungstyp-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'ipm' ); ?>" type="text" value="<?php echo esc_attr( $instance['ipm'] ); ?>" />
	</p>
	<p>Kanäle (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'cha' ); ?>" type="text" value="<?php echo esc_attr( $instance['cha'] ); ?>" />
	</p>
	<p>itemsPerPage:
		<input name="<?php echo $this->get_field_name( 'itemsPerPage' ); ?>" type="text" value="<?php echo esc_attr( $instance['itemsPerPage'] ); ?>" />
	</p>
	<p>Anzeige (extern/intern/all):
		<select name="<?php echo $this->get_field_name( 'dest' ); ?>">
			<?php
			$items = array( 'extern', 'intern', 'all' );
			foreach ($items as $item) {
			 	 $selected = ( $instance['dest'] == $item ) ? 'selected="selected"' : '';
			 	 echo '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
			} 
			?>
		</select>
	</p>
	<p>Anzeige der Veranstaltung bis zum Enddatum (yes/no):
		<select name="<?php echo $this->get_field_name( 'until' ); ?>">
			<?php
			$items = array( 'yes', 'no' );
			foreach ($items as $item) {
			 	 $selected = ( $instance['until'] == $item ) ? 'selected="selected"' : '';
			 	 echo '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
			} 
			?>
		</select>
		<!-- <input name="<?php echo $this->get_field_name( 'until' ); ?>" type="text" value="<?php echo esc_attr( $instance['until'] ); ?>" /> -->
	</p>
	<input name="<?php echo $this->get_field_name( 'tpl' ); ?>" type="hidden" value="2" />
		
<?php
	}
	
	function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ]	= strip_tags( $new_instance[ 'title' ] );
		$instance[ 'vid' ]		= $new_instance[ 'vid' ];
		$instance[ 'region' ]	= $new_instance[ 'region' ];
		$instance[ 'eventtype' ]	= $new_instance[ 'eventtype' ];
		$instance[ 'highlight' ]	= $new_instance[ 'highlight' ];
		$instance[ 'place' ]	= $new_instance[ 'place' ];
		$instance[ 'ipm']	= $new_instance[ 'ipm' ];
		$instance[ 'cha' ]		= $new_instance[ 'cha' ];
		$instance[ 'itemsPerPage' ]	= $new_instance[ 'itemsPerPage' ];
		$instance[ 'dest' ]		= $new_instance[ 'dest' ];
		$instance[ 'until' ]	= $new_instance[ 'until' ];
		$instance[ 'tpl' ]		= $new_instance[ 'tpl' ];
		
		return $instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		$title	= apply_filters( 'widget_title', $instance[ 'title' ] );
		
		echo $before_widget;
		
		if( !empty($title) ) {
			echo $before_title . $title . $after_title;
		}
	
		echo '<div class="et_sidebar">' . et_teaser( $instance ) . '</div>';
		
		echo $after_widget;
	}
}

function et_register_widget() {
	register_widget( 'ET_Widget' );
}
add_action( 'widgets_init', 'et_register_widget' );

/*
 *  CSS der Evangelischen Termine im Head-Bereich einbinden
 *
 * @since 1.0.0
 */
add_action( 'wp_head', 'et_include_css' );
function et_include_css () {
	if( get_option( 'css' ) ) {
		$css = get_option( 'css' );
	} else {
		$css = plugins_url( 'assets/css/evangtermine.css', EVANGTERMINE_PLUGIN_PATH . 'evangtermine' );
	}
	$output = '<link href="' . esc_url( $css ) . '" media="screen, projection" rel="stylesheet" type="text/css" />';
	echo $output;
}

/*
 *  Optionspage auf der Pluginseite aufrufbar
 *
 *	@since 1.8
 */
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'et_plugin_actions' );

function et_plugin_actions( $links ) {
	$links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=options.php') ) .'">Einstellungen</a>';
	return $links;
}
?>
