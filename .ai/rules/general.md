---
paths:
  - '**'
---

# General

## This repo is public: never commit private case details
The repo has to be public for GitHub Pages to serve it, so anything committed is published. The family asked for no private phone numbers on the site; only official lines (Dutch police, Dutch MFA, Turkish 112) may be published. Never commit home addresses, bank details, the parents' or Oscar's own numbers, the ministry's internal case mailbox, medical history, or the family's theory of what happened.

This applies to tests and comments too, not just rendered output. The privacy guards in tests/Feature/SiteTest.php are deliberately written as patterns and config allowlists rather than literal strings, because a test that names what it protects publishes it.

Source material for the case lives outside the repo. Keep it there.
