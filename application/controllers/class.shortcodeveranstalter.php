<?php
class ShortcodeVeranstalter {
	
	/**
	 * Verarbeitet die Attribute aus dem Shortcode et_veranstalter und gibt die Daten aus den Evangelischen Terminen aus.
	 *
	 * @param array $attr
	 * @param string $content
	 * @return string
	 */
	public function etVeranstalter($attr, $content = null) {
		// $etURLParameters identifiziert die Parameter der Evangelischen Termine.
		// Werte, die nicht in diesem Array enthalten sind, werden vor dem Abruf der Evangelischen Termine an die URL angehängt.
		$etURLParameters = array(
				'vid', 'region', 'aid', 'date', 'highlight', 'eventtype', 'people', 'et_q', 'place',
				'person', 'ipm', 'cha', 'until', 'itemsPerPage', 'pageID', 'encoding', 'css', 'etID',
				'Suche', 'action', session_name(), '_token', 'reset', 'dest', 'q'
		);
		// $filterVars beinhaltet die Felder, die vom Filter des Veranstaltermoduls zur Verfügung gestellt werden.
		// Die Werte werden über $_REQUEST gesetzt, wenn nicht ein Reset ausgelöst wurde.
		$filterVars = array('vid', 'region', 'eventtype', 'highlight', 'people', 'et_q', 'q', 'itemsPerPage', 'pageID', 'date');
		
		$etstream = new EtQuery();
		if(get_option ('css') != '') {
			$etstream->setLoadCSS(false);
		}
		
		// Optionen und Parameter zusammenführen
		$etstream->setEtParameters($attr);
		
		// Wenn im Filter der Evangelischen Termine 'Zurücksetzen' geklickt wurde, werden die Standardwerte,
		// die in EtQuery::__construct() gesetzt werden, nicht verändert.
		// Sonst werden die Werte auf $_REQUEST gesetzt.
		if($_REQUEST['reset'] != '1') {
			// Werte aus $_REQUEST zuweisen. Wenn das nicht möglich ist auf den Wert des jeweiligen Parameters.
			foreach($filterVars as $key) {
				if($_REQUEST[$key] != '') {
					$etstream->setEtParameter($key, $_REQUEST[$key]);
				}
			}
		} else {
			// Suchbegriff zurücksetzen
			$_REQUEST['q'] = '';
			$_REQUEST['et_q'] = '';
		}
		
		if ($_REQUEST['q'] <> '') {
			$etstream->setEtParameter('et_q', $_REQUEST['q']);
		}
				
		// Querystring erstellen
		$querystring = $etstream->getQuerystring();
		
		// URL-Parameter, die nicht von den Evangelischen Terminen stammen zusammenstellen
		foreach($_REQUEST as $key => $val){
			if(!in_array($key,$etURLParameters)){
				$querystring .= '&' . $key . '='. $val;
			}
		}
		
		// Suchbegriff an Querystring anhängen - Problem bei den Evangelischen Terminen mit et_q und q.
		$querystring .= '&' . 'et_q' . '=' . $etstream->getEtParameter('et_q');
		$querystring .= '&' . 'q' . '=' . $etstream->getEtParameter('et_q');
		
		// Daten von den Evangelischen Terminen abrufen
		$content = $etstream->retrieveStream($querystring);
		
		// View für die Ausgabe aufrufen
		$view = new EtView('et_veranstalter');
		$content = $view->buildView($content);
		
		return $content;
	}
	
}
?>