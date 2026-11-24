# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Evangelische Termine** (v3.4) — WordPress plugin that embeds the Protestant events calendar from [www.evangelische-termine.de](https://www.evangelische-termine.de) via shortcodes and a sidebar widget. The event HTML is fetched live from the remote host with the WordPress HTTP API (`wp_remote_get()`) and injected into the page. There is no build system; Composer (PHPCS) and npm (end-to-end tests) are used for dev tooling only, the plugin has no runtime dependencies.

The plugin was closed on WordPress.org on 2025-05-01 (security issue). The git history was imported from the WordPress.org SVN. The security issues (#1–#6) are fixed, the plugin is PHP 8 compatible and it has been re-opened on WordPress.org (version 3.4). Further work is tracked in the GitHub issues (milestones 3.5.0 and 3.5.1). A release goes live on WordPress.org as soon as the deploy workflow pushes the tag to SVN.

## Commands

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

# Generate translations (WP-CLI comes from Composer)
composer make:pot   # -> languages/evangtermine.pot (WP-CLI i18n from Composer, no global wp)
composer make:mo    # compile languages/*.po to .mo

# Run WP.org compliance check (requires WP-CLI + plugin-check)
wp plugin check evangtermine
```

## Architecture

Object-oriented, classes are loaded by `autoloader.php` (`Evangtermine_Autoloader`), which maps `ClassName` to `application/{controllers,models,views,helper,forms}/class.classname.php` (lower case).

```
evangtermine.php                         ← entry point: constants, registers shortcodes, widget, CSS hook
autoloader.php                           ← Evangtermine_Autoloader
application/
  configs/defaults.php                   ← ET_DEFAULT_HOST, ET_DEFAULT_PROTOCOL, ET_OPTION_* defaults
  controllers/class.evangtermine_shortcode*.php       ← Evangtermine_ShortcodeVeranstalter, Evangtermine_ShortcodeTeaser, Evangtermine_ShortcodePanels, Evangtermine_ShortcodeMinical
  controllers/class.etwidget.php         ← EtWidget (WP_Widget)
  models/class.evangtermine_query.php               ← Evangtermine_Query: builds the query string, fetches and post-processes the remote HTML
  views/class.evangtermine_view.php                 ← Evangtermine_View: wraps the content in a <div>
  helper/class.evangtermine_includecss.php            ← Evangtermine_IncludeCSS: <link> tag in wp_head
  helper/class.evangtermine_debugging.php             ← Evangtermine_Debugging (only active if ET_DEBUG is true)
  forms/options.php                      ← Settings API page (loaded in wp-admin only)
  forms/class.evangtermine_pluginoptionspage.php      ← "Einstellungen" link on the plugins page
  forms/form.etwidget.php                ← widget form
assets/                                  ← bundled Bootstrap 3.3.7, jQuery, Font Awesome 4.7.0, CSS
languages/readme-translations/                     ← readme.txt translations (de, de formal) for translate.wordpress.org; not in the release
tests/e2e/                               ← Playwright tests against WordPress Playground (blueprint, mock of the calendar host, fixtures); not in the release
docs/specs/                              ← design specs; not in the release
```

**4.0.0 (planned, epic #107):** every event gets its own page on the WordPress site, data from the JSON interface, own templates. The design and all decisions are in [docs/specs/4.0-event-pages.md](docs/specs/4.0-event-pages.md); read it before working on any 4.0 issue and change decisions only through a PR against that file.

**Data flow:** shortcode/widget attributes → `Evangtermine_Query::setEtParameters()` → request values (`$_REQUEST`, `$_GET['etID']`) are merged in → `Evangtermine_Query::getQuerystring()` → `Evangtermine_Query::retrieveStream()` (`wp_remote_get` to `{etprotocol}{ethost}/{veranstaltungen-php|detail-php|teaser|…}`) → `filterStream()` string-replace post-processing → `Evangtermine_View::buildView()`.

**Filter state:** the user's filters travel in the URL only, the plugin does not use a PHP session. `Evangtermine_ShortcodeVeranstalter` reads the filter values from the request (`getRequestValue()`) and passes them to `Evangtermine_Query::setEtParameter()`; `filterStream()` rewrites the links in the remote HTML to the permalink plus the current query string. `reset=1` returns to the shortcode defaults.

**Settings:** `application/forms/options.php` registers seven options (`vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`) in the group `et_group`. Shortcode attributes override them per call.

**Security-critical spots:** everything that takes request data and puts it into the remote URL or into the returned HTML (`Evangtermine_ShortcodeVeranstalter`, `Evangtermine_Query::getQuerystring`, `Evangtermine_Query::filterStream`), and everything that prints option values (`options.php`, `Evangtermine_IncludeCSS`). The remote HTML is returned unfiltered by design.

## Branches and Releases

- `master` = released state (what is on WordPress.org). Protected: only via PR, CI must pass. Version tags are created on `master` and trigger the deploy workflow.
- `develop` = integration branch for further development. Protected like `master`.
- Work in short-lived branches (`fix/<issue>-<topic>`, `feat/<issue>-<topic>`, `chore/<issue>-<topic>`) and open PRs **against `develop`**.
- Release: PR `develop` -> `master`, then tag the merge commit (see `/wp-release`). Urgent security fixes may go to `master` first and are merged back into `develop`.
- Versioning follows [Semantic Versioning](https://semver.org/lang/de/) (`MAJOR.MINOR.PATCH`, always three parts, no pre-release suffixes): MAJOR = breaking changes, MINOR = new backward-compatible features or behaviour changes, PATCH = bug fixes and internal work (tests, docs, coding standard). GitHub milestones are named by the target version (3.5.0, 3.5.1, 4.0.0, 4.1.0). The tag `3.4` predates this rule and stays as is.

## Key Conventions

| Item | Value |
|------|-------|
| Classes | CamelCase, one class per file `class.<lowercase name>.php` |
| Constants | `ET_` prefix |
| Text domain | `evangtermine` |
| Settings option names | `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost` (no `et_` prefix — legacy) |
| API endpoint | `{etprotocol}{ethost}/veranstaltungen-php` (`ET_VERANSTALTER_MODUL`), `/detail-php`, `/teaser` |
| HTTP | `wp_remote_get()`, timeout from `Evangtermine_Query::$connectionTimeout` |
| Commit identity | `habakuk007 <2253878+habakuk007@users.noreply.github.com>` (set locally in this repo, not the global git config) |

## Do Not Change

- `ET_DEFAULT_HOST`, `ET_DEFAULT_PROTOCOL` — API target; users override via settings
- Option key names (`vid`, `region`, etc.) — bare names without prefix; renaming breaks existing installs
- CSS class names prefixed `.et_` — renaming breaks user custom CSS
- Class name `EtWidget` — WordPress stores the widget class name in the database
- Shortcode names `et_veranstalter`, `et_teaser`, `et_panels`, `et_minical`

## Instruction Files

`.agents/` is the source of truth. `.github/instructions/` and `.github/agents/` are tracked copies (GitHub Copilot reads them only there; git cannot store junctions). Never edit them; after changing `.agents/instructions/` or `.agents/agents/` run `bash .agents/scripts/sync-github-copies.sh`. The CI job `ai-config-sync` fails if they differ.

| File | Applies to |
|------|-----------|
| [.agents/instructions/wordpress.instructions.md](.agents/instructions/wordpress.instructions.md) | `**/*.php` |
| [.agents/instructions/a11y.instructions.md](.agents/instructions/a11y.instructions.md) | `**` |
| [.agents/instructions/html-css-style-color-guide.instructions.md](.agents/instructions/html-css-style-color-guide.instructions.md) | `**/*.css`, `**/*.html` |
| [.agents/instructions/readme-txt.instructions.md](.agents/instructions/readme-txt.instructions.md) | `readme.txt` |
| [.agents/instructions/wordpress-org-submission.instructions.md](.agents/instructions/wordpress-org-submission.instructions.md) | `readme.txt`, `changelog.txt`, `**/*.php` |
| [.agents/instructions/ai-tool-sync.instructions.md](.agents/instructions/ai-tool-sync.instructions.md) | `CLAUDE.md`, `AGENTS.md`, `.github/copilot-instructions.md`, `.agents/**`, `.mcp.json` |
| [.agents/instructions/agents.instructions.md](.agents/instructions/agents.instructions.md) | `**/*.agent.md` |
| [.agents/instructions/agent-skills.instructions.md](.agents/instructions/agent-skills.instructions.md) | `**/.agents/skills/**/SKILL.md` |

## Agents

Invoke via Claude Code slash commands (e.g. `/code-review application/models/class.evangtermine_query.php`).

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
| `/translate-de` | [translate-de/SKILL.md](.agents/skills/translate-de/SKILL.md) | German translation (du/Sie) per WordPress Polyglots Style Guide and Glossary |
| `/wp-release` | [wp-release/SKILL.md](.agents/skills/wp-release/SKILL.md) | Full plugin release workflow |
| `/grill-me` | [grill-me/SKILL.md](.agents/skills/grill-me/SKILL.md) | Stress-test a plan through relentless questions |
