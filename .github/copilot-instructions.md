# Copilot Instructions — Evangelische Termine Plugin

## Project Overview

**Evangelische Termine** is a WordPress plugin (v3.3) that embeds the Protestant events database
from [www.evangelische-termine.de](https://www.evangelische-termine.de) into any WordPress site.
It provides shortcodes and a sidebar widget; the event HTML is fetched live from the remote host
with the WordPress HTTP API (`wp_remote_get()`).

The codebase is object-oriented PHP with an autoloader, no build tools, no Composer dependencies
and no npm. The plugin was closed on WordPress.org (security issue); the goal is to fix issues
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
readme.txt                  <- WordPress.org listing
```

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

# WordPress Coding Standard (requires PHPCS + WPCS)
phpcs --standard=WordPress --extensions=php evangtermine.php autoloader.php application/

# Fix auto-fixable issues
phpcbf --standard=WordPress --extensions=php evangtermine.php autoloader.php application/

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
- `ET_DEFAULT_PROTOCOL` — must remain `http://` unless the remote API supports HTTPS
- Option key names (`vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`) — bare names without prefix; renaming breaks existing installs
- CSS class names prefixed with `.et_` — renaming breaks user custom CSS
- The `ET_Widget` class name — WordPress serializes it in the database
