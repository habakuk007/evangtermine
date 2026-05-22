# Copilot Instructions — Evangelische Termine Plugin

## Project Overview

**Evangelische Termine** is a WordPress plugin (v1.8) that embeds the Protestant events database
from [www.evangelische-termine.de](https://www.evangelische-termine.de) into any WordPress site.
It provides two shortcodes and a sidebar widget; all event data is fetched live from the remote
REST API using PHP cURL.

The codebase is **procedural PHP** with no build tools, no Composer dependencies, and no npm.

## File Map

```
evangtermine.php          ← Plugin entry point, shortcode registration, widget class
includes/
  functions.php           ← et_veranstalter() and et_teaser() — cURL + HTML integration
  options.php             ← Admin settings page (Settings API)
assets/
  css/evangtermine.css    ← All frontend and widget styles
uninstall.php             ← Removes plugin options on uninstall
readme.txt                ← WordPress.org listing
```

## Key Conventions

| Item | Value |
|------|-------|
| PHP prefix | `et_` (functions), `ET_` (constants), `ET_Widget` (class) |
| Text domain | `evangtermine` |
| Options prefix | `et_` (e.g. `et_vid`, `et_region`, `et_css`) |
| External API host | `www.evangelische-termine.de` (configurable via `ET_DEFAULT_HOST`) |
| API transport | PHP cURL (direct, not WP HTTP API) |
| Session state | `$_SESSION['session']` object for filter persistence |
| Required PHP ext | `curl` — plugin shows error message if missing |
| Capability | `manage_options` (admin settings page) |
| Shortcodes | `[et_veranstalter]`, `[et_teaser]` |
| Widget | `ET_Widget` class |

## Shortcode Parameters

Both shortcodes accept these attributes:
`vid`, `region`, `eventtype`, `highlight`, `people`, `place`, `person`, `ipm`, `cha`,
`itemsperpage`, `dest`, `until`

`[et_teaser]` additionally accepts `tpl=1` for template mode.

## Development Commands

This plugin has no build system. For local development:

```bash
# Lint PHP (requires PHPCS + WPCS)
phpcs --standard=WordPress evangtermine.php includes/

# Fix auto-fixable issues
phpcbf --standard=WordPress evangtermine.php includes/

# Generate .pot translation file (requires WP-CLI)
wp i18n make-pot . languages/evangtermine.pot

# Check plugin against WP.org guidelines (requires WP-CLI + plugin-check)
wp plugin check evangtermine
```

## When Adding Features

1. Add new shortcode parameters as `$et_defaults` array entries in `evangtermine.php`
2. Pass new params through to `et_veranstalter()` / `et_teaser()` in `includes/functions.php`
3. Add corresponding admin settings fields in `includes/options.php` via Settings API
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
- Option key names (e.g. `et_vid`, `et_region`) — renaming breaks existing installs
- CSS class names prefixed with `.et_` — renaming breaks user custom CSS
- The `ET_Widget` class name — WordPress serializes it in the database
