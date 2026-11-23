=== Evangelische Termine ===

Contributors: habakuk
Tags: events, calendar, church, evangelische termine, elkb
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://trumpkin.de/donate

Shows the events of the German Protestant event calendar "Evangelische Termine" on your site, with shortcodes and a widget.

== Description ==

"Evangelische Termine" (https://www.evangelische-termine.de/) is the event calendar of the Protestant churches in Germany. This plugin fetches the events of one or more organisers, or of a whole region (Dekanat), from that calendar and shows them in WordPress. Shortcodes and a widget control what is shown.

The calendar itself is in German, so its output is in German.

= Shortcodes =

* `[et_veranstalter]`: event list with a filter form, pagination and detail view, for example `[et_veranstalter vid="952"]`
* `[et_teaser]`: event list without a search form, for example `[et_teaser vid="952,988" itemsperpage="5"]`
* `[et_panels]`: events as panels with their images, for example `[et_panels vid="952"]`
* `[et_minical]`: the mini calendar of Evangelische Termine, for example `[et_minical vid="952"]`

The widget "Evangelische Termine" shows the next events in a widget area. Every default from the settings can be overridden per shortcode and per widget. Documentation (German): https://github.com/habakuk007/evangtermine/tree/master/docs

To hide the "Mehr laden" (load more) panel of `[et_panels]`, add `#load1 { display: none; }` to your CSS.

== External service ==

This plugin fetches the events live from an external service: Evangelische Termine ([www.evangelische-termine.de](https://www.evangelische-termine.de)). The host can be changed in the settings, for example for the calendar of another regional church.

* **When:** every time `[et_veranstalter]`, `[et_teaser]`, `[et_panels]` or the widget is shown. The output of `[et_teaser]` and of the widget is cached for 5 minutes. `[et_minical]` loads its data from your own site: its JavaScript calls two public REST routes of this plugin (`/wp-json/evangtermine/v1/minical/events` and `/detail`), and your server fetches the data from the host in the settings. These answers are cached for 5 minutes too.
* **What is sent:** the query parameters (organiser ID, region ID, filters, a visitor's search term and page number, the ID of an event), the URL of the requesting page as Referer and the plugin version in the User-Agent. This request is made by your server, so it does not contain the IP addresses of your visitors. For `[et_minical]` only known filter parameters are passed on (organiser, region, filters, search term), the month and the ID of an event.
* **Returned content:** the HTML delivered by the service is shown on your site as it is, without filtering. Only use a host that you trust. Scripts and libraries (jQuery, jQuery UI, Bootstrap, Font Awesome) are loaded from WordPress and from this plugin, not from the service.
* **What the visitor's browser loads directly:** the images of events in `[et_panels]` and in the detail view, which are stored at the service's hosting provider (currently `et-live.obsv3.de-global-1.spacenet.cloud`) or at the service itself; the popup of `[et_minical]` (shown after a click on an event) additionally references stylesheets, scripts, fonts and images of the service. These servers receive the IP address and the user agent of the visitor. The calendar and the list of events of `[et_minical]`, `[et_teaser]` and the widget load nothing from other servers.
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

= The page shows "The event calendar is currently unavailable". =

The request to the calendar failed or took too long. Check that your server may open outgoing HTTPS connections to the calendar host and that the "Host" setting is correct. The plugin waits up to 15 seconds for an answer; the filter `evangtermine_http_timeout` changes that (1 to 30 seconds).

= New events do not show up in the widget immediately. =

The output of the widget and of `[et_teaser]` is cached for 5 minutes. The filter `evangtermine_cache_ttl` changes the lifetime in seconds; 0 switches the cache off.

= How do I use my own styles? =

Enter the URL of your CSS file under "CSS-Datei" in the settings. It replaces the stylesheet of Evangelische Termine. Enter `nocss` if your theme already contains the styles. The elements use class names starting with `et_`.

== Settings and parameters ==

= Settings =

The settings are under "Settings" > "Evangelische Termine". Several organiser IDs (Veranstalter-ID) are given as a comma-separated list without spaces. The CSS file (CSS-Datei) replaces the stylesheet of Evangelische Termine; enter `nocss` to load no stylesheet. The encoding is `utf8` (default) or `latin1`, the protocol `http://` or `https://` (default), the host (Host des Kalenderbetreibers) defaults to `www.evangelische-termine.de`.

= Parameters =

The parameters are described in the [manual of Evangelische Termine](https://handbuch.evangelische-termine.de/anzeige-im-internet/ausgabe-parameter) (German). The plugin supports:

**vid**: organiser ID or several IDs separated by commas, for example `[et_teaser vid="952,988"]`.

**region**: three-digit region number, or several numbers separated by commas. `[et_veranstalter]` then shows a field to choose the organiser. Set `vid` to `all` or to a preselected organiser.

**eventtype**: ID of the event category. A leading `-` excludes it. Several IDs can be separated by commas.

**highlight**: `all` or `high` (only highlighted events).

**people**, **person**, **place**, **ipm**, **cha**: comma-separated IDs of target groups, contact persons, venues, event types (input masks) and channels.

**itemsperpage**: number of events. For `[et_veranstalter]` use 5, 10, 20, 30, 40, 50 or 100. Default: 20.

**dest**: `extern` (public only, default), `intern` or `all`.

**until**: show an event until its end date (`yes`, default) or not (`no`).

**q**: search terms, separated by spaces.

**auswahl**: `eb` shows adult education events in Bavaria (input mask or channel "Erwachsenenbildung").

= Widget =

The widget form offers these parameters except `people` and `person`, plus a field for additional parameters in the form `param1=value1&param2=value2`.

= Filters for developers =

* `evangtermine_http_timeout`: request timeout in seconds (default 15, 1 to 30).
* `evangtermine_cache_ttl`: cache lifetime of `[et_teaser]` and the widget in seconds (default 300, 0 = off).

= Bundled libraries =

* Bootstrap 3.4.1, CSS only (MIT), https://getbootstrap.com/ - used by `[et_panels]`. Last 3.x release, fixes the known CVEs. No Bootstrap JS.
* Font Awesome 4.7.0, CSS and woff/woff2 fonts (OFL 1.1 / MIT), https://fontawesome.com/v4/ - used by `[et_panels]`. No JavaScript, no known CVE.
* jQuery UI 1.12.1 base theme (MIT), https://jqueryui.com/ - used by `[et_minical]` and the date filter of `[et_veranstalter]`
* Colorbox 1.6.4 (MIT), https://www.jacklmoore.com/colorbox/ - used by `[et_minical]`. Final release, no known CVE.

jQuery, the date picker and moment come from WordPress.

= Alternative =

Evangelische Termine also has its own widget for any CMS (after login at the service: "Information" > "Widgets").

= Credits =

Originally developed by Norbert Räbiger (https://profiles.wordpress.org/regibaer).

== Screenshots ==

1. Settings of the plugin
2. Output of the shortcode `[et_veranstalter]`
3. Output of the shortcode `[et_teaser]`
4. Widget settings
5. Output of the widget

== Changelog ==

= 3.5.0 =
* Changed: the default stylesheet and the placeholder image of the service are delivered by the plugin. Visitors' browsers no longer load them from the service.
* Changed: `[et_minical]` no longer calls the Evangelische Termine from the visitor's browser (this sent the visitor's IP address). The data and the detail view of an event come through two public read-only REST routes of this plugin; your server fetches them from the host in the settings. Only known parameters are passed on, and the answers are cached for 5 minutes (filter `evangtermine_cache_ttl`).
* Changed: the settings page explains every setting below its field, and all labels and values are translated. The stored values do not change.
* Changed: the source strings of the plugin are English now (WordPress.org convention); German translations for `de_DE` and `de_DE_formal` are included.
* Security: unknown request parameters are no longer passed on to the calendar host, the `css` shortcode attribute only accepts `nocss`, and the settings page warns when `http://` is saved.
* Added: a "Documentation" link on the settings page and in the plugin row; user and developer documentation in the `docs/` folder of the repository.

See changelog.txt for older entries.

== Upgrade Notice ==

= 3.4 =
Security update (CVE-2025-28960) and PHP 8 compatibility. Please update. New: 5 minute cache for teaser and widget.
