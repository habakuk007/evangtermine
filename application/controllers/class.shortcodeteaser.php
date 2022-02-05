<?php
class ShortcodeTeaser {
	
	/**
	 * Verarbeitet die Attribute aus dem Shortcode et_teaser und gibt die Daten aus den Evangelischen Terminen aus.
	 * 
	 * @param array $attr
	 * @param string $content
	 * @return string
	 */
	public static function etTeaser($attr, $content = null) {
		
		$etstream = new EtQuery(ET_TEMPLATE_TEASER_SHORTCODE);
		$etstream->setNoDetail(true); // Detailansicht für dieses Modul unterdrücken
		if(get_option ('css') != '') {
			$etstream->setLoadCSS(false);
		}
		
		// Optionen und Parameter zusammenführen
		$etstream->setEtParameters($attr);
		
		// Querystring erstellen
		$querystring = $etstream->getQuerystring();
		
		// Daten von den Evangelischen Terminen abrufen
		$content = $etstream->retrieveStream($querystring);
		
		// View für die Ausgabe aufrufen
		$view = new EtView('et_teaser');
		$content = $view->buildView($content);
		
		return $content;
	}
	
}
?>