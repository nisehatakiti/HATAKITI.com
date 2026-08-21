# HATAKITI.com

HATAKITI.com is a personal archive and knowledge site for thoughts, theatre, performance viewing, films, and related creative activity.

This repository is intentionally independent from the StageArt project. StageArt may be referenced from HATAKITI.com as one of the author's activities, but it is a separate product, system, and codebase.

## Initial direction

HATAKITI.com is not just a conventional blog. It combines:

- free-form daily thoughts and essays
- structured theatre viewing records
- structured film viewing records
- a growing theatre knowledge / textbook section
- searchable archives that connect records and articles

The first implementation target is WordPress. The repository stores the information architecture, content model, UX decisions, and implementation guidance that Claude can use to shape the final WordPress site.

See `docs/` for the current blueprint (`docs/05-Implementation.md` maps each blueprint requirement to the code and explains deployment).

## Implementation

`wp-content/themes/hatakiti` and `wp-content/plugins/hatakiti-core` are a working WordPress theme + companion plugin implementing this blueprint end to end: front page, article/record templates, 観劇記録 / 映画記録 custom post types with structured fields, tag-based theatre-essay organization, search/archives, and the draft-only `/wp-json/hatakiti/v1/draft` endpoint for the future ChatGPT integration. See `docs/05-Implementation.md` for setup steps.
