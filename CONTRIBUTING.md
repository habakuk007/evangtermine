# Contributing

Thank you for helping with this plugin. This file explains how to work on it.

Found a security problem? Do not open an issue or a pull request. Read [SECURITY.md](SECURITY.md).

## Branches

| Branch | Purpose |
|--------|---------|
| `master` | The released state (what is on WordPress.org). Version tags are created here. |
| `develop` | Integration branch for the next version. |

Both branches are protected: changes come in only through a pull request, and the CI checks must pass.

## Workflow

1. Open an issue first, or pick an existing one.
2. Create a short-lived branch from `develop`. Name it after the issue:
   `fix/<issue>-<topic>`, `feat/<issue>-<topic>` or `chore/<issue>-<topic>`,
   for example `fix/33-external-hosts`.
3. Commit your changes. Start the commit message with `fix:`, `feat:`, `chore:` or `docs:` and
   add the issue number, for example `fix: escape the widget title (#12)`.
4. Push the branch and open a pull request **against `develop`**. Fill in the pull request template.
5. The maintainer reviews and merges. Please do not merge yourself.

Releases are made by the maintainer: a pull request from `develop` to `master`, then a version tag.
Urgent security fixes may go to `master` first and are merged back into `develop`.

## Commit identity

Commits of the maintainer use this identity. It is set in the local git config of the repository,
not in the global git config:

```bash
git config user.name "habakuk007"
```

```bash
git config user.email "2253878+habakuk007@users.noreply.github.com"
```

Other contributors use their own name and e-mail address.

## Commands

There is no build step and no npm for the plugin itself. Composer is used for the development
tools only (PHPCS). The plugin has no runtime dependencies.

Install the development tools (once):

```bash
composer install
```

Check the PHP syntax of all files:

```bash
find . -name "*.php" -not -path "./.git/*" -not -path "./vendor/*" -not -path "./assets/*" -exec php -l {} \;
```

Check the compatibility with PHP 7.4 and newer (must pass in CI):

```bash
composer lint:compat
```

Check the WordPress Coding Standards (ruleset in `phpcs.xml`; does not block CI yet):

```bash
composer lint:php
```

Fix what can be fixed automatically:

```bash
composer fix:php
```

Create the translation template (needs WP-CLI):

```bash
wp i18n make-pot . languages/evangtermine.pot
```

Run the WordPress.org plugin check (needs WP-CLI and the plugin "Plugin Check"):

```bash
wp plugin check evangtermine
```

## Testing locally

The easiest way is [WordPress Playground](https://wordpress.github.io/wordpress-playground/).
It needs Node.js and nothing else: no web server, no database.

Run this in the root folder of the repository:

```bash
npx @wp-playground/cli@latest server --auto-mount --login --blueprint=tests/e2e/blueprint.json
```

This starts a WordPress site with the plugin from your working folder. The blueprint
[tests/e2e/blueprint.json](tests/e2e/blueprint.json) switches the debug output on, logs you in as
administrator, and creates one page for each shortcode (`/et-veranstalter/`, `/et-teaser/`,
`/et-panels/`, `/et-minical/`) and the widget in a sidebar. The site fetches real events from
`www.evangelische-termine.de`, so you need an internet connection.

The automated end-to-end tests and their commands are described in
[tests/e2e/README.md](tests/e2e/README.md).

Before you open a pull request, please check by hand:

- the pages of the shortcodes you touched, including filter, search, pagination and detail view
  for `[et_veranstalter]`,
- that the PHP error log and the browser console show no new warnings.

## Rules for the code

- Classes are CamelCase, one class per file, named `class.<lowercase name>.php`.
- Constants start with `ET_`. The text domain is `evangtermine`.
- Security: sanitize every input, escape every output, check a nonce and the capability for every
  action that changes something. The pull request template has a short checklist.
- The plugin must run on PHP 7.4 and newer.

Do **not** change these, because existing sites depend on them:

- the option names `vid`, `region`, `until`, `css`, `encoding`, `etprotocol`, `ethost`,
- the shortcode names `et_veranstalter`, `et_teaser`, `et_panels`, `et_minical`,
- the class name `EtWidget`,
- CSS class names that start with `et_`,
- the constants `ET_DEFAULT_HOST` and `ET_DEFAULT_PROTOCOL`.

More details about the architecture are in [CLAUDE.md](CLAUDE.md).

## License

By contributing you agree that your contribution is licensed under the GPL, version 2 or later
(see [LICENSE](LICENSE)).
