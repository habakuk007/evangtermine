<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Klassen automatisch laden
class Evangtermine_Autoloader {
	
	/**
	 * Evangtermine_Autoloader
	 * Registiert die Methode für das automatische Laden der Klassen.
	 * 
	 * @return boolean
	 */
	public static function Register() {
		return spl_autoload_register(array('Evangtermine_Autoloader', 'Load'));
	}
	
	/**
	 * 
	 * @param string $class
	 * @return boolean (false - Klassendatei existiert nicht oder ist nicht lesbar)
	 */
	private static function Load($class) {
		if (class_exists($class, false)) {
			return false;
		}
		
		$classDirectories = array(
				ET_BASE_PATH . '/application/controllers/',
				ET_BASE_PATH . '/application/models/',
				ET_BASE_PATH . '/application/views/',
				ET_BASE_PATH . '/application/helper/',
				ET_BASE_PATH . '/application/forms/'
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