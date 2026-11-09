<?php
class ShortcodePanels {
	
	/**
	 * Verarbeitet die Attribute aus dem Shortcode et_panel und gibt die Daten aus den Evangelischen Terminen aus.
	 * 
	 * @param array $attr
	 * @param string $content
	 * @return string
	 */
	public static function etPanels($attr, $content = null) {
		
		$etstream = new EtQuery();
// 		$etstream->setNoDetail(true); // Detailansicht für dieses Modul unterdrücken
// 		if(get_option ('css') != '') {
// 			$etstream->setLoadCSS(false);
// 		}
		
		// Optionen und Parameter zusammenführen
		$etstream->setEtParameters($attr);
		
		// Querystring erstellen
		$querystring = $etstream->getQuerystring();
		
		// Daten von den Evangelischen Terminen abrufen
		$etstream->setFilename(ET_PANEL_MODUL);
		$content = $etstream->retrieveStream($querystring);
		
		// View für die Ausgabe aufrufen
		$view = new EtView('et_panel');
		$content = $view->buildView($content);
		
		return $content;
	}
	
}
?>