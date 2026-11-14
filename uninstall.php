<?php
/**
 * Wird von WordPress beim Löschen des Plugins aufgerufen: entfernt die Einstellungen und den Cache des Plugins.
 *
 * @package evangtermine
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Löscht die Einstellungen und die zwischengespeicherten Abrufe der aktuellen Website.
 */
function evangtermine_uninstall_site() {
	global $wpdb;

	// Die Namen der Optionen haben aus historischen Gründen kein Präfix.
	foreach ( array( 'vid', 'region', 'until', 'css', 'encoding', 'etprotocol', 'ethost' ) as $option ) {
		delete_option( $option );
	}

	// Zwischengespeicherte Abrufe (EtQuery::getCacheKey) inklusive ihrer Ablaufzeiten.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_et_stream_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_et_stream_' ) . '%'
		)
	);
}

if ( is_multisite() ) {
	$evangtermine_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $evangtermine_site_ids as $evangtermine_site_id ) {
		switch_to_blog( $evangtermine_site_id );
		evangtermine_uninstall_site();
		restore_current_blog();
	}
} else {
	evangtermine_uninstall_site();
}
