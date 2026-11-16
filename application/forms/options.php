<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// TODO Thumbnail in der Detailansicht ja/nein integrieren

// Optionsmenü hinzufügen
function et_menu() {
	add_options_page( __( 'Evangelische Termine – Einstellungen', 'evangtermine' ), 'Evangelische Termine', 'manage_options', 'options.php', 'et_options' );
}
add_action( 'admin_menu', 'et_menu' );

function et_show_section() {
	echo '<p>' . esc_html__( 'Hier legen Sie die Grundeinstellungen für das Plugin fest. Sie gelten für alle Shortcodes und das Widget, solange dort kein eigener Wert angegeben ist: Die Parameter der Shortcodes und die Einstellungen des Widgets haben Vorrang.', 'evangtermine' ) . '</p>';
}

/**
 * Gibt den Hilfetext unter einem Feld aus. Das Feld verweist mit aria-describedby auf die ID des Textes.
 *
 * @param string $id   ID des Feldes (der Hilfetext erhält die ID "<Feld>_description").
 * @param string $text Bereits übersetzter Hilfetext.
 */
function et_show_description( $id, $text ) {
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
function et_show_select( $id, $name, $items, $current ) {
	echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" aria-describedby="' . esc_attr( $id . '_description' ) . '">';
	foreach ( $items as $value => $label ) {
		echo '<option value="' . esc_attr( $value ) . '"' . selected( $current, (string) $value, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}

// Inputfelder
// Veranstalter-ID(s): kommagetrennte Liste der Veranstalter-IDs der Evangelischen Termine
function et_show_vid() {
	$vid = get_option( 'vid' );
	echo '<input id="et_option_vid" name="vid" type="text" value="' . esc_attr( $vid ) . '" aria-describedby="et_option_vid_description" />';
	et_show_description( 'et_option_vid', __( 'Die ID Ihres Veranstalters bei den Evangelischen Terminen. Sie wird dort nach der Anmeldung oben rechts angezeigt. Mehrere IDs geben Sie durch Kommas getrennt und ohne Leerzeichen an, zum Beispiel 952,988. Ohne Veranstalter-ID werden keine Termine angezeigt.', 'evangtermine' ) );
}

// Dekanats-ID: Dekanatsnummer. Wird als Parameter "region" an die Evangelischen Termine übergeben.
function et_show_region() {
	$region = get_option( 'region' );
	echo '<input id="et_option_region" name="region" type="text" value="' . esc_attr( $region ) . '" aria-describedby="et_option_region_description" />';
	et_show_description( 'et_option_region', __( 'Die Nummer eines Dekanats bei den Evangelischen Terminen. Damit werden die Termine aller Veranstalter dieses Dekanats angezeigt. Mehrere Nummern geben Sie durch Kommas getrennt an. Lassen Sie das Feld leer, wenn nur die Termine der oben genannten Veranstalter erscheinen sollen.', 'evangtermine' ) );
}

// Über diesen Schalter wird eingestellt, ob ein Termin bis zum Ablauf angezeigt wird oder mit Beginn
// des Termins von der Anzeige ausgeschlossen wird.
function et_show_until() {
	$until = (string) get_option( 'until' );
	if ( '' === $until ) {
		$until = ET_OPTION_UNTIL;
	}
	$items = array(
		'yes' => __( 'ja', 'evangtermine' ),
		'no'  => __( 'nein', 'evangtermine' ),
	);
	et_show_select( 'et_option_until', 'until', $items, $until );
	et_show_description( 'et_option_until', __( 'Bei „ja“ bleibt ein Termin sichtbar, bis er zu Ende ist. Bei „nein“ verschwindet er aus der Liste, sobald er begonnen hat. Standard: ja.', 'evangtermine' ) );
}

// Eigene CSS-Datei. Dadurch werden die CSS-Einstellungen der Evangelischen Termine überschrieben.
function et_show_css() {
	$css = get_option( 'css' );
	echo '<input id="et_option_css" name="css" type="text" value="' . esc_attr( $css ) . '" size="40" aria-describedby="et_option_css_description" />';
	et_show_description( 'et_option_css', __( 'Die Adresse (URL) einer eigenen CSS-Datei. Sie wird anstelle des Stylesheets der Evangelischen Termine geladen. Leer: Das Stylesheet der Evangelischen Termine wird verwendet. Mit dem Wert nocss wird gar kein Stylesheet geladen, zum Beispiel wenn die Gestaltung schon in Ihrem Theme enthalten ist.', 'evangtermine' ) );
}

// Encoding der Website einstellen
function et_show_encoding() {
	$encoding = (string) get_option( 'encoding' );
	if ( '' === $encoding ) {
		$encoding = ET_DEFAULT_CHARSET;
	}
	$items = array(
		'utf8'   => 'UTF-8',
		'latin1' => 'ISO-8859-1 (Latin-1)',
	);
	et_show_select( 'et_option_encoding', 'encoding', $items, $encoding );
	et_show_description( 'et_option_encoding', __( 'Die Zeichenkodierung Ihrer Website. Die Evangelischen Termine liefern die Daten in dieser Kodierung. Für nahezu alle WordPress-Seiten ist UTF-8 richtig; ändern Sie den Wert nur, wenn Umlaute falsch dargestellt werden. Standard: UTF-8.', 'evangtermine' ) );
}

/*
 * Protokoll und Host des jeweiligen Kalenderbetreibers
 * Optional: Ist das Feld leer, wird ET_DEFAULT_PROTOCOL (https://) verwendet.
 */
function et_show_etprotocol() {
	$etprotocol = (string) get_option( 'etprotocol' );
	$items      = array(
		/* translators: %s: Protokoll, z.B. https:// */
		''         => sprintf( __( 'Standard (%s)', 'evangtermine' ), ET_DEFAULT_PROTOCOL ),
		'https://' => 'https://',
		'http://'  => 'http://',
	);
	et_show_select( 'et_option_protocol', 'etprotocol', $items, $etprotocol );
	et_show_description( 'et_option_protocol', __( 'Das Protokoll, mit dem Ihr Server die Termine beim Kalenderbetreiber abruft. Wählen Sie http:// nur, wenn der Kalenderbetreiber kein https:// anbietet; die Verbindung ist dann unverschlüsselt.', 'evangtermine' ) );
}

function et_show_ethost() {
	$ethost = get_option( 'ethost' );
	echo '<input id="et_option_ethost" name="ethost" type="text" value="' . esc_attr( $ethost ) . '" size="40" aria-describedby="et_option_ethost_description" />';
	/* translators: %s: Hostname, z.B. www.evangelische-termine.de */
	$description = __( 'Der Server, von dem die Termine abgerufen werden: nur der Hostname, ohne http:// und ohne Pfad. Leer: %s. Ein anderer Wert ist nur nötig, wenn Ihre Landeskirche einen eigenen Kalender betreibt.', 'evangtermine' );
	et_show_description( 'et_option_ethost', sprintf( $description, ET_DEFAULT_HOST ) );
}

// Aufbau der Optionen
function et_options() {
	if( !current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sie haben nicht die erforderlichen Rechte, um diese Seite aufzurufen.', 'evangtermine' ) );
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
function et_sanitize_css( $value ) {
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
function et_sanitize_choice( $value, $allowed, $default ) {
	$value = is_scalar( $value ) ? (string) $value : '';
	return in_array( $value, $allowed, true ) ? $value : $default;
}

function et_sanitize_until( $value ) {
	return et_sanitize_choice( $value, array( 'yes', 'no' ), 'yes' );
}

function et_sanitize_encoding( $value ) {
	return et_sanitize_choice( $value, array( 'utf8', 'latin1' ), 'utf8' );
}

function et_sanitize_etprotocol( $value ) {
	return et_sanitize_choice( $value, array( '', 'https://', 'http://' ), '' );
}

/**
 * Prüft den Host des Kalenderbetreibers: nur ein Hostname (optional mit Port), kein Protokoll, kein Pfad,
 * und das Ziel muss von WordPress als sicher eingestuft werden (keine privaten oder lokalen Adressen).
 * Bei einem ungültigen Wert bleibt der bisherige Wert erhalten.
 *
 * @param mixed $value Eingegebener Wert.
 * @return string
 */
function et_sanitize_ethost( $value ) {
	$value = strtolower( trim( sanitize_text_field( (string) $value ) ) );
	if ( '' === $value ) {
		return '';
	}
	$previous = (string) get_option( 'ethost' );
	if ( ! preg_match( '/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?(:[0-9]{1,5})?$/', $value ) ) {
		add_settings_error( 'ethost', 'et_ethost_invalid', __( 'Der Host darf nur aus einem Domainnamen bestehen (ohne http://, ohne Pfad). Der bisherige Wert wurde beibehalten.', 'evangtermine' ) );
		return $previous;
	}
	if ( ! wp_http_validate_url( 'https://' . $value . '/' ) || ! EtQuery::isSafeHost( $value ) ) {
		add_settings_error( 'ethost', 'et_ethost_unsafe', __( 'Dieser Host ist nicht erlaubt (lokale oder private Adresse bzw. nicht erreichbarer Port). Der bisherige Wert wurde beibehalten.', 'evangtermine' ) );
		return $previous;
	}
	return $value;
}

// Optionsseite initialisieren
function et_admin_init() {
	add_settings_section( 'et_section', __( 'Grundeinstellungen', 'evangtermine' ), 'et_show_section', 'et_menu_slug' );

	add_settings_field( 'vid', __( 'Veranstalter-ID', 'evangtermine' ), 'et_show_vid', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_vid' ) );
	add_settings_field( 'region', __( 'Dekanats-ID', 'evangtermine' ), 'et_show_region', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_region' ) );
	add_settings_field( 'until', __( 'Termine bis zu ihrem Ende anzeigen', 'evangtermine' ), 'et_show_until', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_until' ) );
	add_settings_field( 'css', __( 'CSS-Datei', 'evangtermine' ), 'et_show_css', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_css' ) );
	add_settings_field( 'encoding', __( 'Zeichenkodierung', 'evangtermine' ), 'et_show_encoding', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_encoding' ) );
	add_settings_field( 'etprotocol', __( 'Protokoll', 'evangtermine' ), 'et_show_etprotocol', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_protocol' ) );
	add_settings_field( 'ethost', __( 'Host des Kalenderbetreibers', 'evangtermine' ), 'et_show_ethost', 'et_menu_slug', 'et_section', array( 'label_for' => 'et_option_ethost' ) );

	register_setting( 'et_group', 'vid', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'et_group', 'region', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'et_group', 'until', array( 'sanitize_callback' => 'et_sanitize_until' ) );
	register_setting( 'et_group', 'css', array( 'sanitize_callback' => 'et_sanitize_css' ) );
	register_setting( 'et_group', 'encoding', array( 'sanitize_callback' => 'et_sanitize_encoding' ) );
	register_setting( 'et_group', 'etprotocol', array( 'sanitize_callback' => 'et_sanitize_etprotocol' ) );
	register_setting( 'et_group', 'ethost', array( 'sanitize_callback' => 'et_sanitize_ethost' ) );
}
add_action( 'admin_init', 'et_admin_init' );

?>