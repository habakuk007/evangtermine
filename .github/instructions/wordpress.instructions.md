---
applyTo: 'wp-content/plugins/**,wp-content/themes/**,**/*.php,**/*.inc,**/*.js,**/*.jsx,**/*.ts,**/*.tsx,**/*.css,**/*.scss,**/*.json'
description: 'Coding, security, and testing rules for WordPress plugins and themes'
---

# WordPress Development — Copilot Instructions

**Goal:** Generate WordPress code that is secure, performant, testable, and compliant with official WordPress practices. Prefer hooks, small functions, dependency injection (where sensible), and clear separation of concerns.

## 1) Core Principles
- Never modify WordPress core. Extend via **actions** and **filters**.
- For plugins, always include a header and guard direct execution in entry PHP files.
- Use unique prefixes or PHP namespaces to avoid global collisions.
- Enqueue assets; never inline raw `<script>`/`<style>` in PHP templates.
- Make user‑visible strings translatable and load the correct text domain.

### Plugin header — all available fields

*Source: [Plugin Header Requirements](https://developer.wordpress.org/plugins/the-basics/header-requirements/)*

- `Plugin Name:` *(required)* — displayed in the wp-admin Plugins list.
- `Plugin URI:` — unique URL for the plugin's home page; cannot be a WordPress.org URL.
- `Description:` — ≤ 140 characters; shown under the plugin name in wp-admin.
- `Version:` — use `version_compare()`-compatible format (e.g. `1.0.3`; note `1.02 > 1.1` in PHP).
- `Requires at least:` — minimum WordPress version (e.g. `6.0`).
- `Requires PHP:` — minimum PHP version (e.g. `7.4`).
- `Author:` — plugin author name(s).
- `Author URI:` — author's website.
- `License:` — short licence slug, e.g. `GPL-2.0-or-later`.
- `License URI:` — link to the full licence text.
- `Text Domain:` — must match the string in `load_plugin_textdomain()` and all i18n calls.
- `Domain Path:` — where translation files live, e.g. `/languages`.
- `Update URI:` — prevents accidental WP.org update hijacking for externally-distributed plugins.
- `Requires Plugins:` — comma-separated WP.org slugs of required plugins (WordPress 6.5+).
- `Network:` — set `true` only for network-wide plugins; omit otherwise.

```php
<?php
defined( 'ABSPATH' ) || exit;
/**
 * Plugin Name:       Awesome Feature
 * Plugin URI:        https://example.com/awesome-feature/
 * Description:       Example plugin scaffold.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Example Author
 * Author URI:        https://example.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       awesome-feature
 * Domain Path:       /languages
 * Update URI:        https://example.com/awesome-feature/
 */
```

### Plugin Lifecycle (activation / deactivation / uninstall)

*Source: [Activation & Deactivation Hooks](https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/) · [Uninstall Methods](https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/)*

- **Activation** — `register_activation_hook( __FILE__, $cb )`: create default options via `add_option()`, create custom DB tables, call `flush_rewrite_rules()`.
- **Deactivation** — `register_deactivation_hook( __FILE__, $cb )`: clear temporary data, unschedule WP-Cron tasks, call `flush_rewrite_rules()`. **Never delete permanent data here** — data must survive deactivation/reactivation.
- **Uninstall** — use `uninstall.php` (preferred over `register_uninstall_hook()`). First line must be `defined( 'WP_UNINSTALL_PLUGIN' ) || exit;`. Delete all plugin options, transients, custom tables, and any capabilities added by the plugin.
- **Multisite** — call `delete_site_option()` in addition to `delete_option()` on uninstall for any network-wide data.
- **Updates** — `register_activation_hook` is NOT called on plugin updates. For DB schema upgrades, store a version option and compare it on the `plugins_loaded` hook, then call the upgrade function manually.

```php
// Activation
register_activation_hook( __FILE__, 'myplugin_activate' );
function myplugin_activate() {
    add_option( 'myplugin_version', '1.0' );
    flush_rewrite_rules();
}

// Deactivation — temp cleanup only, never delete permanent data
register_deactivation_hook( __FILE__, 'myplugin_deactivate' );
function myplugin_deactivate() {
    $ts = wp_next_scheduled( 'myplugin_cron_hook' );
    if ( $ts ) {
        wp_unschedule_event( $ts, 'myplugin_cron_hook' );
    }
    flush_rewrite_rules();
}
```

```php
// uninstall.php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
delete_option( 'myplugin_settings' );
delete_option( 'myplugin_version' );
```

### Prefix & File Conventions

*Source: [Best Practices](https://developer.wordpress.org/plugins/plugin-basics/best-practices/)*

- Prefix **all** PHP functions, classes, namespaces, global variables, option names, transient keys, and hook names with a unique plugin prefix (≥ 4–5 characters recommended).
- Forbidden prefixes: `wp_`, `WordPress`, `__` (double underscore). Never reuse another product's registered prefix.
- Begin every directly-accessible PHP file with: `defined( 'ABSPATH' ) || exit;`
- Conventional subfolder layout: `/includes` (PHP classes), `/admin` (admin-only), `/public` (frontend-only), `/assets` or `/js` + `/css` (static resources), `/languages` (translations).
- `is_admin()` reflects the admin-area page-load context, not the user's role. Always pair it with `current_user_can()` for actual permission checks.

### Actions and Filters — Authoring Rules

*Source: [Actions](https://developer.wordpress.org/plugins/hooks/actions/) · [Filters](https://developer.wordpress.org/plugins/hooks/filters/) · [Custom Hooks](https://developer.wordpress.org/plugins/hooks/custom-hooks/)*

- **Actions** (`do_action` / `add_action`): callbacks perform work and must **not** return values (the return value is silently discarded by WordPress).
- **Filters** (`apply_filters` / `add_filter`): callbacks receive a value, optionally modify it, and **must `return`** the value — even if unchanged. Discarding the input silently breaks all later-hooked callbacks.
- Keep filter callbacks as pure transforms; avoid side effects (file I/O, emails, DB writes) inside a filter.
- Priorities: integers, default `10`; lower numbers run earlier. Negative integers and `PHP_INT_MAX` are valid.
- Always prefix custom hook names with your plugin prefix (e.g. `myplugin_before_render`, not just `before_render`).
- Wrap significant plugin output values in `apply_filters()` to make the plugin extensible without modification.

```php
// Filter callback MUST return the value
add_filter( 'myplugin_button_label', 'myplugin_custom_label' );
function myplugin_custom_label( $label ) {
    return $label . ' →'; // always return
}

// Create extensible output
$heading = apply_filters( 'myplugin_page_heading', __( 'My Page', 'my-plugin' ) );
echo '<h1>' . esc_html( $heading ) . '</h1>';
```

## 2) Coding Standards (PHP, JS, CSS, HTML)
- Follow **WordPress Coding Standards (WPCS)** and write DocBlocks for public APIs.
- PHP: Prefer strict comparisons (`===`, `!==`) where appropriate. Be consistent with array syntax and spacing as per WPCS.
- JS: Match WordPress JS style; prefer `@wordpress/*` packages for block/editor code.
- CSS: Use BEM‑like class naming when helpful; avoid over‑specific selectors.
- PHP 7.4+ compatible patterns unless the project specifies higher. Avoid using features not supported by target WP/PHP versions.

### Linting setup suggestions
```xml
<!-- phpcs.xml -->
<?xml version="1.0"?>
<ruleset name="Project WPCS">
  <description>WordPress Coding Standards for this project.</description>
  <file>./</file>
  <exclude-pattern>vendor/*</exclude-pattern>
  <exclude-pattern>node_modules/*</exclude-pattern>
  <rule ref="WordPress"/>
  <rule ref="WordPress-Docs"/>
  <rule ref="WordPress-Extra"/>
  <rule ref="PHPCompatibility"/>
  <config name="testVersion" value="7.4-"/>
</ruleset>
```

```json
// composer.json (snippet)
{
  "require-dev": {
    "dealerdirect/phpcodesniffer-composer-installer": "^1.0",
    "wp-coding-standards/wpcs": "^3.0",
    "phpcompatibility/php-compatibility": "^9.0"
  },
  "scripts": {
    "lint:php": "phpcs -p",
    "fix:php": "phpcbf -p"
  }
}
```

```json
// package.json (snippet)
{
  "devDependencies": {
    "@wordpress/eslint-plugin": "^x.y.z"
  },
  "scripts": {
    "lint:js": "eslint ."
  }
}
```

## 3) Security & Data Handling

*Official references: [Security](https://developer.wordpress.org/apis/security/) · [Escaping](https://developer.wordpress.org/apis/security/escaping/) · [Sanitizing](https://developer.wordpress.org/apis/security/sanitizing/) · [Nonces](https://developer.wordpress.org/apis/security/nonces/)*

- **Escape on output — escape late.** Escape at the point of output, not earlier. Escaping too early and then concatenating can silently double-escape data.
  - HTML element content: `esc_html()` · HTML attribute: `esc_attr()` · URL in `href`/`src`: `esc_url()`
  - URL stored in DB: `esc_url_raw()` · Inline JS value: `esc_js()` · `<textarea>` content: `esc_textarea()`
  - XML/XSL context: `esc_xml()` · Trusted post HTML: `wp_kses_post()` · Custom allowed HTML: `wp_kses( $html, $allowed_tags )`
  - Integers: `absint()` / `(int)`
  - Combined escape + translation (preferred over separate calls): `esc_html__()`, `esc_html_e()`, `esc_html_x()`, `esc_attr__()`, `esc_attr_e()`, `esc_attr_x()`
- **Sanitize on input.** Prefer validation (reject bad input) over sanitization where the expected format is known.
  - Generic single-line text: `sanitize_text_field()` · Multi-line: `sanitize_textarea_field()`
  - Email: `sanitize_email()` · URL: `sanitize_url()` / `esc_url_raw()` · Hex colour: `sanitize_hex_color()`
  - CSS class: `sanitize_html_class()` · Key/identifier: `sanitize_key()` · Integer: `absint()` / `intval()`
  - HTML from editors: `wp_kses_post()`
- **Nonces & capabilities** for forms, AJAX, REST:
  - Add nonces: `wp_nonce_field()` (forms), `wp_create_nonce()` (AJAX/JS).
  - Verify: `check_admin_referer()` (admin forms), `check_ajax_referer()` (AJAX), `wp_verify_nonce()` (other contexts).
  - **Nonces are not authentication or authorisation.** Always pair nonce verification with `current_user_can()`. A valid nonce alone must never grant access.
  - Default nonce lifetime is 24 h; adjust via the `nonce_life` filter if needed.
- **Database:** always use `$wpdb->prepare()` with `%s`/`%d`/`%f` placeholders; never concatenate untrusted input into SQL.
- **Uploads:** validate MIME type; use `wp_handle_upload()` or `media_handle_upload()`.
- **User capabilities:** call `current_user_can( $capability )` before any write operation — a valid nonce alone is not authorisation.
  - WP role hierarchy (ascending): Subscriber → Contributor → Author → Editor → Administrator. Each role inherits all permissions of lower roles.
  - Use the most specific capability available (e.g. `edit_posts`, `edit_others_posts`, `manage_options`).
- **`$_REQUEST` is insecure:** cookies can override POST form values in `$_REQUEST`, making it trivially exploitable. Always use `$_POST` or `$_GET` explicitly.
- **Safe redirects:** after any mutating action, use `wp_safe_redirect( $url )` (validates the redirect to the allowed-hosts list) followed immediately by `exit`.

*Source: [Checking User Capabilities](https://developer.wordpress.org/plugins/security/checking-user-capabilities/)*

```php
// Escape late — at the point of output
echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
echo esc_html__( 'Save settings', 'my-plugin' );

// Nonce: admin form
wp_nonce_field( 'save-settings_' . $post_id );
check_admin_referer( 'save-settings_' . $post_id );

// Nonce: AJAX
wp_localize_script( 'my-js', 'myData', [ 'nonce' => wp_create_nonce( 'my-action' ) ] );
check_ajax_referer( 'my-action' );
```

## 4) Internationalization (i18n)
- Wrap user‑visible strings with translation functions using your text domain:
  - `__( 'Text', 'domain' )` (returns), `_e( 'Text', 'domain' )` (echoes).
  - With context: `_x( 'Text', 'context', 'domain' )` / `_ex()` — for the same string with different meanings in different UI contexts.
  - Plurals: `_n( 'One item', '%s items', $count, 'domain' )` — always pass all 4 arguments.
  - Combined escape + translate (preferred for output): `esc_html__()`, `esc_html_e()`, `esc_attr__()`, `esc_attr_e()`.
- **Text domain rules:**
  - Must match the plugin slug exactly: dashes not underscores, all lowercase (e.g. `my-plugin`, not `my_plugin`).
  - **Never use a variable** as the text domain argument — the WP string extractor cannot find it.
- **Strings with PHP variables** — use `printf()` + `__()` with `%s` / `%1$s` placeholders; never interpolate variables inside `__()` or `_e()`:
  ```php
  // CORRECT
  printf( __( 'Hello, %s!', 'my-plugin' ), esc_html( $username ) );
  // WRONG — not extractable and never translated at runtime
  _e( "Hello, $username!", 'my-plugin' );
  ```
- Add translator hints immediately before calls with placeholders: `/* translators: %s is the user's display name */`
- Assume translated strings may be up to twice as long; design layouts to accommodate.
- Locale-aware formatting: `number_format_i18n()` for numbers; `date_i18n( $format, $timestamp )` for dates.
- Load translations: `load_plugin_textdomain( 'domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' )` on the `init` hook. **Since WP 4.6**, this is optional for plugins hosted on WordPress.org — set `Requires at least: 4.6` and WP fetches translations automatically.
- Keep a `.pot` in `/languages`; regenerate with WP-CLI: `wp i18n make-pot . languages/my-plugin.pot`.

*Source: [How to Internationalize Your Plugin](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/)*

## 5) Performance
- Defer heavy logic to specific hooks; avoid expensive work on `init`/`wp_loaded` unless necessary.
- Use transients or object caching for expensive queries; plan invalidation.
- Enqueue only what you need and conditionally (front vs admin; specific screens/routes).
- Prefer paginated/parameterized queries over unbounded loops.

### Transients API

*Source: [Transients API](https://developer.wordpress.org/apis/transients/)*

Transients are the preferred mechanism for caching data that is expensive to generate. They are stored in the object cache (or DB as fallback) and expire automatically.

- `set_transient( $key, $value, $expiry )` — store a value for up to `$expiry` seconds.
- `get_transient( $key )` — return the cached value, or `false` if expired / not found.
- `delete_transient( $key )` — remove explicitly when the underlying data changes.
- Multisite: use `set_site_transient()` / `get_site_transient()` / `delete_site_transient()` for network-wide data.
- **Expiry is a maximum, not a guaranteed minimum.** A transient can be evicted before its expiry (e.g. object-cache memory pressure). Always have fallback code.
- **Check with `=== false`** (identity), not `== false` — empty strings, `0`, and empty arrays are valid cached values.
- Transient key maximum length: 172 characters.
- WP time constants for readability: `MINUTE_IN_SECONDS`, `HOUR_IN_SECONDS`, `DAY_IN_SECONDS`, `WEEK_IN_SECONDS`, `MONTH_IN_SECONDS`, `YEAR_IN_SECONDS`.
- Hook transient deletion to events that invalidate the data (e.g. `save_post`).

```php
// Standard transient cache pattern
$data = get_transient( 'myplugin_api_response' );
if ( false === $data ) {
    $data = myplugin_fetch_from_api(); // expensive call
    set_transient( 'myplugin_api_response', $data, HOUR_IN_SECONDS );
}

// Invalidate when source data changes
add_action( 'save_post', function() {
    delete_transient( 'myplugin_api_response' );
} );
```

## 6) Admin UI & Settings
- Use **Settings API** for options pages; provide `sanitize_callback` for each setting.
- For tables, follow `WP_List_Table` patterns. For notices, use the admin notices API.
- Avoid direct HTML echoing for complex UIs; prefer templates or small view helpers with escaping.

## 7) REST API
- Register with `register_rest_route()`; always set a `permission_callback`.
- Validate/sanitize request args via the `args` schema.
- Return `WP_REST_Response` or arrays/objects that map cleanly to JSON.

## 8) Blocks & Editor (Gutenberg)
- Use `block.json` + `register_block_type()`; rely on `@wordpress/*` packages.
- Provide server render callbacks when needed (dynamic blocks).
- E2E tests should cover: insert block → edit → save → front‑end render.

## 9) Asset Loading
```php
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'af-frontend',
    plugins_url('assets/frontend.css', __FILE__),
    [],
    '0.1.0'
  );

  wp_enqueue_script(
    'af-frontend',
    plugins_url('assets/frontend.js', __FILE__),
    [ 'wp-i18n', 'wp-element' ],
    '0.1.0',
    true
  );
});
```
- Use `wp_register_style/script` to register first if multiple components depend on the same assets.
- For admin screens, hook into `admin_enqueue_scripts` and check screen IDs.

## 10) Testing
### PHP Unit/Integration
- Use **WordPress test suite** with `PHPUnit` and `WP_UnitTestCase`.
- Test: sanitization, capability checks, REST permissions, DB queries, hooks.
- Prefer factories (`self::factory()->post->create()` etc.) to set up fixtures.

```xml
<!-- phpunit.xml.dist (minimal) -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php" colors="true">
  <testsuites>
    <testsuite name="Plugin Test Suite">
      <directory suffix="Test.php">tests/</directory>
    </testsuite>
  </testsuites>
</phpunit>
```

```php
// tests/bootstrap.php (minimal sketch)
<?php
$_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';
require_once $_tests_dir . '/includes/functions.php';
tests_add_filter( 'muplugins_loaded', function () {
  require dirname(__DIR__) . '/evangtermine.php';
} );
require $_tests_dir . '/includes/bootstrap.php';
```
### E2E
- Use Playwright (or Puppeteer) for editor/front‑end flows.
- Cover basic user journeys and regressions (block insertion, settings save, front‑end render).

## 11) Documentation & Commits
- Keep `README.md` up to date: install, usage, capabilities, hooks/filters, and test instructions.
- Use clear, imperative commit messages; reference issues/tickets and summarize impact.

## 14) HTTP API & External Requests

*Source: [HTTP API](https://developer.wordpress.org/plugins/http-api/) · [Making HTTP Requests](https://developer.wordpress.org/apis/making-http-requests/)*

- Always use the WP HTTP API for external requests — never raw `cURL` or `file_get_contents()`.
  - `wp_remote_get( $url, $args )` — GET request; default timeout 5 s, 5 redirects.
  - `wp_remote_post( $url, $args )` — POST request; pass data in `$args['body']`.
  - `wp_remote_head( $url, $args )` — HEAD request; check headers before a full GET to save bandwidth.
  - `wp_remote_request( $url, $args )` — arbitrary method (DELETE, PUT, PATCH); set `'method'` in `$args`.
- **Always check `is_wp_error( $response )`** before using the response — network failures return a `WP_Error`.
- Response helpers: `wp_remote_retrieve_response_code( $resp )`, `wp_remote_retrieve_body( $resp )`, `wp_remote_retrieve_header( $resp, 'content-type' )`.
- Gate all external calls behind explicit user opt-in (see Section 13 Guideline 7).
- Cache API responses with transients to avoid repeated network calls and respect remote rate limits.

```php
$response = wp_remote_get( 'https://api.example.com/data' );
if ( is_wp_error( $response ) ) {
    // $response->get_error_message()
    return;
}
$code = wp_remote_retrieve_response_code( $response ); // e.g. 200
$body = wp_remote_retrieve_body( $response );          // raw response body
```

## 15) WP-Cron

*Source: [Cron](https://developer.wordpress.org/plugins/cron/) · [Scheduling WP-Cron Events](https://developer.wordpress.org/plugins/cron/scheduling-wp-cron-events/)*

- WP-Cron is **page-load-triggered**, not a real system daemon. Tasks only fire when a page loads; on low-traffic sites they may be delayed significantly.
- **Setup pattern (three steps):**
  1. Register the callback: `add_action( 'myplugin_cron_hook', 'myplugin_cron_callback' );`
  2. Schedule with a guard (e.g. in the activation hook or on `wp_loaded`):
     ```php
     if ( ! wp_next_scheduled( 'myplugin_cron_hook' ) ) {
         wp_schedule_event( time(), 'hourly', 'myplugin_cron_hook' );
     }
     ```
  3. Unschedule in the deactivation hook (see Section 1 Plugin Lifecycle example).
- **Always unschedule** cron tasks on plugin deactivation. Failing to do so leaves orphan tasks running indefinitely.
- Built-in recurrence slugs: `hourly`, `twicedaily`, `daily`, `weekly`. Register custom intervals via the `cron_schedules` filter.
- For precision timing: define `DISABLE_WP_CRON` as `true` in `wp-config.php` and set up a real system cron job to call `wp-cron.php`.

## 16) AJAX

*Source: [AJAX](https://developer.wordpress.org/plugins/javascript/ajax/) · [Server Side PHP and Enqueuing](https://developer.wordpress.org/plugins/javascript/enqueuing/)*

- All AJAX requests must go through `wp-admin/admin-ajax.php` — never link directly to a custom plugin PHP file.
- Register handlers:
  - `add_action( 'wp_ajax_{action}', $cb )` — logged-in users.
  - `add_action( 'wp_ajax_nopriv_{action}', $cb )` — non-logged-in users (add only when intentional).
- **Canonical handler pattern:**

```php
add_action( 'wp_ajax_myplugin_save', 'myplugin_ajax_save' );
function myplugin_ajax_save() {
    check_ajax_referer( 'myplugin-save-nonce' );         // 1. verify nonce
    if ( ! current_user_can( 'edit_posts' ) ) {          // 2. check capability
        wp_send_json_error( 'Forbidden', 403 );
    }
    // 3. read $_POST (never $_REQUEST)
    $value = sanitize_text_field( wp_unslash( $_POST['my_field'] ?? '' ) );
    // 4. respond — wp_send_json_* calls wp_die() automatically
    wp_send_json_success( array( 'saved' => $value ) );
}
```

- Pass the AJAX URL and nonce to JavaScript via `wp_localize_script()`:
  ```php
  wp_localize_script( 'my-script', 'myPluginData', array(
      'ajaxUrl' => admin_url( 'admin-ajax.php' ),
      'nonce'   => wp_create_nonce( 'myplugin-save-nonce' ),
  ) );
  ```
  On admin pages the global JS variable `ajaxurl` is already defined.
- `wp_send_json_success( $data )` and `wp_send_json_error( $data )` auto-call `wp_die()`. If not using them, end every handler with `wp_die()` — never bare `die()` or `exit`.
- **Never use `$_REQUEST`** — cookies take priority over POST values, allowing trivial injection. Use `$_POST` or `$_GET` explicitly.
- Script loading strategies (WordPress 6.3+): pass `array( 'in_footer' => true, 'strategy' => 'defer' )` as the `$args` parameter to `wp_enqueue_script()` / `wp_register_script()` instead of the old boolean.

## 17) Privacy & GDPR

*Source: [Privacy](https://developer.wordpress.org/plugins/privacy/) · [Personal Data Exporter](https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-exporter-to-your-plugin/) · [Personal Data Eraser](https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-eraser-to-your-plugin/)*

If the plugin collects, stores, or processes personal data (names, emails, IP addresses, etc.):

- **Personal data exporter** — register via the `wp_privacy_personal_data_exporters` filter. Callback: `fn( string $email, int $page ): array( 'data' => $items, 'done' => bool )`. Limit to ~500 items per page to avoid timeouts.
- **Personal data eraser** — register via the `wp_privacy_personal_data_erasers` filter. Callback: `fn( string $email, int $page ): array( 'items_removed' => bool, 'items_retained' => bool, 'messages' => array, 'done' => bool )`.
- **Policy contribution** — call `wp_add_privacy_policy_content( $plugin_name, $policy_text )` on `admin_init` to contribute suggested text to the site's Privacy Policy page.
- **Data lifecycle:** delete personal data on plugin uninstall AND whenever the associated user, post, or comment is deleted (hook into `delete_user`, `before_delete_post`, `delete_comment`).
- Avoid logging personal data unnecessarily; when logging is required, anonymize with `wp_privacy_anonymize_data( $type, $data )`.
- Privacy by Design: opt-in default, data minimisation, transparency.

## 18) Custom Database Tables

*Source: [Creating Tables with Plugins](https://developer.wordpress.org/plugins/creating-tables-with-plugins/)*

- **Prefer Options API, post meta, user meta, or comment meta first.** Custom tables are justified only for high-volume, relational, or non-post-centric data.
- Create and update tables via `dbDelta()` — never run raw `CREATE TABLE` or `ALTER TABLE` directly.
- Load the helper before calling `dbDelta`: `require_once ABSPATH . 'wp-admin/includes/upgrade.php';`
- Table name: `$table = $wpdb->prefix . 'myplugin_logs';` — always use `$wpdb->prefix`.
- Character set: append `$wpdb->get_charset_collate()` to the `CREATE TABLE` SQL.
- `dbDelta()` SQL requirements (strict — violations cause silent failures):
  - Each column definition on its own line.
  - Two spaces between `PRIMARY KEY` and its definition.
  - Use `KEY` not `INDEX`; include at least one KEY.
  - No backticks around column or table names.
  - Field types must be lowercase (e.g. `bigint(20)`, `varchar(255)`).
- Store a schema version in an option; compare on `plugins_loaded` and call the install function again if outdated (`register_activation_hook` is not called on updates).
- Drop tables on uninstall: `$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}myplugin_logs" );`

```php
function myplugin_create_tables() {
    global $wpdb;
    $table   = $wpdb->prefix . 'myplugin_logs';
    $collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  user_id bigint(20) NOT NULL,
  event varchar(100) NOT NULL,
  created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  PRIMARY KEY  (id),
  KEY user_id (user_id)
) $collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    update_option( 'myplugin_db_version', '1.0' );
}
register_activation_hook( __FILE__, 'myplugin_create_tables' );
```

## 19) Developer Tools

*Source: [Helper Plugins](https://developer.wordpress.org/plugins/developer-tools/helper-plugins/)*

- **[Query Monitor](https://wordpress.org/plugins/query-monitor/)** — in-browser debugger for database queries, hook callbacks, conditionals, HTTP requests, enqueued scripts/styles, and AJAX. Essential for performance profiling and hook inspection during development.
- **[Plugin Check](https://wordpress.org/plugins/plugin-check/)** — runs the same automated checks used by the WordPress.org plugin review team. Run before every submission or major release to catch compliance issues early.
- **`WP_DEBUG` constants** (add to `wp-config.php` in development only — never in production):
  ```php
  define( 'WP_DEBUG',         true );   // enable PHP errors/warnings
  define( 'WP_DEBUG_LOG',     true );   // write to wp-content/debug.log
  define( 'WP_DEBUG_DISPLAY', false );  // suppress browser output
  define( 'SCRIPT_DEBUG',     true );   // load non-minified JS/CSS
  ```

## 12) What Copilot Must Ensure (Checklist)
- ✅ Unique prefixes/namespaces; no accidental globals.  
- ✅ Nonce + capability checks for any write action (AJAX/REST/forms).  
- ✅ Inputs sanitized; outputs escaped.  
- ✅ User‑visible strings wrapped in i18n with correct text domain (`evangtermine`).  
- ✅ Assets enqueued via APIs (no inline script/style).  
- ✅ Tests added/updated for new behaviors.  
- ✅ Code passes PHPCS (WPCS) and ESLint where applicable.  
- ✅ Avoid direct DB concatenation; always prepare queries.
- ✅ All bundled third-party libraries and assets are GPL-compatible.
- ✅ No obfuscated or minified-with-name-mangling code; build sources included or linked.
- ✅ No feature-gating or functionality restricted behind payment/upgrade.
- ✅ Any external HTTP request is gated behind explicit user opt-in.
- ✅ No third-party CDNs for non-font assets; use WP-bundled libraries (jQuery, etc.).
- ✅ No `<iframe>` elements on admin pages.
- ✅ Admin notices and dashboard widgets are dismissible.
- ✅ "Powered by" / credit links are opt-in and hidden by default.
- ✅ Custom hook names are prefixed with the plugin prefix.
- ✅ All `add_filter()` callbacks `return` the (modified) value.
- ✅ WP-Cron tasks are unscheduled in the deactivation hook.
- ✅ AJAX handlers: `check_ajax_referer()` → `current_user_can()` → process `$_POST`/`$_GET` → `wp_send_json_*()` / `wp_die()`.
- ✅ `$_POST` / `$_GET` used instead of `$_REQUEST` in all request handlers.
- ✅ `is_wp_error()` checked on every `wp_remote_*()` response before use.
- ✅ External API responses cached with transients; fallback code present for cache misses.
- ✅ If the plugin handles personal data: privacy exporter and eraser callbacks registered.
- ✅ Personal data deleted on uninstall and on associated user/post/comment deletion.

## 13) WordPress.org Directory Compliance

### Licensing (Guideline 1)
All files distributed with the plugin — including bundled JavaScript libraries, images, fonts, and PHP dependencies — must use the GPL v2 or later, or a [GPL-compatible license](https://www.gnu.org/licenses/license-list.html). Verify the license of any third-party library before including it.

### Human-Readable Code (Guideline 4)
- Never obfuscate code or use minification that mangles variable/function names.
- If you use a build step (e.g. webpack, esbuild), include the unminified source files in the plugin, or link to a public development repository (e.g. GitHub) in `readme.txt`.
- Compiled/minified production assets are fine as long as the source is accessible.

### No Trialware (Guideline 5)
- Do not restrict or disable any plugin functionality that is then unlocked by payment.
- Trial periods that silently disable features are prohibited.
- All features visible in the plugin must work without purchasing an upgrade.

### External HTTP Requests & User Tracking (Guideline 7)
- Plugins must **not** contact external servers without explicit user consent (opt-in).
- Prohibited without opt-in: automated telemetry, analytics pings, remote asset loading for tracking, passing user data to third parties.
- If a feature requires external communication, expose an explicit settings toggle; default it to **off**.

```php
// Example: gate any outbound call behind a user-enabled option
if ( get_option( 'myplugin_enable_telemetry' ) ) {
    wp_remote_post( 'https://example.com/telemetry', [ 'body' => $data ] );
}
```

### External Assets & Bundled Libraries (Guidelines 8 + 13)
- **Use WordPress-bundled libraries** (jQuery, Backbone, Underscore, SimplePie, etc.) instead of bundling your own copy. Register them with the handle WordPress already defines.
- Do **not** load assets from third-party CDNs (jsDelivr, cdnjs, unpkg, etc.) except for web fonts. Self-host or use the WP-bundled version.
- Do **not** serve plugin updates or install additional plugins/themes from non-WordPress.org servers.
- Do **not** use `<iframe>` elements on admin/settings pages.

### Admin UI Conduct (Guideline 11)
- Upgrade prompts and upsell notices must be **contextual** (settings page only) and **minimal**.
- Sitewide admin notices must be **dismissible** — provide a nonce-protected dismiss action.
- Dashboard widgets added by the plugin must be removable via the standard Screen Options panel.
- Do not add advertising banners in the WordPress admin.

```php
// Dismissible notice pattern
add_action( 'admin_notices', 'myplugin_maybe_show_notice' );
function myplugin_maybe_show_notice() {
    if ( get_option( 'myplugin_notice_dismissed' ) ) {
        return;
    }
    echo '<div class="notice notice-info is-dismissible" id="myplugin-notice">';
    echo '<p>' . esc_html__( 'Notice text.', 'my-plugin' ) . '</p>';
    echo '</div>';
}
```

### Attribution & Credit Links (Guideline 10)
- Any "Powered by [Plugin Name]" or credit link rendered on the front end must be **opt-in** and **hidden by default**.
- Provide a clear, labeled toggle in settings; never require credits to unlock functionality.

### Developer Responsibility for All Bundled Assets (Guideline 2)
- You are responsible for every file you distribute — plugin code, images, fonts, and third-party libraries.
- Before adding any third-party library, verify its licence is GPL-compatible and document its source in comments or `readme.txt`.
- If a security issue is found in bundled code, patch it promptly or remove the component.

### SVN is a Release Repository Only (Guideline 3)
- Only push production-ready, deployable code to SVN. The directory generates a zip on every commit.
- Distributing plugin updates through channels other than WordPress.org while keeping SVN stale is prohibited.
- Tag each release with its version number; never use `trunk` as the `Stable tag`.

### External Services / SaaS (Guideline 6)
- Plugins that interface with external paid or free services are permitted.
- Always document the service in `readme.txt`: what it does, its pricing model, and a link to its Terms of Use.
- Never create an artificial external dependency solely to move code out of the plugin.

### No Illegal, Dishonest, or Manipulative Behaviour (Guideline 9)
- No keyword stuffing, black-hat SEO, or artificial search-ranking manipulation.
- No fake reviews, sockpuppeting (multiple accounts for reviews/ratings), or pressuring users for reviews.
- No implying legal-compliance guarantees ("GDPR-compliant", "ADA-compliant").
- No unauthorized use of the user's server resources (e.g. crypto mining, botnet participation).
- No copying another developer's plugin and presenting it as original work.

### Readme / Public Pages Must Not Spam (Guideline 12)
- Maximum **12 tags** in `readme.txt`; only the first **5** are displayed on WordPress.org (all 12 contribute to search).
- Tags may not include competitor brand names; related product tags are permitted (e.g. `woocommerce` for a WC extension).
- Repeating a tag or keyword counts as keyword stuffing and is prohibited.
- Affiliate links must be **disclosed** and must link **directly** to the affiliate service — no cloaked or redirect URLs.

### SVN Commit Discipline (Guideline 14)
- Commit only deployable code; every SVN commit regenerates the plugin zip file.
- Avoid rapid "cleanup" or "update" commits; use descriptive messages explaining what changed and why.
- Use a VCS (e.g. GitHub) for day-to-day development; push to SVN only when releasing.

### Increment Version for Every Code Release (Guideline 15)
- Users receive update prompts only when the version number increases. Every code change reaching users needs a new version.
- The trunk `readme.txt` `Stable tag` must always match the current deployed version.

### Respect Trademarks and Copyrights (Guideline 17)
- A plugin slug must **not** begin with another product's registered trademark (e.g. `wordpress`, `woocommerce`) unless you are the official owner.
- Choose original, unique plugin names; avoid confusingly similar names to established products.
- Forking another plugin is permitted under the GPL, but you must credit the original and comply with its licence.

*Source: [Detailed Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/) · [Plugin Developer FAQ](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/)*
