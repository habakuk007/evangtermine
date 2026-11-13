<?php

class ShortcodeVeranstalter
{
    /**
     * Parameter, die als ganze Zahl behandelt werden.
     *
     * @var array
     */
    private static $numericKeys = array('pageID', 'itemsPerPage', 'people');

    /**
     * Liefert den bereinigten Wert eines Request-Parameters (GET oder POST, nie Cookies) oder null, wenn er fehlt.
     * Die Werte werden nicht escaped, sondern erst beim Aufbau der URL (urlencode) bzw. der Ausgabe.
     *
     * @param string $key Name des Parameters
     * @return string|null
     */
    public static function getRequestValue($key)
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- öffentliche Filter-Parameter, kein Formular mit Nonce
        $source = array_merge($_GET, $_POST);
        // phpcs:enable
        if (! isset($source[$key]) || ! is_scalar($source[$key])) {
            return null;
        }
        $value = sanitize_text_field(wp_unslash((string) $source[$key]));
        if (in_array($key, self::$numericKeys, true)) {
            $value = (string) absint($value);
        }
        return $value;
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

        // @TODO Default-Werte festlegen und an EtQuery übermitteln
        $etstream = new EtQuery();
        if (get_option('css') != '') {
            $etstream->setLoadCSS(false);
        }

        // Optionen und Parameter zusammenführen
        $etstream->setEtParameters($attr);

        // Die Filterwerte stehen nur in der URL, es gibt keine PHP-Session: Die Links und das Formular im HTML der
        // Evangelischen Termine führen auf diese Seite zurück und enthalten die aktuellen Werte.
        // Wenn im Filter 'Zurücksetzen' geklickt wurde (reset=1), bleiben die Standardwerte aus dem Shortcode
        // bzw. von der Options-Page erhalten. Sonst werden die Werte aus dem Request übernommen.
        if ('1' !== self::getRequestValue('reset')) {
            foreach (array('vid', 'region', 'aid', 'date', 'eventtype', 'highlight', 'people', 'itemsPerPage', 'pageID') as $key) {
                $value = self::getRequestValue($key);
                if (null !== $value && '' !== $value) {
                    $etstream->setEtParameter($key, $value);
                }
            }
        }

        // Parameter für die Suche sichern
        $searchTerm = self::getRequestValue('et_q');
        if (null !== $searchTerm && '' !== $searchTerm) {
            $etstream->setEtParameter('q', $searchTerm);
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
            session_name(), // z.B. PHPSESSID: steht im Formular der Evangelischen Termine und wird nicht weitergegeben.
            '_token',
            'reset',
            'dest',
            'q'
        );
        // GET und POST (keine Cookies): Namen und Werte werden bereinigt, die Werte URL-kodiert.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- öffentliche Filter-Parameter, kein Formular mit Nonce
        $requestKeys = array_keys(array_merge($_GET, $_POST));
        // phpcs:enable
        foreach ($requestKeys as $key) {
            $cleanKey = EtQuery::cleanParameterName($key);
            if ('' === $cleanKey || in_array($cleanKey, $etURLParameters, true)) {
                continue;
            }
            $val = self::getRequestValue($key);
            if (null === $val) {
                continue; // Arrays und andere Nicht-Skalare werden nicht weitergegeben
            }
            $querystring .= '&' . $cleanKey . '=' . urlencode($val);
            Debugging::consoleLog($cleanKey . ': ', $val);
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