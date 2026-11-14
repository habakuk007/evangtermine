<?php

class ShortcodeMinical
{

    /**
     * Registriert die Skripte und Stylesheets des Mini-Kalenders (Hook wp_enqueue_scripts).
     * jQuery, jQuery UI und moment kommen aus WordPress; mitgeliefert werden nur Colorbox, das Stylesheet von jQuery UI
     * (WordPress liefert für das Frontend keines aus) und das eigene Skript.
     * Enthält der aufgerufene Beitrag den Shortcode, werden die Stylesheets gleich im Kopf der Seite eingebunden.
     */
    public static function registerAssets()
    {
        $baseUrl = plugins_url('assets/', BASE_PATH . '/evangtermine.php');

        wp_register_style('evangtermine-jquery-ui', $baseUrl . 'ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1');
        wp_register_style('evangtermine-colorbox', $baseUrl . 'styles/colorbox.css', array(), '1.6.4');
        wp_register_style('evangtermine-minical', $baseUrl . 'styles/et_minical.css', array('evangtermine-jquery-ui', 'evangtermine-colorbox'), ET_VERSION);

        wp_register_script('evangtermine-colorbox', $baseUrl . 'scripts/jquery.colorbox-min.js', array('jquery'), '1.6.4', true);
        wp_register_script(
            'evangtermine-minical',
            $baseUrl . 'scripts/et_minical.js',
            array('jquery', 'jquery-ui-datepicker', 'jquery-ui-tooltip', 'moment', 'evangtermine-colorbox'),
            ET_VERSION,
            true
        );

        $post = get_post();
        if (is_singular() && $post instanceof WP_Post && has_shortcode((string) $post->post_content, 'et_minical')) {
            wp_enqueue_style('evangtermine-minical');
        }
    }

    /**
     * Verarbeitet die Attribute aus dem Shortcode et_minical und gibt den Mini-Kalender der Evangelischen Termine aus.
     *
     * @param array $attr
     * @param string $content
     * @return string
     */
    public static function etMinical($attr, $content = null)
    {
        $etstream = new EtQuery();

        // Optionen und Parameter zusammenführen
        $etstream->setEtParameters($attr);

        if (! wp_script_is('evangtermine-minical', 'registered')) {
            self::registerAssets();
        }

        // Stylesheets und Skripte einbinden. Wird der Shortcode außerhalb des Beitragsinhalts verwendet (z.B. in einem Widget),
        // gibt WordPress die Stylesheets im Fuß der Seite aus.
        wp_enqueue_style('evangtermine-minical');
        wp_enqueue_script('evangtermine-minical');

        // Parameter und Adresse der Evangelischen Termine an das Skript übergeben (nur einmal je Seite).
        if (! wp_script_is('evangtermine-minical', 'done') && ! wp_scripts()->get_data('evangtermine-minical', 'before')) {
            $data = array(
                'parameters' => $etstream->getEtParameters(),
                'baseUrl'    => $etstream->getBrowserBaseUrl()
            );
            wp_add_inline_script(
                'evangtermine-minical',
                'var etMinicalData = ' . wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';',
                'before'
            );
        }

        // Hier erscheint der Minikalender:
        $content .= '<div id="et_minicalendar"></div>';

        return $content;
    }
}
