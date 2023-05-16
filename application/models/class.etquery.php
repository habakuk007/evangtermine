<?php

class EtQuery
{

    private $connectionTimeout = 5;
    private $etFilename;
    private $etParameters;

    // Defaultwerte
    private $etProtocol;
    private $etHost;
    private $noDetail = false;
    private $etLoadCSS = true;

    /**
     *
     * @param int $tpl
     *            Template für die Teaser/Widget-Module (Contentbereich => ET_TEMPLATE_TEASER_SHORTCODE, Sidebar => ET_TEMPLATE_TEASER_WIDGET)
     */
    public function __construct($tpl = null)
    {
        // Werte aus der Optionsseite und den Defaultwerten zusammenführen
        $this->etParameters = array(
            'vid' => get_option('vid'),
            'region' => get_option('region'),
            'aid' => ET_OPTION_AID,
            'eventtype' => ET_OPTION_EVENTTYPE,
            'highlight' => ET_OPTION_HIGHLIGHT,
            'people' => ET_OPTION_PEOPLE,
            'place' => ET_OPTION_PLACE,
            'ipm' => ET_OPTION_IPM,
            'cha' => ET_OPTION_CHA,
            'itemsPerPage' => get_option('itemsPerPage') ? get_option('itemsPerPage') : ET_OPTION_ITEMSPERPAGE,
            'dest' => ET_OPTION_DEST,
            'until' => get_option('until') ? get_option('until') : ET_OPTION_UNTIL,
            'q' => get_option('q') ? get_option('q') : '',
            'css' => 'nocss', // unterbindet das CSS des Teasers in der Sidebar
            'encoding' => get_option('encoding') ? get_option('encoding') : ET_DEFAULT_CHARSET
        );

        // Protokoll für den Abruf der Evangelischen Termine übernehmen und Default-Host setzen
        $this->etProtocol = get_option('etprotocol') ? get_option('etprotocol') : ET_DEFAULT_PROTOCOL;
        $this->etHost = get_option('ethost') ? get_option('ethost') : ET_DEFAULT_HOST;

        // Filename für den Abruf bei den Evangelischen Terminen setzen, bei Teaser-Abrufen auch das zu verwendende Template setzen
        if (! $tpl) {
            $this->setFilename(ET_VERANSTALTER_MODUL);
        } else {
            $this->setFilename(ET_TEASER_MODUL);
            $this->etParameters['tpl'] = $tpl;
        }
    }

    /**
     * Legt den zu verwendenden Filename in Attribut ab.
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->etFilename = $filename;
    }

    /**
     * Setzt die Variable $etLoadCSS, über die entschieden wird, ob das CSS der Evangelischen Termine geladen wird (true),
     * oder ob ein eigenes CSS verwendet werden soll (false).
     *
     * @param boolean $loadCSS
     */
    function setLoadCSS($loadCSS)
    {
        $this->etLoadCSS = $loadCSS;
    }

    /**
     * Setzt einen einzelnen Parameter
     *
     * @param
     *            $key
     * @param
     *            $value
     */
    public function setEtParameter($key, $value)
    {
        if ($key && $value) {
            if ($key == 'et_q') {
                $key = 'q';
            }
            $this->etParameters[$key] = $value;
        }
    }

    /**
     * Liefert einen Parameter zurück
     *
     * @param string $key
     *
     * @return string
     */
    public function getEtParameter($key)
    {
        return $this->etParameters[$key];
    }

    /**
     * Übergibt $etParameters als Rückgabewert
     *
     * @return array $etParameters
     */
    public function getEtParameters()
    {
        return $this->etParameters;
    }

    /**
     * Übernimmt die Werte aus den Shortcode- oder Widget-Einstellungen
     *
     * @param array $etParameters
     *            Werte aus den Shortcode- oder Widget-Einstellungen
     */
    public function setEtParameters($etParameters)
    {
        if (is_array($etParameters)) {
            $this->etParameters = array_merge($this->etParameters, $etParameters);
            if (array_key_exists('itemsperpage', $this->etParameters)) {
                if ($etParameters['itemsperpage']) {
                    $this->etParameters['itemsPerPage'] = $etParameters['itemsperpage'];
                    unset($this->etParameters['itemsperpage']);
                }
            }
        }
    }

    /**
     * Setzt die Variable $noDetail, über die entschieden wird, ob die Detailansicht angezeigt werden darf.
     *
     * @param boolean $noDetail
     */
    public function setNoDetail($noDetail = false)
    {
        $this->noDetail = $noDetail;
    }

    /**
     * Gibt $etParameters als Querystring zurück
     *
     * @return string
     */
    public function getQuerystring()
    {
        $queryString = '';
        foreach ($this->etParameters as $key => $value) {
            // Query bei den Evangelischen Termine korrekt aufbauen: Leerzeichen durch '+' ersetzen.
            if ('q' == $key)
                $value = str_replace(' ', '+', $value);

            // Erwachsenenbildung in Bayern: Wenn auswahl=eb soll weder ein Eingabeformular noch ein Kanal verwendet werden (Abfrage erfolgt direkt in den Evangelischen Terminen).
            // if ( !(($this->etParameters['auswahl'] == 'eb') && ($key == 'ipm')) && !(($this->etParameters['auswahl'] == 'eb') && ($key == 'cha'))) {
            if ('title' != $key) {
                if ('region' != $key) {
                    if ('additionalParameters' != $key) {
                        $queryString .= '&' . $key . '=' . $value;
                    } else {
                        $queryString .= '&' . $value;
                    }
                } else {
                    $queryString .= '&' . 'region' . '=' . $value;
                }
            }
            // } else {
            // $queryString .= '&' . $key . '=all'; // ipm=all wenn Erwachsenenbildung (auswahl=eb) abgefragt werden soll
            // }
        }

        // erstes Zeichen des Querystrings entfernen ('&').
        $queryString = substr($queryString, 1, strlen($queryString) - 1);

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
    public function retrieveStream($queryString)
    {
        global $wp;

        if ($this->etParameters['vid']) {
            // Dateinamen für den Abruf setzen
            $filename = $this->etFilename;

            $host = $this->etHost;

            // Wenn etID übergeben wurde, Detailseite aufrufen
            if (array_key_exists('etID', $_GET)) {
                if (($_GET['etID'] != "") && (! $this->noDetail)) {
                    // TODO eigenes CSS an den $queryString anhängen (Detailansicht)
                    $queryString .= '&ID=' . $_GET['etID'];
                    $filename = ET_DETAILS_MODUL;
                    // $filename='veranstaltung_im_detail' . $_GET['etID'] . 'html';
                }
            }

            Debugging::consoleLog('Querystring: ', $queryString);

            // TODO Prüfen, ob etHost mit dem angegebenen Protokoll erreichbar ist, wenn nicht, das andere Protokoll testen
            $url = $this->etProtocol . $host . '/' . $filename . '?' . $queryString;
            Debugging::consoleLog('URL: ', $url);

            // Abfrage der Evangelischen Termine über die http-Api von Wordpress
            $response = wp_remote_get($url, array(
                'timeout' => $this->connectionTimeout,
                'user-agent' => 'evangtermine/' . ET_VERSION . ' Wordpress-Plugin',
                'headers' => array(
                    'Referer' => home_url($wp->request)
                )
            ));

            if (! is_wp_error($response) && $response['response']['code'] == 200) {
                $stream = $response['body'];
                if ('[{' != substr($stream, 0, 2)) {
                    $stream = $this->filterStream($stream);
                }
            } else {
                // Fehlermeldung $host ist vorübergehend nicht erreichbar
                $stream = '<div>Der Veranstaltungskalender ist derzeit nicht verfügbar.</div>';
            }
        } else {
            $stream = 'Error: Plugin "Evangelische Termine": Veranstalter-ID ist nicht gesetzt';
        }

        if (DEBUG) {
            Debugging::writeOutputStream(BASE_PATH . '/etStream.html', $stream);
        }

        return $stream;
    }

    /**
     * Baut den von den Evangelischen Terminen empfangenen Stream sinnvoll um
     *
     * @param string $stream
     * @return string $stream
     */
    private function filterStream($stream)
    {
        // nocss entfernen - verursacht eine Warnung
        $stream = str_replace('<link rel="stylesheet" type="text/css" href="nocss"  />', '', $stream);
        $stream = str_replace('<link href="nocss" media="screen, projection" rel="stylesheet" type="text/css" />', '', $stream);
        
        // URL für JavaScripts in et_panels anpassen
        $stream = str_replace('<script type="text/javascript" src="/bundles/vket/js/jquery/jquery.min.js"></script>', '<script type="text/javascript" src="' . plugin_dir_url( '' ) . 'evangtermine/assets/scripts/jquery.min.js' . '"></script>', $stream);
        $stream = str_replace('<script type="text/javascript" src="/bundles/vket/css/panels/bootstrap3.3.7.min.js"></script>', '<script type="text/javascript" src="' . plugin_dir_url( '' ) . 'evangtermine/assets/scripts/bootstrap3.3.7.min.js' . '"></script>', $stream);
        $stream = str_replace('<link rel="stylesheet" href="/bundles/vket/css/panels/bootstrap3.3.7.min.css" />', '<link rel="stylesheet" href="' . plugin_dir_url( '' ) . 'evangtermine/assets/styles/bootstrap3.3.7.min.css' . '" />', $stream);
        $stream = str_replace('<link rel="stylesheet" href="/bundles/vket/css/panels/bootstrap-theme3.3.7.min.css" />', '<link rel="stylesheet" href="' . plugin_dir_url( '' ) . 'evangtermine/assets/styles/bootstrap-theme3.3.7.min.css' . '" />', $stream);
        $stream = str_replace('<link rel="stylesheet" href="/bundles/vket/css/font-awesome.min.css" />', '<link rel="stylesheet" href="' . plugin_dir_url( '' ) . 'evangtermine/assets/font-awesome-4.7.0/css/font-awesome.min.css' . '" />', $stream);

        // CSS der Evangelischen Termine entfernen, damit das eigene CSS verwendet werden kann.
        if (! $this->etLoadCSS) {
            $stream = str_replace('<link rel="stylesheet" type="text/css" href="http://www.evangelische-termine.de/bundles/vket/css/publicintegration.css"  />', '', $stream);
            $stream = str_replace('<link rel="stylesheet" type="text/css" href="https://www.evangelische-termine.de/bundles/vket/css/publicintegration.css"  />', '', $stream);
        }

        // Detailansicht des Teasermoduls im Hauptfenster
        // $etID = substr ( $stream, strpos($stream, 'veranstaltung_im_detail' ) + 23, strpos ( $stream, '.html') - strpos($stream, 'veranstaltung_im_detail') - 23);
        // $stream = str_replace ( 'href="javascript:;" ', 'href="' . get_permalink ( $post->ID ) . '?etID=' , $stream );
        // $stream = str_replace ( 'OnClick="ET_openWindow(\'http://www.evangelische-termine.de/veranstaltung_im_detail', '', $stream );
        // zwischen .html und dem Ende der URL alles ersetzen
        // .html?PHPSESSID=fvro05sp1he2294iq431do0972&popup=1&css=none','Detail','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=650,top=10,left=200');
        // ggf. über eine Schleife alle Ersetzungen durchführen

        // URLs umschreiben
        $stream = str_replace('/Upload/', $this->etProtocol . $this->etHost . '/Upload/', $stream);
        $stream = str_replace('http://_HOST_/?', get_permalink() . '?' . $this->getQuerystring() . '&amp;', $stream);

        // Überschrift "Veranstaltungen" entfernen und <h1>-Überschrift der Evangelischen Termine in ein <h2> umwandeln
        $stream = str_replace('<div id="et_headline"><h1>Veranstaltungen</h1></div>', '', $stream);
        $stream = str_replace('<h1>', '<h2>', $stream);
        $stream = str_replace('</h1>', '</h2>', $stream);

        // Doppelpunkte nach dem Titel korrigieren
        $stream = str_replace(' : <span class="et_content_subtitle">', ': <span class="et_content_subtitle">', $stream);

        // Google-Maps über SSL abrufen
        //$stream = str_replace('http://maps.googleapis.com', 'https://maps.googleapis.com', $stream);

        // Obsolete Elemente entfernen
        $stream = str_replace(' language="javascript"', '', $stream);
        $stream = str_replace(' align="absmiddle"', '', $stream);
        $stream = str_replace('<a name="veranstaltungen" ></a>', '', $stream);

        // SSL für die Icons der Barrierefreiheit anpassen
        if (($this->etProtocol == 'https://') && (strstr($stream, 'http://' . $this->etHost . '/'))) {
            $stream = str_replace('http://' . $this->etHost, $this->etProtocol . $this->etHost, $stream);
        }

        // Hintergrundbilder in der Detailansicht umschreiben
        // @deprecated deprecated since version 2.2.2
        // $stream = str_replace ( '<div id="et_detail_header" style="background-image:url(', '<div id="et_detail_header"><img class="et_header_image" src="', $stream );
        // $stream = str_replace ( ') ">', '"/>', $stream );

        // Bild des Ortes in der Detailansicht
        // Dauerhaft groß anzeigen - Problem: der Defaultzustand wird nicht angezeigt.
        // img-Tags werden durch die Style-Parameter ergänzt -> kein Zugriff von jquery auf die styles.
        // TODO Defaultwert für jquery herausfinden
        // TODO Thumbnail abhängig von den Einstellungen im Optionsmenü ein-/ausschalten
        // $stream = str_replace('id="et_place_image_th"', '"id="et_place_image_th" style="display: none;"', $stream);
        // $stream = str_replace('id="et_place_image"', '"id="et_place_image" style="display: inline;"', $stream);
        // $stream = str_replace('name="et_q"', 'name="q"', $stream);

        return $stream;
    }
}
?>