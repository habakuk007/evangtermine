# AGENTS.md — Evangelische Termine

Project guide for all AI agents (OpenCode, Claude Code, GitHub Copilot, VS Code).

## Commands

```bash
# Lint PHP (requires PHPCS + WPCS)
phpcs --standard=WordPress evangtermine.php includes/

# Auto-fix style issues
phpcbf --standard=WordPress evangtermine.php includes/

# Validate readme.txt
# Open https://wordpress.org/plugins/developers/readme-validator/ and paste contents

# Generate translations (requires WP-CLI)
wp i18n make-pot . languages/evangtermine.pot

# Run WordPress.org compliance checks (requires WP-CLI + plugin-check)
wp plugin check evangtermine
```

No build system, no npm, no Composer.

## Project Structure

```
evangtermine.php          ← Plugin entry: shortcodes, widget class, CSS hook
includes/
  functions.php           ← et_veranstalter() and et_teaser() — all cURL logic
  options.php             ← Admin settings page (Settings API, et_group)
assets/
  css/evangtermine.css    ← All frontend/widget styles (.et_ prefix)
uninstall.php             ← Cleans up options on uninstall
readme.txt                ← WordPress.org plugin listing
```

## Architecture

**Data flow (shortcode or widget call):**
1. `evangtermine.php` shortcode handler collects `$et_defaults` from attributes + saved options
2. Calls `et_veranstalter($et_defaults)` or `et_teaser($et_defaults)` in `includes/functions.php`
3. Function builds a query string, makes a cURL GET to the remote Evangelische Termine API
4. Raw HTML response is post-processed (strip remote CSS/JS, rewrite image URLs, rewrite pagination links)
5. Resulting HTML is returned and embedded in a wrapper `<div>`

**Session management:** `et_veranstalter()` uses `$_SESSION['session']` (stdClass) to persist filter state across pagination and filter form submits. Session is started inside the function if not already active.

**Admin settings** (`includes/options.php`): Seven options registered under `et_group` via Settings API. Shortcode attributes and widget fields override these defaults per-call.

## Key Conventions

| Item | Value |
|------|-------|
| PHP prefix | `et_` (functions), `ET_` (constants), `ET_Widget` (class) |
| Text domain | `evangtermine` |
| Capability | `manage_options` |
| Option names | `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost` |
| External API | `{etprotocol}{ethost}/veranstaltungen-php` and `/teaser` |
| Required PHP ext | `curl` |
| Shortcodes | `[et_veranstalter]`, `[et_teaser]` |

## Do Not Change

- `ET_DEFAULT_HOST` / `ET_DEFAULT_PROTOCOL` — API target constants (user-overridable via settings)
- Option key names (`vid`, `region`, etc.) — bare names without prefix; renaming breaks existing installs
- CSS class names prefixed `.et_` — changing breaks user custom CSS
- `ET_Widget` class name — WordPress serializes it as a string in the database

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
