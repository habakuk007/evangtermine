<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Evangtermine_ShortcodeVeranstalter
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
        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- öffentliche Filter-Parameter, kein Formular mit Nonce
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

        // @TODO Default-Werte festlegen und an Evangtermine_Query übermitteln
        $etstream = new Evangtermine_Query();
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

        // Suchbegriff: Das Suchformular sendet 'et_q', die Links (z.B. zur Seite 2) in den Evangelischen Terminen
        // enthalten 'q'. 'et_q' hat Vorrang. Bei reset=1 wird der Suchbegriff aus dem Request ignoriert, ein 'q'-Default
        // aus dem Shortcode bleibt erhalten.
        if ('1' !== self::getRequestValue('reset')) {
            $searchTerm = self::getRequestValue('et_q');
            if (null === $searchTerm) {
                $searchTerm = self::getRequestValue('q');
            }
            if (null !== $searchTerm && '' !== $searchTerm) {
                $etstream->setEtParameter('q', $searchTerm);
            }
        }

        // Querystring erstellen
        $querystring = $etstream->getQuerystring();

        // Andere Request-Parameter werden bewusst nicht an die Evangelischen Termine weitergegeben (Allowlist): Gibt der
        // Kalenderserver einen Wert ungefiltert wieder, wäre das sonst reflektiertes XSS auf dieser Seite (Issue #41).
        // Weitere Parameter setzt der Seitenautor über Shortcode-Attribute.

        // Daten von den Evangelischen Terminen abrufen
        $content = $etstream->retrieveStream($querystring);

        // View für die Ausgabe aufrufen
        $view = new Evangtermine_View('et_veranstalter');
        $content = $view->buildView($content);

        return $content;
    }
       
}
?>