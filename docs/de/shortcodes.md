# Shortcodes

Das Plugin bietet vier Shortcodes. Sie fügen sie in eine Seite oder einen Beitrag ein, im Block-Editor mit dem Block „Shortcode“.

| Shortcode | Zeigt |
|-----------|-------|
| `[et_veranstalter]` | Terminliste mit Filter- und Suchformular, Blätterfunktion und Detailansicht |
| `[et_teaser]` | Terminliste ohne Suchformular |
| `[et_panels]` | Termine als Kacheln (Panels) mit ihren Bildern |
| `[et_minical]` | Den Mini-Kalender von Evangelische Termine |

Alle vier Shortcodes verwenden die [Einstellungen](einstellungen.md) als Vorgabe. Mit Attributen überschreiben Sie einen Wert für diesen einen Shortcode, zum Beispiel `vid="952"`. Welche Attribute es gibt, steht in [Parameter](parameter.md).

Bitte beachten Sie:

- Ohne Veranstalter-ID (in den Einstellungen oder im Shortcode) erscheint eine Fehlermeldung statt der Termine. Ein leeres Attribut (`vid=""`) überschreibt die Einstellung ebenfalls mit „leer“.
- WordPress macht aus Attributnamen Kleinbuchstaben. Schreiben Sie deshalb `itemsperpage` (das Plugin kennt auch `itemsPerPage`).
- Die Beispiele auf dieser Seite verwenden die Veranstalter-IDs `952` und `988` und die Dekanats-ID `201` aus der Beschreibung des Plugins und aus dem Handbuch. Ersetzen Sie sie durch Ihre eigenen.

## `[et_veranstalter]`

Die vollständige Terminliste mit Filterformular (Kategorie, Datum, Suche und weitere), Blätterfunktion und Detailansicht eines Termins. Die Auswahl der Besucher steht in der Adresse der Seite (zum Beispiel `?eventtype=1&pageID=2`), ein Link auf die gefilterte Liste lässt sich also weitergeben. Der Link „Zurücksetzen“ im Filter führt zu den Vorgaben des Shortcodes zurück. Das Plugin verwendet keine PHP-Session.

```text
[et_veranstalter vid="952"]
```

Hinweise:

- Mit `region` ohne eigene Veranstalter-ID: `[et_veranstalter vid="all" region="201"]`. Die Liste enthält dann ein Feld zur Auswahl des Veranstalters.
- Verwenden Sie nur einen `[et_veranstalter]` je Seite: Filter und Blätterfunktion stehen in der Adresse der Seite und gelten damit für jede Liste auf dieser Seite.
- Die Detailansicht eines Termins wird auf derselben Seite angezeigt (Parameter `etID` in der Adresse).

## `[et_teaser]`

Eine einfache Terminliste ohne Suchformular und ohne Detailansicht auf Ihrer Website. Die Ausgabe wird 5 Minuten zwischengespeichert, neue Termine erscheinen also mit etwas Verzögerung (siehe [FAQ](faq.md#neue-termine-erscheinen-im-widget-nicht-sofort)).

```text
[et_teaser vid="952,988" itemsperpage="5"]
```

Solange Sie in den Einstellungen keine eigene CSS-Datei eintragen, liefert der Teaser das Standard-Stylesheet von Evangelische Termine mit.

## `[et_panels]`

Die Termine als Kacheln mit Bild. Die Gestaltung nutzt Bootstrap 3 (nur CSS) und Font Awesome 4.7, beides aus dem Plugin. Unter der Liste erscheint ein Feld „Mehr laden“; wie Sie es ausblenden, steht in [Eigenes CSS](eigenes-css.md#das-feld-mehr-laden-ausblenden).

```text
[et_panels vid="952"]
```

## `[et_minical]`

Der Mini-Kalender von Evangelische Termine: ein Monatskalender. Fährt man mit der Maus über einen Tag mit Terminen, erscheinen die Termine dieses Tages; ein Klick auf einen Termin öffnet die Detailansicht in einem Überlagerungsfenster.

```text
[et_minical vid="952"]
```

Der Mini-Kalender wird mit JavaScript aufgebaut. Seine Daten holt der **Browser des Besuchers** vom Host aus den Einstellungen, nicht Ihr Server. Der Server der Evangelischen Termine erfährt dadurch die IP-Adresse der Besucher. Welche Server der Browser bei den einzelnen Shortcodes direkt kontaktiert (Bilder, Stylesheets, Daten des Mini-Kalenders), steht im Abschnitt „External service“ der `readme.txt` (auf der Seite des Plugins bei WordPress.org). Beachten Sie das in Ihrer Datenschutzerklärung.

Der Mini-Kalender funktioniert nur, wenn der Host in den Einstellungen zulässig ist (siehe [Einstellungen](einstellungen.md#host-des-kalenderbetreibers)); sonst bleibt die Fläche leer.

## Mehrere Shortcodes und Widgets

Sie können verschiedene Shortcodes auf derselben Seite und zusätzlich das Widget verwenden. Jede Ausgabe wird unabhängig abgerufen.

## Weiter

- [Parameter](parameter.md) für alle Attribute
- [Eigenes CSS](eigenes-css.md) für die Gestaltung
