---
name: php-lint
description: Run PHPCS and PHPCBF to lint and auto-fix PHP files against the WordPress Coding Standard. Use when asked to lint PHP, check coding standards, fix style issues, run phpcs, run phpcbf, check WPCS compliance, or validate PHP code quality.
---

# PHP Lint — PHPCS / PHPCBF for WordPress

Lint and auto-fix PHP files against the WordPress Coding Standard (WPCS) and check PHP compatibility (PHPCompatibilityWP, PHP 7.4+).

The configuration lives in the repository:

- `composer.json` — dev tooling only (PHPCS, WPCS, PHPCompatibilityWP) and the scripts `lint:php`, `lint:compat`, `fix:php`. Not part of the release.
- `phpcs.xml` — ruleset `WordPress` + `PHPCompatibilityWP`, `testVersion` `7.4-`, text domain `evangtermine`. Scans `evangtermine.php`, `autoloader.php` and `application/`; `assets/` and `vendor/` are excluded.

## When to Use

- Before committing PHP changes
- Checking if code passes WordPress.org plugin review requirements
- Auto-fixing whitespace, alignment, and formatting issues
- Verifying a file is WPCS-compliant
- Checking that code still runs on PHP 7.4 and newer

## Prerequisites

Install the dev tooling once (creates `vendor/`, which is git-ignored):

```bash
composer install
```

Check the installation:

```bash
vendor/bin/phpcs --version
vendor/bin/phpcs -i   # should list "WordPress" and "PHPCompatibilityWP"
```

Use the project's `vendor/bin/phpcs`, not a globally installed `phpcs` — a global install may lack PHPCompatibilityWP or have other versions.

## Workflows

### PHP compatibility (blocking in CI)

```bash
composer lint:compat
```

Runs only the PHPCompatibilityWP sniffs for PHP 7.4+. This must pass; CI fails otherwise (job `PHPCS`).

### Coding standard, whole plugin

```bash
# Full ruleset from phpcs.xml
composer lint:php

# Summary per file only
composer lint:php -- --report=summary
```

Not blocking in CI until issue #13 (existing violations) is done. New and changed code should still be clean.

### Lint a single file or directory

Paths on the command line replace the file list from `phpcs.xml`; the ruleset still applies.

```bash
vendor/bin/phpcs evangtermine.php
vendor/bin/phpcs application/models/
```

### Auto-fix fixable violations

```bash
# All plugin PHP files
composer fix:php

# A single file
vendor/bin/phpcbf evangtermine.php
```

PHPCBF fixes whitespace, alignment, bracket placement, and most formatting issues. It **cannot** fix logic issues like missing escaping or nonces — those require manual fixes. Review the diff afterwards and keep mass auto-fixes in their own commit.

### Lint only specific rules

```bash
# Check only security-related sniffs
vendor/bin/phpcs --sniffs=WordPress.Security.EscapeOutput,WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
```

### Write a report file

```bash
vendor/bin/phpcs --report=full --report-file=phpcs-report.txt
```

Do not commit the report file.

## Common Violations in This Plugin

Current state (issue #13): about 1900 errors, roughly 1600 of them auto-fixable. See the distribution with `vendor/bin/phpcs --report=source`.

| Violation | Sniff | Fix |
|-----------|-------|-----|
| Spaces instead of tabs, spacing inside parentheses, trailing whitespace | `Generic.WhiteSpace.*`, `PEAR.Functions.FunctionCallSignature.*`, `WordPress.WhiteSpace.*` | `composer fix:php` |
| Missing or incomplete doc comments | `Squiz.Commenting.*` | Write them by hand |
| camelCase variables, properties and methods; file names `class.<name>.php` | `WordPress.NamingConventions.*`, `WordPress.Files.FileName` | Do **not** rename public names or files: the autoloader, `EtWidget` and the "Do Not Change" list in `CLAUDE.md` depend on them. Exclude in `phpcs.xml` or ignore with a reason instead |
| Unescaped output | `WordPress.Security.EscapeOutput` | `esc_html()`, `esc_attr()`, `esc_url()`. The remote event HTML is returned unfiltered by design — ignore that spot with a `phpcs:ignore` comment that says why |
| Request data without nonce check or sanitizing | `WordPress.Security.NonceVerification`, `WordPress.Security.ValidatedSanitizedInput` | `wp_unslash()` + sanitize; a nonce where a form changes state. Public GET filters need an explained ignore |

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `ERROR: the "WordPress" coding standard is not installed` | Run `composer install`; use `vendor/bin/phpcs`, not a global `phpcs` |
| `ERROR: Referenced sniff "PHPCompatibilityWP" does not exist` | Same: `composer install`, then `vendor/bin/phpcs -i` |
| `phpcs: command not found` | Run `composer install` and call `vendor/bin/phpcs` or the Composer scripts |
| Hundreds of violations | Known state, see issue #13. Run `composer fix:php` for the auto-fixable ones, then review the remainder |
| `Squiz.PHP.CommentedOutCode` false positives | Add `// phpcs:ignore` with the sniff name inline if intentional |
