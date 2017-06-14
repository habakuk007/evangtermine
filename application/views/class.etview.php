<?php
/**
 * View für den Shortcode et_veranstalter. Dem Konstruktor wird die Klasse für den <div>-Wrapper übergeben.
 * @author Norbert Räbiger
 *
 */
class EtView {
	private $wrapcontent = '<div class="###etwrapperclass###">###content###</div>';

	/**
	 * Der Konstruktor baut den Wrapper für die Ausgabe des $content auf.
	 * Der Parameter ist die CSS-Klasse für das umschließende <div>-Tag.
	 * 
	 * @param string $etwrapperclass
	 */
	public function __construct($etwrapperclass = null) {
		if($etwrapperclass != null) {
			$this->wrapcontent = str_replace('###etwrapperclass###', $etwrapperclass, $this->wrapcontent);
		} else {
			$this->wrapcontent = str_replace('###etwrapperclass###', 'et_evangtermine', $this->wrapcontent);
		}
	}
	
	/**
	 * buildView fügt den $content in den umschließenden <div>-Container ein.
	 * 
	 * @param string $content
	 * @return string
	 */
	public function buildView($content) {
		$buildviewcontent = str_replace('###content###', $content, $this->wrapcontent);
		
		return $buildviewcontent;
	}
}
?>