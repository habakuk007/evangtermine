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
- `bash .agents/skills/gh-bot/gh-bot.sh pr <branch> "<title>" "<body>"` – open a PR (base `develop`; `hotfix/*` and the branch `develop` (release PR) → `master`)
- `bash .agents/skills/gh-bot/gh-bot.sh pr <branch> "<title>" --body-file <path>` – open a PR, reading the body from a file (multi-line bodies; same path rules as `comment --body-file`)
- `bash .agents/skills/gh-bot/gh-bot.sh comment <pr-number> "<text>"` – comment on a PR (short bodies only)
- `bash .agents/skills/gh-bot/gh-bot.sh comment <pr-number> --body-file <path>` – comment on a PR, reading the body from a file (multi-line bodies with quotes or newlines)
- `bash .agents/skills/gh-bot/gh-bot.sh list` – list open PRs

Release PR: `gh-bot.sh pr develop "Release X.Y.Z" --body-file <path>` opens `develop` → `master`.

### Posting a multi-line / formatted comment

The `bash` that runs `gh-bot.sh` is **WSL / Git-Bash** — a Linux shell whose working
directory is the repo. The `--body-file` check runs in *that* shell first, so a
**Windows-style absolute path (`C:\…` / `C:/…`) fails** with
`ERROR: --body-file: … not found`. (A relative path or the inline form never have this
problem.)

Use one of these — in order of preference:

1. **Relative path (recommended).** Write the body file *inside the repo* and pass it
   **relative** to the working directory:
   ```bash
   # 1. write the body to a file inside the repo, e.g. .gh-bot-comment-131.md
   # 2. post it by relative path, then clean up
   bash .agents/skills/gh-bot/gh-bot.sh comment 131 --body-file .gh-bot-comment-131.md
   rm .gh-bot-comment-131.md
   ```
   A relative path resolves in both the WSL shell (the existence check) and the Windows
   `gh.exe` the script hands it to.

2. **Inline for short bodies.** `comment <pr> "<text>"`. The script quotes the text for
   the shell, but keep the body short and free of heavy backtick / `$` / double-quote use.

3. **Absolute Windows path (works, but avoid).** The script now maps a `C:\…` / `C:/…`
   path into the WSL namespace for the existence check, so it no longer fails — but a
   relative path (1) is simpler and more reliable. Prefer it.

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
