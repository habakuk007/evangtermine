<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function et_veranstalter( $et_defaults ) {
	// Parameter aus Shortcodes und Widget einlesen
	if( $et_defaults[ 'vid' ] || $et_defaults[ 'region' ] ) {
		$encoding = get_option( 'encoding' ) ? get_option( 'encoding' ) : ET_DEFAULT_CHARSET;
		$css = 'nocss'; // Über die Evangelischen Termine geliefertes CSS wird unterdrückt

		// Session für Evangelischen Termine aufbauen
		if( !session_id() ) {
			session_start();
		}
		
		if( !isset ( $_SESSION[ 'session' ] ) ) {
			$session = new stdClass;
			$_SESSION[ 'session' ] = $session;
		} else {
			$session = $_SESSION[ 'session' ];
		}
		
		// Sessionvariable setzen
		function et_setsessionvar( $key, $sess, $default = null ) {
			$numeric_keys = array( 'pageID', 'itemsPerPage', 'people' );
			$is_set = isset( $_REQUEST[ $key ] ) && '' !== $_REQUEST[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $is_set ) {
				if ( in_array( $key, $numeric_keys, true ) ) {
					$sess->{ $key } = absint( $_REQUEST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				} else {
					$sess->{ $key } = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
				if ( 'pageID' !== $key ) {
					$sess->pageID = 1;
				}
			}
			if ( ! isset( $sess->{ $key } ) || '' === $sess->{ $key } ) {
				$sess->{ $key } = $default;
			}
		}
		
		// Filter zurücksetzen
		function et_resetsessionvars( $sess, $defaults ) {
			$sess->vid = $defaults[ 'vid' ];
			$sess->region = $defaults[ 'region' ];
			$sess->eventtype = $defaults[ 'eventtype' ];
			$sess->highlight = $defaults[ 'highlight' ];
			$sess->people = $defaults[ 'people' ];
			$sess->pagID = 1;
			$sess->et_q = '';
			$sess->itemsPerPage = $defaults[ 'itemsPerPage' ];
			$sess->date = '';
		}
		
		if ( 1 === absint( isset( $_REQUEST['reset'] ) ? $_REQUEST['reset'] : 0 ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			et_resetsessionvars( $session, $et_defaults );
		} else {
			et_setsessionvar( 'vid', $session, $et_defaults[ 'vid' ] );
			et_setsessionvar( 'region', $session, $et_defaults[ 'region' ] );
			et_setsessionvar( 'date', $session, '' );
			et_setsessionvar( 'eventtype', $session, $et_defaults[ 'eventtype' ] );
			et_setsessionvar( 'highlight', $session, $et_defaults[ 'highlight' ] );
			et_setsessionvar( 'people', $session, $et_defaults[ 'people' ] );
			et_setsessionvar( 'itemsPerPage', $session, $et_defaults[ 'itemsPerPage' ] );
			et_setsessionvar( 'pageID', $session, '1' );
			
			$raw_et_q  = isset( $_REQUEST['et_q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['et_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw_reset = isset( $_REQUEST['reset'] ) ? absint( $_REQUEST['reset'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' !== $raw_et_q ) {
				$session->et_q = $raw_et_q;
				if ( 1 === $raw_reset ) {
					$session->et_q = '';
				}
			} else {
				if ( 'search' === $raw_action ) {
					$session->et_q = '';
				}
			}
		}
			
		$querystring = 'vid='		. $session->vid .
						'&'.'region='	. $session->region .
						'&date='			. $session->date .
						'&highlight='	. $session->highlight .
						'&eventtype='	. $session->eventtype .
						'&people='		. $session->people .
						'&et_q='			. $session->et_q .
						'&place='		. $et_defaults[ 'place' ] .
						'&person='		. $et_defaults[ 'person' ] .
						'&ipm='			. $et_defaults[ 'ipm' ] .
						'&cha='			. $et_defaults[ 'cha' ] .
						'&until='		. $et_defaults[ 'until' ] .
						'&itemsPerPage=' . $session->itemsPerPage .
						'&pageID='		. $session->pageID .
						'&encoding='	. $encoding .
						'&css='			. $css;
						
		$et_vars = array( 'vid', 'region', 'aid', 'date', 'highlight', 'eventtype', 'people', 'et_q', 'place',
					'person', 'ipm', 'cha', 'until', 'itemsPerPage', 'pageID', 'encoding', 'css', 'etID', 'Suche', 'action', session_name(), '_token', 'reset' );
		foreach ( $_REQUEST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! in_array( $key, $et_vars, true ) ) {
				$querystring .= '&' . sanitize_key( $key ) . '=' . rawurlencode( sanitize_text_field( wp_unslash( (string) $value ) ) );
			}
		}

		$filename = 'veranstaltungen-php';
		$et_id = isset( $_REQUEST['etID'] ) ? absint( $_REQUEST['etID'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 0 !== $et_id ) {
			$querystring .= '&ID=' . $et_id;
			$filename = 'detail-php';
		}
		$protocol = get_option ( 'etprotocol' ) ? get_option ( 'etprotocol' ) : ET_DEFAULT_PROTOCOL;
		$host = get_option( 'ethost' ) ? get_option( 'ethost' ) : ET_DEFAULT_HOST;
		$url = $protocol . $host . '/'. $filename . '?' . $querystring;

		if( function_exists( 'curl_init' ) ) {
			$sobl = curl_init( $url );
			curl_setopt( $sobl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $sobl, CURLOPT_USERAGENT, 'Veranstalter-Script 2.0' );
			curl_setopt( $sobl, CURLOPT_REFERER, $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] );
			curl_setopt( $sobl, CURLOPT_CONNECTTIMEOUT, 2 );
			$pagecontent = curl_exec( $sobl );
			$sobl_info = curl_getinfo( $sobl );
			if( '200' == $sobl_info['http_code'] ){	
				$pagecontent = str_replace( '<div id="et_headline"><h1>Veranstaltungen</h1></div>', '', $pagecontent );
				$pagecontent = str_replace( '<h1>', '<h2>', $pagecontent );
				$pagecontent = str_replace( '</h1>', '</h2>', $pagecontent );
				$pagecontent = str_replace( '<h1 id="et_detail_title">', '<h2 id="et_detail_title">', $pagecontent );
				$pagecontent = str_replace( '/Upload/', 'http://' . $host . '/Upload/', $pagecontent );
				$pagecontent = str_replace( 'http://_HOST_/?', get_permalink( $post->ID ).'?'.$querystring.'&amp;', $pagecontent ); // 'https://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ]
				$pagecontent = str_replace( '<link rel="stylesheet" type="text/css" href="'. $host .'/bundles/vket/css/publicintegration.css"  />', '', $pagecontent); // Diese Stylesheets liefern im Veranstalter-Script 2.0 weitere CSS-Dateien, die allerdings nicht benötigt werden.
				$pagecontent = str_replace( '<link rel="stylesheet" type="text/css" href="'. $host .'/bundles/vket/js/jquery/css/smoothness/jquery-ui-1.10.3.custom.min.css"  />', '', $pagecontent); // Diese Stylesheets liefern im Veranstalter-Script 2.0 weitere CSS-Dateien, die allerdings nicht benötigt werden.
				$pagecontent = str_replace( '<link href="nocss" media="screen, projection" rel="stylesheet" type="text/css" />', '', $pagecontent); // Mit der Angabe nocss wird verhindert, dass das CSS der Evangelischen Termine geladen wird. Durch diese Zeile wird der Code aus dem HTML gelöscht.
				
				if( 'veranstaltungen-php' == $filename ) {
					$pagecontent = str_replace( '<script type="text/javascript" src="' . $host . '/js/e19e663.js"></script>', '', $pagecontent);
				}
				if( 'detail-php' == $filename ) {
					$pagecontent = str_replace( '<script type="text/javascript" src="' . $host . '/js/fa34c0d.js"></script><script language="javascript" type="text/javascript">', '', $pagecontent);
					$pagecontent = str_replace( '$(function() {', '', $pagecontent);
					$pagecontent = str_replace( '$(\'#et_place_image_th\').livequery(function(){', '', $pagecontent);
					$pagecontent = str_replace( '$(this)', '', $pagecontent);
					$pagecontent = str_replace( '.mouseover(function() {', '', $pagecontent);
					$pagecontent = str_replace( '$(this).hide();', '', $pagecontent);
					$pagecontent = str_replace( '$(\'#et_place_image\').slideDown();', '', $pagecontent);
					$pagecontent = str_replace( '});', '', $pagecontent); 
					$pagecontent = str_replace( '$(\'#et_place_image\').livequery(function(){', '', $pagecontent);
					$pagecontent = str_replace( '$(this)', '', $pagecontent); 
					$pagecontent = str_replace( '.mouseout(function() {', '', $pagecontent); 
					$pagecontent = str_replace( '$(this).slideUp();', '', $pagecontent);
					$pagecontent = str_replace( '$(\'#et_place_image_th\').show();', '', $pagecontent);
					$pagecontent = str_replace( 'function ET_openWindow(etURL,windowName,features) { window.open(etURL,windowName,features);}', '', $pagecontent);
					$pagecontent = str_replace( '</script>', '', $pagecontent);
					$pagecontent = str_replace( '.hide();', '', $pagecontent);
					$pagecontent = str_replace( '.slideUp();', '', $pagecontent);
				}
				$content = $pagecontent;
			} else {
				$content = 'Der Terminkalender ist derzeit nicht erreichbar!';
			}
			curl_close( $sobl );
		} else {
			$content = "Das Plugin benötigt das PHP-Modul curl.";
		}
	} else {
		$content = 'Veranstalter-ID ist nicht definiert. Einstellungen -> Evangelische Termine';
	}
	return $content;
}

function et_teaser( $et_defaults ) {
	// Parameter aus Shortcodes und Widget einlesen
	if( $et_defaults[ 'vid' ] ) {
		$encoding = get_option( 'encoding' ) ? get_option( 'encoding' ) : ET_DEFAULT_CHARSET;
		$css = 'nocss'; // Über die Evangelischen Termine geliefertes CSS wird unterdrückt
		
		$querystring = '';
		foreach( $et_defaults as $key => $value ) {
			if( 'title' != $key ) {
				if( 'region' != $key ) {
					$querystring .= '&' . $key . '=' . $value;
				} else {
					$querystring .= '&'.'region' . '=' . $value;
				}
			}
		}
		$querystring .= '&encoding=' . $encoding . '&css=' . $css;
		$querystring = substr( $querystring, 1, strlen( $querystring) - 1 );  

		$protocol = get_option ( 'etprotocol' ) ? get_option ( 'etprotocol' ) : ET_DEFAULT_PROTOCOL;
		$host = get_option( 'ethost' ) ? get_option( 'ethost' ) : ET_DEFAULT_HOST;
		$url = $protocol . $host . '/teaser?' . $querystring;

		if( function_exists( 'curl_init' ) ) {
			$sobl = curl_init( $url );
			curl_setopt( $sobl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $sobl, CURLOPT_USERAGENT, 'TeaserScript' );
			curl_setopt( $sobl, CURLOPT_REFERER, $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] );
			curl_setopt( $sobl, CURLOPT_CONNECTTIMEOUT, 5 );
			$pagecontent = curl_exec( $sobl );
			$sobl_info = curl_getinfo( $sobl );
			if( '200' == $sobl_info['http_code'] ){
				$pagecontent = str_replace( '<link href="nocss" media="screen, projection" rel="stylesheet" type="text/css" />', '', $pagecontent ); // Mit der Angabe nocss wird verhindert, dass das CSS der Evangelischen Termine geladen wird. Durch diese Zeile wird der Code aus dem HTML gelöscht.
				$pagecontent = str_replace( 'PHPSESSID', 'PHPSESSIDunused', $pagecontent ); // CSS-Fehler beim erstmaligen Aufruf einer Detailseite in einem neuen Fenster wird verhindert.
				$content = $pagecontent;
			} else {
				$content = 'Der Terminkalender ist derzeit nicht erreichbar!';
			}
			curl_close( $sobl );
		} else {
			$content = "Das Plugin benöt das PHP-Modul curl.";
		}
	} else {
		$content = 'Veranstalter-ID ist nicht definiert. Einstellungen -> Evangelische Termine';
	}
	return $content;
}
?>
