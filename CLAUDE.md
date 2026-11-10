# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Evangelische Termine** (v3.3) — WordPress plugin that embeds the Protestant events calendar from [www.evangelische-termine.de](https://www.evangelische-termine.de) via shortcodes and a sidebar widget. The event HTML is fetched live from the remote host with the WordPress HTTP API (`wp_remote_get()`) and injected into the page. There is no build system, no Composer and no npm.

The plugin was closed on WordPress.org on 2025-05-01 (security issue). The git history was imported from the WordPress.org SVN; the work here is to fix the security issues (issues #1–#6), make it PHP 8 compatible and get it re-opened. See `HANDOVER.md` for the current state if present.

## Commands

```bash
# Lint PHP syntax
find . -name "*.php" -not -path "./.git/*" -not -path "./assets/*" -exec php -l {} \;

# WordPress Coding Standard (requires PHPCS + WPCS)
phpcs --standard=WordPress --extensions=php evangtermine.php autoloader.php application/

# Auto-fix
phpcbf --standard=WordPress --extensions=php evangtermine.php autoloader.php application/

# Generate translations (requires WP-CLI)
wp i18n make-pot . languages/evangtermine.pot

# Run WP.org compliance check (requires WP-CLI + plugin-check)
wp plugin check evangtermine
```

## Architecture

Object-oriented, classes are loaded by `autoloader.php` (`AutoloaderET`), which maps `ClassName` to `application/{controllers,models,views,helper,forms}/class.classname.php` (lower case).

```
evangtermine.php                         ← entry point: constants, registers shortcodes, widget, CSS hook
autoloader.php                           ← AutoloaderET
application/
  configs/defaults.php                   ← ET_DEFAULT_HOST, ET_DEFAULT_PROTOCOL, ET_OPTION_* defaults
  controllers/class.shortcode*.php       ← ShortcodeVeranstalter, ShortcodeTeaser, ShortcodePanels, ShortcodeMinical
  controllers/class.etwidget.php         ← EtWidget (WP_Widget)
  models/class.etquery.php               ← EtQuery: builds the query string, fetches and post-processes the remote HTML
  views/class.etview.php                 ← EtView: wraps the content in a <div>
  helper/class.includecss.php            ← IncludeCSS: <link> tag in wp_head
  helper/class.debugging.php             ← Debugging (only active if DEBUG is true)
  forms/options.php                      ← Settings API page (loaded in wp-admin only)
  forms/class.pluginoptionspage.php      ← "Einstellungen" link on the plugins page
  forms/form.etwidget.php                ← widget form
assets/                                  ← bundled Bootstrap 3.3.7, jQuery, Font Awesome 4.7.0, CSS
```

**Data flow:** shortcode/widget attributes → `EtQuery::setEtParameters()` → request values (`$_REQUEST`, `$_GET['etID']`) are merged in → `EtQuery::getQuerystring()` → `EtQuery::retrieveStream()` (`wp_remote_get` to `{etprotocol}{ethost}/{veranstaltungen-php|detail-php|teaser|…}`) → `filterStream()` string-replace post-processing → `EtView::buildView()`.

**Filter state:** `ShortcodeVeranstalter` stores the user's filters in `$_SESSION['etsession']` (stdClass) to keep them across pagination. The code does not call `session_start()` itself.

**Settings:** `application/forms/options.php` registers seven options (`vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`) in the group `et_group`. Shortcode attributes override them per call.

**Security-critical spots:** everything that takes request data and puts it into the remote URL or into the returned HTML (`ShortcodeVeranstalter`, `EtQuery::getQuerystring`, `EtQuery::filterStream`), and everything that prints option values (`options.php`, `IncludeCSS`). The remote HTML is returned unfiltered by design.

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
| Settings option names | `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost` (no `et_` prefix — legacy) |
| API endpoint | `{etprotocol}{ethost}/veranstaltungen-php` (`ET_VERANSTALTER_MODUL`), `/detail-php`, `/teaser` |
| HTTP | `wp_remote_get()`, timeout from `EtQuery::$connectionTimeout` |
| Commit identity | `habakuk007 <2253878+habakuk007@users.noreply.github.com>` (set locally in this repo, not the global git config) |

## Do Not Change

- `ET_DEFAULT_HOST`, `ET_DEFAULT_PROTOCOL` — API target; users override via settings
- Option key names (`vid`, `region`, etc.) — bare names without prefix; renaming breaks existing installs
- CSS class names prefixed `.et_` — renaming breaks user custom CSS
- Class name `EtWidget` — WordPress stores the widget class name in the database
- Shortcode names `et_veranstalter`, `et_teaser`, `et_panels`, `et_minical`

## Instruction Files

| File | Applies to |
|------|-----------|
| [.agents/instructions/wordpress.instructions.md](.agents/instructions/wordpress.instructions.md) | `**/*.php` |
| [.agents/instructions/a11y.instructions.md](.agents/instructions/a11y.instructions.md) | `**` |
| [.agents/instructions/html-css-style-color-guide.instructions.md](.agents/instructions/html-css-style-color-guide.instructions.md) | `**/*.css`, `**/*.html` |
| [.agents/instructions/readme-txt.instructions.md](.agents/instructions/readme-txt.instructions.md) | `readme.txt` |
| [.agents/instructions/ai-tool-sync.instructions.md](.agents/instructions/ai-tool-sync.instructions.md) | `CLAUDE.md`, `AGENTS.md`, `.github/copilot-instructions.md`, `.agents/**`, `.mcp.json` |
| [.agents/instructions/agents.instructions.md](.agents/instructions/agents.instructions.md) | `**/*.agent.md` |
| [.agents/instructions/agent-skills.instructions.md](.agents/instructions/agent-skills.instructions.md) | `**/.agents/skills/**/SKILL.md` |

## Agents

Invoke via Claude Code slash commands (e.g. `/code-review application/models/class.etquery.php`).

| Command | Agent | Purpose |
|---------|-------|---------|
| `/code-review` | [code-review.agent.md](.agents/agents/code-review.agent.md) | WPCS compliance, security, backwards-compat review |
| `/wp-security-review` | [wp-security-review.agent.md](.agents/agents/wp-security-review.agent.md) | XSS, CSRF, SQLi, privilege escalation audit |
| `/refine-issue` | [refine-issue.agent.md](.agents/agents/refine-issue.agent.md) | Add acceptance criteria, technical notes, edge cases to a GitHub issue |

## Skills

Invoke via Claude Code slash commands (e.g. `/gh issue create`).

| Command | Skill | Purpose |
|---------|-------|---------|
| `/gh` | [gh/SKILL.md](.agents/skills/gh/SKILL.md) | GitHub CLI: issues, PRs, releases |
| `/gh-bot` | [gh-bot/SKILL.md](.agents/skills/gh-bot/SKILL.md) | Open PRs and PR comments as bot identity `habakuk-ai` (commits/pushes stay `habakuk007`) |
| `/php-lint` | [php-lint/SKILL.md](.agents/skills/php-lint/SKILL.md) | PHPCS/PHPCBF for WordPress Coding Standard |
| `/wp-release` | [wp-release/SKILL.md](.agents/skills/wp-release/SKILL.md) | Full plugin release workflow |
| `/grill-me` | [grill-me/SKILL.md](.agents/skills/grill-me/SKILL.md) | Stress-test a plan through relentless questions |
