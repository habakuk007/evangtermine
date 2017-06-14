<?php
class EtQuery {
	private $userAgent = 'Wordpress-Plugin evangtermine 2.0';
	private $connectionTimeout = 5;
	private $etFilename;
	private $etParameters; // Defaultwerte
	private $etProtocol;
	private $etHost;
	private $noDetail = false;
	private $etLoadCSS = true;
	
	/**
	 *
	 * @param int $tpl
	 *        	Template für die Teaser/Widget-Module (Contentbereich => ET_TEMPLATE_TEASER_SHORTCODE, Sidebar => ET_TEMPLATE_TEASER_WIDGET)
	 */
	public function __construct($tpl = null) {
		// Werte aus der Optionsseite und den Defaultwerten zusammenführen
		$this->etParameters = array (
				'vid' => get_option ( 'vid' ),
				'region' => get_option ( 'region' ),
				'eventtype' => ET_OPTION_EVENTTYPE,
				'highlight' => ET_OPTION_HIGHLIGHT,
				'people' => ET_OPTION_PEOPLE,
				'place' => ET_OPTION_PLACE,
				'ipm' => ET_OPTION_IPM,
				'cha' => ET_OPTION_CHA,
				'itemsPerPage' => get_option ( 'itemsPerPage' ) ? get_option ( 'itemsPerPage' ) : ET_OPTION_ITEMSPERPAGE,
				'dest' => ET_OPTION_DEST,
				'until' => get_option ( 'until' ) ? get_option ( 'until' ) : ET_OPTION_UNTIL,
				'q' => get_option ( 'q' ) ? get_option ( 'q' ) : '',
				'css' => 'nocss', // unterbindet das CSS des Teasers in der Sidebar
				'encoding' => get_option ( 'encoding' ) ? get_option ( 'encoding' ) : ET_DEFAULT_CHARSET 
		);
		
		// Protokoll für den Abruf der Evangelischen Termine übernehmen und Default-Host setzen
		$this->etProtocol = get_option ( 'etprotocol' ) ? get_option ( 'etprotocol' ) : ET_DEFAULT_PROTOCOL;
		$this->etHost = get_option ( 'ethost' ) ? get_option ( 'ethost' ) : ET_DEFAULT_HOST;
		
		// Filename für den Abruf bei den Evangelischen Terminen setzen, bei Teaser-Abrufen auch das zu verwendende Template setzen
		if (! $tpl) {
			$this->setFilename ( ET_VERANSTALTER_MODUL );
		} else {
			$this->setFilename ( ET_TEASER_MODUL );
			$this->etParameters ['tpl'] = $tpl;
		}
	}
	
	/**
	 * Legt den zu verwendenden Filename in Attribut ab.
	 */
	function setFilename($filename) {
		$this->etFilename = $filename;
	}
	
	/**
	 * Setzt die Variable $etLoadCSS, über die entschieden wird, ob das CSS der Evangelischen Termine geladen wird (true),
	 * oder ob ein eigenes CSS verwendet werden soll (false).
	 * 
	 * @param boolean $loadCSS
	 */
	function setLoadCSS($loadCSS) {
		$this->etLoadCSS = $loadCSS;
	}
	
	/**
	 * Setzt einen einzelnen Parameter
	 *
	 * @param
	 *        	$key
	 * @param
	 *        	$value
	 */
	public function setEtParameter($key, $value) {
		if ($key && $value) {
			$this->etParameters [$key] = $value;
		}
	}
	
	/**
	 * Liefert einen Parameter zurück
	 *
	 * @param string $key        	
	 *
	 * @return string
	 */
	public function getEtParameter($key) {
		return $this->etParameters [$key];
	}
	
	/**
	 * Übergibt $etParameters als Rückgabewert
	 *
	 * @return array $etParameters
	 */
	public function getEtParameters() {
		return $this->etParameters;
	}
	
	/**
	 * Übernimmt die Werte aus den Shortcode- oder Widget-Einstellungen
	 *
	 * @param array $etParameters
	 *        	Werte aus den Shortcode- oder Widget-Einstellungen
	 */
	public function setEtParameters($etParameters) {
		if (is_array ( $etParameters )) {
			$this->etParameters = array_merge ( $this->etParameters, $etParameters );
			if ($etParameters ['itemsperpage']) {
				$this->etParameters ['itemsPerPage'] = $etParameters ['itemsperpage'];
				unset ( $this->etParameters ['itemsperpage'] );
			}
		}
	}
	
	/**
	 * Setzt die Variable $noDetail, über die entschieden wird, ob die Detailansicht angezeigt werden darf.
	 *
	 * @param boolean $noDetail        	
	 */
	public function setNoDetail($noDetail = false) {
		$this->noDetail = $noDetail;
	}
	
	/**
	 * Gibt $etParameters als Querystring zurück
	 *
	 * @return string
	 */
	public function getQuerystring() {
		$queryString = '';
		foreach ( $this->etParameters as $key => $value ) {
			if ('title' != $key) {
				if ('region' != $key) {
					$queryString .= '&' . $key . '=' . $value;
				} else {
					$queryString .= '&' . 'region' . '=' . $value;
				}
			}
		}
		
		// erstes Zeichen des Querystrings entfernen ('&').
		$queryString = substr ( $queryString, 1, strlen ( $queryString ) - 1 );
		
		return $queryString;
	}
	
	/**
	 * Ruft die Daten aus der Datenbank der Evangelischen Termine ab.
	 * In $queryString werden die
	 * Parameter für die Abfrage übergeben.
	 *
	 * @param string $queryString        	
	 * @return string
	 */
	public function retrieveStream($queryString) {
		if ($this->etParameters ['vid']) {
			// Dateinamen für den Abruf setzen
			$filename = $this->etFilename;
			
			// Wenn etID übergeben wurde, Detailseite aufrufen
			if (($_GET ['etID'] != "") && (! $this->noDetail)) {
				// TODO eigenes CSS an den $queryString anhängen (Detailansicht)
				$queryString .= '&ID=' . $_GET ['etID'];
				$filename = ET_DETAILS_MODUL;
			}
			
			$host = $this->etHost;
			// TODO Prüfen, ob etHost mit dem angegebenen Protokoll erreichbar ist, wenn nicht, das andere Protokoll testen
			$url = $this->etProtocol . $host . '/' . $filename . '?' . $queryString;
			
			if (function_exists ( 'curl_init' )) {
				$sobl = curl_init ( $url );
				curl_setopt ( $sobl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt ( $sobl, CURLOPT_USERAGENT, 'EvangTermine' );
				curl_setopt ( $sobl, CURLOPT_REFERER, $_SERVER ['HTTP_HOST'] . $_SERVER ['PHP_SELF'] );
				curl_setopt ( $sobl, CURLOPT_CONNECTTIMEOUT, 5 );
				$stream = curl_exec ( $sobl );
				$sobl_info = curl_getinfo ( $sobl );
				if ($sobl_info ['http_code'] == '200') {
					$stream = $this->filterStream ( $stream );
				} else {
					// Fehlermeldung $host ist vorübergehend nicht erreichbar
					$stream = '<div>Der Veranstaltungskalender ist derzeit nicht verfügbar.</div>';
				}
			} else {
				// Fehlermeldung, wenn cURL nicht installiert ist
				$stream = '<div>Das benötigte PHP-Modul cURL ist nicht installiert.</div>';
			}
		} else {
			$stream = 'Error: Plugin "Evangelische Termine": Veranstalter-ID ist nicht gesetzt';
		}
		
		return $stream;
	}
	
	/**
	 * Baut den von den Evangelischen Terminen empfangenen Stream sinnvoll um
	 *
	 * @param string $stream        	
	 * @return string $stream
	 */
	private function filterStream($stream) {
		// nocss entfernen - verursacht eine Warnung
		$stream = str_replace ( '<link rel="stylesheet" type="text/css" href="nocss"  />', '', $stream );
		
		// CSS der Evangelischen Termine entfernen, damit das eigene CSS verwendet werden kann.
		if (!$this->etLoadCSS) {
			$stream = str_replace ( '<link rel="stylesheet" type="text/css" href="http://www.evangelische-termine.de/bundles/vket/css/publicintegration.css"  />', '', $stream );
			$stream = str_replace ( '<link rel="stylesheet" type="text/css" href="https://www.evangelische-termine.de/bundles/vket/css/publicintegration.css"  />', '', $stream );
		}
		
		// URLs umschreiben
		$stream = str_replace ( '/Upload/', $this->etProtocol . $this->etHost . '/Upload/', $stream );
		$stream = str_replace ( 'http://_HOST_/?', get_permalink ( $post->ID ) . '?' . $this->getQuerystring () . '&amp;', $stream );
		
		// Überschrift "Veranstaltungen" entfernen und <h1>-Überschrift der Evangelischen Termine in ein <h2> umwandeln
		$stream = str_replace ( '<div id="et_headline"><h1>Veranstaltungen</h1></div>', '', $stream );
		$stream = str_replace ( '<h1>', '<h2>', $stream );
		$stream = str_replace ( '</h1>', '</h2>', $stream );
		
		// Doppelpunkte nach dem Titel korrigieren
		$stream = str_replace ( ' : <span class="et_content_subtitle">', ': <span class="et_content_subtitle">', $stream );
		
		// Google-Maps über SSL abrufen
		$stream = str_replace ( 'http://maps.googleapis.com', 'https://maps.googleapis.com', $stream );
		
		// Obsolete Elemente entfernen
		$stream = str_replace ( ' language="javascript"', '', $stream );
		$stream = str_replace ( ' align="absmiddle"', '', $stream );
		$stream = str_replace ( '<a name="veranstaltungen" ></a>', '', $stream );
		
		// Hintergrundbilder in der Detailansicht umschreiben
		$stream = str_replace ( '<div id="et_detail_header" style="background-image:url(', '<div id="et_detail_header"><img class="et_header_image" src="', $stream );
		$stream = str_replace ( ') ">', '"/>', $stream );
		
		// Bild des Ortes in der Detailansicht
		// Dauerhaft groß anzeigen - Problem: der Defaultzustand wird nicht angezeigt.
		// img-Tags werden durch die Style-Parameter ergänzt -> kein Zugriff von jquery auf die styles.
		// TODO Defaultwert für jquery herausfinden
		// TODO Thumbnail abhängig von den Einstellungen im Optionsmenü ein-/ausschalten
		// $stream = str_replace('id="et_place_image_th"', '"id="et_place_image_th" style="display: none;"', $stream);
		// $stream = str_replace('id="et_place_image"', '"id="et_place_image" style="display: inline;"', $stream);
		
		return $stream;
	}
}
?>