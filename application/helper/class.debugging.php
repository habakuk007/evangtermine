<?php
class Debugging {
	
	/**
	 * consoleLog
	 *
	 * @param string $key
	 * @param mixed $data
	 */
	public static function consoleLog($description, $data) {
		if (DEBUG) {
			$output = '';
			
			if (is_array ( $data )) {
				foreach ( $data as $key => $value ) {
					$output .= $key . ' => ' . ( is_scalar( $value ) ? $value : wp_json_encode( $value ) ) . "\n";
				}
			} else {
				$output = is_scalar( $data ) ? (string) $data : wp_json_encode( $data );
			}
			
			// Nur Debug-Ausgabe: als JSON-String ausgeben, damit Werte aus dem Request das Script nicht verlassen können.
			echo '<script>console.log(' . wp_json_encode( $description . ': ' . $output, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ');</script>' . PHP_EOL;
		}
	}
	
	/**
	 * writeOutputStream
	 *
	 * @param string $filename
	 * @param string $outputStream
	 */
	public static function writeOutputStream($filename, $outputStream) {
		if (DEBUG) {
			$fb = fopen ( $filename, "w+" );
			fwrite ( $fb, $outputStream );
			fclose ( $fb );
		}
	}
}
?>