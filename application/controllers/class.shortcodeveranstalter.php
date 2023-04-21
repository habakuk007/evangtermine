<?php

class ShortcodeVeranstalter
{
    /**
     * Setzt die Session-Werte
     * 
     * @param string $key Ausgabe-Parameter (vgl. http://handbuch.evangelische-termine.de/anzeige-im-internet/ausgabe-parameter)
     * @param object $sess Object für die Session-Werte des Plugins
     * @param object $etparam Object EtQuery
     * @param string $default Default-Werte, wenn der Wert nicht in $sess enthalten ist
     */
    public static function setSessionVar($key, $sess, $etparam, $default = NULL)
    {
        if (array_key_exists($key, $_REQUEST) && $_REQUEST[$key] != "") {
            $sess->{$key} = esc_html($_REQUEST[$key]); // Session-Werte setzen
            $etparam->setEtParameter($key, $sess->{$key}); // Parameter für die Evangelischen Termine setzen
            if ($key != 'pageID') {
                $sess->pageID = 1;
            }
        }
        if (empty($sess->{$key})) {
            $sess->{$key} = $default;
        }
    }
    
    /**
     * Setzt die Werte in $sess auf die Werte zurück, die im Shortcode angegeben wurden oder von der Options-Page stammen.
     * 
     * @param object $sess Object für die Session-Werte des Plugins
     * @param object $defaults Default-Werte aus dem Shortcode oder der Options-Page
     */
    public static function resetSessionVars($sess, $defaults)
    {
        $sess->vid = $defaults->getEtParameter('vid');
        $sess->eventtype = $defaults->getEtParameter('eventtype');
        $sess->highlight = $defaults->getEtParameter('highlight');
        $sess->people = $defaults->getEtParameter('people');
        $sess->pageID = 1;
        $sess->et_q = '';
        $sess->itemsPerPage = $defaults->getEtParameter('itemsPerPage');
        $sess->date = '';
    }
 
    /**
     * Verarbeitet die Attribute aus dem Shortcode et_veranstalter und gibt die Daten aus den Evangelischen Terminen aus.
     *
     * @param array $attr
     * @param string $content
     * @return string
     */
    public static function etVeranstalter($attr, $content = null)
    { 

        // Session-Handling starten
        // Wordpress startet anscheinend immer eine Session, deshalb wird session_start() nicht benötigt
        //if ( !session_id() ) {
        //    session_start();
        //}
        if ( !isset ( $_SESSION['etsession'] ) ) {
            $session = new stdClass;
            $_SESSION['etsession'] = $session;

        } else {
            $session = $_SESSION['etsession'];
        }
        
        // @TODO Default-Werte festlegen und an EtQuery übermitteln
        $etstream = new EtQuery();
        if (get_option('css') != '') {
            $etstream->setLoadCSS(false);
        }

        // Optionen und Parameter zusammenführen
        $etstream->setEtParameters($attr);

        // Wenn im Filter der Evangelischen Termine 'Zurücksetzen' geklickt wurde, werden die Standardwerte,
        // die in EtQuery::__construct() gesetzt werden, nicht verändert.
        // Sonst werden die Werte auf $_REQUEST gesetzt.
        if (isset($_REQUEST['reset']) && $_REQUEST['reset'] == '1') {
            ShortcodeVeranstalter::resetSessionVars($session, $etstream);
        } else {
            ShortcodeVeranstalter::setSessionVar('vid', $session, $etstream, $etstream->getEtParameter('vid'));
            ShortcodeVeranstalter::setSessionVar('region', $session, $etstream, $etstream->getEtParameter('region'));
            ShortcodeVeranstalter::setSessionVar('aid', $session, $etstream, $etstream->getEtParameter('aid'));
            ShortcodeVeranstalter::setSessionVar('date', $session, $etstream, '');
            ShortcodeVeranstalter::setSessionVar('eventtype', $session, $etstream, $etstream->getEtParameter('eventtype'));
            ShortcodeVeranstalter::setSessionVar('highlight', $session, $etstream, $etstream->getEtParameter('highlight'));
            ShortcodeVeranstalter::setSessionVar('people', $session, $etstream, $etstream->getEtParameter('people'));
            ShortcodeVeranstalter::setSessionVar('itemsPerPage', $session, $etstream, $etstream->getEtParameter('itemsPerPage'));
            ShortcodeVeranstalter::setSessionVar('pageID', $session, $etstream, '1');
                        
            if (isset($_REQUEST['et_q']) && $_REQUEST['et_q'] != '') {
                $session->et_q = $_REQUEST['et_q'];
                if (isset($_REQUEST['reset']) && $_REQUEST['reset'] == '1') {
                    $session->et_q = '';
                }
            } else {
                if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'search') {
                    $session->et_q = '';
                }
            }
        }
        
        // Parameter für die Suche sichern
        if (array_key_exists('et_q', $_REQUEST)) {
            if ($_REQUEST['et_q'] != '') { // ($_REQUEST['et_q'] <> $_REQUEST['q']){
                $etstream->setEtParameter('q', $_REQUEST['et_q']);
            }
        }
        
        // Querystring erstellen
        $querystring = $etstream->getQuerystring();

        // URL-Parameter, die nicht von den Evangelischen Terminen stammen zusammenstellen
        // $etURLParameters identifiziert die Parameter der Evangelischen Termine.
        // Werte, die nicht in diesem Array enthalten sind, werden vor dem Abruf der Evangelischen Termine an die URL angehängt.
        $etURLParameters = array(
            'vid',
            'region',
            'aid',
            'date',
            'highlight',
            'eventtype',
            'people',
            'et_q',
            'place',
            'person',
            'ipm',
            'cha',
            'until',
            'itemsPerPage',
            'pageID',
            'encoding',
            'css',
            'etID',
            'Suche',
            'action',
            session_name(),
            '_token',
            'reset',
            'dest',
            'q'
        );
        foreach ($_REQUEST as $key => $val) {
            if (! in_array($key, $etURLParameters)) {
                $querystring .= '&' . $key . '=' . esc_html($val);
                Debugging::consoleLog($key . ': ', esc_html($val));
            }
        }

        // Daten von den Evangelischen Terminen abrufen
        $content = $etstream->retrieveStream($querystring);

        // View für die Ausgabe aufrufen
        $view = new EtView('et_veranstalter');
        $content = $view->buildView($content);

        return $content;
    }
       
}
?>