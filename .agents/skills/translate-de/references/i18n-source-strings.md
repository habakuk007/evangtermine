# Writing Translatable Source Strings

Rules for the English strings and gettext calls in the plugin's PHP/JS code. Summary, in our own words, of [How to internationalize your plugin](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) (2026-11-12), the [Meta translations handbook](https://make.wordpress.org/meta/handbook/documentation/translations/) and the [JavaScript i18n section](https://developer.wordpress.org/apis/handbook/internationalization/#internationalizing-javascript) (read 2026-11-28). The page wins on differences. General background is in [plugin-author-guide.md](plugin-author-guide.md); PHP-wide rules are in `.agents/instructions/wordpress.instructions.md`.

## Functions

- Use the WordPress wrappers, not raw gettext: `__()`, `_e()`, `_x()`, `_ex()`, `_n()`, `_nx()`, `_n_noop()`, `_nx_noop()`, `translate_nooped_plural()`.
- Escaping variants for output: `esc_html__()`, `esc_html_e()`, `esc_html_x()`, `esc_attr__()`, `esc_attr_e()`, `esc_attr_x()`. Strings in HTML attributes must be escaped; escaping all output is good practice.
- Numbers and dates: `number_format_i18n()`, `date_i18n()`.

## Text domain

- Equals the plugin slug: `evangtermine` (lowercase, dashes, no spaces). Only one domain per plugin.
- Must be a **literal string** in every call, never a variable or constant (the extractor cannot read it).
- Header `Text Domain:` is optional since WordPress 4.6 when it equals the slug, but harmless (it translates the header while the plugin is inactive). `Domain Path:` is only needed outside WordPress.org.
- Core words such as "Settings" still need the plugin's own domain.
- `add-textdomain.php` (WordPress i18n tools) adds a domain to existing calls; it does not replace an existing one.

## Placeholders and sentences

- Never put variables into the translated string (`"Hello $name"`); use `sprintf()`/`printf()` with `%s`, `%d` and a single-quoted string.
- More than one placeholder: positional (`%1$s`, `%2$s`) so translators can reorder.
- Do not build sentences from translated fragments; translate whole sentences.
- Translator comment: the **last** PHP comment before the call, starting with `translators:`, describing placeholders or ambiguous text, e.g. `/* translators: 1: city name 2: ZIP code */`.

## Plurals and context

- `_n( $singular, $plural, $count, $domain )`. Many languages have more than two forms. Prefer strings that work for any number (`%s item`) over `One item`. Handle a special case for exactly 1 in code with a separate string.
- Delayed choice: `_n_noop()` / `_nx_noop()` plus `translate_nooped_plural()`.
- `_x()`/`_ex()` with a context for the same English word with different meanings (`noun`, `verb`).

## Style of source strings

- Full sentences, plain English, no slang, no abbreviations; assume translations can be twice as long.
- Split at paragraph level, not whole pages. No leading or trailing whitespace in a string.
- No HTML or unusual markup in strings unless unavoidable; no URLs unless language-specific.
- Same wording and punctuation across similar strings (`Posts:` vs `Posts`).
- Use `\n`, not `\r`. Never translate the empty string (add a context if needed).

## Loading translations

- Since WordPress 4.6 translations from translate.wordpress.org load automatically for WordPress.org plugins; `load_plugin_textdomain()` is **not required** when `Requires at least` is 4.6 or higher (this plugin deliberately omits it, see `evangtermine.php`).
- To load own `.mo` files anyway, use the `load_textdomain_mofile` filter.
- Do not call `load_plugin_textdomain()` several times or use several domains: one language pack per plugin.

## JavaScript (WordPress 5.0+)

- Register the script with the dependency `wp-i18n`, then `wp_set_script_translations( $handle, 'evangtermine' )`; a third argument (path) is only needed outside WordPress.org.
- In JS: `const { __, _x, _n, sprintf } = wp.i18n;` with the same literal text domain. Also available: `_nx`.
- Before 5.0: `wp_localize_script()`.

## Extraction

The translation system scans PHP files for gettext calls on import; `.pot`/`.po` files are not needed for it. Create the template with `composer make:pot`. Keep third-party PHP libraries in `/vendor` (skipped on import).
