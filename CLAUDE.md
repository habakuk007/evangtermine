# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Evangelische Termine** (v1.8) — WordPress plugin that embeds the Protestant events calendar from [www.evangelische-termine.de](https://www.evangelische-termine.de) via two shortcodes and a sidebar widget. All event HTML is fetched live from the remote API using PHP cURL and injected into the page. There is no build system, no Composer, and no npm.

## Commands

```bash
# Lint PHP (requires PHPCS + WPCS)
phpcs --standard=WordPress evangtermine.php includes/

# Auto-fix
phpcbf --standard=WordPress evangtermine.php includes/

# Generate translations (requires WP-CLI)
wp i18n make-pot . languages/evangtermine.pot

# Run WP.org compliance check (requires WP-CLI + plugin-check)
wp plugin check evangtermine
```

## Architecture

**Entry point:** `evangtermine.php` registers two shortcodes (`et_veranstalter_shortcode`, `et_teaser_shortcode`) and the `ET_Widget` class, loads admin files conditionally, and enqueues CSS.

**Data flow:** Shortcode/widget attributes → `$et_defaults` array → `et_veranstalter()` or `et_teaser()` in `includes/functions.php` → cURL request to `{protocol}{host}/veranstaltungen-php?{querystring}` → raw HTML response → string-replace post-processing → returned and embedded in page.

**Filter state:** `et_veranstalter()` uses PHP sessions (`$_SESSION['session']` stdClass) to persist the user's filter selections (vid, region, eventtype, etc.) and current page across requests. The session is initialized inside the function call, not at plugin boot.

**Settings:** `includes/options.php` registers seven options (`vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`) under `et_group` using the Settings API. Shortcodes and widget read these as defaults; shortcode attributes override them per-call.

**HTML post-processing:** `et_veranstalter()` strips remote CSS/JS links and rewrites internal URLs (image paths, pagination links) so the embedded content works within WordPress.

## Key Conventions

| Item | Value |
|------|-------|
| PHP prefix | `et_` (functions), `ET_` (constants), `ET_Widget` (class) |
| Text domain | `evangtermine` |
| Settings option names | `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost` (no `et_` prefix — legacy) |
| API endpoint (full) | `{etprotocol}{ethost}/veranstaltungen-php` or `/teaser` |
| cURL timeout | 2 s (veranstalter), 5 s (teaser) |

## Do Not Change

- `ET_DEFAULT_HOST`, `ET_DEFAULT_PROTOCOL` — API target; users override via settings
- Option key names (`vid`, `region`, etc.) — bare names without prefix; renaming breaks existing installs
- CSS class names prefixed `.et_` — renaming breaks user custom CSS
- `ET_Widget` class name — WordPress serializes it as a string in the database

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

Invoke via Claude Code slash commands (e.g. `/code-review includes/functions.php`).

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
| `/php-lint` | [php-lint/SKILL.md](.agents/skills/php-lint/SKILL.md) | PHPCS/PHPCBF for WordPress Coding Standard |
| `/wp-release` | [wp-release/SKILL.md](.agents/skills/wp-release/SKILL.md) | Full plugin release workflow |
| `/grill-me` | [grill-me/SKILL.md](.agents/skills/grill-me/SKILL.md) | Stress-test a plan through relentless questions |
