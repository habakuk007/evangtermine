# Copilot Instructions — Evangelische Termine Plugin

## Project Overview

**Evangelische Termine** is a WordPress plugin (v3.3) that embeds the Protestant events database
from [www.evangelische-termine.de](https://www.evangelische-termine.de) into any WordPress site.
It provides shortcodes and a sidebar widget; the event HTML is fetched live from the remote host
with the WordPress HTTP API (`wp_remote_get()`).

The codebase is object-oriented PHP with an autoloader, no build tools, no runtime Composer dependencies (Composer and npm are dev tooling only: PHPCS and the end-to-end tests).
The plugin was closed on WordPress.org (security issue); the goal is to fix issues
#1–#6, support PHP 8 and get it re-opened.

## File Map

```
evangtermine.php            <- Plugin entry point, shortcode and widget registration
autoloader.php              <- AutoloaderET (application/{controllers,models,views,helper,forms})
application/
  configs/defaults.php      <- ET_DEFAULT_HOST, ET_DEFAULT_PROTOCOL, ET_OPTION_* defaults
  controllers/              <- ShortcodeVeranstalter/Teaser/Panels/Minical, EtWidget
  models/class.etquery.php  <- EtQuery: query string, wp_remote_get, HTML post-processing
  views/class.etview.php    <- EtView: <div> wrapper
  helper/                   <- IncludeCSS, Debugging
  forms/                    <- options.php (Settings API), widget form
assets/                     <- bundled Bootstrap, jQuery, Font Awesome, CSS
tests/e2e/                  <- Playwright tests against WordPress Playground (not in the release)
readme.txt                  <- WordPress.org listing
```

## Branches and Releases

- `master` = released state (what is on WordPress.org). Protected: only via PR, CI must pass. Version tags are created on `master` and trigger the deploy workflow.
- `develop` = integration branch for further development. Protected like `master`.
- Work in short-lived branches (`fix/<issue>-<topic>`, `feat/<issue>-<topic>`, `chore/<issue>-<topic>`) and open PRs **against `develop`**.
- Release: PR `develop` -> `master`, then tag the merge commit (see `/wp-release`). Urgent security fixes may go to `master` first and are merged back into `develop`.

## Key Conventions

| Item | Value |
|------|-------|
| Classes | CamelCase, one class per file `class.<lowercase name>.php` |
| Constants | `ET_` prefix |
| Text domain | `evangtermine` |
| Options names | `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost` (no prefix — legacy) |
| External API host | `www.evangelische-termine.de` (configurable via option `ethost`) |
| API transport | WordPress HTTP API (`wp_remote_get()`) |
| Session state | `$_SESSION['etsession']` object for filter persistence |
| Capability | `manage_options` (admin settings page) |
| Shortcodes | `[et_veranstalter]`, `[et_teaser]`, `[et_panels]`, `[et_minical]` |
| Widget | `EtWidget` class |

## Development Commands

This plugin has no build system. For local development:

```bash
# Lint PHP syntax
find . -name "*.php" -not -path "./.git/*" -not -path "./assets/*" -exec php -l {} \;

# Install the dev tooling (PHPCS, WPCS, PHPCompatibilityWP) - dev only, not part of the release
composer install

# PHP compatibility check for PHP 7.4+ (blocking in CI)
composer lint:compat

# WordPress Coding Standard, full ruleset from phpcs.xml (not blocking in CI until issue #13)
composer lint:php

# Auto-fix
composer fix:php

# Install the E2E tooling (Playwright, WordPress Playground) - dev only, not part of the release
npm ci && npx playwright install chromium

# E2E tests in a real WordPress (WordPress Playground) with a mocked calendar host, see tests/e2e/README.md
npm run test:e2e

# Optional E2E checks against the real www.evangelische-termine.de (not run in CI)
npm run test:e2e:live

# Start the test site for manual testing: http://127.0.0.1:8881
npm run playground

# Generate .pot translation file (requires WP-CLI)
wp i18n make-pot . languages/evangtermine.pot

# Check plugin against WP.org guidelines (requires WP-CLI + plugin-check)
wp plugin check evangtermine
```

## When Adding Features

1. Add new parameters to `EtQuery` (defaults) and the relevant controller in `application/controllers/`
2. Sanitize request input and escape all output (see the WordPress instructions)
3. Add corresponding admin settings fields in `application/forms/options.php`
4. Register the setting with `register_setting()` and a sanitize callback
5. Update `readme.txt` changelog and bump `Version:` + `Stable tag:`

## Coding Standards Applied Automatically

| Instruction | Applies To |
|-------------|-----------|
| [WordPress](.agents/instructions/wordpress.instructions.md) | `**/*.php` |
| [Accessibility](.agents/instructions/a11y.instructions.md) | `**` |
| [HTML/CSS Colors](.agents/instructions/html-css-style-color-guide.instructions.md) | `**/*.css`, `**/*.html` |
| [readme.txt](.agents/instructions/readme-txt.instructions.md) | `readme.txt` |
| [AI Tool Sync](.agents/instructions/ai-tool-sync.instructions.md) | `CLAUDE.md`, `AGENTS.md`, `.github/copilot-instructions.md`, `.agents/instructions/**`, `.mcp.json` |
| [Agent Files](.agents/instructions/agents.instructions.md) | `**/*.agent.md` |
| [Skill Files](.agents/instructions/agent-skills.instructions.md) | `**/.agents/skills/**/SKILL.md` |

## Available Agents

| Agent | Purpose |
|-------|---------|
| [WordPress Code Reviewer](.agents/agents/code-review.agent.md) | WPCS compliance, security, backwards compat |
| [WP Security Reviewer](.agents/agents/wp-security-review.agent.md) | XSS, CSRF, SQLi, privilege escalation audit |
| [Issue Refiner](.agents/agents/refine-issue.agent.md) | Add acceptance criteria, technical notes, edge cases |

## Important: Do Not Change

- `ET_DEFAULT_HOST` — external API hostname; users override via settings, not code
- `ET_DEFAULT_PROTOCOL` — default is `https://` (overridable by the `etprotocol` option); never default to plain HTTP
- Option key names (`vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`) — bare names without prefix; renaming breaks existing installs
- CSS class names prefixed with `.et_` — renaming breaks user custom CSS
- The `ET_Widget` class name — WordPress serializes it in the database
