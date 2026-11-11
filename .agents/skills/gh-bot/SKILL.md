---
name: gh-bot
description: Open pull requests and write PR comments on GitHub as the AI bot identity (habakuk-ai) while commits and pushes stay under the developer's own identity (habakuk007), so the developer can review and approve AI-authored PRs. Use whenever a branch must be proposed as a PR or an AI reply must be posted on a PR.
---

# GitHub bot identity (gh-bot)

GitHub does not let a PR author approve their own PR. So the AI opens PRs and comments as the
separate account `habakuk-ai`, and the developer (`habakuk007`) reviews, approves and merges.
All other git and `gh` work stays under the developer's identity.

## Who acts as whom

| Step | Tool | Identity |
|---|---|---|
| commit | `git commit` | developer (`habakuk007`, local git config); the bot is never the commit author |
| push | plain `git push origin <branch>` | **developer**, own credentials |
| open the PR | `bash .agents/skills/gh-bot/gh-bot.sh pr ...` | **bot** |
| comment on a PR | `bash .agents/skills/gh-bot/gh-bot.sh comment ...` | **bot** (AI replies are recognisable as AI) |
| read PRs, issues, CI (`gh pr view`, `gh pr checks`, …) | plain `gh` | developer |
| approve, merge, close | human only | developer |

Push first with plain `git push -u origin <branch>`, then open the PR with `gh-bot.sh pr`.
Never use the bot for pushes.

## Commands

- `bash .agents/skills/gh-bot/gh-bot.sh check` – verify the bot token (login, repo access, expiry)
- `bash .agents/skills/gh-bot/gh-bot.sh pr <branch> "<title>" "<body>"` – open a PR (base `develop`; `hotfix/*` → `master`)
- `bash .agents/skills/gh-bot/gh-bot.sh comment <pr-number> "<text>"` – comment on a PR
- `bash .agents/skills/gh-bot/gh-bot.sh list` – list open PRs

Release PRs `develop` → `master` are not covered by `pr`; ask the user how to open them.

If a command fails with setup instructions, show them to the user **completely** and stop.
If the output contains a WARNING, pass it on to the user and continue.

## Setup (once per PC, by the user)

1. The bot account `habakuk-ai` needs write access to the repository (collaborator) and a
   personal access token (classic: `repo`; fine-grained: Contents, Pull requests and Issues
   read/write for this repository), with an expiry date.
2. The user runs `powershell -ExecutionPolicy Bypass -File .agents\skills\gh-bot\set-token.ps1`
   in their own terminal. The token is stored DPAPI-encrypted under
   `%LOCALAPPDATA%\claude\secrets`.
3. `GH_TOKEN` / `GITHUB_TOKEN` must **not** be set in the Windows or Claude environment,
   otherwise plain `gh` and `git push` also run as the bot. `check` warns about this.

## Never

- print, store or ask for a token (the user runs `set-token.ps1` in their own terminal),
- run `set-token.ps1` yourself,
- approve or merge PRs – that is the human reviewer's job,
- post bot comments or open PRs unless the user asked for it or the current task includes it,
- commit or push unless the user asked for that step.

Project rules still apply: commit message format and branch/PR targets are in `CLAUDE.md`.
