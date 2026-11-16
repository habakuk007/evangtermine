# Evangelische Termine

WordPress plugin that shows the events of the German Protestant event calendar
[Evangelische Termine](https://www.evangelische-termine.de/) on your site, with shortcodes and a widget.

The plugin fetches the event list live from the calendar and puts it into the page.
The calendar is in German, so the output of the plugin and its settings page are in German too.

- WordPress.org page: <https://wordpress.org/plugins/evangtermine/>
- Issues: <https://github.com/habakuk007/evangtermine/issues>
- Full user documentation (all settings and parameters, FAQ, changelog): [readme.txt](readme.txt)

## Status

The plugin was **closed on WordPress.org on 2025-05-01** because of a security issue.
This repository is the maintained continuation. The security issues are fixed in version 3.4.
The work to get the plugin re-opened on WordPress.org is tracked in
[issue #17](https://github.com/habakuk007/evangtermine/issues/17).

Until the plugin is open again, it cannot be installed from the "Plugins" screen in WordPress.
Install it from this repository instead (see below).

## Requirements

- WordPress 5.0 or newer
- PHP 7.4 or newer
- Your server must be allowed to open outgoing HTTPS connections to the calendar host
  (default: `www.evangelische-termine.de`)

## Installation

1. Download the released state as a ZIP file:
   [master.zip](https://github.com/habakuk007/evangtermine/archive/refs/heads/master.zip).
   The `master` branch always holds the newest released version.
2. Unpack it, rename the folder from `evangtermine-master` to `evangtermine` and upload it to
   `/wp-content/plugins/`.
3. Activate the plugin on the "Plugins" screen in WordPress.
4. Enter your organiser ID (Veranstalter-ID) under "Settings" > "Evangelische Termine".
   You find the ID at the top right after you log in at <https://www.evangelische-termine.de/>.
5. Add a shortcode to a page or post, or add the widget to a widget area.

## Shortcodes

| Shortcode | What it shows |
|-----------|---------------|
| `[et_veranstalter]` | Event list with a filter and search form, pagination and a detail view |
| `[et_teaser]` | Event list without a search form |
| `[et_panels]` | Events as panels with their images |
| `[et_minical]` | The mini calendar of Evangelische Termine |

Examples:

```text
[et_veranstalter vid="952"]
[et_teaser vid="952,988" itemsperpage="5"]
[et_panels vid="952"]
[et_minical vid="952"]
```

### Attributes

All four shortcodes accept the same attributes. An attribute overrides the value from the settings page
for this one shortcode. The plugin passes the attributes on to the calendar as query parameters.

| Attribute | Meaning | Default |
|-----------|---------|---------|
| `vid` | Organiser ID, several IDs separated by commas, or `all` together with `region` | from the settings |
| `region` | Region (Dekanat) number, or several numbers separated by commas | from the settings |
| `eventtype` | ID of the event category; a leading `-` excludes the category | empty |
| `highlight` | `all` = all events, `high` = only highlights | `all` |
| `people` | Target group IDs, separated by commas | `0` |
| `person` | Contact person IDs, separated by commas | not set |
| `place` | Venue IDs, separated by commas | `all` |
| `ipm` | Event type IDs (input masks), separated by commas | `all` |
| `cha` | Channel IDs, separated by commas | `all` |
| `itemsperpage` | Number of events to show | `20` |
| `dest` | `extern` (public events), `intern` (internal events) or `all` | `extern` |
| `until` | Show an event until its end date: `yes` or `no` | `yes` |
| `q` | Search terms, separated by spaces | empty |

A `vid` is needed, in the settings or in the shortcode. Without it `[et_veranstalter]`, `[et_teaser]`
and `[et_panels]` show an error message instead of the events.

The parameters are explained in detail in the
[manual of Evangelische Termine](http://handbuch.evangelische-termine.de/Ausgabe-Parameter/) (German)
and in [readme.txt](readme.txt).

## Widget

The widget "Evangelische Termine" shows a list of the next events in a widget area.
Its form offers the same parameters as the shortcodes, plus a field for additional parameters
in the form `param1=value1&param2=value2`.

## External service

The plugin sends requests to an external service (Evangelische Termine) every time a shortcode or
the widget is shown. What is sent and what the visitor's browser loads from the service is described
in the section "External service" of [readme.txt](readme.txt).

## Contributing and security

- How to contribute: [CONTRIBUTING.md](CONTRIBUTING.md)
- How to report a security problem: [SECURITY.md](SECURITY.md). Please do not open a public issue for it.

## Credits

Originally developed by Norbert Räbiger ([regibaer](https://profiles.wordpress.org/regibaer)).
Maintained by [habakuk](https://profiles.wordpress.org/habakuk/).

## License

GPLv2 or later. See [LICENSE](LICENSE).
