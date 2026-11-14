# End-to-end tests

These tests run the plugin in a real WordPress and check it with a real browser.

- **WordPress**: [WordPress Playground](https://wordpress.github.io/wordpress-playground/) (`@wp-playground/cli`). It needs only Node.js, no Docker, no PHP, no database.
- **Browser**: [Playwright](https://playwright.dev/) with Chromium.
- **Calendar host**: replaced by a mock. The tests do not need `www.evangelische-termine.de`.

Nothing in this folder is part of the release (`tests`, `package.json` and `package-lock.json` are listed in `.distignore`).

## Requirements

- Node.js 20.18 or newer (tested with Node 24 and 26)
- Internet access for the first start (Playground downloads WordPress and the theme)

## Commands

Run all commands in the root folder of the repository.

```bash
# Install the tools (one time, and after package-lock.json changed)
npm ci
```

```bash
# Install the browser for Playwright (one time)
npx playwright install chromium
```

```bash
# Run the tests with the mocked calendar host (this is what CI runs)
npm run test:e2e
```

```bash
# Open the HTML report of the last run
npm run test:e2e:report
```

```bash
# Optional: a few checks against the real www.evangelische-termine.de
npm run test:e2e:live
```

```bash
# Start only the test site, for manual testing: http://127.0.0.1:8881
npm run playground
```

`npm run test:e2e` starts Playground by itself and stops it at the end. If a test site is already running on the port (for example from `npm run playground`), the tests use it. This saves the start time of about 2 minutes.

Useful variations:

```bash
# Only one file, or only tests with a matching title
npm run test:e2e -- security.spec.js
npm run test:e2e -- -g "pagination"
```

```bash
# Other PHP or WordPress version (bash syntax; in PowerShell: $env:ET_E2E_PHP = '7.4')
ET_E2E_PHP=7.4 ET_E2E_WP=6.8 npm run test:e2e
```

| Variable | Default | Meaning |
|----------|---------|---------|
| `ET_E2E_PHP` | `8.3` | PHP version of Playground (`7.4`, `8.0` … `8.5`) |
| `ET_E2E_WP` | `latest` | WordPress version of Playground |
| `ET_E2E_PORT` | `8881` | Port of the test site |

The variables are used only when the tests start Playground themselves, not when a test site is already running.

## Files

| File | Purpose |
|------|---------|
| `blueprint.json` | Sets up the test site: debug output on, theme Twenty Twenty-One, option `vid` = `1474`, one page per shortcode, the widget in the footer, and a small must-use plugin that loads the mock. |
| `mock/et-e2e-mock.php` | Test helper inside WordPress: mock of the calendar host and control interface for the tests. |
| `fixtures/*.html` | Answers of the mocked calendar host. The markup is copied from real answers (November 2026), the events are invented. |
| `playwright.config.js` | Playwright configuration: starts Playground, projects `mock` and `live`. |
| `global-setup.js` | Waits until the plugin is active and saves the login cookies of the administrator. |
| `helpers.js` | Shared code of the tests: `site` (control interface), `visit()`, check for PHP messages. |
| `specs/*.spec.js` | The tests. `live.spec.js` belongs to the project `live`, all other files to `mock`. |

## How the mock works

`mock/et-e2e-mock.php` uses the WordPress filter `pre_http_request`. When the plugin calls `wp_remote_get()` for `/veranstaltungen-php`, `/detail-php`, `/teaser` or `/panels`, the helper

1. records the request (URL, headers, number of cookies, user agent, timeout), and
2. in mock mode answers with a file from `fixtures/` instead of asking the real host.

Which fixture is used:

| Request | Fixture |
|---------|---------|
| `veranstaltungen-php` | `list-page1.html`; with `pageID` > 1: `list-page2.html`; with a search term `q`: `list-search.html`; with `vid=1474` or the search term `nichts`: `list-empty.html` |
| `detail-php` | `detail.html` for `ID=1001`, else `detail-error.html` |
| `teaser` | `teaser-tpl1-css.html` (et_teaser with the default styles), `teaser-tpl1-nocss.html` (with `css=nocss`), `teaser-tpl2.html` (widget) |
| `panels` | `panels.html` |

A host check with a local HTTP server is not possible: the plugin refuses `localhost` and private addresses as calendar host (`EtQuery::isSafeHost()`, `et_sanitize_ethost()`). This is why the mock is a filter inside WordPress.

The helper is switched off after the start of the test site (the plugin talks to the real host, good for manual testing). Every test switches it on with a reset.

### Control interface

The tests talk to the helper with `POST /?et_e2e=<action>` and a JSON body. In the tests use the fixture `site` from `helpers.js`.

| Action | In the tests | Meaning |
|--------|--------------|---------|
| `reset` | `site.reset( setup )` (runs before every test) | Mock on, empty request log, empty teaser cache, default options (`vid` = `1474`), empty `debug.log`. `setup` can change the mock (`mode`, `fail`, `ttl`, `session`) and write plugin options directly into the database (`options`). |
| `config` | `site.config( { fail: '' } )` | Change settings of the mock without a reset. |
| `state` | `site.phpMessages()` | Request log and content of `wp-content/debug.log`. |
| `info` | `site.info()` | Versions of PHP, WordPress and the plugin. |
| `widget-form`, `widget-update` | see `render.spec.js` | Output of `EtWidget::form()` and result of `EtWidget::update()`. |

Settings of the mock:

- `mode`: `mock` (fixtures) or `live` (real host)
- `fail`: `error` (connection error) or `http500` (HTTP status 500), empty = normal answer
- `ttl`: value for the filter `evangtermine_cache_ttl` (`0` = no cache)
- `session`: `true` starts a PHP session on every request

The helper also prints its state (request log, `debug.log`) into a hidden `<script id="et-e2e-state">` at the end of every page. The tests read it from there (`site.requests( page, filter )`). This saves one request per check: a request to Playground is slow because WordPress starts again every time.

The control interface has no access check. Never copy the helper to a real website.

## What the tests check

| File | Content |
|------|---------|
| `render.spec.js` | The four shortcodes and the widget show the calendar; widget form and saving; scripts come from WordPress and the plugin, not from the calendar host. |
| `veranstalter.spec.js` | `et_veranstalter`: pagination, search, filter, reset, detail view. Every test runs without and with an active PHP session. |
| `security.spec.js` | XSS probes (`et_q`, unknown parameters, `etID`), cookies are not forwarded, https by default, unsafe hosts are not requested, `et_minical` with an apostrophe. |
| `options.spec.js` | Options page: escaping, select fields, `ethost` is rejected for local addresses, output of the `css` option in the page head. |
| `cache.spec.js` | Teaser and widget cache, `evangtermine_cache_ttl`, errors are not cached, default CSS of `et_teaser`. |
| `live.spec.js` | Structure of the output with the real calendar host (organiser `1474` and region `407`). |

PHP messages: the test site runs with `WP_DEBUG`, `WP_DEBUG_DISPLAY` and `WP_DEBUG_LOG`. `visit()` and `site.requests()` fail when the page shows a PHP warning, notice, deprecation or error, or when `debug.log` has one.

The browser is not allowed to load anything from other hosts (see the fixture `page` in `helpers.js`). The blocked addresses are in `page.externalRequests`.

## Writing a test

```js
const { test, expect, visit } = require( '../helpers' );

test( 'the teaser shows five events', async ( { page, site } ) => {
	await visit( page, '/et-teaser/' );
	await expect( page.locator( '.et_teaser .et_content_row' ) ).toHaveCount( 5 );

	const requests = await site.requests( page, { module: 'teaser', tpl: '1' } );
	expect( requests ).toHaveLength( 1 );
} );
```

- Check what a visitor sees and what the plugin sends to the calendar host. Do not check internal details like `$_SESSION`.
- The tests share one WordPress site, so they run one after another (`workers: 1`).
- A new page for the tests: add it to the list `$pages` in `blueprint.json` and start the test site again.
- If a test shows a bug in the plugin that is not fixed yet, mark it with `test.fail()` and a comment. Do not delete the test. In the output such a test has an `x`, but it counts as passed. When the bug is fixed, the test fails with "Expected to fail, but passed": then remove `test.fail()`.

Known bugs that have such a test today: the widget form does not show the saved title (`render.spec.js`); the search term is lost on page 2, "Zurücksetzen" keeps a term that is still in the search field, and the `<h1>` of the detail view is not changed to `<h2>` (`veranstalter.spec.js`, group "known bugs").

## CI

The job `e2e` in `.github/workflows/ci.yml` runs `npm run test:e2e` for PHP 7.4 and 8.0 to 8.5 with the latest WordPress. The Playwright report of every run is uploaded as an artifact (`playwright-report-php<version>-wp<version>`). The live tests do not run in CI.

## Problems and solutions

- **The port is in use or reserved** (Windows): the default port of Playground, 9400, is inside a reserved range on some Windows machines (`netsh interface ipv4 show excludedportrange protocol=tcp`). The tests use 8881. Use `ET_E2E_PORT` for another port.
- **`npx @wp-playground/cli` fails with `ECONNRESET`**: use the installed version (`npm ci`, then `npm run playground`), not `npx` with a download.
- **The first start is slow**: Playground downloads WordPress (1 to 3 minutes). Later starts take about 2 minutes on Windows.
- **The tests are slow**: every request starts WordPress inside WebAssembly (2 to 3 seconds per request on a Windows laptop, much faster on Linux). Keep the number of page loads per test small.
- **A test site keeps running after a stopped test run** (Windows): find the process with `netstat -ano | findstr :8881` and stop it with `taskkill /PID <pid> /F`.
- **`curl` shows redirect loops**: Playground logs in automatically with a redirect. Use a cookie jar: `curl -L -c jar -b jar http://127.0.0.1:8881/`.
- **Changed `blueprint.json`**: stop the test site and start it again. Changes in the plugin code, the mock and the fixtures are used at once (the folder is mounted).
