<?php

/**
 * Formular für die Widget-Einstellungen im Wordpress-Backend
 * Design -> Widgets -> Evangelische Termine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
	<p><?php esc_html_e( 'Title:', 'evangtermine' ); ?> 
		<input name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
	</p>
	<p><?php esc_html_e( 'Organizer ID (comma-separated list, no spaces):', 'evangtermine' ); ?> 
		<input name="<?php echo esc_attr( $this->get_field_name( 'vid' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['vid'] ); ?>" />
	</p>
	<p><?php esc_html_e( 'Deanery ID (comma-separated list, no spaces):', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'region' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['region'] ); ?>" />
	</p>
	<p><?php esc_html_e( 'Eventtype ID (comma-separated list, no spaces):', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'eventtype' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['eventtype' ] ); ?>" />
	</p>
	<p><?php esc_html_e( 'Highlight (all/high):', 'evangtermine' ); ?>
		<select name="<?php echo esc_attr( $this->get_field_name( 'highlight' ) ); ?>">
			<?php
			$items = array(
				'all'  => _x( 'all', 'widget highlight option', 'evangtermine' ),
				'high' => _x( 'high', 'widget highlight option', 'evangtermine' ),
			);
			foreach ($items as $item => $label) {
			 	 echo '<option value="' . esc_attr( $item ) . '"' . selected( (string) $instance['highlight'], $item, false ) . '>' . esc_html( $label ) . '</option>';
			} 
			?>
		</select>
	</p>
	<p><?php esc_html_e( 'Venue ID (comma-separated list, no spaces):', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'place' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['place'] ); ?>" />
	</p>
	<p><?php esc_html_e( 'Event type ID (comma-separated list, no spaces):', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'ipm' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['ipm'] ); ?>" />
	</p>
	<p><?php esc_html_e( "Adult education (if 'eb' is selected, the fields Event type ID and Channels are ignored):", 'evangtermine' ); ?>
		<select name="<?php echo esc_attr( $this->get_field_name( 'auswahl' ) ); ?>" >
			<?php
			$items = array( '', 'eb' );
			foreach ($items as $item) {
				echo '<option value="' . esc_attr( $item ) . '"' . selected( (string) $instance['auswahl'], $item, false ) . '>' . esc_html( $item ) . '</option>';
			}
			?>
		</select>
	</p>	
	<p><?php esc_html_e( 'Channels (comma-separated list, no spaces):', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'cha' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['cha'] ); ?>" />
	</p>
	<p><?php esc_html_e( 'itemsPerPage:', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'itemsPerPage' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['itemsPerPage'] ); ?>" />
	</p>
	<p><?php esc_html_e( 'Display (extern/intern/all):', 'evangtermine' ); ?>
		<select name="<?php echo esc_attr( $this->get_field_name( 'dest' ) ); ?>">
			<?php
			$items = array(
				'extern' => _x( 'extern', 'widget display option', 'evangtermine' ),
				'intern' => _x( 'intern', 'widget display option', 'evangtermine' ),
				'all'    => _x( 'all', 'widget display option', 'evangtermine' ),
			);
			foreach ($items as $item => $label) {
			 	 echo '<option value="' . esc_attr( $item ) . '"' . selected( (string) $instance['dest'], $item, false ) . '>' . esc_html( $label ) . '</option>';
			} 
			?>
		</select>
	</p>
	<p><?php esc_html_e( 'Show the event until its end date (yes/no):', 'evangtermine' ); ?>
		<select name="<?php echo esc_attr( $this->get_field_name( 'until' ) ); ?>">
			<?php
			$items = array(
				'yes' => __( 'yes', 'evangtermine' ),
				'no'  => __( 'no', 'evangtermine' ),
			);
			foreach ($items as $item => $label) {
			 	 echo '<option value="' . esc_attr( $item ) . '"' . selected( (string) $instance['until'], $item, false ) . '>' . esc_html( $label ) . '</option>';
			} 
			?>
		</select>
		<!-- <input name="<?php echo esc_attr( $this->get_field_name( 'until' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['until'] ); ?>" /> -->
	</p>
	<p><?php esc_html_e( 'Search terms (comma-separated list):', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'q' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['q'] ); ?>" />
	</p>
	<input name="<?php echo esc_attr( $this->get_field_name( 'tpl' ) ); ?>" type="hidden" value="2" />
	<p><?php esc_html_e( 'Additional parameters (format param1=value1&param2=value2[&..]):', 'evangtermine' ); ?>
		<input name="<?php echo esc_attr( $this->get_field_name( 'additionalParameters' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['additionalParameters' ] ); ?>" />
	</p>