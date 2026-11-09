---
description: 'Guidelines for creating custom agent files for GitHub Copilot and Claude Code'
applyTo: '**/*.agent.md'
---

# Custom Agent File Guidelines

Instructions for creating effective `.agent.md` files that provide specialized expertise for specific development tasks in GitHub Copilot and Claude Code.

## Required Frontmatter

Every agent file must include YAML frontmatter:

```yaml
---
description: 'Brief description of the agent purpose and capabilities'
name: 'Agent Display Name'
tools: ['read', 'edit', 'search']
model: 'claude-sonnet-4-6'
---
```

### Frontmatter Fields

| Field | Required | Notes |
|-------|----------|-------|
| `description` | **Yes** | Single-quoted, 50–150 chars, clearly states purpose. Used by Copilot for agent discovery. |
| `name` | Optional | Display name (title case). Defaults to filename without extension. |
| `tools` | Optional | See tool aliases below. Omit to allow all tools. |
| `model` | Recommended | `'claude-sonnet-4-6'`, `'claude-opus-4-7'`, `'gpt-4o'` |
| `target` | Optional | `'vscode'` or `'github-copilot'`. Omit for both. |
| `infer` | Optional | Set `false` to require manual agent selection (default: `true`). |
| `handoffs` | Optional | VS Code 1.106+ only. See handoffs section. |

### Standard Tool Aliases

| Alias | Covers | Notes |
|-------|--------|-------|
| `execute` | shell, Bash, PowerShell | Use only when agent must run commands |
| `read` | Read, view | File reading |
| `edit` | Edit, Write, MultiEdit | File modification |
| `search` | Grep, Glob | Code/file search |
| `agent` | Task | Invoke sub-agents |
| `web` | WebSearch, WebFetch | External lookups |
| `github` | github/* | GitHub MCP tools |

**Principle of least privilege:** only enable tools the agent actually needs.

### Handoffs (VS Code only)

Enable guided workflows that chain agents together:

```yaml
handoffs:
  - label: Review Changes
    agent: code-review
    prompt: 'Review the changes just made for WordPress security and WPCS compliance.'
    send: false
```

## Agent Prompt Structure

Below the frontmatter, the markdown body defines the agent's behavior:

1. **Identity & Role** — who the agent is and its primary purpose
2. **Core Responsibilities** — specific tasks it performs
3. **Approach & Methodology** — how it works
4. **Guidelines & Constraints** — what to do / avoid
5. **Output Expectations** — format and quality standards

### Prompt Writing Rules

- Use imperative mood: "Analyze", "Report", "Check" — not "You should"
- Define clear scope limits (what the agent does AND does not do)
- Keep total content under 30,000 characters
- Use headers, bullets, and tables for scannability

## File Organization

- **Location:** `.agents/agents/` (source of truth) — Copilot reads via `.github/agents/` junction
- **Naming:** `kebab-case-purpose.agent.md`
- **Allowed chars:** `a-z`, `A-Z`, `0-9`, `-`, `_`, `.`
- Each agent in `.agents/agents/` needs a corresponding `.claude/commands/<name>.md` slash command

## Creation Checklist

- [ ] `description` present, single-quoted, 50–150 chars
- [ ] `name` specified (recommended)
- [ ] `tools` minimal and sufficient for the agent's purpose
- [ ] `model` specified
- [ ] Clear identity/role section in body
- [ ] Core responsibilities listed
- [ ] Guidelines and constraints defined
- [ ] Output format specified
- [ ] Filename is `kebab-case.agent.md`
- [ ] Corresponding `.claude/commands/<name>.md` created
- [ ] Agent table updated in `CLAUDE.md`, `AGENTS.md`, `.github/copilot-instructions.md`

## Common Mistakes to Avoid

- ❌ Missing or vague `description` (breaks Copilot auto-discovery)
- ❌ Description not wrapped in single quotes
- ❌ Granting `execute` when only `read`+`search` are needed
- ❌ Vague instructions without scope boundaries
- ❌ Creating agent without matching `.claude/commands/` entry
- ❌ Editing `.github/agents/` directly (it's a junction — edit `.agents/agents/` instead)

## Official Documentation

- [Creating Custom Agents](https://docs.github.com/en/copilot/how-tos/use-copilot-agents/coding-agent/create-custom-agents)
- [Custom Agents in VS Code](https://code.visualstudio.com/docs/copilot/customization/custom-agents)
- [Awesome Copilot Agents](https://github.com/github/awesome-copilot/tree/main/agents)
