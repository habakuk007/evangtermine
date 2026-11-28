# Polyglots Plugin and Theme Authors Guide: Summary

Summary, in our own words, of the Polyglots Plugin/Theme Authors Guide and the pages it links to (read 2026-11-28). When a page and this summary differ, the page wins. Dates are the "last modified" dates of the pages.

Sources:
- [Plugin/Theme Authors Guide](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/) (2026-11-05)
- [PTE request](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/pte-request/) (2023-07-08) and [gettext](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/gettext/) (2020-08-18)
- [How to translate](https://make.wordpress.org/polyglots/handbook/translating/how-to-translate/) (2026-01-07), [General expectations](https://make.wordpress.org/polyglots/handbook/translating/expectations/) (2026-07-29), [GlotPress on translate.wordpress.org](https://make.wordpress.org/polyglots/handbook/translating/glotpress-translate-wordpress-org/) (2023-03-07)
- [Roles and capabilities](https://make.wordpress.org/polyglots/handbook/about/roles-and-capabilities/) (2024-10-04), [FAQ](https://make.wordpress.org/polyglots/handbook/frequently-asked-questions/), [Meta handbook: translations](https://make.wordpress.org/meta/handbook/documentation/translations/) (2021, partly outdated)

Rules for writing the source strings are in [i18n-source-strings.md](i18n-source-strings.md).

## Roles

| Role | Can do |
|------|--------|
| Contributor | Suggest translations (status Waiting); import `.po`/`.mo` as Waiting |
| PTE (Project Translation Editor) | Approve/reject strings of one project in one locale; import as Current. Appointed by a GTE on request of the plugin author or of contributors |
| Super PTE | Same for a group of projects (e.g. all plugins) in one locale |
| GTE (General Translation Editor) | All projects of one locale. Promoted by the Locale Manager |
| Locale Manager | GTE duties, plus Rosetta site, appointing GTEs |
| CLPTE (Cross-Locale PTE) | Import and validate its own strings for one project in many locales; cannot review other people's strings; not combinable with a PTE on one account |
| Global Mentor | Triages blog requests, resolves conflicts, appoints CLPTEs |

## Routes to get translations

- **Machine translation without human review by a fluent speaker is never accepted.** It tends to ignore glossary and style and to break placeholders and HTML.
- **Community on translate.wordpress.org (GlotPress):** volunteers suggest, a GTE/PTE approves.
- **Own translators:** become PTE through a request post, or (professional vendor) CLPTE.
- Editors get no notification about new strings, so ask for review: locale Rosetta site, locale Slack, `#polyglots` on the global Slack ([chat](https://make.wordpress.org/chat/)), or a post on the [Polyglots blog](https://make.wordpress.org/polyglots/) tagged with the locale (e.g. `#de_DE`). Wait at least two weeks before escalating (locale team, then a global post, then the global mentors; inactive teams: [process](https://make.wordpress.org/polyglots/handbook/translating/teams/inactive-translations/)).

## PTE request (plugin author)

1. Contact the translators you want to nominate first (Slack DM, or `@username` in your support forum).
2. Log in to WordPress.org and create a post on make.wordpress.org/polyglots. **One post for all plugins/themes.**
3. Title: `PTE Request for [PLUGIN_NAME]`. Body: who you are, the plugin(s), the request to add the named WordPress.org users as translation editors.
4. Plugin directory URL(s), each as a list item (a leading minus avoids a link preview).
5. One line per locale: checkbox (`o`), the locale tag as plain text with `#` (e.g. `#de_DE`, from the "WP Locale" column of [the teams page](https://make.wordpress.org/polyglots/teams/)), then the `@usernames`.
6. End with an invitation to comment.

Notes: every GTE of a tagged locale is notified automatically. Variants such as `_informal` do not need their own request. Many locales validate requests themselves first: read the locale start guide and glossary/style guide ([list](https://make.wordpress.org/polyglots/handbook/translating/glossaries-and-style-guides-per-locale/)); have a decent number of translated strings; request only for languages you know. First posts of new accounts stay pending until a global mentor publishes them (usually within days). A translator can request PTE with the same kind of post, see [How to translate](https://make.wordpress.org/polyglots/handbook/translating/how-to-translate/).

**CLPTE (professional translators), all five criteria:** (1) vendor uses the community style guides and glossaries and links them publicly; (2) a new dedicated account with contact info ([examples](https://make.wordpress.org/polyglots/teams/clpte-list/)); (3) translation sources (vendor) disclosed; (4) organization has a Slack account on chat.wordpress.org and notifies the GTEs; (5) display name marks the role, e.g. `XYZ Translation Team (CLPTE)`. Request on the front page of the Polyglots blog, tag `clpte`.

## Locale rules: German (de_DE, de_DE_formal)

Sources (read 2026-11-28): [Erste Schritte](https://de.wordpress.org/team/handbook/polyglots-team/hilf-mit-bei-der-uebersetzung/erste-schritte/), [Wie die deutsche Community PTE-Rechte vergibt](https://de.wordpress.org/team/handbook/polyglots-team/hilf-mit-bei-der-uebersetzung/wie-die-deutsche-community-pte-rechte-vergibt/), [Style Guide](https://de.wordpress.org/team/handbook/polyglots-team/style-guide/). The English subpage "How the German Community handles PTE Requests" was not read.

- **Where to ask:** a post on the Polyglots blog (tag `#de_DE`) **or** the channel `#polyglots` of the German Slack (<https://de.wordpress.org/slack/>). Both are valid. When suggesting translations, post the permalink in `#polyglots` to speed up the review; no string count and no waiting time are given ("bitte habe etwas Geduld").
- **Who decides:** the German GTEs, under fixed rules. PTE rights cover **both branches** (`default` = du and `formal` = Sie): review, reject, overwrite, approve.
- **Plugin authors:** every author, German-speaking or not, gets PTE for the project on request, and may nominate a helper who translated the project. Translations must be checked by someone who speaks German; the team tells authors that translations are otherwise unchecked.
- **Order of authority:** glossary, then style guide, then the official spelling rules (grammis.ids-mannheim.de), then Duden. Glossary terms (dashed underline in GlotPress) are mandatory.
- **No machine translation** for de_DE. Reject your own faulty suggestions and resubmit corrected ones so reviewers see the fix.
- Translate both branches: switch with the dropdown or replace `locale/de/default/` by `locale/de/formal/` in the URL.
- Other German locales: `de_AT` has only a glossary; `de_CH` has its own [handbook](https://de-ch.wordpress.org/team/handbook/uebersetzungen/) and an informal glossary (not read).

## Locale rules: English (en_GB and others)

Sources (read 2026-11-28): [en_GB style and start guide](https://en-gb.wordpress.org/translations/), the en_GB team feed. The `en_ZA` start guide (<https://en-za.wordpress.org/get-involved/>) was not read. `en_AU`, `en_CA`, `en_NZ` list only a glossary, no PTE process.

- **en_GB process:** first submit many strings (the page says hundreds) as a normal contributor; strings are checked a couple of times a week and validated usually within a few days. Then request PTE for the project with a Polyglots blog post in the standard format. Recently seen high-quality submissions (ideally 100 % of the plugin) are approved as soon as possible. Authors may request PTE for their own plugin or nominate a helper. Discussion: Slack `#en-gb` (invite on the page).
- **en_GB style:** no ampersands, Oxford comma, 24-hour time; for variants use "Copy from original".
- **Source strings must be English.** The team rejects en_GB suggestions when the source strings are in another language; fix the source first, and translate from English, never from another language.
- For the other English locales use the generic request above and check the team's start guide first.

## Statuses on translate.wordpress.org

| Status | Meaning |
|--------|---------|
| Untranslated | No suggestion |
| Current (green) | Approved. **Only these end up in language packs** |
| Waiting (yellow) | Suggested, not yet approved |
| Fuzzy (orange) | Approved string whose source changed slightly; must be reviewed |
| Changes requested (blue) | Editor wants edits (typo, glossary, consistency); resubmit |
| Rejected (red), Old (purple) | Rejected / replaced by a newer approved string |
| Warning | Validation problem (placeholders, HTML tags, length); fix or an editor discards it |

Editing your own pending suggestion rejects the previous one. Discussion on a string is public; enable e-mails at <https://translate.wordpress.org/settings/>. Import (`.po`/`.mo`) via "Import Translations" at the bottom of a project page: any user as Waiting, PTE as Current; strings not in the source are ignored. Export via "Export".

## Thresholds and timing

- **First language pack of a plugin locale: at least 90 % Current in the Stable sub-project** (strings in code, **not** the readme). After the first pack the threshold no longer applies; every approved change triggers a new pack.
- The pack is generated about 30 minutes after a translation update; sites download it within hours.
- **Readme translations:** no threshold, deployed almost immediately after approval; validated separately.
- WordPress core (for comparison): 90 % front end, 75 % administration.
- Third-party PHP libraries in a `/vendor` folder are skipped on string import.

## After a language pack exists

- Shown on the Rosetta site of the locale; installed automatically.
- Since WordPress 4.6/4.7 community translations override bundled ones, **unless the text domain differs from the slug**.
- Bundled `.po`/`.mo` of a locale may then be removed from the release.
- Import and pack logs: Slack `#meta-language-packs` (search by slug). Status: <https://translate.wordpress.org/projects/wp-plugins/evangtermine/>.
- "This plugin is not properly prepared for localization" on the status page usually means a wrong text domain or non-literal domain arguments.

## Finding translators

Sticky support-forum topic, a call for volunteers in `readme.txt`, a dismissible admin notice (localized), link to [How to translate](https://make.wordpress.org/polyglots/handbook/translating/how-to-translate/). Job board: <https://jobs.wordpress.net/job_category/translation/>.

## General expectations for translators (short)

Links only to wordpress.org or the locale's official docs (commercial links can get a translator removed). Translate organically, not literally; match the polite-informal tone; no slang, no ideology, no emojis added. Never change behaviour (e.g. `target="_blank"`). Use glossary, style guide and the [Consistency tool](https://translate.wordpress.org/consistency/). Keep placeholders (`%s`, `%d`, `%1$s`, `%%` for a literal percent sign). Large low-quality submissions may be bulk-rejected.

## gettext background

WordPress uses GNU gettext, each message is translated on its own; `__()` returns, `_e()` echoes the translation, the original text is the fallback. `.po` files are edited with GlotPress or Poedit and compiled with `msgfmt -o file.mo file.po` (here: `composer make:mo`). A `.po` entry has a `#:` location comment, `msgid` (source) and `msgstr`; the header holds Project-Id-Version, PO-Revision-Date, Last-Translator, Language-Team, `Content-Type: text/plain; charset=UTF-8` and `Content-Transfer-Encoding: 8bit`. Use LF line endings and UTF-8 without BOM.

## What this means for the plugin

- Text domain is `evangtermine`, equal to the slug: required, never change it.
- `de_DE` (du) and `de_DE_formal` (Sie) are separate locales; the 90 % rule applies to each. One PTE request for `#de_DE` should cover the variants.
- Readme strings are separate projects (see `languages/readme-translations/README.md`) without threshold.
- Drafts from a machine or from Claude must be reviewed by a German speaker before import; use the checklist in `SKILL.md`.
- Once language packs exist for a locale, decide per release whether to drop the bundled `.po`/`.mo`.
- Other locales have their own style guides, see the [per-locale list](https://make.wordpress.org/polyglots/handbook/translating/glossaries-and-style-guides-per-locale/); the German ones are in `style-guide.md` and `glossary.md`.
