---
name: wp-release
description: Execute the full WordPress plugin release workflow for the evangtermine plugin. Use when asked to release a new version, cut a release, bump the version, tag a release, update the changelog, or prepare a new plugin version.
---

# WordPress Plugin Release — evangtermine

Complete release workflow: version bump → changelog → commit → tag → GitHub release.

## When to Use

- Releasing a new plugin version
- Bumping version after a bug fix or feature
- Preparing a release for WordPress.org SVN submission

## Prerequisites

- `gh` CLI authenticated (`gh auth status`)
- Clean working tree (`git status`)
- All changes committed; features are merged into `develop`, the release PR `develop` -> `master` is merged and you are on the updated `master` (tags are only created on `master`)

## Workflow

### Step 1: Determine the new version

Review recent commits to decide the version bump:

```bash
git log --oneline $(git describe --tags --abbrev=0)..HEAD
```

Version rules (SemVer-light for WP plugins):
- **Patch** (`3.3` → `3.3.1`): bug fixes, no API changes
- **Minor** (`3.3` → `3.4`): new features, backwards-compatible
- **Major** (`3.3` → `4.0`): breaking changes (option key renames, class renames, shortcode changes)

### Step 2: Update version in `evangtermine.php`

Change the `Version:` header line:

```php
 * Version: X.Y.Z
```

### Step 3: Update `readme.txt`

Change the `Stable tag:` line:

```
Stable tag: X.Y.Z
```

Update `Tested up to:` if tested against a newer WordPress version.

### Step 4: Write the changelog entry

Run to see all commits since the last tag:

```bash
git log $(git describe --tags --abbrev=0)..HEAD --format="%s%n%b"
```

Add a new entry at the TOP of `== Changelog ==` in `readme.txt`:

```
= X.Y.Z =
* Fixed: <description of fix>
* Added: <description of addition>
```

Follow the rules in `.agents/instructions/readme-txt.instructions.md`:
- One bullet per logical change (not per file)
- Verb prefix: `Fixed`, `Added`, `Changed`, `Removed`, `Improved`
- Omit CI-only or formatting-only changes

Add `== Upgrade Notice ==` entry **only** if there are breaking changes.

### Step 5: Run PHPCS one last time

```bash
phpcs --standard=WordPress --extensions=php evangtermine.php autoloader.php application/
```

Fix any violations before committing.

### Step 6: Commit and tag

```bash
git add evangtermine.php readme.txt
git commit -m "chore: release X.Y.Z"
git tag X.Y.Z
git push && git push --tags
```

### Step 7: GitHub release and release ZIP

Pushing the tag also starts `.github/workflows/release-zip.yml` (no approval needed). It builds
`evangtermine-X.Y.Z.zip` with `.github/scripts/build-release.sh` (same file set as the WordPress.org
package, top-level folder `evangtermine/`), checks (via the GitHub API) that the tag is on `master` and equals the plugin
version, and creates the GitHub release with generated notes and the ZIP attached. Only plain version tags (`X.Y` or `X.Y.Z`) are built; there are no pre-release tags. Do **not** run `gh release create` yourself right after the push (the two would race).
Wait for the workflow, then replace the generated notes if you want:

```bash
gh release edit X.Y.Z --title "v X.Y.Z" --notes-file release-notes.md
```

Notes template (`release-notes.md`):

```markdown
## What's Changed
* Fixed: <description>
* Added: <description>

**Full Changelog:** https://github.com/habakuk007/evangtermine/compare/<prev-tag>...X.Y.Z
```

If the release already exists, the workflow only uploads the ZIP (`--clobber`). Check that the asset
`evangtermine-X.Y.Z.zip` is on the release page. To try the build before tagging, run
`bash .github/scripts/build-release.sh` (needs rsync, unzip and zip or python3; on Windows use WSL). The ZIP lands in
`build/`. CI builds and checks it on every PR (job "Release files", ZIP as workflow artifact).

### Step 8: Validate readme.txt (optional but recommended)

Open https://wordpress.org/plugins/developers/readme-validator/ and paste the contents of `readme.txt`. Fix any errors before SVN submission.

## Post-Release (WordPress.org SVN)

**Preferred: let the deploy workflow do it.** Pushing the version tag on `master` runs `.github/workflows/deploy.yml` (10up action, environment `Deploy`, reviewer required). It respects `.distignore` and publishes `.wordpress-org/` (screenshots, banner, icon) to the SVN root-level `assets/`. A manual run is a dry run. What ships: the plugin PHP code, `application/`, `assets/`, `languages/` (.po/.mo), `readme.txt`, `changelog.txt`, `LICENSE` and `composer.json` (kept on purpose: it documents the dev tooling and licence; `composer.lock` is excluded). `build/` (local build output), `tests/`, `vendor/`, `node_modules/`, zips and all AI/tool files are excluded via `.distignore`. Check with `rsync -rc --exclude-from=.distignore ./ <tmpdir>/` and confirm no zips/tests/vendor/build/AI files and a size below 10 MB. Read `.agents/instructions/wordpress-org-submission.instructions.md` first (§8 common rejections, §9 automated security review).

Before tagging:
- Run Plugin Check including the runtime checks (`wp plugin check evangtermine --require=./wp-content/plugins/plugin-check/cli.php`, see `wordpress-org-submission.instructions.md` §8) and fix everything in the "Plugin repo" category.
- Make sure the wordpress.org account that pushes to SVN has two-factor authentication enabled (human task, cannot be checked from here).
- Run `/wp-security-review` on the release diff. Since June 2026 every release goes through an automated AI/Jetpack Scan review, and a version above the blocking threshold is withheld from the update API (fix and release a new version).
- **Pushing to SVN makes the release live immediately.** The plugin is open on WordPress.org again, so a pushed tag reaches all users with the next update check.

Only if the workflow cannot be used, manually (never copy dev files — respect `.distignore`):

```bash
# Check out SVN (first time)
svn co https://plugins.svn.wordpress.org/evangtermine/ svn-evangtermine
cd svn-evangtermine

# Copy the release files to trunk (excludes everything listed in .distignore)
rsync -r --delete --exclude-from=/path/to/evangtermine/.distignore /path/to/evangtermine/ trunk/
# Screenshots/banner/icon go to the root-level assets/ (not trunk/assets)
cp /path/to/evangtermine/.wordpress-org/* assets/

# Create a new tag
svn cp trunk tags/X.Y.Z

# Commit to SVN (a message is required)
svn add --force trunk/ tags/X.Y.Z/ assets/
svn commit -m "Release X.Y.Z"
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Tag already exists | `git tag -d X.Y.Z && git push origin :X.Y.Z` then re-tag |
| `Stable tag` mismatch | `Version:` in PHP header and `Stable tag:` in readme.txt must be identical |
| readme.txt validator errors | Check short description ≤ 150 chars, `Tested up to` format (`6.7` not `6.7.1`) |
