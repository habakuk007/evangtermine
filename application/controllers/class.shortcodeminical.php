<?php

class ShortcodeMinical
{

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
        // $etstream->setNoDetail(true); // Detailansicht für dieses Modul unterdrücken
        // if(get_option ('css') != '') {
        // $etstream->setLoadCSS(false);
        // }

        // Optionen und Parameter zusammenführen
        $etstream->setEtParameters($attr);

        // Querystring erstellen
        $etparameters = $etstream->getEtParameters();

        // Ausgabe vorbereiten
        $content .= "
<!-- Externe CSS und Javascipt-Dateien laden: -->

<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css\">
<link rel=\"stylesheet\" href=\"//evangelische-termine.de/bundles/vket/css/colorbox.css\">
<script src=\"//evangelische-termine.de/bundles/vket/js/et_minical.js\"></script>

 
<!-- Formattierungen für den Tooltip: -->
<style type=\"text/css\">
  .et_minical_hr {
    border: 0; height: 1px; background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0));
  }
  .et_minical_placename {
    color:#666666;
    font-size:80%;
  }
  .et_minical_time {
    font-weight: bold;
  }
  a.et_minical_link {
    text-decoration: none;
  }
  .et_minical_title {
  }
  .ui_widget {
    font-family: 'Lato',Helvetica,Arial,Lucida,sans-serif;
  }
  .entry-content tr td, body.et-pb-preview #main-content .container tr td {
    border-top: 1px solid #eee;
    padding: 0px 0px;
  }
	.entry-content tr th, .entry-content thead th, body.et-pb-preview #main-content .container tr th, body.et-pb-preview #main-content .container thead th {
    color: #555;
  	font-weight: bold;
    padding: 0px;
	}
</style>
 
<script>
    /* Weitere Parameter können eingefügt werden */
    var parameter = {
";

        // Parameter in "key: '<parameter>'," übertragen
        Debugging::consoleLog("querystring", $etparameters);
        foreach ($etparameters as $key => $value) {
            $content .= $key . " : '" . $value ."',";
        }
        $content .= "
    };
    var host = 'evangelische-termine.de';
</script>
 
<!-- Hier erscheint der Minikalender: -->
<div id=\"et_minicalendar\"></div>";

        return $content;
    }
}
?>