---
description: 'Guidelines for creating high-quality Agent Skills for GitHub Copilot and Claude Code'
applyTo: '**/.agents/skills/**/SKILL.md,**/.github/skills/**/SKILL.md,**/.claude/skills/**/SKILL.md'
---

# Agent Skills File Guidelines

Instructions for creating effective and portable Agent Skills that enhance AI agents with specialized capabilities, workflows, and bundled resources.

## What Are Agent Skills?

Agent Skills are self-contained folders with a `SKILL.md` plus optional bundled resources (scripts, templates, reference docs). They teach agents a specialized workflow on demand — loaded only when the user's request matches the skill description.

Key characteristics:
- **Portable**: Works across VS Code, Copilot CLI, Claude Code
- **Progressive loading**: Only loaded when relevant (description match)
- **Resource-bundled**: Can include scripts, templates, reference docs
- **On-demand**: Activated automatically based on prompt relevance

## Directory Structure

| Location | Scope |
|----------|-------|
| `.agents/skills/<skill-name>/` | Project — **source of truth** |
| `.github/skills/<skill-name>/` | Symlink/junction to `.agents/skills/` |

Each skill **must** have its own subdirectory containing at minimum a `SKILL.md` file.

## Required SKILL.md Format

### Frontmatter (Required)

```yaml
---
name: my-skill
description: What this skill does and when to use it. Use when asked to X, Y, or Z. Supports A, B, C.
---
```

| Field | Required | Constraints |
|-------|----------|-------------|
| `name` | **Yes** | Lowercase, hyphens for spaces, max 64 chars |
| `description` | **Yes** | States WHAT it does + WHEN to use it + keywords, max 1024 chars |
| `license` | No | SPDX identifier or `Complete terms in LICENSE.txt` |

### Description — CRITICAL

The `description` is the **primary discovery mechanism**. Copilot reads only `name` and `description` to decide whether to load a skill. A vague description means the skill never activates.

**Good:**
```yaml
description: Run PHPCS/PHPCBF to lint and auto-fix PHP files against the WordPress Coding Standard. Use when asked to lint, check coding standards, fix PHP style issues, or run phpcs/phpcbf.
```

**Poor:**
```yaml
description: PHP linting helper
```

Include: (1) what it does, (2) when to use it, (3) keywords users might say.

### Body Content — Recommended Sections

| Section | Purpose |
|---------|---------|
| `# Title` | One-line overview |
| `## When to Use` | Trigger scenarios (reinforces description) |
| `## Prerequisites` | Required tools, install instructions |
| `## Workflows` | Numbered steps for common tasks |
| `## Troubleshooting` | Common errors and fixes |
| `## References` | Links to docs or bundled files |

## Optional Resource Bundling

| Folder | Purpose | AI Modifies? |
|--------|---------|-------------|
| `scripts/` | Executable automation | No — runs as-is |
| `references/` | Documentation read into context | No — read as-is |
| `assets/` | Static files used in output | No |
| `templates/` | Starter code the AI extends | Yes |

Keep `SKILL.md` body under 500 lines — split large workflows into `references/` files and link to them.

## File Organization

- **Location:** `.agents/skills/<name>/SKILL.md` (source of truth)
- **Naming:** `kebab-case-topic` for the skill directory
- Each skill in `.agents/skills/` needs a corresponding `.claude/commands/<name>.md` slash command

## Creation Checklist

- [ ] `name` is lowercase with hyphens, ≤ 64 characters
- [ ] `description` states WHAT, WHEN, and relevant KEYWORDS
- [ ] Body has When to Use, Prerequisites, and Workflows sections
- [ ] SKILL.md body under 500 lines (split large content into `references/`)
- [ ] No hardcoded credentials or secrets
- [ ] Corresponding `.claude/commands/<name>.md` created
- [ ] Skill table updated in `AGENTS.md`

## Common Mistakes to Avoid

- ❌ Vague description with no triggers or keywords
- ❌ Skill body over 500 lines (split into references/)
- ❌ Hardcoded paths or credentials
- ❌ Creating skill without matching `.claude/commands/` entry

## Official Documentation

- [VS Code Agent Skills](https://code.visualstudio.com/docs/copilot/customization/agent-skills)
- [Awesome Copilot Skills](https://github.com/github/awesome-copilot/blob/main/docs/README.skills.md)
