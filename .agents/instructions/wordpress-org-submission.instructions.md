---
applyTo: 'readme.txt,changelog.txt,*.php'
description: 'Submitting a plugin to WordPress.org: the Add page, developer account, manual review, slug and naming, closure and re-opening a closed plugin, SVN usage, and readme/changelog rules.'
---

# Submitting & Re-opening a Plugin on WordPress.org

This plugin (Evangelische Termine) was **closed on WordPress.org on 2025-05-01** for a
security issue. The project goal is to fix the security issues (issues #1–#6), make it
PHP 8 compatible, and **get it re-opened**. This file describes the WordPress.org
submission and review process so a re-submission / re-opening goes right the first time.

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

## 5) Closure & re-opening (this plugin's path)
- A **closed** plugin's page shows as closed and no zips are generated; it cannot be downloaded or installed. The SVN stays accessible for forking. After 60 days the closure note reveals the broad reason (Guideline Violation / Security) without detail.
- Plugins are closed for **guideline violations, security issues, or by author request**; all committers are notified for active security / guideline issues.
- **Re-opening:** if it was closed for a **security** issue, **fix the issue, reply to the closure email, and the plugin is usually re-opened** (unless there are more security or severe guideline issues). For guideline closures it depends on severity and whether you are a repeat offender.
- **Every plugin must pass a current standards and security review to be restored — this is not optional.**
- **For this plugin:** it was closed 2025-05-01 for a security issue. The re-open path is:
  1. Fix the security issues (issues #1–#6) and make it PHP 8 compatible.
  2. Re-submit via the Add page / reply to the closure email (see §2–§3).
  3. The mechanical SVN push is in the `wp-release` skill; validate `readme.txt` at the [readme-validator](https://wordpress.org/plugins/developers/readme-validator/).
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
  - Validate at the [readme-validator](https://wordpress.org/plugins/developers/readme-validator/).

## 8) What does not apply to this plugin
- **Block-specific / Block Directory** rules ([Block Specific Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/block-specific-plugin-guidelines/)) apply only to *block-only* plugins. Evangelische Termine is a **shortcode + widget plugin with no blocks**, so those rules do **not** apply. They would apply only if the plugin is ever converted to a block plugin — in which case all of that document must be followed (on top of the Detailed Plugin Guidelines).

## 9) Community conduct
- When the agents or the bot post to **GitHub** (PR / issue comments) or email the **Plugins team** / use the **support forums**, follow the [WordPress Community Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/): be respectful and constructive, accept feedback, and never harass or retaliate against reporters. See the `gh-bot` skill for how AI-authored posts are made.

## Sources
- [Developer Information (landing)](https://de.wordpress.org/plugins/developers/) · [Add your Plugin](https://de.wordpress.org/plugins/developers/add/)
- [Plugin Developer FAQ](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/)
- [Detailed Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/) · [Block Specific Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/block-specific-plugin-guidelines/)
- [How Your readme.txt Works](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/) · [Plugin Assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
- [Reporting Plugin Security Issues](https://developer.wordpress.org/plugins/wordpress-org/plugin-security/reporting-plugin-security-issues/) · [Community Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/)
