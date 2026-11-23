# Widget

Das Widget „Evangelische Termine“ zeigt die nächsten Termine in einem Widget-Bereich (zum Beispiel in der Seitenleiste). Sie fügen es unter „Design“ > „Widgets“ hinzu (bei Block-Themes im Website-Editor über den Block „Legacy-Widget“).

Das Widget zeigt eine Terminliste wie `[et_teaser]`, ohne Suchformular und ohne Detailansicht. Die Ausgabe wird 5 Minuten zwischengespeichert (siehe [FAQ](faq.md#neue-termine-erscheinen-im-widget-nicht-sofort)).

## Felder

Die Felder eines neuen Widgets sind mit den [Einstellungen](einstellungen.md) vorbelegt (und, wo es keine Einstellung gibt, mit den Standardwerten). Lassen Sie ein Feld, das einen Wert braucht (Veranstalter-ID, Termine pro Seite), nicht leer: Ein leeres Feld überschreibt den Wert aus den Einstellungen mit „leer“.

| Feld im Formular | Parameter | Standard |
|------------------|-----------|----------|
| Titel | – (nur die Überschrift des Widgets) | „Nächste Veranstaltungen“ bei einem neuen Widget; ein leerer Titel bleibt leer |
| Veranstalter-ID (kommaseparierte Liste, keine Leerzeichen) | `vid` | aus den Einstellungen |
| Dekanats-ID (kommaseparierte Liste, keine Leerzeichen) | `region` | aus den Einstellungen |
| Veranstaltungskategorie-ID (kommaseparierte Liste, keine Leerzeichen) | `eventtype` | leer |
| Highlight (alle/Highlights) | `highlight` | `all` |
| Veranstaltungsort-ID (kommaseparierte Liste, keine Leerzeichen) | `place` | `all` |
| Veranstaltungstyp-ID (Eingabemaske, kommaseparierte Liste, keine Leerzeichen) | `ipm` | `all` |
| Erwachsenenbildung | `auswahl` | leer; `eb` zeigt die Termine der Erwachsenenbildung und ignoriert dabei die Felder Veranstaltungstyp-ID und Kanäle |
| Kanäle (kommaseparierte Liste, keine Leerzeichen) | `cha` | `all` |
| itemsPerPage | `itemsPerPage` | `20` |
| Anzeige (extern/intern/alle) | `dest` | `extern` |
| Anzeige der Veranstaltung bis zum Enddatum (ja/nein) | `until` | aus den Einstellungen (ja) |
| Suchbegriffe (durch Leerzeichen getrennt) | `q` | leer |
| Weitere Parameter | `additionalParameters` | leer |

Das Widget hat keine Felder für `people` (Zielgruppe) und `person` (Ansprechpartner). Verwenden Sie dafür das Feld „Weitere Parameter“.

## Weitere Parameter

Im Feld „Weitere Parameter“ geben Sie Parameter an, für die es kein eigenes Feld gibt, in der Form `param1=wert1&param2=wert2`. Zum Beispiel für die Zielgruppe „Kinder“ und deutschsprachige Termine:

```text
people=5&lang=deu
```

Die Namen und Werte der Parameter stehen im [Handbuch](https://handbuch.evangelische-termine.de/anzeige-im-internet/ausgabe-parameter). Das Feld nimmt nur Buchstaben, Ziffern und die Zeichen `_ = & % . , + : ~ -` an; alles andere wird beim Speichern entfernt.

Die weiteren Parameter werden an die Abfrage angehängt. Wiederholen Sie darin keinen Parameter, für den es ein eigenes Feld gibt; welcher der beiden Werte dann gilt, entscheidet der Kalenderserver.

## Siehe auch

- [Parameter](parameter.md): alle Parameter im Überblick
- [Eigenes CSS](eigenes-css.md)
