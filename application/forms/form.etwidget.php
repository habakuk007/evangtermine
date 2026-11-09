<?php

/**
 * Formular für die Widget-Einstellungen im Wordpress-Backend
 * Design -> Widgets -> Evangelische Termine
 */

?>
	<p>Titel: 
		<input name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
	</p>
	<p>Veranstalter-ID (kommaseparierte Liste, keine Leerzeichen): 
		<input name="<?php echo $this->get_field_name( 'vid' ); ?>" type="text" value="<?php echo esc_attr( $instance['vid'] ); ?>" />
	</p>
	<p>Dekanats-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'region' ); ?>" type="text" value="<?php echo esc_attr( $instance['region'] ); ?>" />
	</p>
	<p>Eventtype-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'eventtype' ); ?>" type="text" value="<?php echo esc_attr( $instance['eventtype' ] ); ?>" />
	</p>
	<p>Highlight (all/high):
		<select name="<?php echo $this->get_field_name( 'highlight' ); ?>">
			<?php
			$items = array( 'all', 'high' );
			foreach ($items as $item) {
			 	 $selected = ( $instance['highlight'] == $item ) ? 'selected="selected"' : '';
			 	 echo '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
			} 
			?>
		</select>
	</p>
	<p>Veranstaltungsort-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'place' ); ?>" type="text" value="<?php echo esc_attr( $instance['place'] ); ?>" />
	</p>
	<p>Veranstaltungstyp-ID (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'ipm' ); ?>" type="text" value="<?php echo esc_attr( $instance['ipm'] ); ?>" />
	</p>
	<p>Erwachsenenbildung (bei Auswahl von 'eb' werden die Felder Veranstaltungstyp-ID und Kanäle ignoriert):
		<select name="<?php echo $this->get_field_name( 'auswahl') ?>" >
			<?php
			$items = array( '', 'eb' );
			foreach ($items as $item) {
				$selected = ( $instance['auswahl'] == $item ) ? 'selected="selected"' : '';
				echo '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
			}
			?>
		</select>
	</p>	
	<p>Kanäle (kommaseparierte Liste, keine Leerzeichen):
		<input name="<?php echo $this->get_field_name( 'cha' ); ?>" type="text" value="<?php echo esc_attr( $instance['cha'] ); ?>" />
	</p>
	<p>itemsPerPage:
		<input name="<?php echo $this->get_field_name( 'itemsPerPage' ); ?>" type="text" value="<?php echo esc_attr( $instance['itemsPerPage'] ); ?>" />
	</p>
	<p>Anzeige (extern/intern/all):
		<select name="<?php echo $this->get_field_name( 'dest' ); ?>">
			<?php
			$items = array( 'extern', 'intern', 'all' );
			foreach ($items as $item) {
			 	 $selected = ( $instance['dest'] == $item ) ? 'selected="selected"' : '';
			 	 echo '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
			} 
			?>
		</select>
	</p>
	<p>Anzeige der Veranstaltung bis zum Enddatum (yes/no):
		<select name="<?php echo $this->get_field_name( 'until' ); ?>">
			<?php
			$items = array( 'yes', 'no' );
			foreach ($items as $item) {
			 	 $selected = ( $instance['until'] == $item ) ? 'selected="selected"' : '';
			 	 echo '<option value="' . $item . '" ' . $selected . '>' . $item . '</option>';
			} 
			?>
		</select>
		<!-- <input name="<?php echo $this->get_field_name( 'until' ); ?>" type="text" value="<?php echo esc_attr( $instance['until'] ); ?>" /> -->
	</p>
	<p>Suchbegriffe (durch Komma getrennte Liste):
		<input name="<?php echo $this->get_field_name( 'q' ); ?>" type="text" value="<?php echo esc_attr( $instance['q'] ); ?>" />
	</p>
	<input name="<?php echo $this->get_field_name( 'tpl' ); ?>" type="hidden" value="2" />
	<p>Weitere Parameter (Format param1=value1&amp;param2=value2[&amp;..]):
		<input name="<?php echo $this->get_field_name( 'additionalParameters' ); ?>" type="text" value="<?php echo esc_attr( $instance['additionalParameters' ] ); ?>" />
	</p>