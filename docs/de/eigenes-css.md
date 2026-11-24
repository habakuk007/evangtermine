# Eigenes CSS

Das Aussehen der Termine bestimmt ein Stylesheet. Sie haben drei Möglichkeiten.

| Einstellung „CSS-Datei“ | Wirkung |
|-------------------------|---------|
| leer | Es gilt das Stylesheet von Evangelische Termine. |
| Adresse einer CSS-Datei | Das Plugin bindet Ihre Datei ein. Sie ersetzt das Stylesheet von Evangelische Termine. |
| `nocss` | Es wird gar kein Stylesheet geladen. Das ist richtig, wenn Ihr Theme die Gestaltung schon enthält. |

## Eigene CSS-Datei einbinden

1. Legen Sie eine CSS-Datei an einer Stelle ab, die im Netz erreichbar ist, zum Beispiel in der Mediathek Ihrer Website oder im Ordner Ihres Child-Themes.
2. Tragen Sie die vollständige Adresse unter „Einstellungen“ > „Evangelische Termine“ > „CSS-Datei“ ein, zum Beispiel `https://www.example.org/wp-content/uploads/termine.css`.
3. Speichern Sie die Einstellungen.

Das Plugin bindet die Datei im Kopf jeder Seite Ihrer Website ein, nach den Stylesheets des Themes. Das Standard-Stylesheet von Evangelische Termine entfällt dann bei `[et_veranstalter]`, `[et_teaser]` und dem Widget. `[et_panels]` und `[et_minical]` bringen eigene Stylesheets mit; Ihre Datei ergänzt sie.

Die Adresse wird beim Speichern bereinigt; ungültige Zeichen werden entfernt. Das Shortcode-Attribut `css` können Sie nicht für eine Adresse verwenden, es akzeptiert nur den Wert `nocss`.

## Die Klassen

Die Elemente der Ausgabe haben Klassen und IDs, die mit `et_` beginnen. Die Container-Klassen unten stammen vom Plugin und bleiben erhalten. Alle anderen Namen liefert der Kalender.

Jede Ausgabe steckt in einem Container mit der Klasse des Moduls:

| Ausgabe von | Klasse des Containers |
|-------------|-----------------------|
| `[et_veranstalter]` | `et_veranstalter` |
| `[et_teaser]` | `et_teaser` |
| `[et_panels]` | `et_panel` |
| Widget | `et_sidebar` |
| `[et_minical]` | `#et_minicalendar` (eine ID) |

Die Elemente innerhalb des Containers liefert der Kalender. Häufige Klassen sind `et_content_row` (eine Zeile der Liste, abwechselnd mit `et_even` und `et_odd`), `et_content_title`, `et_content_date`, `et_placename`, `et_personname` und `et_description`. Die Namen kommen von Evangelische Termine; das Plugin kann sie nicht ändern. Welche Klassen es genau gibt, sehen Sie in den Entwicklerwerkzeugen Ihres Browsers (Rechtsklick auf ein Element, „Untersuchen“).

Beispiel: die Zeilen der Liste hervorheben.

```css
.et_teaser .et_content_row {
    padding: 0.5em;
    border-bottom: 1px solid #ccc;
}
```

Das Standard-Stylesheet des Teasers blendet einige Angaben aus, zum Beispiel den Ort (`.et_city`). Einblenden können Sie sie in Ihrem CSS:

```css
.et_teaser .et_city {
    display: inline;
}
```

## Das Feld „Mehr laden“ ausblenden

`[et_panels]` zeigt unter den Kacheln ein Feld „Mehr laden“. Es hat die ID `load1`. Zum Ausblenden fügen Sie in Ihr CSS ein:

```css
#load1 {
    display: none;
}
```

Wenn Sie keine eigene CSS-Datei eingebunden haben, tragen Sie die Regel in „Design“ > „Customizer“ > „Zusätzliches CSS“ ein (bei Block-Themes: „Design“ > „Editor“ > „Stile“ > „Zusätzliches CSS“).

## Hinweise

- Eine geänderte CSS-Datei sehen Besucher erst, wenn ihr Browser sie neu lädt. Der Zwischenspeicher des Plugins (5 Minuten bei Teaser und Widget) betrifft nur die Termine, nicht die CSS-Datei.
- Ob und von welchem Server der Browser der Besucher ein Stylesheet direkt lädt, steht im Abschnitt „External service“ der `readme.txt`. Mit einer eigenen CSS-Datei oder mit `nocss` in den Einstellungen wird das Standard-Stylesheet von Evangelische Termine nicht mehr eingebunden.
