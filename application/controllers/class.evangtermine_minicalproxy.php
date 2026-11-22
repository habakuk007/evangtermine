<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Same-origin proxy für den Mini-Kalender ([et_minical]).
 *
 * Das Skript des Mini-Kalenders ruft die Daten nicht mehr aus dem Browser der Besucher bei den Evangelischen Terminen ab
 * (das hätte die IP-Adresse der Besucher übermittelt, Issue #33), sondern über zwei öffentliche, lesende REST-Routen
 * der eigenen Website. Der Server ruft die Evangelischen Termine ab, mit dem Host und Protokoll aus den Einstellungen.
 *
 * - GET /wp-json/evangtermine/v1/minical/events: Veranstaltungen eines Monats (JSON)
 * - GET /wp-json/evangtermine/v1/minical/detail: Detailansicht einer Veranstaltung (HTML für den Colorbox-Iframe)
 *
 * Nur die Parameter aus der Positivliste werden weitergegeben, alle Werte werden bereinigt und URL-kodiert.
 */
class Evangtermine_MinicalProxy
{

    const REST_NAMESPACE = 'evangtermine/v1';

    // Parameter, die der Mini-Kalender an die Evangelischen Termine weitergibt (alles andere wird verworfen).
    const FILTER_PARAMETERS = array(
        'vid', 'region', 'aid', 'eventtype', 'highlight', 'people', 'person', 'place', 'ipm', 'cha',
        'auswahl', 'itemsPerPage', 'dest', 'until', 'q', 'encoding', 'css'
    );

    // Höchstlänge eines Parameterwerts.
    const MAX_VALUE_LENGTH = 200;

    // Timeout für den Abruf in Sekunden (Filter 'evangtermine_http_timeout', begrenzt auf 1 bis 30).
    const DEFAULT_TIMEOUT = 10;

    /**
     * Registriert die Routen (Hook rest_api_init).
     */
    public static function registerRoutes()
    {
        register_rest_route(self::REST_NAMESPACE, '/minical/events', array(
            'methods'             => 'GET',
            'callback'            => array(__CLASS__, 'handleEvents'),
            'permission_callback' => '__return_true' // öffentlich, nur lesend
        ));
        register_rest_route(self::REST_NAMESPACE, '/minical/detail', array(
            'methods'             => 'GET',
            'callback'            => array(__CLASS__, 'handleDetail'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Adressen der beiden Routen für das Skript des Mini-Kalenders.
     *
     * @return array
     */
    public static function getUrls()
    {
        return array(
            'eventsUrl' => rest_url(self::REST_NAMESPACE . '/minical/events'),
            'detailUrl' => rest_url(self::REST_NAMESPACE . '/minical/detail')
        );
    }

    /**
     * Baut aus der Anfrage die Parameter für die Evangelischen Termine. Nur Parameter aus der Positivliste.
     *
     * @param array $params Abfrageparameter der Anfrage
     * @return array|WP_Error Parameter (name => bereinigter Wert)
     */
    public static function buildEventsQuery($params)
    {
        $query = array();
        foreach (self::FILTER_PARAMETERS as $name) {
            if (isset($params[$name]) && is_scalar($params[$name])) {
                $value = self::cleanValue($params[$name]);
                if ('' !== $value) {
                    $query[$name] = $value;
                }
            }
        }

        // Monat im Format M.JJ oder MM.JJ
        $month = isset($params['month']) && is_scalar($params['month']) ? trim((string) $params['month']) : '';
        if (! preg_match('/^(?:0?[1-9]|1[0-2])\.[0-9]{2}$/', $month)) {
            return new WP_Error('evangtermine_bad_request', 'Invalid month.', array('status' => 400));
        }

        $day = isset($params['d']) && is_scalar($params['d']) ? trim((string) $params['d']) : '0';
        if (! preg_match('/^[0-9]{1,2}$/', $day)) {
            return new WP_Error('evangtermine_bad_request', 'Invalid day.', array('status' => 400));
        }

        $query['d'] = $day;
        $query['month'] = $month;
        return $query;
    }

    /**
     * Bereinigt einen Parameterwert: Text ohne Tags, Steuerzeichen und überflüssige Leerzeichen, höchstens 200 Zeichen.
     *
     * @param mixed $value
     * @return string
     */
    private static function cleanValue($value)
    {
        $value = sanitize_text_field(wp_unslash((string) $value));
        return mb_substr($value, 0, self::MAX_VALUE_LENGTH);
    }

    /**
     * Route .../minical/events
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function handleEvents($request)
    {
        $query = self::buildEventsQuery($request->get_query_params());
        if (is_wp_error($query)) {
            return $query;
        }

        $result = self::fetch('json', $query, 'application/json');
        if (is_wp_error($result)) {
            return $result;
        }

        $data = json_decode($result['body'], true);
        if (! is_array($data)) {
            return new WP_Error('evangtermine_bad_response', 'The event calendar sent an unexpected answer.', array('status' => 502));
        }

        $response = new WP_REST_Response($data);
        $response->header('Cache-Control', 'public, max-age=' . self::getCacheTtl());
        return $response;
    }

    /**
     * Route .../minical/detail
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function handleDetail($request)
    {
        $params = $request->get_query_params();
        $id = isset($params['ID']) && is_scalar($params['ID']) && preg_match('/^[0-9]{1,10}$/', (string) $params['ID']) ? absint($params['ID']) : 0;
        if ($id < 1) {
            return new WP_Error('evangtermine_bad_request', 'Invalid ID.', array('status' => 400));
        }

        $result = self::fetch('detail-bt', array('ID' => $id), 'text/html');
        if (is_wp_error($result)) {
            return $result;
        }

        // Relative Adressen (Stylesheet, Bilder, Links) der Detailansicht beziehen sich auf den Host der Evangelischen Termine.
        $html = $result['body'];
        $base = '<base href="' . esc_url($result['base'] . '/') . '">';
        $count = 0;
        $withBase = preg_replace('/(<head\b[^>]*>)/i', '$1' . $base, $html, 1, $count);
        $html = ($count && null !== $withBase) ? $withBase : $base . $html;

        $response = new WP_REST_Response($html);
        $response->header('Content-Type', $result['contentType']);
        $response->header('Cache-Control', 'public, max-age=' . self::getCacheTtl());
        // Fremder Inhalt im Iframe der eigenen Website: in eine eigene Origin sperren und nicht umdeuten lassen.
        $response->header('Content-Security-Policy', 'sandbox allow-scripts allow-popups allow-popups-to-escape-sandbox');
        $response->header('X-Content-Type-Options', 'nosniff');

        // Das HTML wird unverändert ausgegeben, nicht als JSON.
        add_filter('rest_pre_serve_request', array(__CLASS__, 'serveRaw'), 10, 4);
        return $response;
    }

    /**
     * Gibt die Antwort der Route .../minical/detail als HTML aus (Filter rest_pre_serve_request).
     *
     * @param bool             $served
     * @param WP_HTTP_Response $result
     * @param WP_REST_Request  $request
     * @param WP_REST_Server   $server
     * @return bool
     */
    public static function serveRaw($served, $result, $request, $server)
    {
        if (! is_string($result->get_data()) || false === strpos((string) $request->get_route(), '/minical/detail')) {
            return $served;
        }
        $headers = $result->get_headers();
        if (isset($headers['Content-Type'])) {
            header('Content-Type: ' . $headers['Content-Type']);
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inhalt der Evangelischen Termine, bewusst unverändert (Iframe mit sandbox)
        echo $result->get_data();
        return true;
    }

    /**
     * Ruft ein Modul der Evangelischen Termine ab (Host und Protokoll aus den Einstellungen), mit kurzem Cache.
     *
     * @param string $module       Modul (json, detail-bt)
     * @param array  $query        Bereinigte Parameter
     * @param string $expectedType Erwarteter Content-Type (Anfang)
     * @return array|WP_Error array('body' => ..., 'contentType' => ..., 'base' => ...)
     */
    private static function fetch($module, $query, $expectedType)
    {
        $et = new Evangtermine_Query();
        $base = $et->getRemoteBaseUrl();
        if ('' === $base) {
            return new WP_Error('evangtermine_unavailable', 'The event calendar is currently unavailable.', array('status' => 503));
        }

        $url = $base . '/' . $module . '?' . http_build_query($query);

        $ttl = self::getCacheTtl();
        $cacheKey = 'et_stream_minical_' . md5(wp_json_encode(array(ET_VERSION, $url)));
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached) && isset($cached['body'], $cached['contentType'])) {
                $cached['base'] = $base;
                return $cached;
            }
        }

        $response = wp_remote_get($url, array(
            'timeout'            => self::getTimeout(),
            'reject_unsafe_urls' => true,
            'user-agent'         => 'evangtermine/' . ET_VERSION . ' Wordpress-Plugin'
        ));
        if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
            return new WP_Error('evangtermine_unavailable', 'The event calendar is currently unavailable.', array('status' => 502));
        }

        $contentType = (string) wp_remote_retrieve_header($response, 'content-type');
        if (0 !== stripos($contentType, $expectedType)) {
            // Eine Detailansicht muss HTML sein. Bei JSON ist die Angabe des Hosts nicht verlässlich, die Prüfung folgt am Inhalt.
            if ('text/html' === $expectedType) {
                return new WP_Error('evangtermine_bad_response', 'The event calendar sent an unexpected answer.', array('status' => 502));
            }
            $contentType = $expectedType;
        }

        $result = array(
            'body'        => (string) wp_remote_retrieve_body($response),
            'contentType' => $contentType
        );
        if ($ttl > 0 && '' !== $result['body']) {
            set_transient($cacheKey, $result, $ttl);
        }
        $result['base'] = $base;
        return $result;
    }

    /**
     * Lebensdauer des Caches in Sekunden (Filter 'evangtermine_cache_ttl', Standard 5 Minuten, 0 = kein Cache).
     *
     * @return int
     */
    private static function getCacheTtl()
    {
        return max(0, (int) apply_filters('evangtermine_cache_ttl', 5 * MINUTE_IN_SECONDS));
    }

    /**
     * Timeout für den Abruf in Sekunden (Filter 'evangtermine_http_timeout', begrenzt auf 1 bis 30).
     *
     * @return int
     */
    private static function getTimeout()
    {
        return min(30, max(1, (int) apply_filters('evangtermine_http_timeout', self::DEFAULT_TIMEOUT)));
    }
}
