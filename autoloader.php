<?php

// Klassen automatisch laden
class AutoloaderET {
	
	/**
	 * AutoloaderET
	 * Registiert die Methode für das automatische Laden der Klassen.
	 * 
	 * @return boolean
	 */
	public static function Register() {
		return spl_autoload_register(array('AutoloaderET', 'Load'));
	}
	
	/**
	 * 
	 * @param string $class
	 * @return boolean (false - Klassendatei existiert nicht oder ist nicht lesbar)
	 */
	private static function Load($class) {
		if (class_exists($class)) {
			return false;
		}
		
		$classDirectories = array(
				BASE_PATH . '/application/controllers/',
				BASE_PATH . '/application/models/',
				BASE_PATH . '/application/views/',
				BASE_PATH . '/application/helper/',
				BASE_PATH . '/application/forms/'
		);
		
		foreach ($classDirectories as $classDirectory) {
			$classFile = $classDirectory . 'class.' . strtolower($class) . '.php';
		
			if(file_exists($classFile) && is_readable($classFile)) {
				require_once $classFile;
				return true;
			}
			
		}
	}
		
}
?>