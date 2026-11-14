# AGENTS.md — Evangelische Termine

Project guide for all AI agents (OpenCode, Claude Code, GitHub Copilot, VS Code).

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

# Validate readme.txt
# Open https://wordpress.org/plugins/developers/readme-validator/ and paste contents

# Generate translations (requires WP-CLI)
wp i18n make-pot . languages/evangtermine.pot

# Run WordPress.org compliance checks (requires WP-CLI + plugin-check)
wp plugin check evangtermine
```

No build system. Composer (PHPCS) and npm (end-to-end tests) are used for dev tooling only; the plugin has no runtime dependencies.

## Project Structure

```
evangtermine.php          <- Plugin entry: constants, shortcodes, widget registration, CSS hook
autoloader.php            <- AutoloaderET: application/{controllers,models,views,helper,forms}/class.<name>.php
application/
  configs/defaults.php    <- ET_DEFAULT_HOST, ET_DEFAULT_PROTOCOL, ET_OPTION_* defaults
  controllers/            <- ShortcodeVeranstalter/Teaser/Panels/Minical, EtWidget
  models/class.etquery.php <- EtQuery: query string + wp_remote_get + HTML post-processing
  views/class.etview.php  <- EtView: <div> wrapper
  helper/                 <- IncludeCSS (wp_head), Debugging
  forms/                  <- options.php (Settings API, et_group), widget form
assets/                   <- bundled Bootstrap 3.3.7, jQuery, Font Awesome 4.7.0, CSS
tests/e2e/                <- Playwright tests against WordPress Playground (not in the release)
readme.txt                <- WordPress.org plugin listing
```

## Architecture

**Data flow (shortcode or widget call):**
1. `evangtermine.php` registers the shortcodes (`et_veranstalter`, `et_teaser`, `et_panels`, `et_minical`) and the widget
2. The controller collects attributes and saved options into an `EtQuery` object
3. Request values (`$_REQUEST`, `$_GET['etID']`) are merged into the query
4. `EtQuery::retrieveStream()` fetches the remote Evangelische Termine page with `wp_remote_get()`
5. `EtQuery::filterStream()` post-processes the HTML (strip remote CSS/JS, rewrite image and pagination URLs)
6. `EtView::buildView()` wraps the HTML in a `<div>`, which is returned to WordPress

**Session management:** `ShortcodeVeranstalter` keeps the filter state in `$_SESSION['etsession']` (stdClass) across pagination and filter form submits. It does not call `session_start()` itself.

**Admin settings** (`application/forms/options.php`): Seven options registered under `et_group` via Settings API. Shortcode attributes and widget fields override these defaults per call.

**Security-critical spots:** request data that reaches the remote URL or the returned HTML (`ShortcodeVeranstalter`, `EtQuery::getQuerystring`, `EtQuery::filterStream`) and printed option values (`options.php`, `IncludeCSS`).

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
| Capability | `manage_options` |
| Option names | `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost` |
| External API | `{etprotocol}{ethost}/veranstaltungen-php`, `/detail-php`, `/teaser` |
| HTTP | `wp_remote_get()` (WordPress HTTP API) |
| Shortcodes | `[et_veranstalter]`, `[et_teaser]`, `[et_panels]`, `[et_minical]` |
| Commit identity | `habakuk007 <2253878+habakuk007@users.noreply.github.com>` (repo-local git config) |

## Do Not Change

- `ET_DEFAULT_HOST` / `ET_DEFAULT_PROTOCOL` — API target constants (user-overridable via settings)
- Option key names (`vid`, `region`, etc.) — bare names without prefix; renaming breaks existing installs
- CSS class names prefixed `.et_` — changing breaks user custom CSS
- `EtWidget` class name — WordPress stores the widget class name in the database
- Shortcode names

## Available Agents

All agent definitions live in `.agents/agents/` (source of truth).
`.github/agents/` is a directory junction — never edit files there directly.
Each agent has a corresponding Claude Code slash command in `.claude/commands/`.

| Agent | Claude Command | Purpose |
|-------|---------------|---------|
| [code-review.agent.md](.agents/agents/code-review.agent.md) | `/code-review` | WPCS compliance, security, backwards-compat review |
| [wp-security-review.agent.md](.agents/agents/wp-security-review.agent.md) | `/wp-security-review` | XSS, CSRF, SQLi, privilege escalation audit |
| [refine-issue.agent.md](.agents/agents/refine-issue.agent.md) | `/refine-issue` | Add acceptance criteria, technical notes, edge cases to a GitHub issue |

## Available Skills

All skills live in `.agents/skills/` (source of truth).
Each skill has a corresponding Claude Code slash command in `.claude/commands/`.

| Skill | Claude Command | Purpose |
|-------|---------------|---------|
| [gh/SKILL.md](.agents/skills/gh/SKILL.md) | `/gh` | GitHub CLI: issues, PRs, releases, labels |
| [gh-bot/SKILL.md](.agents/skills/gh-bot/SKILL.md) | `/gh-bot` | Open PRs and PR comments as the bot identity `habakuk-ai`; commits and pushes stay `habakuk007` |
| [php-lint/SKILL.md](.agents/skills/php-lint/SKILL.md) | `/php-lint` | PHPCS/PHPCBF for WordPress Coding Standard |
| [wp-release/SKILL.md](.agents/skills/wp-release/SKILL.md) | `/wp-release` | Full plugin release workflow (version bump → tag → GitHub release) |
| [grill-me/SKILL.md](.agents/skills/grill-me/SKILL.md) | `/grill-me` | Stress-test a plan through relentless questions |

## Available Instruction Files

All instructions live in `.agents/instructions/` (source of truth).
`.github/instructions/` is a directory junction pointing to the same folder.

| File | Applies To |
|------|-----------|
| [wordpress.instructions.md](.agents/instructions/wordpress.instructions.md) | `**/*.php` |
| [a11y.instructions.md](.agents/instructions/a11y.instructions.md) | `**` |
| [html-css-style-color-guide.instructions.md](.agents/instructions/html-css-style-color-guide.instructions.md) | `**/*.css`, `**/*.html` |
| [readme-txt.instructions.md](.agents/instructions/readme-txt.instructions.md) | `readme.txt` |
| [ai-tool-sync.instructions.md](.agents/instructions/ai-tool-sync.instructions.md) | `CLAUDE.md`, `AGENTS.md`, `.github/copilot-instructions.md`, `.agents/**`, `.mcp.json` |
| [agents.instructions.md](.agents/instructions/agents.instructions.md) | `**/*.agent.md` |
| [agent-skills.instructions.md](.agents/instructions/agent-skills.instructions.md) | `**/.agents/skills/**/SKILL.md` |

## MCP Servers

| Server | Transport | Command / URL |
|--------|-----------|---------------|
| playwright-mcp | stdio | `npx @playwright/mcp@latest` |
| markitdown | stdio | `uvx markitdown-mcp@0.0.1a4` |
| context7 | stdio | `npx @upstash/context7-mcp@1.0.31` |
| github-mcp | http | `https://api.githubcopilot.com/mcp/` |
| microsoft-docs | http | `https://learn.microsoft.com/api/mcp` |

Config: `.mcp.json` (Claude Code) · `.vscode/mcp.json` (VS Code)
