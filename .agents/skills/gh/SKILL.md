---
name: gh
description: Use the GitHub CLI (gh) to manage issues, pull requests, releases, labels, and milestones for this repository. Use when asked to create or update issues, open or merge PRs, cut a release, list open PRs, assign labels, or do any GitHub repository management task.
---

# GitHub CLI (gh)

Manage this repository via the `gh` CLI. The repo is `evangtermine` on GitHub.

## When to Use

- Creating, editing, closing, or listing issues
- Creating, reviewing, merging, or listing pull requests
- Creating releases and uploading release assets
- Managing labels and milestones
- Checking CI status for a PR

## Prerequisites

```bash
# Verify auth
gh auth status

# If not logged in
gh auth login
```

## Workflows

### Issues

```bash
# Create an issue
gh issue create --title "Title" --body "Description" --label "bug"

# List open issues
gh issue list

# View an issue
gh issue view 42

# Close an issue
gh issue close 42

# Add a comment
gh issue comment 42 --body "Fixed in commit abc123."
```

### Pull Requests

```bash
# Create a PR (pushes current branch if needed)
gh pr create --title "fix: correct cURL timeout" --body "Fixes #42"

# List open PRs
gh pr list

# View a PR
gh pr view 17

# Check CI status
gh pr checks 17

# Merge a PR (squash)
gh pr merge 17 --squash --delete-branch

# Review a PR
gh pr review 17 --approve
gh pr review 17 --request-changes --body "Please add escaping."
```

### Releases

WordPress plugin releases must follow this tagging convention:
- Tag name = `Version:` field in `evangtermine.php` = `Stable tag:` in `readme.txt`
- Use SemVer: `1.9`, `1.9.1`, `2.0.0`

```bash
# Create a release (tag must match plugin version)
gh release create 1.9 \
  --title "v1.9" \
  --notes "$(cat <<'EOF'
## What's Changed
* Fixed cURL timeout handling
* Added https:// protocol option
EOF
)"

# List releases
gh release list

# View a release
gh release view 1.9

# Upload a zip asset to a release
gh release upload 1.9 evangtermine.zip
```

### Labels

```bash
# List labels
gh label list

# Create a label
gh label create "wordpress" --color "#0075ca" --description "WordPress-specific"

# Add label to an issue
gh issue edit 42 --add-label "bug,wordpress"
```

### Milestones

```bash
# List milestones
gh api repos/:owner/:repo/milestones --jq '.[].title'

# Create a milestone
gh api repos/:owner/:repo/milestones \
  --method POST \
  --field title="v2.0" \
  --field due_on="2026-12-31T00:00:00Z"
```

### Useful Shortcuts

```bash
# Open repo in browser
gh browse

# View recent workflow runs
gh run list

# Watch a running workflow
gh run watch

# Download workflow artifacts
gh run download <run-id>
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `gh: command not found` | Install from https://cli.github.com/ |
| `authentication required` | Run `gh auth login` |
| `HTTP 422` on release | Tag already exists — use `gh release edit` instead |
| PR creation fails | Ensure branch is pushed: `git push -u origin HEAD` |
