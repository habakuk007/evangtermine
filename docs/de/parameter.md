# Parameter

Diese Seite listet die Parameter, die das Plugin kennt. Was die Werte bedeuten (zum Beispiel welche Nummer welche Kategorie oder Zielgruppe ist), steht im [Handbuch von Evangelische Termine](https://handbuch.evangelische-termine.de/anzeige-im-internet/ausgabe-parameter).

Die Angaben auf dieser Seite wurden gegen den Code des Plugins geprüft (Version 3.5). Wie der Kalenderserver einen Wert auswertet, entscheidet der Server.

## Shortcode-Attribute

Diese Attribute gelten für alle vier Shortcodes: `[et_veranstalter]`, `[et_teaser]`, `[et_panels]` und `[et_minical]`. Das Plugin nimmt die Attribute des Shortcodes, ergänzt sie um die [Einstellungen](einstellungen.md) und die Standardwerte aus der Tabelle und sendet sie an den Kalender. Ein Attribut überschreibt die Einstellung.

| Attribut | Bedeutung | Standard | Beispiel |
|----------|-----------|----------|----------|
| `vid` | Veranstalter-ID; mehrere IDs mit Komma getrennt; `all` zusammen mit `region` | Einstellung „Veranstalter-ID“ | `vid="952,988"` |
| `region` | Dekanats-Nummer (dreistellig); mehrere mit Komma getrennt | Einstellung „Dekanats-ID“ | `region="201"` |
| `eventtype` | Kategorie; ein vorangestelltes `-` schließt sie aus; mehrere mit Komma getrennt | leer | `eventtype="-1"` |
| `highlight` | `all` (alle Termine) oder `high` (nur Highlights) | `all` | `highlight="high"` |
| `people` | Zielgruppen; mehrere mit Komma getrennt | `0` (alle) | `people="5"` |
| `person` | Ansprechpartner; mehrere mit Komma getrennt; `all` setzt zurück | nicht gesetzt | `person="574,568"` |
| `place` | Veranstaltungsorte; mehrere mit Komma getrennt; `all` setzt zurück | `all` | `place="45,32"` |
| `ipm` | Veranstaltungstypen (Eingabemasken); mehrere mit Komma getrennt | `all` | `ipm="60"` |
| `cha` | Kanäle; mehrere mit Komma getrennt | `all` | `cha="2"` |
| `itemsperpage` | Anzahl der Termine je Seite. Bei `[et_veranstalter]` sind 5, 10, 20, 30, 40, 50 und 100 vorgesehen | `20` | `itemsperpage="5"` |
| `dest` | `extern` (öffentliche Termine), `intern` oder `all` | `extern` | `dest="all"` |
| `until` | `yes`: Termin bis zu seinem Ende anzeigen; `no`: nur bis zum Beginn | Einstellung „Termine bis zu ihrem Ende anzeigen“ (`yes`) | `until="no"` |
| `q` | Suchbegriffe, durch Leerzeichen getrennt (nicht durch Komma); durchsucht unter anderem Titel, Beschreibung, Ort und Person | leer | `q="Senioren Konzert"` |
| `auswahl` | Vordefinierte Auswahl; `eb` zeigt die Termine der Erwachsenenbildung in Bayern (Veranstaltungstyp oder Kanal „Erwachsenenbildung“) | nicht gesetzt | `auswahl="eb"` |

Beispiel mit mehreren Attributen:

```text
[et_teaser vid="952,988" eventtype="1" highlight="high" itemsperpage="5"]
```

### Weitere Parameter des Kalenders

Das Plugin gibt **jedes** Attribut eines Shortcodes unverändert an den Kalender weiter. Sie können deshalb auch Parameter verwenden, die die Tabelle nicht aufführt, zum Beispiel `kk`, `bf`, `tags`, `lang`, `prof`, `topic`, `ctype`, `d`, `month`, `year` oder `date`. Sie sind im Handbuch beschrieben. Das Plugin prüft sie nicht; ob ein Modul (Liste, Teaser, Panels, Mini-Kalender) sie berücksichtigt, bestimmt der Kalender. Schreiben Sie die Namen klein, weil WordPress Attributnamen in Kleinbuchstaben umwandelt (`itemsPerPage` wird vom Plugin erkannt, `pageID` nicht).

```text
[et_teaser vid="952" lang="deu" itemsperpage="5"]
```

## Widget

Das Widget hat ein Formular mit festen Feldern. Welche Felder es gibt, steht in [Widget](widget.md). Sie entsprechen den Attributen `vid`, `region`, `eventtype`, `highlight`, `place`, `ipm`, `auswahl`, `cha`, `itemsperpage` (im Formular `itemsPerPage`), `dest`, `until` und `q`. `people` und `person` gibt es im Widget nur über das Feld „Weitere Parameter“ (`additionalParameters`), das eine Zeichenkette der Form `param1=wert1&param2=wert2` annimmt.

## Parameter in der Adresse der Seite (`[et_veranstalter]`)

`[et_veranstalter]` liest diese Parameter aus der Adresse der Seite (GET) und aus abgeschickten Formularen (POST). Sie kommen aus den Links und dem Formular der Terminliste selbst; Sie müssen sie nicht von Hand schreiben.

| Parameter | Bedeutung |
|-----------|-----------|
| `vid`, `region`, `eventtype`, `highlight`, `people`, `aid`, `date` | Werte des Filterformulars, sie ersetzen die Werte des Shortcodes |
| `itemsPerPage` | Termine je Seite (ganze Zahl) |
| `pageID` | Aktuelle Seite der Blätterfunktion (ganze Zahl) |
| `et_q` | Suchbegriff aus dem Suchformular; hat Vorrang vor `q` |
| `q` | Suchbegriff aus den Links der Terminliste (zum Beispiel „nächste Seite“) |
| `etID` | Die Detailansicht des Termins mit dieser ID anzeigen (ganze Zahl) |
| `reset` | `reset=1` ignoriert die übrigen Werte aus der Adresse und kehrt zu den Vorgaben des Shortcodes zurück |

Alle anderen Parameter in der Adresse der Seite werden nicht an den Kalender weitergegeben. Zusätzliche Parameter setzen Sie über Attribute des Shortcodes.

Die Parameter `place`, `person`, `ipm`, `cha`, `until` und `dest` kommen nicht aus der Adresse, sie sind nur über Attribute oder Einstellungen einstellbar.

## Interne Parameter

Diese Werte setzt das Plugin selbst. Sie sind keine Parameter für Seitenautoren:

| Parameter | Bedeutung |
|-----------|-----------|
| `aid` | Wird vom Plugin leer vorbelegt und ist im Handbuch nicht beschrieben. Nicht verwenden. |
| `css` | Das Plugin sendet standardmäßig `nocss`; bei `[et_teaser]` ohne eigene CSS-Datei wird der Parameter weggelassen. Als Attribut ist nur der Wert `nocss` erlaubt, jeder andere Wert wird verworfen. Eine eigene CSS-Datei tragen Sie in den Einstellungen ein, siehe [Eigenes CSS](eigenes-css.md). |
| `encoding` | Stammt aus der Einstellung „Zeichenkodierung“. Ändern Sie sie dort, nicht im Shortcode. |
| `tpl` | Wählt das Teaser-Template (1 für `[et_teaser]`, 2 für das Widget). |
| `title` | Nur die Überschrift des Widgets; wird nicht an den Kalender gesendet. |
| `additionalParameters` | Das Feld „Weitere Parameter“ des Widgets, siehe oben. |
| `pageID` | Als Attribut nicht sinnvoll (WordPress schreibt den Namen klein); die Seite der Blätterfunktion steht in der Adresse. |
