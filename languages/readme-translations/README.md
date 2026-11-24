# Readme translations (translate.wordpress.org)

Translations of the `readme.txt` (plugin directory page). They are **not** part of the plugin: WordPress never loads a readme translation from a file, and this folder is excluded from the release (`.distignore`). It is a subfolder of `languages/`; `composer make:mo` does not descend into it, so these files are not compiled.

| File | Locale | Address |
|------|--------|---------|
| `readme-de_DE.po` | German (`de`) | informal (du) |
| `readme-de_DE_formal.po` | German (Formal) (`de/formal`) | formal (Sie) |

Both files are exports of the project "Stable Readme (latest release)" of https://translate.wordpress.org/projects/wp-plugins/evangtermine/stable-readme/ , with the `msgstr` filled in. Keep the `msgid`s and the GlotPress header unchanged.

`readme-de_DE.mo` is the compiled informal file (for an import in the MO format). It is generated: after changing `readme-de_DE.po`, copy the `.po` to an empty folder, compile it with `php vendor/wp-cli/wp-cli/php/boot-fs.php i18n make-mo <folder>` (the same command as `composer make:mo`, but on that folder) and copy the `.mo` back.

## Import

1. Log in at translate.wordpress.org with an account that may import: General Translation Editor (GTE) of the locale or Project Translation Editor (PTE) of the plugin. The plugin author can request PTE status in the Polyglots team.
2. Open the locale page of the project, for example `.../stable-readme/de/default/` or `.../stable-readme/de/formal/`, and use "Import Translations" (format: PO). Choose the status "current" only if you want to approve the strings directly.
3. The import is propagated to the dev-readme project (the Polyglots FAQ describes the sync of dev and stable). Readme strings reach the plugin page almost immediately; the 90 % rule for language packs applies to the plugin strings only.

## Update

After a release the readme strings change. Export the project again (Export, format PO, "all current" or "only matching the filter"), translate the new strings in the same way as in the existing file and replace the file here.
