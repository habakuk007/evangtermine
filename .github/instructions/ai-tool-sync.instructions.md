---
applyTo: 'CLAUDE.md,AGENTS.md,.github/copilot-instructions.md,.agents/**,.mcp.json,opencode.json,.vscode/mcp.json,.claude/commands/**'
description: 'Rules for keeping AI-tool config files in sync when any one of them is modified.'
---

# AI Tool Configuration Sync

## Architecture

`.agents/` is the **single source of truth** for all instructions, agents, and skills.
Tracked copies under `.github/` make the content available to other tools. Edit only `.agents/` and re-copy; never edit the copies.

```
.agents/
  instructions/       ← source of truth for all instruction files
  agents/             ← source of truth for all agent definitions
  skills/             ← source of truth for all skill definitions

.github/
  instructions/       ← tracked copy of .agents/instructions/  (do not edit directly)
  agents/             ← tracked copy of .agents/agents/        (do not edit directly)

.claude/commands/     ← slash commands that reference agents and skills

CLAUDE.md             ← instruction + agent + skill tables + project facts
AGENTS.md             ← instruction + agent + skill tables + MCP table + project facts
.github/copilot-instructions.md  ← same tables + project facts
.mcp.json             ← MCP server definitions for Claude Code
.vscode/mcp.json      ← MCP server definitions for VS Code / Copilot Chat
opencode.json         ← OpenCode config (reads AGENTS.md for context)
```

---

## When you add a new instruction file

1. Create `.agents/instructions/<kebab-case-topic>.instructions.md`
2. Include required frontmatter: `applyTo` (glob) and `description` (one-line)
3. Add a row to the instruction table in **all three**:
   - `CLAUDE.md` (under "Instruction Files")
   - `AGENTS.md` (under "Available Instruction Files")
   - `.github/copilot-instructions.md` (under "Coding Standards Applied Automatically")
4. Copy the file to `.github/instructions/` as well. In the repository that directory is a set of **tracked copies**, not a junction (a clone on GitHub/Linux has no junction, and Copilot reads only `.github/instructions/`). Verify with `diff -rq .agents/instructions .github/instructions` — it must print nothing.

---

## When you remove an instruction file

1. Delete from `.agents/instructions/` and from `.github/instructions/`.
2. Remove the row from all three instruction tables.

---

## When you add a new agent

1. Create `.agents/agents/<kebab-case-name>.agent.md` following `agents.instructions.md`
2. Create `.claude/commands/<name>.md` with:
   ```markdown
   Read and follow the agent instructions in `.agents/agents/<name>.agent.md`.
   Ignore the YAML frontmatter `tools:` array — use your own available tools instead.
   Context for this task: $ARGUMENTS
   ```
3. Add a row to the agent table in `CLAUDE.md`, `AGENTS.md`, and `.github/copilot-instructions.md`
4. Copy the file to `.github/agents/` as well (tracked copies, see above). Verify with `diff -rq .agents/agents .github/agents`.

---

## When you remove an agent

1. Delete from `.agents/agents/` only.
2. Delete the corresponding `.claude/commands/<name>.md`.
3. Remove the row from all three agent tables.

---

## When you add a new skill

1. Create `.agents/skills/<name>/SKILL.md` following `agent-skills.instructions.md`
2. Create `.claude/commands/<name>.md` with:
   ```markdown
   Read and follow the skill instructions in `.agents/skills/<name>/SKILL.md`.
   Ignore the YAML frontmatter `tools:` array — use your own available tools instead.
   Context for this task: $ARGUMENTS
   ```
3. Add a row to the skill table in `CLAUDE.md` and `AGENTS.md`

---

## When you remove a skill

1. Delete from `.agents/skills/` only.
2. Delete the corresponding `.claude/commands/<name>.md`.
3. Remove the row from the skill tables in `CLAUDE.md` and `AGENTS.md`.

---

## When you change `.mcp.json`

1. Update the **MCP Servers** table in `AGENTS.md` to match.
2. If the server is also useful for VS Code Copilot Chat, mirror it in `.vscode/mcp.json`.

---

## When you update `CLAUDE.md` with project facts

`CLAUDE.md` and `AGENTS.md` overlap on: architecture summary, key conventions, and the "Do Not Change" list.
After editing either file, check the other and apply the same update if the fact appears there too.

---

## When you update `.github/copilot-instructions.md`

- Verify the instruction, agent, and skill tables still match `.agents/` contents.
- Verify the "Do Not Change" items still match those in `CLAUDE.md`.

---

## Naming conventions

| Artifact | Pattern |
|----------|---------|
| Instruction file | `kebab-case-topic.instructions.md` |
| Agent file | `kebab-case-purpose.agent.md` |
| Skill directory | `kebab-case-name/SKILL.md` |
| Claude command | `<same-name-as-agent-or-skill>.md` |
