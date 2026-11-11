/*
 * Grundlage für die Inline-Skripte im HTML der Evangelischen Termine ([et_veranstalter], Detailansicht, [et_panels]).
 *
 * Ersetzt die Skripte, die der Kalender sonst von seinem eigenen Host nachlädt (jQuery, common.js): jQuery und der
 * Datepicker kommen aus WordPress, diese Datei stellt bereit, was die Inline-Skripte darüber hinaus erwarten.
 */
( function ( window ) {
	'use strict';

	// WordPress liefert jQuery im noConflict-Modus aus, die Skripte des Kalenders verwenden $.
	window.$ = window.$ || window.jQuery;

	// Deutsche Einstellungen für den Datepicker im Filter; das Datumsformat erwartet der Kalender so.
	window.dateoptions = window.dateoptions || {
		closeText: 'Schließen',
		prevText: 'Zurück',
		nextText: 'Weiter',
		currentText: 'Heute',
		monthNames: [ 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' ],
		monthNamesShort: [ 'Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez' ],
		dayNames: [ 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag' ],
		dayNamesShort: [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ],
		dayNamesMin: [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ],
		weekHeader: 'Wo',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
	};

	if ( typeof window.ET_openWindow !== 'function' ) {
		window.ET_openWindow = function ( url, name, features ) {
			window.open( url, name, features );
		};
	}
} )( window );
