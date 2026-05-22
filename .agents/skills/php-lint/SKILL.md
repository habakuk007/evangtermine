---
name: php-lint
description: Run PHPCS and PHPCBF to lint and auto-fix PHP files against the WordPress Coding Standard. Use when asked to lint PHP, check coding standards, fix style issues, run phpcs, run phpcbf, check WPCS compliance, or validate PHP code quality.
---

# PHP Lint — PHPCS / PHPCBF for WordPress

Lint and auto-fix PHP files against the WordPress Coding Standard (WPCS).

## When to Use

- Before committing PHP changes
- Checking if code passes WordPress.org plugin review requirements
- Auto-fixing whitespace, alignment, and formatting issues
- Verifying a file is WPCS-compliant

## Prerequisites

PHPCS + WPCS must be installed. Check first:

```bash
phpcs --version
phpcs -i   # should list "WordPress" in installed standards
```

If missing, install globally:

```bash
# Via Composer (recommended — install in project)
composer require --dev \
  dealerdirect/phpcodesniffer-composer-installer \
  wp-coding-standards/wpcs \
  phpcompatibility/php-compatibility

# Or globally
composer global require wp-coding-standards/wpcs
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs/
```

## Workflows

### Lint a file or directory

```bash
# Single file
phpcs --standard=WordPress evangtermine.php

# All plugin PHP files
phpcs --standard=WordPress --extensions=php evangtermine.php includes/

# With progress indicator and summary
phpcs -p --standard=WordPress --report=summary evangtermine.php includes/

# Show source rule names (useful for understanding violations)
phpcs --standard=WordPress -s evangtermine.php
```

### Auto-fix fixable violations

```bash
# Fix a single file
phpcbf --standard=WordPress evangtermine.php

# Fix all plugin PHP files
phpcbf --standard=WordPress --extensions=php evangtermine.php includes/
```

PHPCBF fixes whitespace, alignment, bracket placement, and most formatting issues. It **cannot** fix logic issues like missing escaping or nonces — those require manual fixes.

### Lint only specific rules

```bash
# Check only security-related sniffs
phpcs --standard=WordPress --sniffs=WordPress.Security.EscapeOutput,WordPress.Security.NonceVerification evangtermine.php
```

### Generate a full HTML report

```bash
phpcs --standard=WordPress --report=full --report-file=phpcs-report.txt evangtermine.php includes/
```

## Common Violations in This Plugin

| Violation | File | Fix |
|-----------|------|-----|
| Missing `esc_html()` / `esc_attr()` | `options.php`, `evangtermine.php` | Wrap outputs in escaping functions |
| Direct use of `$_REQUEST` | `functions.php` | Use `$_POST` or `$_GET` explicitly |
| Raw `cURL` instead of `wp_remote_get()` | `functions.php` | Refactor to WP HTTP API (future work) |
| Missing nonce verification | `options.php` | Add `check_admin_referer()` |
| Unescaped `echo` | `options.php` | Use `esc_html()`, `esc_attr()`, `esc_url()` |

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `ERROR: the "WordPress" coding standard is not installed` | Run `phpcs -i` and install WPCS (see Prerequisites) |
| `phpcs: command not found` | Install via Composer or check PATH |
| Hundreds of violations | Run `phpcbf` first to fix auto-fixable ones, then review remainder |
| `Squiz.PHP.CommentedOutCode` false positives | Add `// phpcs:ignore` inline if intentional |
