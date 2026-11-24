# Release process

This page describes how a version gets from a branch to WordPress.org. The step-by-step checklist for the person who releases is the `/wp-release` skill ([.agents/skills/wp-release/SKILL.md](../../.agents/skills/wp-release/SKILL.md)); this page explains the process behind it.

## Branches

| Branch | Role |
|--------|------|
| `master` | The released state, what is on WordPress.org. Protected: changes only through a pull request, CI must pass. Version tags are created on `master` |
| `develop` | Integration branch for further development. Protected like `master` |
| `fix/<issue>-<topic>`, `feat/<issue>-<topic>`, `chore/<issue>-<topic>`, `docs/<issue>-<topic>` | Short-lived work branches. Pull requests go against `develop` |

Urgent security fixes may go to `master` first (branch `hotfix/*`) and are merged back into `develop`. See [CONTRIBUTING.md](../../CONTRIBUTING.md) for the workflow and the commit identity.

## Release

1. **Prepare on `develop`:** all changes are merged and CI is green. Run the Plugin Check including the runtime checks, and run `/wp-security-review` on the release diff.
2. **Version and changelog:** in a pull request, set `Version:` in `evangtermine.php`, `ET_VERSION`, `Stable tag:` in `readme.txt` (all identical), update `Tested up to`, and add the changelog entry (and an Upgrade Notice only for breaking changes). Follow [.agents/instructions/readme-txt.instructions.md](../../.agents/instructions/readme-txt.instructions.md); `readme.txt` has a size limit of about 10 KB.
3. **Release pull request:** `develop` into `master`. Open it with `bash .agents/skills/gh-bot/gh-bot.sh pr develop "Release X.Y.Z" --body-file <path>` (as the bot, so the developer can approve). CI must pass, including the job "Release files" that builds the file set and the ZIP.
4. **Tag:** create the tag (`X.Y` or `X.Y.Z`) on the merge commit on `master` and push it.

## What the tag starts

| Workflow | What it does |
|----------|--------------|
| `.github/workflows/deploy.yml` | Deploys to the WordPress.org SVN with the 10up action. The tagged commit must be contained in `master`. The job waits for the approval of the environment `Deploy`, which holds the SVN secrets. **Pushing to SVN makes the release live** for all users with their next update check |
| `.github/workflows/release-zip.yml` | Builds `evangtermine-X.Y.Z.zip` and attaches it to the GitHub release. It needs no approval and never touches the SVN |

A manual start of `deploy.yml` is a dry run: it does everything except the commit to the SVN.

## What is shipped

The package contains the plugin code, `application/`, `assets/`, `languages/` (`.po` and `.mo`), `readme.txt`, `changelog.txt`, `LICENSE` and `composer.json`. Everything in `.distignore` is excluded: tests, tooling, AI and editor files, `.github`, `docs/`, `README.md`, `CONTRIBUTING.md`, `SECURITY.md`, the readme translations and the `.pot` template. CI checks the file set on every pull request (`.github/scripts/build-release.sh`). Screenshots, banner and icon come from `.wordpress-org/` and go to the SVN `assets/` folder, not into the plugin.

## Documentation

`docs/` is not part of the release. The documentation is read on GitHub, on the default branch (`master`); it reaches readers with the release merge. Pull requests that change behaviour should update the pages in `docs/de/` and `readme.txt` together. CI checks the Markdown (markdownlint) and the relative links in `docs/` and `README.md`.
