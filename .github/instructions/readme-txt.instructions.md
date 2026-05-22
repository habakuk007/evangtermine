---
applyTo: 'readme.txt,changelog.txt'
description: 'WordPress.org readme.txt format rules: header fields, section structure, changelog authoring from git log, Upgrade Notice limits, version sync, and file-size management.'
---

# WordPress.org `readme.txt` Standards

Rules for authoring and maintaining `readme.txt` in this plugin. The file controls the
plugin's listing on `wordpress.org/plugins/evangtermine` and drives automatic updates.
Reference: [How Your readme.txt Works](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/)

---

## 1) Header Fields

The header block is the first section of `readme.txt`. Every field has strict requirements.

### `Stable tag` — CRITICAL

- Must always equal the `Version:` field in `evangtermine.php`.
- Numbers and periods only (SemVer: `MAJOR.MINOR.PATCH`). Never use `trunk`.
- WordPress.org reads the `Stable tag` to determine which `/tags/` directory to serve.
  A mismatch between `Stable tag` and the PHP header version causes the wrong version
  to appear on the download button.

### Short description (the line after the last header field)

- Maximum **150 characters**. No Markdown markup. Will be hard-truncated by the directory.
- Describes what the plugin does in plain language; not a marketing tagline.

### `Tested up to`

- Major.minor format only — e.g. `6.9`, never `WP 6.9` or `6.9.1`.
- Update this field each release cycle when testing against a newer WordPress version.
- The directory automatically appends the minor patch; plugins should not break on minor updates.

### `Tags`

- Up to **12 tags** are permitted; only the **first 5** are displayed on WordPress.org (all 12 are used for search indexing).
- Lowercase, comma-separated. Tags beyond 12 are ignored entirely.
- Do not use competitor brand names as tags — this violates WordPress.org Guideline 12.
- Do not use tags unique to this plugin alone (they won't appear in tag browsing).
- Do not repeat keywords already in the plugin name or description — this is keyword stuffing and is penalised.

*Source: [Detailed Plugin Guidelines #12](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#12-public-facing-pages-on-wordpress-org-readmes-must-not-spam) · [Plugin Developer FAQ](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/)*

### `Contributors`

- Comma-separated list of **WordPress.org usernames** — case-sensitive, no spaces around commas.
- Do not use email addresses, GitHub handles, or display names here.

### `Requires at least` and `Requires PHP`

- Since WordPress 5.8 these values are parsed from the plugin's main PHP file header, not from `readme.txt`.
- Keep both files in sync, but treat `evangtermine.php` as the authoritative source.
- Format: numbers only — `6.0`, `7.4`. No "WP" or "PHP" prefix.

---

## 2) Required and Recommended Sections

### Required
- `== Description ==` — what the plugin does; written for end users, not developers.
- `== Changelog ==` — version history; see Section 4 for authoring rules.

### Recommended
- `== Installation ==` — omit only if the plugin has zero custom install steps.
- `== Frequently Asked Questions ==` — address real support questions.
- `== Screenshots ==` — numbered captions that must match screenshot files uploaded to the SVN
  `/assets/` directory. Never reference a screenshot number that does not have a corresponding file.
- `== Upgrade Notice ==` — per-version notes for users; see Section 5.

### Custom sections

Custom sections (e.g. `== Credits ==`) are allowed but use them sparingly. Users expect the
standard layout; unexpected sections are often skipped.

---

## 3) Markdown

`readme.txt` uses a subset of Markdown. The following work reliably:

- `**bold**`, `*italic*`
- `` `inline code` ``
- Unordered lists with `*` or `-`
- Numbered lists
- `[Link text](URL)`
- YouTube / Vimeo URLs on a line by themselves (auto-embedded)

Do not use HTML tags, definition lists, or tables — they are not reliably rendered.

---

## 4) Changelog Authoring Rules

### Order
Entries are in **descending version order** — newest version at the top.

### Always derive from `git log`

Never write `* Version bump` as the sole changelog entry. Before writing a new entry, run:

```
git log {previous_tag}..HEAD --format="%s%n%b"
```

Read every commit message and body, then summarise the **logical changes** — not the file names.

### One bullet per logical change

Group related file edits into a single bullet that describes the user- or developer-visible outcome.

- BAD: `* Updated includes/functions.php and includes/options.php`
- GOOD: `* Fixed cURL timeout handling: now shows error message when external API is unreachable`

### Verb prefix

Start each bullet with one of: `Added` / `Fixed` / `Changed` / `Removed` / `Updated` / `Improved`.

### What to include

- Functional changes visible to end users (new options, UI changes, bug fixes)
- Changes visible to developers (renamed functions, constants, option keys)
- Security fixes
- Breaking changes (option renames, removed shortcodes)

### What to omit

- Pure CI / GitHub Actions changes with no user or developer impact
- Whitespace / formatting fixes with no functional effect
- Internal test-only changes (unless they affect the public test API)

### File size management

WordPress.org flags readmes larger than 10 KB. When the file approaches that limit:

1. Create `changelog.txt` in the plugin root.
2. Move all entries older than the two most recent releases into `changelog.txt`.
3. Add a note at the bottom of `== Changelog ==`: `See changelog.txt for older entries.`

### Format reference

Follow [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) conventions for verb prefixes and entry structure — this is the format WordPress.org explicitly recommends.

---

## 5) Upgrade Notice Rules

- Maximum **150 characters** per version entry. The directory truncates longer notices.
- Only add an entry when users need to **take action** or must be **aware of a breaking change**
  (renamed options, removed shortcodes, template changes).
- Skip releases that contain only internal fixes, test updates, or CI changes.
- Phrasing: one sentence, imperative or informational, e.g.:
  `Option keys renamed from et_ to evangtermine_. Re-save settings after upgrading.`

---

## 6) Version Bump Checklist

Follow these steps in order every time the version number changes:

1. Update `Version:` in the plugin header inside `evangtermine.php`.
2. Update `Stable tag:` in `readme.txt` to the same value.
3. Add a new `= X.Y.Z =` entry at the top of `== Changelog ==` derived from `git log`.
4. Add or update the `== Upgrade Notice ==` entry if the release contains breaking changes.
5. Commit with message `chore: release X.Y.Z` and tag the commit `X.Y.Z`.

---

## 7) Validation

Before every release, validate `readme.txt` at:
`https://wordpress.org/plugins/developers/readme-validator/`

All fields must pass without errors. Common failures:
- `Stable tag` does not match the plugin PHP header version
- Short description exceeds 150 characters
- `Tested up to` contains a patch version or "WP" prefix
- Screenshot captions reference numbers with no corresponding SVN asset file

---

## 8) Plugin SVN Assets

*Source: [How Your Plugin Assets Work](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/) · [Plugin Headers (banners)](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/#plugin-headers) · [Plugin Icons](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/#plugin-icons) · [Readme developer reference](https://wordpress.org/plugins/developers/#readme)*

The top-level SVN `/assets/` directory (same level as `trunk/` and `tags/`) stores images
shown on the WordPress.org plugin page. This is **not** the plugin's own `assets/` folder inside `trunk/`.

All images are served via CDN; allow up to 6 hours for changes to propagate after an SVN commit.

### Plugin Banner

Shown at the top of the plugin page on WordPress.org.

- `banner-772x250.(jpg|png)` — normal banner, **772 × 250 px** *(required for any banner to appear)*
- `banner-1544x500.(jpg|png)` — retina (high-DPI) banner, **1544 × 500 px** *(only works if the normal banner also exists)*
- `banner-772x250-rtl.(jpg|png)` — RTL language variant of the normal banner
- Max file size: **4 MB** (CDN-cached; smaller is better).
- Do not use official product or brand logos as the sole banner design.

### Plugin Icon

Shown in WordPress.org search results and in the wp-admin Plugins list.

- `icon-128x128.(png|jpg|gif)` — normal icon, **128 × 128 px** *(required for a custom icon)*
- `icon-256x256.(png|jpg|gif)` — retina icon, **256 × 256 px**
- `icon.svg` — SVG icon; **must** be accompanied by a PNG fallback (`icon-128x128.png`)
- Max file size: **1 MB**.
- An auto-generated icon is used when no custom icon files are present.
- Do not use official brand or product logos.

### Screenshots

- Filename pattern: `screenshot-1.(png|jpg)`, `screenshot-2.(png|jpg)`, etc. — **lowercase only** (uppercase names are silently ignored).
- Each numbered file maps to the corresponding caption line inside `== Screenshots ==` in `readme.txt`. Keep the count in sync.
- Screenshots must be local files committed to SVN — external image URLs are not supported.
- Max file size: **10 MB** per screenshot.

### SVN MIME-type (if images download instead of display)

```
svn propset svn:mime-type image/png *.png
svn propset svn:mime-type image/jpeg *.jpg
```
