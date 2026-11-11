<?php
class EtWidget extends WP_Widget {
	
	/**
	 * EtWidget in Wordpress registrieren
	 */
	function __construct() {
		parent::__construct(
				'EtWidget', 
				'Evangelische Termine', 
				array('description' => __('Zeigt eine Liste der nächsten Veranstaltungen an.', 'evangtermine'))
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
		// Felder, die nicht in den Grundeinstellungen vorkommen, vorbelegen (sonst "Undefined array key" ab PHP 8)
		$instance = wp_parse_args($etstream->getEtParameters(), array(
			'auswahl'              => '',
			'additionalParameters' => ''
		));

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
		$new_instance = is_array($new_instance) ? $new_instance : array();
		$old_instance = is_array($old_instance) ? $old_instance : array();

		// Nur die Felder des Formulars übernehmen (form.etwidget.php), alles andere wird verworfen.
		$textFields = array('title', 'vid', 'region', 'eventtype', 'place', 'ipm', 'cha', 'itemsPerPage', 'q');
		// Auswahlfelder mit ihren erlaubten Werten; der erste Wert ist der Standard.
		$selectFields = array(
			'highlight' => array('all', 'high'),
			'auswahl'   => array('', 'eb'),
			'dest'      => array('extern', 'intern', 'all'),
			'until'     => array('yes', 'no')
		);

		$instance = array();

		foreach ($textFields as $field) {
			if (isset($new_instance[$field]) && is_scalar($new_instance[$field])) {
				$instance[$field] = sanitize_text_field((string) $new_instance[$field]);
			} elseif (isset($old_instance[$field]) && is_scalar($old_instance[$field])) {
				$instance[$field] = sanitize_text_field((string) $old_instance[$field]);
			}
		}

		foreach ($selectFields as $field => $allowed) {
			foreach (array($new_instance, $old_instance) as $source) {
				if (isset($source[$field]) && is_scalar($source[$field]) && in_array((string) $source[$field], $allowed, true)) {
					$instance[$field] = (string) $source[$field];
					break;
				}
			}
			if (! isset($instance[$field])) {
				$instance[$field] = $allowed[0];
			}
		}

		// Weitere Parameter: fertiger Teil-Querystring, nur URL-taugliche Zeichen (wie in EtQuery::getQuerystring).
		$additional = '';
		if (isset($new_instance['additionalParameters']) && is_scalar($new_instance['additionalParameters'])) {
			$additional = (string) $new_instance['additionalParameters'];
		} elseif (isset($old_instance['additionalParameters']) && is_scalar($old_instance['additionalParameters'])) {
			$additional = (string) $old_instance['additionalParameters'];
		}
		$instance['additionalParameters'] = preg_replace('/[^A-Za-z0-9_=&%.,+:~\-]/', '', $additional);

		// Das Widget verwendet immer das Teaser-Template für die Sidebar.
		$instance['tpl'] = ET_TEMPLATE_TEASER_WIDGET;

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
		$args = wp_parse_args($args, array(
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => ''
		));

		$etstream = new EtQuery(ET_TEMPLATE_TEASER_WIDGET);
		$etstream->setNoDetail(true); // Detailansicht für dieses Modul unterdrücken
		if(get_option ('css') != '') {
			$etstream->setLoadCSS(false);
		}
		
		$etstream->setEtParameters($instance);
		
		// Der Titel wird beim Aufbau des Querystrings übersprungen (EtQuery::getQuerystring) und hier nur ausgegeben.
		$title	= apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '');

		$queryString = $etstream->getQuerystring();
		$content = $etstream->retrieveStream($queryString);
		
		// View für die Ausgabe aufrufen
		$view = new EtView('et_sidebar');
		$content = $view->buildView($content);
		
		// $content in den Widget-Code einpacken und ausgeben
		$output = $args['before_widget'];

		if(!empty($title)) {
			$output .= $args['before_title'] . esc_html($title) . $args['after_title'];
		}

		$output .= $content;
		$output .= $args['after_widget'];
		
		echo $output;
	}
}
?>