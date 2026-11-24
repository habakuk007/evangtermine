<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Evangtermine_ShortcodePanels {
	
	/**
	 * Verarbeitet die Attribute aus dem Shortcode et_panel und gibt die Daten aus den Evangelischen Terminen aus.
	 * 
	 * @param array $attr
	 * @param string $content
	 * @return string
	 */
	public static function etPanels($attr, $content = null) {
		
		$etstream = new Evangtermine_Query();
		
		// Optionen und Parameter zusammenführen
		$etstream->setEtParameters($attr);
		
		// Querystring erstellen
		$querystring = $etstream->getQuerystring();
		
		// Daten von den Evangelischen Terminen abrufen
		$etstream->setFilename(ET_PANEL_MODUL);
		$content = $etstream->retrieveStream($querystring);
		
		// View für die Ausgabe aufrufen
		$view = new Evangtermine_View('et_panel');
		$content = $view->buildView($content);
		
		return $content;
	}
	
}
?>