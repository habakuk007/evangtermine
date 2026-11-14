/*
 * Mini-Kalender der Evangelischen Termine (Shortcode [et_minical]).
 *
 * Verwendet jQuery, jQuery UI (Datepicker, Tooltip) und moment aus WordPress sowie Colorbox
 * (assets/scripts/jquery.colorbox-min.js). Die Daten des Shortcodes stehen in etMinicalData:
 * parameters = Abfrageparameter, baseUrl = Adresse der Evangelischen Termine (aus den Einstellungen).
 */
( function ( $ ) {
	'use strict';

	if ( typeof window.etMinicalData === 'undefined' || ! window.etMinicalData.baseUrl ) {
		return;
	}

	var parameters = window.etMinicalData.parameters || {};
	var baseUrl = window.etMinicalData.baseUrl;
	var eventdata = {};

	/*
	 * Tooltip, der geöffnet bleibt, solange der Mauszeiger auf dem Tag oder auf dem Tooltip steht
	 * (sonst wären die Links im Tooltip nicht erreichbar). Eigenes Widget, damit der Tooltip von
	 * jQuery UI für andere Skripte der Website unverändert bleibt.
	 */
	$.widget( 'evangtermine.etMinicalTooltip', $.ui.tooltip, {
		options: {
			hoverTimeout: 200,
		},
		_stillHovered: function ( target ) {
			var tooltipData = this._find( target );
			return target.is( ':hover' ) || ( tooltipData && $( tooltipData.tooltip ).is( ':hover' ) );
		},
		close: function ( event ) {
			var that = this;
			// this._super ist nur während dieses Aufrufs gesetzt, deshalb für den verzögerten Aufruf merken.
			var superClose = this._super;
			var target = $( event ? event.currentTarget : this.element );
			var delayedEvent;

			if ( event && event.type === 'mouseleave' ) {
				// Erst schließen, wenn weder der Tag noch der Tooltip unter dem Mauszeiger liegt.
				// jQuery leert event.currentTarget nach der Verarbeitung, deshalb ein eigenes Ereignis mit dem Ziel.
				delayedEvent = $.Event( 'mouseleave', { currentTarget: target[ 0 ], target: target[ 0 ] } );
				( function wait() {
					setTimeout( function () {
						if ( that._stillHovered( target ) ) {
							wait();
						} else {
							superClose.call( that, delayedEvent );
						}
					}, that.options.hoverTimeout );
				} )();
				return;
			}
			this._super.apply( this, arguments );
		},
	} );

	// Text aus den Daten der Evangelischen Termine für die Ausgabe als HTML maskieren.
	function esc( value ) {
		return $( '<div>' )
			.text( value === null || value === undefined ? '' : String( value ) )
			.html();
	}

	function beforeShowDay( day ) {
		var key = window.moment( day ).format( 'YYYY-MM-DD' );
		if ( Object.prototype.hasOwnProperty.call( eventdata, key ) ) {
			return [ true, '' ];
		}
		return [ false, 'et_daydisabled' ];
	}

	function loadMonthData( year, month ) {
		var shortYear = String( year ).substring( 2, 4 );
		var url = baseUrl + '/json?' + $.param( parameters ) + '&d=0&month=' + month + '.' + shortYear;

		eventdata = {};

		$.getJSON( url, function ( data ) {
			$.each( data, function ( i, item ) {
				var event = item && item.Veranstaltung;
				if ( ! event || ! event.START ) {
					return;
				}
				var key = String( event.START ).substring( 0, 10 );
				var id = parseInt( event.ID, 10 ) || 0;
				var text =
					'<a class="et_minical_link" href="#" data-eventid="' + id + '">' +
					'<span class="et_minical_time">' + esc( event.START_UHRZEIT ) + ' Uhr</span> : ' +
					'<span class="et_minical_title">' + esc( event._event_TITLE ) + '</span>' +
					'<br /><span class="et_minical_placename">' + esc( event._place_NAME ) + '</span></a>';

				if ( Object.prototype.hasOwnProperty.call( eventdata, key ) ) {
					eventdata[ key ] += '<hr class="et_minical_hr" />' + text;
				} else {
					eventdata[ key ] = text;
				}
			} );
			$( '#et_minicalendar' ).datepicker( 'refresh' );
		} );
	}

	$( function () {
		var calendar = $( '#et_minicalendar' );
		if ( ! calendar.length ) {
			return;
		}

		var now = window.moment();
		loadMonthData( now.format( 'YYYY' ), now.format( 'M' ) );

		calendar.datepicker( {
			beforeShowDay: beforeShowDay,
			dayNamesMin: [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ],
			firstDay: 1,
			monthNames: [ 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' ],
			dateFormat: 'yy-mm-dd',
			onChangeMonthYear: loadMonthData,
		} );

		$( document ).etMinicalTooltip( {
			items: 'div.ui-datepicker tbody a',
			position: { my: 'left top', at: 'left bottom' },
			content: function () {
				var cell = $( this ).parent();
				var day = window.moment( {
					y: cell.attr( 'data-year' ),
					M: cell.attr( 'data-month' ),
					d: $( this ).text(),
				} );
				return eventdata[ day.format( 'YYYY-MM-DD' ) ];
			},
		} );

		$( document ).on( 'click', '.et_minical_link', function ( e ) {
			e.preventDefault();
			var id = parseInt( $( this ).data( 'eventid' ), 10 ) || 0;
			$.colorbox( {
				iframe: true,
				href: baseUrl + '/detail-bt?ID=' + id,
				width: '100%',
				height: '100%',
				maxWidth: '900px',
			} );
		} );
	} );
} )( jQuery );
