# Einstellungen

Die Einstellungen finden Sie unter „Einstellungen“ > „Evangelische Termine“. Sie gelten für alle Shortcodes und das Widget, solange dort kein eigener Wert angegeben ist. Ein Shortcode-Attribut oder ein Widget-Feld hat immer Vorrang vor der Einstellung.

In einer Multisite-Installation hat jede Website ihre eigenen Einstellungen.

| Einstellung | Option | Standard | Zulässige Werte |
|-------------|--------|----------|-----------------|
| Veranstalter-ID | `vid` | leer | eine ID, mehrere IDs mit Komma getrennt, oder `all` zusammen mit einer Dekanats-ID |
| Dekanats-ID | `region` | leer | eine dreistellige Dekanatsnummer, mehrere mit Komma getrennt |
| Termine bis zu ihrem Ende anzeigen | `until` | ja | `yes` (ja) oder `no` (nein) |
| CSS-Datei | `css` | leer | leer, eine Adresse (URL) oder `nocss` |
| Zeichenkodierung | `encoding` | UTF-8 | `utf8` (UTF-8) oder `latin1` (ISO-8859-1) |
| Protokoll | `etprotocol` | Standard (`https://`) | Standard, `https://` oder `http://` |
| Host des Kalenderbetreibers | `ethost` | leer, das heißt `www.evangelische-termine.de` | ein Hostname, optional mit Port |

Die Namen in der Spalte „Option“ sind die Namen, unter denen WordPress die Werte speichert. Sie haben aus historischen Gründen kein Präfix.

## Veranstalter-ID

Die ID Ihres Veranstalters bei Evangelische Termine. Ohne ID zeigt das Plugin keine Termine, sondern eine Fehlermeldung. Das gilt auch, wenn nur eine Dekanats-ID eingetragen ist: Dann tragen Sie bei „Veranstalter-ID“ den Wert `all` ein.

Beispiel für mehrere Veranstalter: `952,988`

## Dekanats-ID

Die Nummer eines Dekanats. Es werden die Termine aller Veranstalter dieses Dekanats gezeigt. Die Liste der Nummern finden Sie im [Handbuch](https://handbuch.evangelische-termine.de/anzeige-im-internet/ausgabe-parameter) unter dem Parameter „region“. Lassen Sie das Feld leer, wenn nur die Termine der eingetragenen Veranstalter erscheinen sollen.

## Termine bis zu ihrem Ende anzeigen

Bei „ja“ bleibt ein Termin in der Liste, bis er zu Ende ist. Bei „nein“ verschwindet er, sobald er begonnen hat.

## CSS-Datei

Die Adresse einer eigenen CSS-Datei. Sie wird anstelle des Stylesheets von Evangelische Termine verwendet. Mit dem Wert `nocss` wird gar kein Stylesheet geladen, zum Beispiel wenn Ihr Theme die Gestaltung schon enthält. Mehr dazu in [Eigenes CSS](eigenes-css.md).

## Zeichenkodierung

Die Kodierung Ihrer Website. Die Evangelischen Termine liefern die Daten in dieser Kodierung. UTF-8 stimmt für fast alle WordPress-Websites; ändern Sie den Wert nur, wenn Umlaute falsch angezeigt werden.

## Protokoll

Das Protokoll, mit dem Ihr Server die Termine abruft. Standard ist `https://`, unabhängig davon, ob Ihre eigene Website verschlüsselt ist. Wählen Sie `http://` nur, wenn der Kalenderbetreiber kein `https://` anbietet. Die Verbindung ist dann unverschlüsselt, und beim Speichern erscheint eine Warnung.

## Host des Kalenderbetreibers

Der Server, von dem die Termine geladen werden: nur der Hostname, ohne `http://` und ohne Pfad (optional mit Port, zum Beispiel `kalender.example.org:8080`). Leer bedeutet `www.evangelische-termine.de`. Einen anderen Wert brauchen Sie nur, wenn Ihre Landeskirche einen eigenen Kalender betreibt.

Aus Sicherheitsgründen gelten diese Einschränkungen:

- Der Wert darf nur aus einem Hostnamen (und optional einem Port) bestehen, ohne `http://` und ohne Pfad.
- Lokale und private Adressen werden abgelehnt: `localhost`, Namen mit der Endung `.localhost`, IP-Adressen in privaten oder reservierten Bereichen (zum Beispiel `192.168.…`, `127.0.0.1`, `169.254.…`) und Namen, die auf solche Adressen zeigen.
- Ports, die WordPress für Abrufe nicht zulässt (außer 80, 443 und 8080), werden abgelehnt.
- Bei einem ungültigen Wert zeigt die Einstellungsseite eine Fehlermeldung und behält den bisherigen Wert.

Die Prüfung wird bei jedem Abruf wiederholt. Wird ein unzulässiger Host erkannt, erscheint „Der Veranstaltungskalender ist derzeit nicht verfügbar.“ statt der Termine.

Das HTML, das der Kalenderserver liefert, wird ungefiltert auf Ihrer Website ausgegeben. Tragen Sie deshalb nur einen Host ein, dem Sie vertrauen.
