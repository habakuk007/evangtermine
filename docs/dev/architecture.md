# Architecture

The plugin has no runtime dependencies and no build step. It fetches HTML from the calendar host with the WordPress HTTP API and puts it into the page. Composer (PHPCS) and npm (end-to-end tests) are used for development only.

## Files

```text
evangtermine.php                    entry point: constants, shortcodes, widget, hooks
autoloader.php                      Evangtermine_Autoloader
uninstall.php                       removes options, widget settings and cache
application/
  configs/defaults.php              ET_DEFAULT_*, ET_OPTION_*, module names, template ids
  controllers/                      shortcodes (Evangtermine_Shortcode*) and the widget (EtWidget)
  models/class.evangtermine_query.php   builds the query, fetches and post-processes the HTML
  views/class.evangtermine_view.php     wraps the content in a <div>
  helper/                           custom CSS hook, debug output
  forms/                            settings page (wp-admin only), plugin row link, widget form
assets/                             bundled CSS, scripts and fonts (shipped)
languages/                          de_DE and de_DE_formal translations
tests/e2e/                          Playwright tests against WordPress Playground (not shipped)
docs/                               this documentation (not shipped)
```

Everything that is not needed at runtime is listed in `.distignore` and stays out of the WordPress.org package.

## Autoloader

`Evangtermine_Autoloader` maps the class `Foo_Bar` to `class.foo_bar.php` (lower case) in `application/controllers/`, `models/`, `views/`, `helper/` and `forms/`. One class per file. The constant `ET_BASE_PATH` is the plugin folder.

## Classes

| Class | Role |
|-------|------|
| `evangtermine` | Registers the shortcodes `et_veranstalter`, `et_teaser`, `et_panels`, `et_minical`, the widget, the options page (only in wp-admin), the custom CSS and the mini calendar assets |
| `Evangtermine_Query` | Merges settings, defaults and attributes; builds the query string; fetches and filters the remote HTML; host check; timeout and cache filters |
| `Evangtermine_ShortcodeVeranstalter` | List with filter form: reads the allowed request parameters |
| `Evangtermine_ShortcodeTeaser`, `Evangtermine_ShortcodePanels` | Teaser and panels: no request parameters |
| `Evangtermine_ShortcodeMinical` | Registers and enqueues scripts and styles; hands the parameters and the browser base URL to the script |
| `EtWidget` | `WP_Widget`; the class name is stored by WordPress in the database and must not change |
| `Evangtermine_View` | Wraps the output in a `<div>` with the class `et_veranstalter`, `et_teaser`, `et_panel` or `et_sidebar` |
| `Evangtermine_IncludeCSS` | Enqueues the custom CSS file from the settings |
| `Evangtermine_Debugging` | Console output, only if `ET_DEBUG` is true |
| `Evangtermine_PluginOptionsPage` | Settings link in the plugin row |

## Data flow

```text
shortcode attributes / widget instance
        |
        v
Evangtermine_Query::__construct   settings (get_option) + defaults
Evangtermine_Query::setEtParameters   attributes override
        |
(et_veranstalter only)  request values: vid, region, aid, date, eventtype, highlight, people,
        |               itemsPerPage, pageID, et_q / q  (reset=1 skips them)
        v
Evangtermine_Query::getQuerystring    names cleaned, values urlencode()d
        |
        v
Evangtermine_Query::retrieveStream
   - no vid                          -> error message
   - etID > 0 and detail allowed     -> module detail-php
   - host check (isSafeHost)         -> "calendar unavailable" on failure
   - cache (teaser module only)      -> transient et_stream_<md5>
   - wp_remote_get({protocol}{host}/{module}?{query})
   - filterStream()                  -> string replacements
   - localizeLibraries()             -> jQuery and the datepicker from WordPress
        |
        v
Evangtermine_View::buildView          wraps in <div class="...">
```

Modules (`application/configs/defaults.php`): `veranstaltungen-php` (list), `teaser`, `detail-php`, `panels`. The widget and `[et_teaser]` use the `teaser` module with the template ids 2 and 1 (`ET_TEMPLATE_TEASER_WIDGET`, `ET_TEMPLATE_TEASER_SHORTCODE`).

`[et_minical]` is the exception: PHP does not fetch anything. It outputs `<div id="et_minicalendar">` and a script with the parameters (`etMinicalData`); `assets/scripts/et_minical.js` loads the data in the visitor's browser.

### Parameter handling

- `Evangtermine_Query::setEtParameters()` takes every attribute and passes it on. Only `itemsperpage` is mapped to `itemsPerPage`, and the attribute `css` is accepted only with the value `nocss`.
- `getQuerystring()` skips `title`, cleans the parameter names (`cleanParameterName()`), URL-encodes every value and appends `additionalParameters` after removing every character that is not URL safe.
- Request parameters other than the ones above are not passed to the host.

### Filter state

The state of the filter and the pagination of `[et_veranstalter]` lives only in the URL. There is no PHP session. `filterStream()` rewrites the links in the remote HTML to the permalink of the current page plus the current query string. `reset=1` returns to the defaults of the shortcode.

### Output post-processing (`filterStream()`)

Replaces bundled libraries (Bootstrap, Font Awesome, jQuery UI CSS) with the copies in `assets/`, removes the host stylesheet if a custom CSS is set, rewrites `/Upload/` paths and the `_HOST_` placeholder links, turns `<h1>` into `<h2>` and removes obsolete attributes. The HTML itself is returned unfiltered by design (see [security-model.md](security-model.md)).

## Settings

`application/forms/options.php` registers seven options in the group `et_group`, each with a sanitize callback: `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`. The names have no prefix for historical reasons and must not be renamed. Values are per site in a multisite.

## Filters and constants

| Name | Type | Meaning |
|------|------|---------|
| `evangtermine_http_timeout` | filter | Timeout of the request in seconds, default 15, limited to 1 to 30 |
| `evangtermine_cache_ttl` | filter | Lifetime of the teaser and widget cache in seconds, default 300, `0` switches it off |
| `ET_DEFAULT_HOST`, `ET_DEFAULT_PROTOCOL`, `ET_OPTION_UNTIL`, `ET_DEFAULT_CHARSET` | constants | Defaults. They can be defined before the plugin loads (for example in `wp-config.php`); users override the host and protocol in the settings. Do not change the defaults in the plugin |
| `ET_DEBUG` | constant | Console output in `Evangtermine_Debugging`, default `false` |

With `WP_DEBUG` on, a failed fetch writes one line to the debug log (host and reason, no query string).

## Conventions that must not change

- Option names (`vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`)
- CSS class names starting with `et_`
- The class name `EtWidget`
- The shortcode names
- Text domain `evangtermine`, source strings in English, German in `languages/`

## Tests and tooling

- PHP syntax and compatibility (PHP 7.4 to 8.5) and the WordPress Coding Standard run in CI (`composer lint:compat`, `composer lint:php`).
- End-to-end tests run in WordPress Playground with a mocked calendar host: see [tests/e2e/README.md](../../tests/e2e/README.md).
