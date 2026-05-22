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
- All changes committed and on `master`

## Workflow

### Step 1: Determine the new version

Review recent commits to decide the version bump:

```bash
git log --oneline $(git describe --tags --abbrev=0)..HEAD
```

Version rules (SemVer-light for WP plugins):
- **Patch** (`1.8` → `1.8.1`): bug fixes, no API changes
- **Minor** (`1.8` → `1.9`): new features, backwards-compatible
- **Major** (`1.8` → `2.0`): breaking changes (option key renames, class renames, shortcode changes)

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
phpcs --standard=WordPress --extensions=php evangtermine.php includes/
```

Fix any violations before committing.

### Step 6: Commit and tag

```bash
git add evangtermine.php readme.txt
git commit -m "chore: release X.Y.Z"
git tag X.Y.Z
git push && git push --tags
```

### Step 7: Create the GitHub release

```bash
gh release create X.Y.Z \
  --title "v X.Y.Z" \
  --notes "$(cat <<'EOF'
## What's Changed
* Fixed: <description>
* Added: <description>

**Full Changelog:** https://github.com/<owner>/evangtermine/compare/<prev-tag>...X.Y.Z
EOF
)"
```

### Step 8: Validate readme.txt (optional but recommended)

Open https://wordpress.org/plugins/developers/readme-validator/ and paste the contents of `readme.txt`. Fix any errors before SVN submission.

## Post-Release (WordPress.org SVN)

If publishing to WordPress.org, after the GitHub release:

```bash
# Check out SVN (first time)
svn co https://plugins.svn.wordpress.org/evangtermine/ svn-evangtermine
cd svn-evangtermine

# Copy plugin files to trunk
cp -r /path/to/evangtermine/* trunk/

# Create a new tag
svn cp trunk tags/X.Y.Z

# Commit to SVN
svn add --force trunk/ tags/X.Y.Z/
svn commit -m "Release X.Y.Z"
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Tag already exists | `git tag -d X.Y.Z && git push origin :X.Y.Z` then re-tag |
| `Stable tag` mismatch | `Version:` in PHP header and `Stable tag:` in readme.txt must be identical |
| readme.txt validator errors | Check short description ≤ 150 chars, `Tested up to` format (`6.7` not `6.7.1`) |
