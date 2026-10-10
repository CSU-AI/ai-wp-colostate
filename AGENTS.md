# AI @ CSU rebuild rules

## Purpose

This repository is the working source for the complete rebuild of `ai.colostate.edu`. It contains a snapshot of the old site, editable replacement content, the proposed sitemap, design guidance, the AI tool chooser workstream, and WordPress transfer artifacts.

## Source of truth

- Editable page copy lives in `content/planned/` as Markdown.
- `sitemap.yml` defines the proposed navigation and points to planned content files. It does not duplicate page copy.
- `content/legacy/` and `archive/` are historical reference material. Do not edit them to draft the new site.
- WordPress is a rendering and review target, not the only copy of the content.
- Record local and production WordPress IDs in the planned page's front matter.

## WordPress workflow

- The full process is in `wordpress/build-notes/production-workflow.md`.
- All MCP calls target the Local by Flywheel site (`emcp-multisite-local-ai`) unless the user explicitly states otherwise.
- Create new pages as local Elementor drafts.
- Do not replace, publish, delete, or change the configured homepage without explicit approval.
- Never point a write-enabled MCP connection at production without explicit instruction in that session. The production connection is `emcp-prod-ai`; it is read-only unless the user enables writes for that session.
- Production pages are created as drafts. Publishing happens in section releases, one top-level sitemap section at a time, with explicit approval.
- Before updating an existing production page, compare its modified time with `production_modified` in the front matter. If they differ, stop and report the drift.
- Record `production_post_id` and `production_modified` after every push. Record media mappings in `wordpress/media-map.csv`.
- Recheck internal URLs, media URLs, forms, dynamic content, and redirects after each release.

## Shared multisite network

`ai.colostate.edu` is one subsite on a network shared by hundreds of sites.

- Never edit, upload, update, or write files into `csu-theme`, including ACF JSON.
- Never network-activate plugins or change network settings. Activate `aicsu-tool-chooser` on the ai subsite only.
- Never change the ai subsite's active theme.
- Never use super admin credentials for MCP or scripts.
- Schema for the tool directory lives in `wordpress/tool-chooser/acf-json/`. Change it in Local ACF, then run `export-acf-json.php`. Do not sync it into the production database.

## Elementor

- New site pages use native Elementor containers and widgets. Atomic (v4) elements are enabled on Local and production.
- Avoid HTML widgets, embeds, and custom code in pushed pages. The production MCP account lacks `unfiltered_html` on multisite, so that markup is stripped; flag it for manual entry instead.
- Prefer Elementor controls, global tokens, reusable templates, and Theme Builder over custom CSS or JavaScript.
- Add custom code only when native Elementor and installed plugins cannot meet a confirmed requirement.
- Keep content structure semantic: one H1, ordered heading levels, meaningful link text, and real lists for list content.
- Add responsive behavior for desktop, tablet, and mobile.
- Render and review pages visually. Do not judge layout from the element tree alone.

## Accessibility and CSU brand

- Target WCAG 2.1 AA or better.
- Preserve keyboard access, visible focus, sufficient contrast, useful alternative text, and non-color status cues.
- Use current CSU brand colors and only approved accessible color pairings.
- Typography is normally inherited from the CSU theme or Elementor global styles.
- Use sentence case in body copy and Title Case for headings and buttons when required by the surrounding CSU site.

## Content

- Write directly and name pages by what visitors need to do.
- Avoid filler and unsupported claims.
- Do not use em dashes in final copy.
- Preserve source URLs in legacy files and cite authoritative sources in planned content where policy or compliance claims are made.
- Treat `status: approved` as editorial approval to build locally, not permission to publish.
- Allowed lifecycle values: `captured`, `drafting`, `content-review`, `approved`, `built-local`, `qa`, `ready-production`, `published`.

## AI tool chooser

- The chooser is a separate workstream documented in `docs/tool-finder-setup.md`.
- Preserve `docs/aicsu-design-system.css`, `docs/aicsu-tool-finder.js`, and `wireframe.html` while that work continues.
- Do not make the chooser architecture a requirement for ordinary content pages.
- Revisit its implementation after the main sitemap and content priorities are established.

## Repository safety

- The repository is private. Production post IDs, release logs, and media maps may be committed. Never commit credentials, private URLs, personal-vault paths, protected data, or WordPress authentication material.
- Preserve unrelated local changes.
- Do not edit CSU theme files.
