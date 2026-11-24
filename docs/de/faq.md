# Häufige Fragen und Probleme

## Wo finde ich meine Veranstalter-ID?

Melden Sie sich bei [www.evangelische-termine.de](https://www.evangelische-termine.de/) an. Die Veranstalter-ID steht auf jeder Seite oben rechts im Eingabebereich.

## Die Seite zeigt „Fehler: Plugin „Evangelische Termine“: Die Veranstalter-ID ist nicht gesetzt.“

Es ist weder in den [Einstellungen](einstellungen.md) noch im Shortcode oder Widget eine Veranstalter-ID eingetragen. Tragen Sie sie ein.

Das gilt auch, wenn Sie nur eine Dekanats-ID (`region`) angeben: Das Plugin braucht immer einen Wert bei `vid`. Verwenden Sie dann `vid="all"`, zum Beispiel `[et_teaser vid="all" region="201"]`.

Auch ein leeres Attribut (`vid=""`) oder ein geleertes Widget-Feld überschreibt die Einstellung mit „leer“.

## Die Seite zeigt „Der Veranstaltungskalender ist derzeit nicht verfügbar.“

Der Abruf beim Kalender ist fehlgeschlagen. Mögliche Gründe:

1. **Der Kalender antwortet nicht oder zu langsam.** Das Plugin wartet bis zu 15 Sekunden. Versuchen Sie es später noch einmal. Eine längere Wartezeit (1 bis 30 Sekunden) stellen Sie mit dem Filter `evangtermine_http_timeout` ein, zum Beispiel in der `functions.php` Ihres Child-Themes:

   ```php
   add_filter( 'evangtermine_http_timeout', function () {
       return 25;
   } );
   ```

2. **Ihr Server darf keine Verbindung nach außen öffnen.** Manche Hoster sperren ausgehende Verbindungen. Fragen Sie Ihren Hoster, ob Ihr Server `www.evangelische-termine.de` über HTTPS (Port 443) erreichen darf.
3. **WordPress selbst blockiert externe Zugriffe.** Steht in der `wp-config.php` die Zeile `define( 'WP_HTTP_BLOCK_EXTERNAL', true );`, darf WordPress nur Hosts erreichen, die in `WP_ACCESSIBLE_HOSTS` aufgeführt sind. Tragen Sie den Host des Kalenders dort ein, und den Host aus den Einstellungen, falls Sie einen anderen verwenden:

   ```php
   define( 'WP_HTTP_BLOCK_EXTERNAL', true );
   define( 'WP_ACCESSIBLE_HOSTS', 'api.wordpress.org,*.wordpress.org,www.evangelische-termine.de' );
   ```

4. **Der Host in den Einstellungen ist nicht zulässig** (lokale oder private Adresse), siehe [Einstellungen](einstellungen.md#host-des-kalenderbetreibers).
5. **Das Protokoll passt nicht.** Prüfen Sie die Einstellung „Protokoll“. Mit „Standard“ wird `https://` verwendet.

Die Besucher sehen in allen diesen Fällen nur die kurze Meldung. Den genauen Grund schreibt das Plugin ins Debug-Log von WordPress, wenn dort `WP_DEBUG` und `WP_DEBUG_LOG` eingeschaltet sind (Datei `wp-content/debug.log`). Die Zeile beginnt mit `evangtermine: fetch failed for host`.

Die Meldung wird nicht zwischengespeichert: Sobald der Kalender wieder erreichbar ist, erscheinen die Termine wieder.

## Neue Termine erscheinen im Widget nicht sofort

Die Ausgabe von Widget und `[et_teaser]` wird 5 Minuten zwischengespeichert, damit nicht jeder Seitenaufruf den Kalender abfragt. Ein neuer Termin erscheint deshalb nach spätestens 5 Minuten. Die Lebensdauer in Sekunden ändern Sie mit dem Filter `evangtermine_cache_ttl`; `0` schaltet den Zwischenspeicher aus:

```php
add_filter( 'evangtermine_cache_ttl', '__return_zero' );
```

Wenn Sie zusätzlich ein Cache-Plugin oder einen Cache Ihres Hosters verwenden, wird die ganze Seite oft noch länger zwischengespeichert. Leeren Sie dann auch diesen Cache.

`[et_veranstalter]` und `[et_panels]` werden vom Plugin nicht zwischengespeichert.

## Meine Auswahl im Filter geht verloren oder die Liste blättert nicht

Der Filter und die Blätterfunktion von `[et_veranstalter]` stehen in der Adresse der Seite (zum Beispiel `?eventtype=1&pageID=2`). Das Plugin verwendet keine PHP-Session. Ein Cache-Plugin, das Adressen mit Parametern gleich behandelt wie die Adresse ohne Parameter, liefert dann immer dieselbe Seite aus. Stellen Sie das Cache-Plugin so ein, dass Seiten mit `?`-Parametern nicht oder getrennt zwischengespeichert werden.

## Die Suche braucht lange oder bricht ab

Die Suche beim Kalender dauert oft länger als eine normale Abfrage. Das Plugin wartet bis zu 15 Sekunden darauf. Reicht das nicht, erhöhen Sie die Wartezeit mit dem Filter `evangtermine_http_timeout` (siehe oben).

## Die Umlaute sehen falsch aus

Prüfen Sie die Einstellung „Zeichenkodierung“. Fast alle WordPress-Websites verwenden UTF-8.

## Mein eigenes CSS wirkt nicht

- Ist die Adresse in der Einstellung „CSS-Datei“ vollständig (mit `https://`) und im Browser erreichbar?
- Hat der Browser die Datei neu geladen? Leeren Sie den Cache des Browsers und eines Cache-Plugins.
- Sind Ihre Regeln spezifisch genug? Verwenden Sie die Container-Klassen als Vorspann, zum Beispiel `.et_teaser .et_content_row`.

Mehr dazu in [Eigenes CSS](eigenes-css.md).

## Der Mini-Kalender bleibt leer

Der Mini-Kalender wird im Browser der Besucher aufgebaut, seine Daten holt Ihr Server. Er bleibt leer, wenn

- der Host in den Einstellungen nicht zulässig ist (lokale oder private Adresse),
- Ihr Server den Kalender nicht erreicht (die Route antwortet dann mit einem Fehler),
- die REST-API Ihrer Website gesperrt ist (zum Beispiel durch ein Sicherheits-Plugin) oder
- JavaScript ausgeschaltet ist.

Öffnen Sie die Entwicklerwerkzeuge des Browsers (Taste F12), Reiter „Netzwerk“, und prüfen Sie die Abfragen an `/wp-json/evangtermine/v1/minical/`.

## Kann ich den Kalender einer anderen Landeskirche verwenden?

Ja, wenn die Landeskirche einen eigenen Kalender-Server mit derselben Schnittstelle betreibt. Tragen Sie den Hostnamen unter „Host des Kalenderbetreibers“ ein, siehe [Einstellungen](einstellungen.md#host-des-kalenderbetreibers).

## Wie kann ich eine Liste ohne Suchformular anzeigen?

Verwenden Sie `[et_teaser]` statt `[et_veranstalter]`. `[et_veranstalter]` zeigt immer das Filterformular.

## Datenschutz: Was wird an den Kalender gesendet?

Welche Daten Ihr Server und der Browser der Besucher an den Kalender senden, steht im Abschnitt „External service“ der `readme.txt` (auf der Seite des Plugins bei WordPress.org). Die Datenschutzerklärung des Kalenders finden Sie unter [www.evangelische-termine.de/datenschutz](https://www.evangelische-termine.de/datenschutz).

## Wo melde ich einen Fehler?

Als [Issue auf GitHub](https://github.com/habakuk007/evangtermine/issues). Sicherheitsprobleme melden Sie bitte nicht öffentlich, sondern wie in [SECURITY.md](../../SECURITY.md) beschrieben.
