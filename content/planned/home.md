---
title: "AI @ CSU Fort Collins"
slug: "home"
status: drafting
priority: P0
parent:
source_urls:
  - https://ai.colostate.edu/
redirect_from: []
template: landing
owner:
partners: []
last_reviewed:
local_post_id:
production_post_id:
---

# AI @ CSU Fort Collins

## Purpose

Help visitors find an approved tool, understand safe use, and reach role-specific resources quickly.

## Required content

- Actionable hero with quick access to CSU-GPT and RamGPT when available
- Audience paths for faculty, staff, researchers, and students
- Approved tools, training, and news shortcuts
- Latest article and upcoming workshop
- Links to CSU System AI, Data Governance, and the Division of IT

## Editorial decisions needed

- Confirm which tool receives the primary hero action.
- Confirm the source for live workshop and news content.

## Build status

**Live.** The redesigned home page is published as the front page on production (post 937, published by David on 2026-11-09). Local has the build at `/home-concept/` (post 390). Details and the page CSS are in `wordpress/home-concept/README.md`.

Contents so far:

- Hero with the Quick Match filter (`[aicsu_home_hero]` and `[aicsu_quick_match]`, plugin 0.4.0). Level 4 data is deliberately not offered, because no tool is approved for it.
- Responsible AI section: seven reflection prompts as cards on brand Tan, built from native Elementor widgets.

Still to build: the approved tools cards, the latest article and upcoming workshop, and the footer links. Open items are listed in the pull request that added this concept.
