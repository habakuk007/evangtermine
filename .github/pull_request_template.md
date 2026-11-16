<!-- Security fix for a problem that is not public yet? Do not open a public pull request. See SECURITY.md. -->

## What and why

<!-- What does this pull request change, and why? Link the issue: "Closes #123". -->

## How it was tested

<!-- For example: pages from tests/e2e/blueprint.json opened in Playground, PHP versions, commands that were run. -->

## Checklist

- [ ] The pull request goes against `develop` (only urgent security fixes go against `master`).
- [ ] `composer lint:compat` passes.
- [ ] Nothing from the "Do Not Change" list in `CLAUDE.md` was renamed (option names, shortcode names, `EtWidget`, `.et_` CSS classes).
- [ ] `readme.txt` (changelog) is updated, if users notice the change.

## Security checklist

Tick each line, or write "n/a" behind it if the pull request does not touch that area.

- [ ] **Escaping:** every output is escaped late, with the right function (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`, `wp_json_encode()`).
- [ ] **Sanitizing:** every input (`$_GET`, `$_POST`, shortcode attributes, widget fields, options) is sanitized and validated before it is used or saved; values that go into the remote URL are URL-encoded.
- [ ] **Nonce:** every form and every action that changes something checks a nonce.
- [ ] **Capability:** every action that changes something checks `current_user_can()`.
- [ ] No new request to an external host, and no new file loaded from an external host in the visitor's browser (or it is described in `readme.txt` under "External service").
