---
applyTo: 'readme.txt,changelog.txt,**/*.php'
description: 'Submitting a plugin to WordPress.org: the Add page, developer account, manual review, slug and naming, closure and re-opening a closed plugin, SVN usage, and readme/changelog rules.'
---

# Submitting & Re-opening a Plugin on WordPress.org

This plugin (Evangelische Termine) was **closed on WordPress.org on 2025-05-01** for a
security issue. The security issues (#1–#6) were fixed, the plugin was made PHP 8
compatible and it has been **re-opened** (version 3.4). This file keeps the WordPress.org
submission and review process as a reference for future releases, a new plugin or another closure.

The mechanical SVN push is in the `wp-release` skill; this file covers the *process*
around it (account, review, closure, re-opening, naming).

## 1) Before you submit

### Requirements (a quick checklist; the full rules are in `wordpress.instructions.md` §13)
- **GPL v2 or later** (or GPL-compatible) for every file — incl. bundled libraries, images, and fonts.
- **Human-readable source** — no obfuscation or name-mangling; if you minify, ship the un-minified source or link to it in `readme.txt`.
- **No artificial restrictions** — no trialware, license gates, paywalls, or quotas that lock features.
- **Must use the WordPress.org SVN** repository — the directory is a *hosting* service, not just a listing.
- **No external "powered by" / credit links** without explicit user opt-in (off by default).
- Follow the [Detailed Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/) (summarised in `wordpress.instructions.md` §13).

### Submission file
- A single **`.zip`, under 10 MB**, in the standard WordPress plugin format (installable via "Upload Plugin").
- **Production-ready**: complete, no errors, no stray logs/dev tools, and no unused test / docs / `node_modules` / `vendor` folders (that is what pushes plugins over 10 MB).
- No placeholders.

### What WordPress.org will not accept
- Plugins with **no meaningful purpose / no practical functionality**.
- Anything that facilitates **illegal or harmful activity** (black-hat SEO, content spinners, fake-content generators, scrapers, hate-related plugins).
- **Arbitrary code execution**: PHP/JS editors, file managers, or AI tools that generate code to be *executed* on the site. (Escaped HTML output is fine; unescaped / eval'd code is not.)
- Downloading or executing code from external sources, or acting only as a tool to install other plugins / themes.
- **Framework / library / boilerplate** plugins, or plugins that need other plugins / themes installed to work.
- Plugins that merely **duplicate** existing work or WordPress core, or that are just copies of another plugin.
- Plugins whose functionality is already extensively represented, unless they offer clear, meaningful differentiation.

## 2) Submitting

1. **Register / log in** to a [WordPress.org account](https://login.wordpress.org/). Submitting as an official organisation requires the official organisation account, not an individual one.
2. Go to the **Add your Plugin** page and upload the `.zip`: https://de.wordpress.org/plugins/developers/add/
3. You get an **automated confirmation email** and the plugin enters the **review queue**.
   - If no security / compatibility / guideline / presentation issues are found, it is approved.
   - If issues are found, you get an email (subject: `[WordPress Plugin Directory] Review in Progress: {Plugin Name}`). **Check spam/junk** and **allow-list `plugins@wordpress.org`**.
4. The **Plugins team** is at `plugins@wordpress.org` (replies within 7 business days). **Reply to the existing review thread** — do not start a new thread for the same case. Do not contact them for account issues.

## 3) Review timing & resubmission
- A small, correct plugin is typically approved within **~14 days** of initial review.
- The team aims to reply within **~10 business days** (the team is 100% volunteers; a couple of days over a weekend/holiday is not a red flag).
- A review that is **not complete within 3 months is rejected** to keep the queue manageable (4000+ plugins are mid-review at any time).
- **Only resubmit** if you were *rejected after 3 months* (find the review email, fix the issues, resubmit — even years later is fine). For **any other** rejection, **reply to the email**; do not resubmit.
- Submit **one plugin at a time** (up to 10 only with 1M+ active installations); do **not** open multiple accounts to bypass this.
- No queue-jumping, except for a genuine security / legal issue — then email `plugins@wordpress.org` and explain.

## 4) Slug & naming
- The **slug** is derived from the **`Plugin Name`** header (e.g. `Boaty McBoatface` → `boaty-mcboatface`) and drives the public URL, the install-folder name, the SVN / trac address, and the **text domain**.
- The **slug cannot be changed after approval** — choose carefully. The **display name** is changeable; the slug may be changed **once**, before review begins.
- Naming restrictions: no vulgarity; no "WordPress" or "Plugin" in the slug (except extreme cases); no version numbers in the slug; only English letters and Arabic numerals; and **no trademarked term / project name at the start of the name** (unless you are the official owner) — never try to "work around" it (e.g. `WuuCommerce`).
- A name that already exists (on WordPress.org, or *outside* it with a significant user base) will be rejected — the update API matches on the folder name, so a collision can hijack updates of the other plugin.

## 5) Closure & re-opening (this plugin's path, done)
- A **closed** plugin's page shows as closed and no zips are generated; it cannot be downloaded or installed. The SVN stays accessible for forking. After 60 days the closure note reveals the broad reason (Guideline Violation / Security) without detail.
- Plugins are closed for **guideline violations, security issues, or by author request**; all committers are notified for active security / guideline issues.
- **Re-opening:** if it was closed for a **security** issue, **fix the issue, reply to the closure email, and the plugin is usually re-opened** (unless there are more security or severe guideline issues). For guideline closures it depends on severity and whether you are a repeat offender.
- Expect the plugin to be re-reviewed against current standards before it is restored (see "Unverified" note below).
- **For this plugin:** it was closed 2025-05-01 for a security issue and has been re-opened since. This was the path (kept as a reference):
  1. Fix the security issues (issues #1–#6), make it PHP 8 compatible and work through §8 (common review rejections) and §9 (automated security review).
  2. **Reply to the closure thread** with `plugins@wordpress.org`, say what was fixed and ask for a re-review / re-opening. Do **not** use the Add page for this: the slug `evangtermine` is still taken by the closed plugin (an upload would fail with "name already exists"), and it must stay the same because the slug cannot be renamed.
  3. If the team asks for a fresh upload, they will say so in the thread — only then use the Add page, and follow the thread's instructions for the slug.
  4. Only after the team re-opens it: push to SVN (see the `wp-release` skill) and validate `readme.txt` at the [readme-validator](https://wordpress.org/plugins/developers/readme-validator/).
- **Unverified against the live pages** (the FAQ states "security closures can reopen after fixes", but not the exact procedure): the "reply to the closure thread" route and the claim that a current standards/security review is always required. Re-check the FAQ section "Closed Plugins" and the closure email before acting.
- Note: closure is permanent and there is no "temporary" closure — fix the bug and keep it live, or update the readme to say it is unsupported.

## 6) SVN repository (after approval)
- Put code in **`trunk/`** (not a subdirectory — that breaks the zip generator).
- Release by copying `trunk/` to **`tags/X.Y.Z`** (tags are **version numbers only**; Semantic Versioning recommended).
- Keep only **1–2** old tags — SVN is a release repo, not a version-control repo (use git / GitHub for history).
- Images (banner, icon, screenshots) go in the root-level **`assets/`** (sibling of `trunk/` and `tags/`), *not* the plugin's own `assets/`.
- **No zips / compressed files**, no SVN externals (they do not make it into the downloadable zip). Minified JS is fine if the un-minified source is included or linked.
- **Pushing to SVN makes the plugin live immediately** — there is no "off" switch except closing. Do not push until you are ready to go live.
- The directory reflects changes within minutes, but allow up to **6 hours** (and 6–14 days for search indexing).

## 7) readme.txt & changelog on WordPress.org
- Full rules are in `readme-txt.instructions.md`. Key points:
  - Bump the version for **every code** release; **not** for readme / icon / header-only changes (still update `trunk/` and the `tags/` folder).
  - Keep the **current major version + one back** in `== Changelog ==`; move older entries to `changelog.txt` (WordPress.org recommends [Keep a Changelog](https://keepachangelog.com/)).
  - `Tested up to:` ≤ the current release candidate (never a non-released version like a future RC).
  - Keep `readme.txt` under about 10 KB (move old changelog entries to `changelog.txt`), tags ≤ 5, short description < 150 characters without markup.
  - Open point: the main plugin file header has no `Plugin URI`, `Text Domain` or `License URI`. Add `Text Domain: evangtermine` (matches the slug) and `License URI`; a unique `Plugin URI` is recommended.
  - Validate at the [readme-validator](https://wordpress.org/plugins/developers/readme-validator/).

## 8) Common review rejections (checklist before every (re)submission)
Source: [Common Issues](https://developer.wordpress.org/plugins/wordpress-org/common-issues/). The reviewers' most frequent grounds are unescaped output, unsanitized input and form handling without a nonce.
- **Input/output:** sanitize and validate input, escape late on output. Never process whole `$_POST`/`$_GET`/`$_REQUEST`; read single keys. `esc_url_raw()` sanitizes, `esc_url()` escapes. `__()`/`_e()` do **not** escape — use `esc_html__()`, `esc_attr__()`, `esc_html_e()`. Use `wp_kses_post()`/`wp_kses()` for HTML, `wp_json_encode()` for JSON, `$wpdb->prepare()` for SQL.
- **Direct access:** `if ( ! defined( 'ABSPATH' ) ) { exit; }` in every PHP file that executes code (check `application/**`, `uninstall.php`).
- **Prefixes:** prefix functions, classes, constants, options, globals. **Two- or three-letter prefixes are not accepted**, and `wp_`, `__` and a bare `_` are forbidden. Open point for this plugin: the internal symbols are now prefixed `Evangtermine_*` / `evangtermine_*` / `ET_BASE_PATH` / `ET_DEBUG`; what remains at risk are the legacy names `ET_*` (two letters), `EtWidget` and the `et_*` shortcodes — this collides with the "Do Not Change" list (option keys, `.et_` CSS classes, `EtWidget`, shortcode names). Decide with the maintainer (e.g. rename internal-only symbols, keep the stored/public names) and ask the Plugins team if unsure; do not rename stored names unilaterally.
- **i18n:** text, context and domain must be literal strings; the text domain must equal the slug (`evangtermine`).
- **Assets:** enqueue with `wp_enqueue_script()`/`wp_enqueue_style()` (and the inline variants); no remote JS/CSS/images (exceptions: GPL-compatible font CDNs, service APIs, oEmbed). Do not ship libraries WordPress already bundles (jQuery, …). Open point: bundled jQuery under `assets/`, and the external image host used by the remote HTML (see the external-resources memory) — be ready to justify or remove them.
- **Plugin standards:** main file named after the slug (`evangtermine.php`) with complete headers; `readme.txt` complete and valid; GPL-compatible license in header **and** readme, identical; `Stable tag` equals `Version`; no update checkers or phone-home code; no dev folders (tests, demos, node/grunt tooling) in the release — keep `composer.json`; source or a public link for minified files.
- **External services:** document each one in `readme.txt` (what it does, link, terms and privacy links). Done for evangelische-termine.de; keep it current when the host option changes behaviour.
- **Test with `WP_DEBUG` on** and run `wp plugin check evangtermine` (CI: `plugin-check.yml`). WP-CLI runs only the static checks by default; add `--require=./wp-content/plugins/plugin-check/cli.php` to include the runtime checks. To silence a known false positive use `--ignore-codes=<code>` or a `.pcpignore` file (Plugin Check 2.2.0+), or report it at github.com/WordPress/plugin-check. Passing the "Plugin repo" category is what the review expects; the other categories are best practice. "Tools > Plugin Check SVN" scans the SVN repository for unexpected files after a release.
- **Developer account:** enable two-factor authentication on your wordpress.org account and use a unique password for SVN (the plugin team's security advice for plugin developers; see the [Security Handbook](https://developer.wordpress.org/apis/security/) and the learn.wordpress.org tutorial "Introduction to securely developing plugins").

## 9) Automated security review and compliance claims
- **Automated security review (since June 2026, [page](https://developer.wordpress.org/plugins/wordpress-org/automated-security-review/)):** every release has a cooldown in which AI models and Jetpack Scan analyse the diff. If the highest risk score reaches the blocking threshold, that version is **withheld from the update API** and all committers get an email with file and line. Only that version is affected; older versions keep serving. Fix the issue and publish a **new** version (fixing is usually faster than appealing; false positives: reply to the email). Consequence for releases: run `/wp-security-review` before tagging, and do not assume a pushed tag has reached users.
- **Compliance disclaimers ([page](https://developer.wordpress.org/plugins/wordpress-org/compliance-disclaimers/), guideline 9):** never claim that the plugin creates, provides or guarantees legal compliance (GDPR, ADA, cookie law). Say it "helps" or "assists". This applies to `readme.txt`, descriptions, assets and the plugin name.

## 10) Plugin assets (banner, icon, screenshots)
Source: [Plugin Assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/).
- They live in the **root-level `assets/` folder of the SVN repo** (next to `trunk/` and `tags/`), never in `trunk/assets` or `tags/x/assets`. The plugin's own `assets/` folder is the plugin's CSS/JS and is something else.
- Banner `banner-772x250.(png|jpg)` (+ `banner-1544x500` for retina, only in addition to the small one); icon `icon-128x128` / `icon-256x256` or `icon.svg` (with a PNG fallback); screenshots `screenshot-1.png` … in lower case, matching the `== Screenshots ==` list. Limits: banner 4 MB, icon 1 MB, screenshot 10 MB. No official logos you do not own.
- If images download instead of displaying: `svn propset svn:mime-type image/png *.png` (only affects new files unless re-run).
- Changes appear after minutes, up to about 6 hours.

## 11) What does not apply to this plugin
- **Block-specific / Block Directory** rules ([Block Specific Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/block-specific-plugin-guidelines/)) apply only to *block-only* plugins. Evangelische Termine is a **shortcode + widget plugin with no blocks**, so those rules do **not** apply. They would apply only if the plugin is ever converted to a block plugin — in which case all of that document must be followed (on top of the Detailed Plugin Guidelines).

## 12) Community conduct
- When the agents or the bot post to **GitHub** (PR / issue comments) or email the **Plugins team** / use the **support forums**, follow the [WordPress Community Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/): be respectful and constructive, accept feedback, and never harass or retaliate against reporters. See the `gh-bot` skill for how AI-authored posts are made.

## Sources
- [Developer Information (landing)](https://de.wordpress.org/plugins/developers/) · [Add your Plugin](https://de.wordpress.org/plugins/developers/add/)
- [Plugin Developer FAQ](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/)
- [Detailed Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/) · [Block Specific Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/block-specific-plugin-guidelines/)
- [How Your readme.txt Works](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/) · [Plugin Assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
- [Reporting Plugin Security Issues](https://developer.wordpress.org/plugins/wordpress-org/plugin-security/reporting-plugin-security-issues/) · [Community Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/)
