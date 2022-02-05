<?php
class EtWidget extends WP_Widget {
	
	/**
	 * EtWidget in Wordpress registrieren
	 */
	function __construct() {
		parent::__construct(
				'EtWidget', 
				'Evangelische Termine', 
				array('description' => __('Zeigt eine Liste der nächsten Veranstaltungen an.'))
		);
	}
	
	/**
	 * Formular im Backend aufbauen
	 * 
	 * @param array $instance
	 */
	function form($instance) {
		$etstream = new EtQuery(ET_TEMPLATE_TEASER_WIDGET);
		$etstream->setEtParameters($instance);

		$etstream->setEtParameter('title', 'Nächste Veranstaltungen');
		$instance = $etstream->getEtParameters();

		// Formular einbinden
		// TODO evtl. eigene Klasse für das Formular entwickeln
		include(BASE_PATH. '/application/forms/form.etwidget.php');
	}
	
	/**
	 * Update der Formularfelder durchführen
	 * 
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array 
	 */
	function update($new_instance, $old_instance) {
		$instance = array_merge($old_instance, $new_instance);
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	/**
	 * Widget ausgeben
	 * @param array $args
	 * @param array $instance
	 */
	function widget($args, $instance) {
		// $args in Variablen umwandeln ($before_widget, $after_widget, $before_title, $after_title)
		// => https://codex.wordpress.org/Function_Reference/the_widget
		// => https://developer.wordpress.org/themes/functionality/widgets/
		extract($args); 
		
		$etstream = new EtQuery(ET_TEMPLATE_TEASER_WIDGET);
		$etstream->setNoDetail(true); // Detailansicht für dieses Modul unterdrücken
		if(get_option ('css') != '') {
			$etstream->setLoadCSS(false);
		}
		
		$etstream->setEtParameters($instance);
		
		$title	= apply_filters('widget_title', $instance['title']);
		
		// Titel aus dem Array entfernen (wird für den Abruf nicht benötigt)
		if(array_key_exists('title', $instance)) {
			unset($instance['title']);
		}
		
		$queryString = $etstream->getQuerystring();
		$content = $etstream->retrieveStream($queryString);
		
		// View für die Ausgabe aufrufen
		$view = new EtView('et_sidebar');
		$content = $view->buildView($content);
		
		// $content in den Widget-Code einpacken und ausgeben
		$output = $before_widget;
		
		if(!empty($title)) {
			$output .= $before_title . $title . $after_title;
		}
				
		$output .= $content;
		$output .= $after_widget;
		
		echo $output;
	}
}
?>