# Installation

Das Plugin zeigt die Veranstaltungen des evangelischen Veranstaltungskalenders [Evangelische Termine](https://www.evangelische-termine.de/) auf Ihrer WordPress-Website.

## Voraussetzungen

- WordPress 5.0 oder neuer
- PHP 7.4 oder neuer
- Ihr Server muss ausgehende HTTPS-Verbindungen zum Kalender öffnen dürfen (Standard: `www.evangelische-termine.de`). Falls Ihr Hoster das sperrt, hilft die [FAQ](faq.md#die-seite-zeigt-der-veranstaltungskalender-ist-derzeit-nicht-verfügbar).
- Eine Veranstalter-ID, oder die Nummer eines Dekanats (siehe unten)

## Plugin installieren

1. Öffnen Sie in WordPress „Plugins“ > „Installieren“ und suchen Sie nach „Evangelische Termine“.
2. Klicken Sie auf „Jetzt installieren“ und dann auf „Aktivieren“.

Wer das Plugin von Hand installieren möchte: Die ZIP-Datei der aktuellen Version steht auf der [Releases-Seite](https://github.com/habakuk007/evangtermine/releases). Laden Sie sie unter „Plugins“ > „Installieren“ > „Plugin hochladen“ hoch und aktivieren Sie das Plugin.

## Erste Einrichtung

1. Melden Sie sich bei [www.evangelische-termine.de](https://www.evangelische-termine.de/) an. Ihre **Veranstalter-ID** steht auf jeder Seite oben rechts im Eingabebereich.
2. Öffnen Sie in WordPress „Einstellungen“ > „Evangelische Termine“ und tragen Sie die ID bei „Veranstalter-ID“ ein. Mehrere IDs trennen Sie mit Komma, ohne Leerzeichen.
3. Speichern Sie die Einstellungen. Die übrigen Felder können Sie zunächst so lassen, wie sie sind; sie sind in [Einstellungen](einstellungen.md) beschrieben.
4. Fügen Sie in eine Seite oder einen Beitrag einen Shortcode ein, zum Beispiel `[et_veranstalter]`. Im Block-Editor nehmen Sie dafür den Block „Shortcode“. Oder fügen Sie unter „Design“ > „Widgets“ das Widget „Evangelische Termine“ hinzu.

Die Ausgabe sehen Sie auf der veröffentlichten Seite (oder in der Vorschau). Im Editor selbst wird sie nicht angezeigt.

Ohne Veranstalter-ID zeigt das Plugin statt der Termine die Meldung „Fehler: Plugin „Evangelische Termine“: Die Veranstalter-ID ist nicht gesetzt.“ an.

## Deinstallieren

Beim Löschen des Plugins unter „Plugins“ entfernt WordPress auch die Einstellungen, die Widget-Einstellungen und den Zwischenspeicher des Plugins. Bei einer Multisite-Installation gilt das für alle Websites des Netzwerks.

## Nächste Schritte

- [Einstellungen](einstellungen.md)
- [Shortcodes](shortcodes.md)
- [Widget](widget.md)
