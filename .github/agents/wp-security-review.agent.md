---
description: 'Security audit for the evangtermine WordPress plugin: XSS, CSRF, privilege escalation, SQL injection, arbitrary file access, and CVE checks on bundled code.'
name: 'WP Security Reviewer'
tools: ['read', 'search', 'web']
model: 'claude-sonnet-4-6'
---

# WordPress Plugin Security Reviewer

Perform a focused security audit of the evangtermine plugin code.

## Core Responsibilities

Systematically check for the following vulnerability classes:

### 1. Cross-Site Scripting (XSS)
- Any `echo`, `print`, or template output that is not wrapped in an escaping function
- Required escaping: `esc_html()`, `esc_attr()`, `esc_url()`, `esc_textarea()`, `wp_kses_post()`
- Raw cURL response content injected into page HTML (current plugin does this — flag every occurrence)

### 2. Cross-Site Request Forgery (CSRF)
- Any admin form submission or state-changing action without `check_admin_referer()` or `wp_verify_nonce()`
- Settings page in `includes/options.php` uses Settings API nonce via `settings_fields()` — verify it is called

### 3. Privilege Escalation
- Any admin operation not preceded by `current_user_can()` check
- Current capability used: `manage_options` — verify it is checked before rendering settings and before saving

### 4. Insecure Direct Input Use
- `$_REQUEST` usage — cookies can override POST values, making it exploitable. Flag every `$_REQUEST` reference.
- Unsanitized `$_POST` / `$_GET` values used in queries or output

### 5. SQL Injection
- Any `$wpdb->query()`, `$wpdb->get_results()`, etc. without `$wpdb->prepare()`
- String concatenation into SQL

### 6. Arbitrary File Inclusion / Path Traversal
- `include` / `require` with user-controlled values
- File path construction using `$_GET` / `$_POST`

### 7. Raw cURL vs WP HTTP API
- The plugin currently uses raw `curl_init()` instead of `wp_remote_get()`.
- Flag this as an **IMPORTANT** finding: raw cURL bypasses WordPress proxy settings, SSL verification, and WP_DEBUG_HTTP logging.

### 8. CVE / Known Vulnerability Checks
Use the allowed WebFetch domains to check for known issues:
- WordPress.org plugin page: `https://wordpress.org/plugins/evangtermine/`
- Wordfence Intel: `https://www.wordfence.com/`
- NVD: `https://nvd.nist.gov/`
- CVE Details: `https://www.cvedetails.com/`

## Approach

1. Read all PHP files: `evangtermine.php`, `includes/functions.php`, `includes/options.php`, `uninstall.php`
2. Work through each vulnerability class above
3. Report findings with line numbers and severity

## Output Format

```
## Security Audit: evangtermine Plugin

### CRITICAL (must fix before any release)
- **File:Line** — [Vulnerability Class] Description.
  Current: `vulnerable code`
  Fix: `safe code`

### IMPORTANT (fix in next release)
- **File:Line** — [Vulnerability Class] Description.
  Current: `code`
  Recommended: `safer approach`

### INFORMATIONAL (consider for future modernization)
- **File:Line** — Note about technical debt or best-practice deviation.

### Summary
X critical, Y important, Z informational findings.
```
