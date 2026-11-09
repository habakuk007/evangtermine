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
			
			// TODO Array-Ausgabe mit $output = print_r($data, true)
			if (is_array ( $data )) {
				foreach ( $data as $key => $value ) {
					$output .= $key . ' => ' . $value . '\n';
				}
			} else {
				$output = $data;
			}
			
			echo '<script>console.log(\'' . $description . ': ' . $output . '\');</script>' . PHP_EOL;
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