# Security Policy

## Supported versions

Only the newest released version gets security fixes.

| Version | Supported |
|---------|-----------|
| 3.4 and newer | Yes |
| 3.3 and older | No. These versions have known security issues. Please update. |

## How to report a security problem

Please **do not** open a public issue, pull request or forum post for a security problem.

Report it in private through GitHub:

1. Open <https://github.com/habakuk007/evangtermine/security/advisories/new>
   (or the tab "Security" of the repository, then "Report a vulnerability").
2. Describe the problem. Helpful are:
   - the version of the plugin, of WordPress and of PHP,
   - which shortcode, widget or settings page is affected,
   - the steps to reproduce it,
   - what an attacker could do with it.

Only you and the maintainer can see the report. You need a GitHub account for it.

## What you can expect

This plugin is maintained by one person in their free time. The times below are goals, not guarantees.

| Step | Goal |
|------|------|
| First answer that the report has arrived | within 7 days |
| Assessment (confirmed or not, and how serious) | within 14 days |
| Fixed version | within 30 days for serious problems, within 90 days for others |

You are kept informed in the private report until the problem is closed.

## Disclosure policy

- Please keep the problem private until a fixed version is released, or until 90 days after your
  report have passed, whichever comes first. If more time is needed, the maintainer asks you for it.
- After the fixed version is released, the maintainer publishes a GitHub Security Advisory and
  names the fix in the changelog of `readme.txt`. A CVE number is requested if the problem
  qualifies for one.
- You are named as the reporter in the advisory, if you want that.
- Details that help to exploit the problem are not published before users had time to update.

## Scope

In scope is the code of this plugin. Problems in the external service
[www.evangelische-termine.de](https://www.evangelische-termine.de/) itself are not handled here;
please report them to the operator of that service. Problems in WordPress itself belong to the
[WordPress security team](https://wordpress.org/about/security/).
