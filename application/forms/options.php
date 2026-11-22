<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO Thumbnail in der Detailansicht ja/nein integrieren

// Optionsmenü hinzufügen
function evangtermine_menu() {
	add_options_page( __( 'Evangelische Termine – Settings', 'evangtermine' ), 'Evangelische Termine', 'manage_options', 'options.php', 'evangtermine_options' );
}
add_action( 'admin_menu', 'evangtermine_menu' );

function evangtermine_show_section() {
	echo '<p>' . esc_html__( 'Here you set the basic settings for the plugin. They apply to all shortcodes and the widget unless a value is given there: the shortcode parameters and the widget settings take precedence.', 'evangtermine' ) . '</p>';
}

/**
 * Gibt den Hilfetext unter einem Feld aus. Das Feld verweist mit aria-describedby auf die ID des Textes.
 *
 * @param string $id   ID des Feldes (der Hilfetext erhält die ID "<Feld>_description").
 * @param string $text Bereits übersetzter Hilfetext.
 */
function evangtermine_show_description( $id, $text ) {
	echo '<p class="description" id="' . esc_attr( $id . '_description' ) . '">' . esc_html( $text ) . '</p>';
}

/**
 * Gibt ein Auswahlfeld aus. Gespeichert wird der Wert, angezeigt wird die (übersetzte) Beschriftung.
 *
 * @param string $id      ID des Feldes.
 * @param string $name    Name der Option.
 * @param array  $items   Wert => Beschriftung.
 * @param string $current Gespeicherter Wert.
 */
function evangtermine_show_select( $id, $name, $items, $current ) {
	echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" aria-describedby="' . esc_attr( $id . '_description' ) . '">';
	foreach ( $items as $value => $label ) {
		echo '<option value="' . esc_attr( $value ) . '"' . selected( $current, (string) $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}

// Inputfelder
// Veranstalter-ID(s): kommagetrennte Liste der Veranstalter-IDs der Evangelischen Termine
function evangtermine_show_vid() {
	$vid = get_option( 'vid' );
	echo '<input id="et_option_vid" name="vid" type="text" value="' . esc_attr( $vid ) . '" aria-describedby="et_option_vid_description" />';
	evangtermine_show_description( 'et_option_vid', __( 'The ID of your organizer at Evangelische Termine. It is shown at the top right after you log in there. Enter several IDs separated by commas and without spaces, for example 952,988. Without an organizer ID no events are shown.', 'evangtermine' ) );
}

// Dekanats-ID: Dekanatsnummer. Wird als Parameter "region" an die Evangelischen Termine übergeben.
function evangtermine_show_region() {
	$region = get_option( 'region' );
	echo '<input id="et_option_region" name="region" type="text" value="' . esc_attr( $region ) . '" aria-describedby="et_option_region_description" />';
	evangtermine_show_description( 'et_option_region', __( 'The number of a deanery (Dekanat) at Evangelische Termine. The events of all organizers of this deanery are shown. Enter several numbers separated by commas. Leave the field empty if only the events of the organizers above should appear.', 'evangtermine' ) );
}

// Über diesen Schalter wird eingestellt, ob ein Termin bis zum Ablauf angezeigt wird oder mit Beginn
// des Termins von der Anzeige ausgeschlossen wird.
function evangtermine_show_until() {
	$until = (string) get_option( 'until' );
	if ( '' === $until ) {
		$until = ET_OPTION_UNTIL;
	}
	$items = array(
		'yes' => __( 'yes', 'evangtermine' ),
		'no'  => __( 'no', 'evangtermine' ),
	);
	evangtermine_show_select( 'et_option_until', 'until', $items, $until );
	evangtermine_show_description( 'et_option_until', __( 'With “yes” an event stays visible until it has ended. With “no” it disappears from the list as soon as it has started. Default: yes.', 'evangtermine' ) );
}

// Eigene CSS-Datei. Dadurch werden die CSS-Einstellungen der Evangelischen Termine überschrieben.
function evangtermine_show_css() {
	$css = get_option( 'css' );
	echo '<input id="et_option_css" name="css" type="text" value="' . esc_attr( $css ) . '" size="40" aria-describedby="et_option_css_description" />';
	evangtermine_show_description( 'et_option_css', __( 'The address (URL) of your own CSS file. It is loaded instead of the Evangelische Termine stylesheet. Empty: the Evangelische Termine stylesheet is used. With the value nocss no stylesheet is loaded at all, for example if the styling is already part of your theme.', 'evangtermine' ) );
}

// Encoding der Website einstellen
function evangtermine_show_encoding() {
	$encoding = (string) get_option( 'encoding' );
	if ( '' === $encoding ) {
		$encoding = ET_DEFAULT_CHARSET;
	}
	$items = array(
		'utf8'   => 'UTF-8',
		'latin1' => 'ISO-8859-1 (Latin-1)',
	);
	evangtermine_show_select( 'et_option_encoding', 'encoding', $items, $encoding );
	evangtermine_show_description( 'et_option_encoding', __( 'The character encoding of your website. Evangelische Termine delivers the data in this encoding. UTF-8 is correct for almost all WordPress sites; only change the value if umlauts are displayed incorrectly. Default: UTF-8.', 'evangtermine' ) );
}

/*
 * Protokoll und Host des jeweiligen Kalenderbetreibers
 * Optional: Ist das Feld leer, wird ET_DEFAULT_PROTOCOL (https://) verwendet.
 */
function evangtermine_show_etprotocol() {
	$etprotocol = (string) get_option( 'etprotocol' );
	$items      = array(
		/* translators: %s: protocol, e.g. https:// */
		''         => sprintf( __( 'Default (%s)', 'evangtermine' ), ET_DEFAULT_PROTOCOL ),
		'https://' => 'https://',
		'http://'  => 'http://',
	);
	evangtermine_show_select( 'et_option_protocol', 'etprotocol', $items, $etprotocol );
	evangtermine_show_description( 'et_option_protocol', __( 'The protocol your server uses to fetch the events from the calendar operator. Choose http:// only if the calendar operator does not offer https://; the connection is then unencrypted.', 'evangtermine' ) );
}

function evangtermine_show_ethost() {
	$ethost = get_option( 'ethost' );
	echo '<input id="et_option_ethost" name="ethost" type="text" value="' . esc_attr( $ethost ) . '" size="40" aria-describedby="et_option_ethost_description" />';
	/* translators: %s: host name, e.g. www.evangelische-termine.de */
	$description = __( 'The server the events are fetched from: the host name only, without http:// and without a path. Empty: %s. Another value is only needed if your regional church (Landeskirche) runs its own calendar.', 'evangtermine' );
	evangtermine_show_description( 'et_option_ethost', sprintf( $description, ET_DEFAULT_HOST ) );
}

// Aufbau der Optionen
function evangtermine_options() {
	if( !current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'evangtermine' ) );
	}
	?>
<div class="wrap">
	<h1>Evangelische Termine</h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'et_group' ); ?>
		<?php do_settings_sections( 'et_menu_slug' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
<?php
}

/**
 * Bereinigt die Eingabe für das Feld "CSS-Datei": leer, "nocss" oder eine URL.
 *
 * @param mixed $value Eingegebener Wert.
 * @return string
 */
function evangtermine_sanitize_css( $value ) {
	$value = trim( sanitize_text_field( (string) $value ) );
	if ( '' === $value || 'nocss' === $value ) {
		return $value;
	}
	return esc_url_raw( $value );
}

/**
 * Lässt nur einen der erlaubten Werte zu, sonst den Standardwert.
 *
 * @param mixed  $value   Eingegebener Wert.
 * @param array  $allowed Erlaubte Werte.
 * @param string $default Standardwert.
 * @return string
 */
function evangtermine_sanitize_choice( $value, $allowed, $default ) {
	$value = is_scalar( $value ) ? (string) $value : '';
	return in_array( $value, $allowed, true ) ? $value : $default;
}

function evangtermine_sanitize_until( $value ) {
	return evangtermine_sanitize_choice( $value, array( 'yes', 'no' ), 'yes' );
}

function evangtermine_sanitize_encoding( $value ) {
	return evangtermine_sanitize_choice( $value, array( 'utf8', 'latin1' ), 'utf8' );
}

function evangtermine_sanitize_etprotocol( $value ) {
	$value = evangtermine_sanitize_choice( $value, array( '', 'https://', 'http://' ), '' );
	if ( 'http://' === $value ) {
		add_settings_error(
			'et_group',
			'et_http_protocol',
			__( 'Warning: With http:// the connection to the calendar operator is unencrypted. A third party on the network could change the events that are shown on your site. Use https:// if the calendar operator offers it.', 'evangtermine' ),
			'warning'
		);
	}
	return $value;
}

/**
 * Prüft den Host des Kalenderbetreibers: nur ein Hostname (optional mit Port), kein Protokoll, kein Pfad,
 * und das Ziel muss von WordPress als sicher eingestuft werden (keine privaten oder lokalen Adressen).
 * Bei einem ungültigen Wert bleibt der bisherige Wert erhalten.
 *
 * @param mixed $value Eingegebener Wert.
 * @return string
 */
function evangtermine_sanitize_ethost( $value ) {
	$value = strtolower( trim( sanitize_text_field( (string) $value ) ) );
	if ( '' === $value ) {
		return '';
	}
	$previous = (string) get_option( 'ethost' );
	if ( ! preg_match( '/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?(:[0-9]{1,5})?$/', $value ) ) {
		add_settings_error( 'ethost', 'et_ethost_invalid', __( 'The host must consist of a domain name only (without http://, without a path). The previous value was kept.', 'evangtermine' ) );
		return $previous;
	}
	if ( ! wp_http_validate_url( 'https://' . $value . '/' ) || ! Evangtermine_Query::isSafeHost( $value ) ) {
		add_settings_error( 'ethost', 'et_ethost_unsafe', __( 'This host is not allowed (local or private address, or unreachable port). The previous value was kept.', 'evangtermine' ) );
		return $previous;
	}
	return $value;
}

// Optionsseite initialisieren
function evangtermine_admin_init() {
	add_settings_section( 'et_section', __( 'Basic settings', 'evangtermine' ), 'evangtermine_show_section', 'et_menu_slug' );

	add_settings_field( 'vid', __( 'Organizer ID', 'evangtermine' ), 'evangtermine_show_vid', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_vid' ) );
	add_settings_field( 'region', __( 'Deanery ID', 'evangtermine' ), 'evangtermine_show_region', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_region' ) );
	add_settings_field( 'until', __( 'Show events until they end', 'evangtermine' ), 'evangtermine_show_until', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_until' ) );
	add_settings_field( 'css', __( 'CSS file', 'evangtermine' ), 'evangtermine_show_css', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_css' ) );
	add_settings_field( 'encoding', __( 'Character encoding', 'evangtermine' ), 'evangtermine_show_encoding', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_encoding' ) );
	add_settings_field( 'etprotocol', __( 'Protocol', 'evangtermine' ), 'evangtermine_show_etprotocol', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_protocol' ) );
	add_settings_field( 'ethost', __( 'Calendar operator host', 'evangtermine' ), 'evangtermine_show_ethost', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_ethost' ) );

	register_setting( 'et_group', 'vid', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'et_group', 'region', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'et_group', 'until', array( 'sanitize_callback' => 'evangtermine_sanitize_until' ) );
	register_setting( 'et_group', 'css', array( 'sanitize_callback' => 'evangtermine_sanitize_css' ) );
	register_setting( 'et_group', 'encoding', array( 'sanitize_callback' => 'evangtermine_sanitize_encoding' ) );
	register_setting( 'et_group', 'etprotocol', array( 'sanitize_callback' => 'evangtermine_sanitize_etprotocol' ) );
	register_setting( 'et_group', 'ethost', array( 'sanitize_callback' => 'evangtermine_sanitize_ethost' ) );
}
add_action( 'admin_init', 'evangtermine_admin_init' );

?>