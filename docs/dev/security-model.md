# Security model

This page states what the plugin guarantees and what it trusts. It contains no exploit details. To report a problem, follow [SECURITY.md](../../SECURITY.md); please do not open a public issue.

## Trust boundaries

| Source | Trusted? | Handling |
|--------|----------|----------|
| Administrator (settings page, widget form) | Yes, but input is validated | Sanitize callbacks per option; widget `update()` keeps only the known fields; capability check `manage_options` on the settings page |
| Page author (shortcode attributes) | Yes | Attributes are passed to the calendar as query parameters, names cleaned and values URL-encoded |
| Visitor (URL and form values) | **No** | Only an allowlist of filter parameters is read, values are sanitized and URL-encoded; everything else is dropped |
| Calendar host (remote HTML) | **Trusted by design** | Returned as received, see below |

## Guarantees

- **Allowed hosts.** The host from the settings must be a plain host name (optionally with a port), without scheme or path. Local and private addresses are rejected: `localhost`, `*.localhost`, private and reserved IP ranges, and names that resolve to reserved ranges (including the link-local address range used for cloud metadata). The check runs when the setting is saved and again before every fetch. The request is made with WordPress' `reject_unsafe_urls`, which also refuses unusual ports.
- **No request data in the host or the path.** The module name is a constant. Visitor values only reach the query string, where every value is URL-encoded and every parameter name is reduced to a safe character set. The detail id is cast to an integer.
- **Request parameters are an allowlist.** `[et_veranstalter]` reads only the documented filter parameters from GET and POST (never cookies). Other request parameters are not passed on to the calendar host. Shortcode attributes are set by page authors, not by visitors.
- **Escaping.** Option values printed on the settings page and in the CSS link are escaped. The widget title is escaped. The values the plugin writes into links of the remote HTML are escaped. The debug output encodes values as a JSON string.
- **Custom CSS.** The URL of the custom CSS is validated with `esc_url_raw()` when saved. The attribute `css` accepts only `nocss`.
- **Widget form.** Select fields accept only their allowed values; `additionalParameters` is reduced to URL-safe characters.
- **No session.** The plugin does not use the PHP session and stores no data about visitors. The only server-side state is the 5-minute cache of the teaser output (transients).
- **Bundled libraries.** The libraries that are needed in the browser are shipped in `assets/` (no CDN). jQuery, jQuery UI and moment come from WordPress.
- **Uninstall.** `uninstall.php` removes the options, the widget settings and the cache.

## What the plugin trusts

The HTML delivered by the calendar host is output **as it is**, without filtering. It contains forms, inline scripts and links that the plugin cannot sanitize without breaking the calendar. The administrator chooses the host, so the host in the settings must be a server the site owner trusts. A host that is compromised can inject content into the page. The mini calendar escapes the texts of the calendar in its tooltip.

The plan for 4.0 replaces this by JSON data and templates owned by the plugin (see [../specs/4.0-event-pages.md](../specs/4.0-event-pages.md)).

## What the visitor's browser contacts

Besides your site, the visitor's browser can contact external servers directly (for example for event images; the mini calendar goes through a same-origin REST route and does not contact the calendar host from the browser). The list is in the section "External service" of `readme.txt` and is kept there; it is the source of truth.

## Reviewing a change

When a change takes request data and puts it into the remote URL or into returned HTML (`Evangtermine_ShortcodeVeranstalter`, `Evangtermine_Query::getQuerystring`, `Evangtermine_Query::filterStream`), or prints an option value (`options.php`, `Evangtermine_IncludeCSS`), run the review of `/wp-security-review` and keep the E2E security tests green (`tests/e2e/specs/security.spec.js`).
