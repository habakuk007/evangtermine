=== Evangelische Termine ===

Contributors: habakuk
Tags: events, calendar, church, evangelische termine, elkb
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Shows the events of the German Protestant event calendar "Evangelische Termine" on your site, with shortcodes and a widget.

== Description ==

"Evangelische Termine" (https://www.evangelische-termine.de/) is the event calendar of the Protestant churches in Germany. This plugin fetches the events of one or more organisers, or of a whole region (Dekanat), from that calendar and shows them in WordPress. Shortcodes and a widget control what is shown.

The calendar itself is in German, so the output of the plugin and its settings page are in German.

= Features =

* Shortcode `[et_veranstalter]`: event list with a filter and search form, pagination and a detail view
* Shortcode `[et_teaser]`: event list without a search form
* Shortcode `[et_panels]`: events as panels with their images
* Shortcode `[et_minical]`: the mini calendar of Evangelische Termine
* Widget "Evangelische Termine": list of the next events for a widget area
* Settings page for the defaults of the plugin
* Every default can be overridden per shortcode and per widget
* Your own CSS file can replace the styles of Evangelische Termine

= Shortcodes =

`[et_veranstalter]` calls the organiser module of Evangelische Termine. It shows a filter form and the event list below it.
`[et_veranstalter vid="952"]`

`[et_teaser]` calls the teaser module. It shows an event list without a search form.
`[et_teaser vid="952,988" itemsperpage="5"]`

`[et_panels]` shows the events as panels with their images.
`[et_panels vid="952"]`
To hide the panel with the "Mehr laden" (load more) link, add this to your CSS:

`
#load1 {
	display: none;
}
`

`[et_minical]` embeds the mini calendar of Evangelische Termine.
`[et_minical vid="952"]`

All shortcodes accept the parameters listed under "Settings and parameters".

== External service ==

This plugin fetches the events live from an external service: Evangelische Termine ([www.evangelische-termine.de](https://www.evangelische-termine.de)). The host can be changed in the settings, for example for the calendar of another regional church.

* **When:** every time `[et_veranstalter]`, `[et_teaser]`, `[et_panels]` or the widget is shown. The output of `[et_teaser]` and of the widget is cached for 5 minutes. `[et_minical]` loads its data with JavaScript from the visitor's browser, from the host in the settings; a click on an event loads the detail view from there in an iframe.
* **What is sent:** the query parameters (organiser ID, region ID, filters, a visitor's search term and page number, the ID of an event), the URL of the requesting page as Referer and the plugin version in the User-Agent. The request is made by your server, so the IP addresses of your visitors are not sent. `[et_minical]` is the exception, because the visitor's browser sends the request itself.
* **Returned content:** the HTML delivered by the service is shown on your site as it is. It can reference further files on www.evangelische-termine.de (styles, scripts, images), which the visitor's browser then loads from there.
* **Privacy policy of the service:** [https://www.evangelische-termine.de/datenschutz](https://www.evangelische-termine.de/datenschutz)

== Installation ==

1. Upload the folder `evangtermine` to `/wp-content/plugins/`, or install the plugin from the "Plugins" screen in WordPress.
2. Activate the plugin on the "Plugins" screen.
3. Enter your organiser ID under "Settings" > "Evangelische Termine".
4. Add a shortcode to a page or post, or add the widget "Evangelische Termine" to a widget area.

== Frequently Asked Questions ==

= Where do I find my organiser ID? =

Log in at https://www.evangelische-termine.de/. The organiser ID (Veranstalter-ID) is shown at the top right.

= Can I show a list without the search form? =

Yes. `[et_veranstalter]` always shows the filter form. Use `[et_teaser]` for a plain list.

= My regional church runs its own calendar host. Can I use it? =

Yes. Enter the host name under "Settings" > "Evangelische Termine" > "Host". Local and private addresses are rejected.

= The page shows "Der Veranstaltungskalender ist derzeit nicht verfügbar". =

The request to the calendar failed or took too long. Check that your server may open outgoing HTTPS connections to the calendar host and that the "Host" setting is correct. The plugin waits up to 15 seconds for an answer; the filter `evangtermine_http_timeout` changes that (1 to 30 seconds).

= New events do not show up in the widget immediately. =

The output of the widget and of `[et_teaser]` is cached for 5 minutes. The filter `evangtermine_cache_ttl` changes the lifetime in seconds; 0 switches the cache off.

= How do I use my own styles? =

Enter the URL of your CSS file under "CSS-Datei" in the settings. It replaces the stylesheet of Evangelische Termine. Enter `nocss` if your theme already contains the styles. The elements use class names starting with `et_`.

== Settings and parameters ==

= Settings =

The settings are under "Settings" > "Evangelische Termine".

**Veranstalter-ID** (organiser ID): shown at the top right after logging in at https://www.evangelische-termine.de/. Several IDs can be given as a comma-separated list without spaces, for example `id1,id2,id3`.

**Dekanats-ID** (region ID): the ID of a Dekanat in Evangelische Termine. It is sent as `region` and shows the events of all organisers in that region.

**Until**: whether events are shown until their end date (`yes` or `no`). Default: `yes`.

**CSS-Datei** (CSS file): the URL of a CSS file that is used instead of the stylesheet delivered by Evangelische Termine. Enter `nocss` to load no stylesheet at all, for example when the styles are part of your theme.

**Encoding**: the character encoding of your site (`utf8` or `latin1`). The data is delivered in that encoding. Default: `utf8`.

**Protokoll** (protocol): `http://` or `https://`. If nothing is selected, the plugin uses `https://`.

**Host**: the server that hosts the calendar. Default: `www.evangelische-termine.de`.

= Parameters =

The parameters are described in detail in the [manual of Evangelische Termine](http://handbuch.evangelische-termine.de/Ausgabe-Parameter/) (German). The plugin supports:

**vid**: organiser ID or several IDs separated by commas, for example `[et_teaser vid="952,988"]`.

**region**: three-digit region number, or several numbers separated by commas. The filter form of `[et_veranstalter]` then shows an additional field to choose the organiser. Set `vid` to `all` or to a preselected organiser.

**eventtype**: ID of the event category. A leading `-` excludes the category. Several IDs can be separated by commas.

**highlight**: `all` shows all events, `high` only the events marked as highlight.

**people**: comma-separated list of target group IDs.

**person**: comma-separated list of contact person IDs.

**place**: comma-separated list of venue IDs.

**ipm**: comma-separated list of event type IDs (input masks).

**cha**: comma-separated list of channel IDs.

**itemsperpage**: number of events to show. For `[et_veranstalter]` use 5, 10, 20, 30, 40, 50 or 100. Default: 20.

**dest**: which events are shown: `extern` (public only), `intern` (internal only) or `all`. Default: `extern`.

**until**: whether an event is shown until its end date (`yes` or `no`). Default: `yes`.

**q**: search terms, separated by spaces.

**auswahl**: `eb` shows adult education events in Bavaria, that is events created with the input mask or the channel "Erwachsenenbildung".

= Widget =

The widget form offers the same parameters, plus a field for additional parameters in the form `param1=value1&param2=value2`.

= Filters for developers =

* `evangtermine_http_timeout`: timeout of the request to the calendar in seconds (default 15, limited to 1 to 30).
* `evangtermine_cache_ttl`: lifetime of the cache for `[et_teaser]` and the widget in seconds (default 300, 0 switches it off).

= Bundled libraries =

* Bootstrap 3.3.7 (MIT), https://getbootstrap.com/ - used by `[et_panels]`
* Font Awesome 4.7.0 (font: SIL OFL 1.1, CSS: MIT), https://fontawesome.com/v4/ - used by `[et_panels]`
* jQuery UI 1.12.1 base theme, stylesheet and icons only (MIT), https://jqueryui.com/ - used by `[et_minical]`
* Colorbox 1.6.4 (MIT), https://www.jacklmoore.com/colorbox/ - used by `[et_minical]`

`[et_panels]` and `[et_minical]` use the jQuery that ships with WordPress; `[et_minical]` also uses its jQuery UI and moment.

= Alternative =

Evangelische Termine also offers its own widget that works in any CMS. You find it after logging in at https://www.evangelische-termine.de/ under "Information" > "Widgets".

= Credits =

Originally developed by Norbert Räbiger (https://profiles.wordpress.org/regibaer).

== Screenshots ==

1. Settings of the plugin
2. Output of the shortcode `[et_veranstalter]`
3. Output of the shortcode `[et_teaser]`
4. Widget settings
5. Output of the widget

== Changelog ==

= 3.4 =
* Security: fixed cross-site scripting and further security issues (including CVE-2025-28960). Request parameters are sanitized and URL-encoded, options and output are escaped, and the calendar host is checked against local and private addresses.
* Security: the widget settings are limited to the known fields and sanitized when saved; output in the widget form is escaped.
* Changed: the calendar is fetched over https:// by default.
* Changed: the output of `[et_teaser]` and of the widget is cached for 5 minutes (filter `evangtermine_cache_ttl`, 0 switches the cache off).
* Changed: jQuery is loaded from WordPress; the bundled copy was removed.
* Changed: `[et_minical]` uses jQuery, jQuery UI and moment from WordPress instead of its own copies (jQuery 1.12.4), and its styles and scripts are enqueued the WordPress way. The own CSS file from the settings is enqueued as well.
* Fixed: `[et_minical]` uses the host from the settings instead of a fixed address, and texts from the calendar are escaped in its tooltip.
* Fixed: the search in `[et_veranstalter]` often ran into a timeout ("Der Veranstaltungskalender ist derzeit nicht verfügbar"). The request now waits up to 15 instead of 5 seconds (filter `evangtermine_http_timeout`).
* Fixed: `[et_teaser]` is delivered with the default styles of Evangelische Termine as long as no own CSS file is set.
* Fixed: compatibility with PHP 8.0 to 8.5 (undefined array keys in the widget and its form, HTTP status through the WordPress API).
* New: deleting the plugin removes its settings and its cache (uninstall.php).
* New: `Requires PHP: 7.4`, description of the external service, readme in English.

= 3.3 =
* Fixed: display problem in `[et_panels]`.

= 3.2 =
* Fixed: jQuery is loaded locally instead of from a CDN.

= 3.1 =
* Fixed: `[et_veranstalter]` no longer passed on the search term.

= 3.0 =
* cURL is no longer required; the calendar is fetched with the HTTP API of WordPress.
* Fixed: session handling in `[et_veranstalter]`.

= 2.3.1 =
* Added: field for additional parameters in the widget.

= 2.3 =
* Added: shortcode `[et_minical]`.

= 2.2.15 =
* Fixed: deprecation "Non-static method should be called statically".

= 2.2.10 =
* Fixed: `cha` was ignored when `auswahl=eb` was set at the same time.

= 2.2.8 =
* Fixed: a new stylesheet link in the data of Evangelische Termine is removed again when `nocss` is set.

= 2.2.7 =
* Fixed: replaced the deprecated function `create_function`.

= 2.2.6 =
* Added: shortcode `[et_panels]`.
* Fixed: IPv6 resolution.

= 2.2.5 =
* Renamed the class `Autoloader` to `AutoloaderET` to avoid conflicts with other plugins.

= 2.2.3 =
* The accessibility icons in the detail view are loaded over SSL when available.

= 2.2 =
* The parameter `auswahl=eb` shows adult education events in Bavaria.

= 2.0 =
* Large parts of the code were rewritten.
* The scripts of Evangelische Termine are no longer removed.
* Fixed: Google Maps is loaded over https.
* Fixed: images are loaded from Evangelische Termine.

= 1.9 =
* New: full-text search with the parameter `q`.

= 1.8 =
* New: the host of the calendar can be set on the settings page, so the plugin can be used for any regional church.
* New: link to the settings on the "Plugins" screen.
* Fixed: saving the select fields on the settings page.

= 1.7 =
* Fixed: problem with `nocss`.

= 1.6 =
* Changed: `__construct()` in the widget class.

= 1.5 =
* Fixed: some scripts in the data of Evangelische Termine are removed.

= 1.2 =
* New: documentation.

= 1.1 =
* Fixed: session handling; values are passed directly to the pager.

== Upgrade Notice ==

= 3.4 =
Security update (CVE-2025-28960) and PHP 8 compatibility. Please update. New: 5 minute cache for teaser and widget.
