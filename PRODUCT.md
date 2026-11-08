# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Colorado State University Fort Collins community, all four groups confirmed:

- Faculty and instructors: teach with AI, write syllabus language, design assignments.
- Staff and administrators: pick approved tools, learn data rules, get IT support.
- Researchers: grants, IRB, research data security with AI.
- Students: responsible use, tools they can access, wellbeing resources.

Most arrive with a task ("which tool may I use with this data?"), not to browse.

## Product Purpose

`ai.colostate.edu` is the Fort Collins campus home for AI: approved tools, teaching, research, training, IT support, news, and local governance. It is a full rebuild of the existing site. Success: a visitor finds the right tool, rule, or guidance quickly and correctly.

## Positioning

Campus-specific one-stop. Fort Collins tools, teaching, research, training, and support in one place. System-level policy, legal guidance, and risk methodology are linked to authoritative sites (`ai.csusystem.edu`, `datagov.csusystem.edu`), not duplicated.

## Operating Context

- WordPress subsite on a shared multisite network (hundreds of sites), CSU Theme, Elementor (atomic elements), ACF Pro. Built Local-first (Local by Flywheel), pushed to production in section releases.
- Content source of truth is Markdown in `content/planned/`; `sitemap.yml` defines navigation. Primary nav: Tools, Teaching, Research, Training, IT Support, News, Resources, About.
- Separate AI tool chooser workstream (`docs/`, `wireframe.html`, `aicsu-tool-chooser` plugin).

## Capabilities and Constraints

- Cannot edit the CSU theme or change the subsite theme. Production MCP account lacks `unfiltered_html`, so avoid HTML widgets and custom code.
- Prefer native Elementor controls and global tokens over custom CSS/JS.
- Theme quirks (title banner, button styling, paragraph color) are in `wordpress/build-notes/page-building.md`.
- Open decisions (CONTENT-PLAN.md): relationship among CSU-GPT, RamGPT, and nebulaONE; authoritative approved-tool list and data classifications; approval process owners; governance bodies and contacts.

## Brand Commitments

Current CSU brand ("Find Your Energy" palette, brand.colostate.edu/color). Only listed accessible color pairings are approved. Title Case headings and buttons, sentence case body. No em dashes in copy. Write directly; name pages by what visitors need to do.

## Evidence on Hand

Legacy site snapshot in `archive/` and `content/legacy/`; screenshots in `design/screenshots`. No testimonials, usage numbers, or case studies established; do not fabricate them.

## Product Principles

- Tools first: the first navigation task is finding an approved tool.
- Campus-specific, system-linked: own local guidance, link authority elsewhere.
- Safety stated in words: approval status and data limits never rely on color or script alone.
- Name pages by what the visitor needs to do.
- Content is Markdown first; WordPress renders it.

## Accessibility & Inclusion

WCAG 2.1 AA or better. Keyboard access, visible focus, sufficient contrast, useful alt text, non-color status cues, responsive on desktop, tablet, mobile.
