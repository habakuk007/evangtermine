<?php
// if(file_exists(__DIR__ . '/../../defaults-local.php')){
// 	require_once (__DIR__ . '/../../defaults-local.php');
// }

// set defaults
if(!defined('ET_DEFAULT_HOST')) {
	define( 'ET_DEFAULT_HOST', 'www.evangelische-termine.de' );
}

// Der Abruf der Evangelischen Termine erfolgt serverseitig und standardmäßig verschlüsselt (https), unabhängig davon,
// ob die eigene Website SSL verwendet. In den Einstellungen kann das Protokoll bei Bedarf überschrieben werden.
if(!defined('ET_DEFAULT_PROTOCOL')) {
	define( 'ET_DEFAULT_PROTOCOL', 'https://');
}

if(!defined('ET_OPTION_UNTIL')) {
	define( 'ET_OPTION_UNTIL', 'yes' );
}

if(!defined('ET_DEFAULT_CHARSET')) {
	define( 'ET_DEFAULT_CHARSET', 'utf8' );
}

define( 'ET_OPTION_AID', '');
define( 'ET_OPTION_EVENTTYPE', '' );
define( 'ET_OPTION_HIGHLIGHT', 'all' );
define( 'ET_OPTION_PEOPLE', '0' );
define( 'ET_OPTION_PERSON', 'all' );
define( 'ET_OPTION_PLACE', 'all' );
define( 'ET_OPTION_IPM', 'all' );
define( 'ET_OPTION_CHA', 'all' );
define( 'ET_OPTION_ITEMSPERPAGE', '20' );
define( 'ET_OPTION_DEST', 'extern' );

define( 'ET_VERANSTALTER_MODUL', 'veranstaltungen-php');
// define( 'ET_VERANSTALTER_MODUL', 'veranstaltungen' ); // TODO neuen Shortcode entwickeln
define( 'ET_TEASER_MODUL', 'teaser');
define( 'ET_DETAILS_MODUL', 'detail-php');
define( 'ET_PANEL_MODUL', 'panels');
define( 'ET_JSON', 'json');

define( 'ET_TEMPLATE_TEASER_SHORTCODE', '1' );
define( 'ET_TEMPLATE_TEASER_WIDGET', '2');
?>