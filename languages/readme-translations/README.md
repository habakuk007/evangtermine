# Readme translations (translate.wordpress.org)

Translations of the `readme.txt` (plugin directory page). They are **not** part of the plugin: WordPress never loads a readme translation from a file, and this folder is excluded from the release (`.distignore`). It is a subfolder of `languages/`; `composer make:mo` does not descend into it, so these files are not compiled.

| File | Locale | Address |
|------|--------|---------|
| `readme-de_DE.po` | German (`de`) | informal (du) |
| `readme-de_DE_formal.po` | German (Formal) (`de/formal`) | formal (Sie) |
| `readme-de_DE-3.4.po` | German (`de`) | informal (du), strings of the readme of release 3.4 |
| `readme-de_DE_formal-3.4.po` | German (Formal) (`de/formal`) | formal (Sie), strings of the readme of release 3.4 |

Both files are exports of the project "Stable Readme (latest release)" of https://translate.wordpress.org/projects/wp-plugins/evangtermine/stable-readme/ , with the `msgstr` filled in. Keep the `msgid`s and the GlotPress header unchanged.

## Files for release 3.4

The plain files match the readme of the next release (3.5.0). The `*-3.4.po` files contain the `msgid`s of the readme of release 3.4 (`tags/3.4`, the current stable tag on WordPress.org), so they match the strings that translate.wordpress.org currently holds. Import these first. The `msgid`s were derived from `readme.txt` at tag `3.4` (the GlotPress export only lists translated strings), so a few multi-line paragraphs may not match; the import shows how many strings were taken over.

The matching files for the plugin strings (Development project) are in `languages/3.4/`. They are also excluded from the release. Delete the `*-3.4.po` files and `languages/3.4/` once 3.5.0 is released and the projects hold the new strings.

## Import

1. Log in at translate.wordpress.org with an account that may import: General Translation Editor (GTE) of the locale or Project Translation Editor (PTE) of the plugin. The plugin author can request PTE status in the Polyglots team.
2. Open the locale page of the project, for example `.../stable-readme/de/default/` or `.../stable-readme/de/formal/`, and use "Import Translations" (format: PO). Choose the status "current" only if you want to approve the strings directly.
3. The import is propagated to the dev-readme project (the Polyglots FAQ describes the sync of dev and stable). Readme strings reach the plugin page almost immediately; the 90 % rule for language packs applies to the plugin strings only.

## Update

After a release the readme strings change. Export the project again (Export, format PO, "all current" or "only matching the filter"), translate the new strings in the same way as in the existing file and replace the file here.
