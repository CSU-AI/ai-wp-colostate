# Home page concept (live as the front page)

**Status: live.** David published the redesigned AI @ CSU home page and made it the front page on production (post **937**, retitled "Home") on 2026-11-09, after plugin 0.4.0 was installed and the draft was checked. It was built on Local first (post 390, `/home-concept/`).

Production page CSS is in `home-concept.page-settings.json`. `{{POST_ID}}` is the production post ID. Scope the margin rule by `.elementor-<post id>`, not by `body.page-id-<id>`: the theme drops the page-id body class on the front page, which first left a 64px gap under the hero.

Section still to build: the approved tools cards, the latest article and workshop, and the footer links.

This folder exists so the page is not lost when only the Local database holds it.

## Files

- `home-concept.elementor.json`: the Elementor element tree (a hero shortcode container and the Responsible AI section). Page-specific URLs are tokens, listed below.
- `home-concept.page-settings.json`: page settings, including the page CSS (`custom_css`) that production needs too.

## Tokens to resolve at push time

| Token | Local value | Production target |
|---|---|---|
| `{{CHOOSER_URL}}` | `/tools/help-me-choose/` (Local post 52, under a placeholder `/tools/` parent) | The Help Me Choose page (sitemap `/tools/help-me-choose/`, production post 796) |
| `{{TEACHING_URL}}` | `/teaching/` | `/teaching/` |
| `{{TRAINING_URL}}` | `/training/` | `/training/` |
| `{{RESEARCH_URL}}` | `/research/` | `/research/` |
| `{{RESPONSIBLE_USE_URL}}` | `/resources/responsible-use/` | `/resources/responsible-use/` |
| `{{MENTAL_HEALTH_URL}}` | `/resources/responsible-use/mental-health/` | `/resources/responsible-use/mental-health/` |
| `{{WHITEBOARD_URL}}` | personal SharePoint whiteboard | Ask David. Kept out of Git on purpose (private URL). |

The CSU-GPT hero button link (`primary_url`) is the tool's own URL, also in the tree. The Responsible AI links to outside sources came from the shared mockup and need a link check.

## Done and still to do

1. Release plugin 0.4.0 (zip, upload as super admin, activate on the ai subsite only), then preview the draft under the real theme.
2. Hide the theme title banner for post 937 in wp-admin (the MCP cannot write `_wc_post_layout_head`; set Page Layout > head to "Hide the header").
3. Link-check the Responsible AI sources and confirm the whiteboard link works for NetID users.
4. Publish only in the home page release.

Done on production, 2026-11-09: the draft page (post 937, with its page CSS rewritten for that post ID) and the tool order below (applied to all 16 published tools with `update-post`; this changes the live tool chooser order immediately).

## Reference: how the push was done

1. Production must run plugin `aicsu-tool-chooser` 0.4.0 or later. The page uses `[aicsu_home_hero]` and `[aicsu_quick_match]`. Release it per the workflow (zip, upload as super admin, activate on the ai subsite only). Without it the shortcodes print as plain text.
2. Production MCP: enable writes for the session, authenticate through `/mcp` in an interactive session, run `detect-elementor-version`. Production is a draft push only.
3. Create the page as a **draft** (`create-page` with the tree). Keep the front page unchanged. Apply `home-concept.page-settings.json` with `update-page-settings`, replacing `page-id-390` in the CSS with the new post ID.
4. The hero container needs CSS classes `wc-container-wide wc-container-gap-none`; the Responsible AI container needs `wc-container-wide aicsu-rai` (already set in the tree).
5. Apply the tool order below, then check the Quick Match list on the draft.

## Tool directory order (applied on production 2026-11-09)

CSU-GPT is second; the tools below it shift down by one. Set each tool's Order (`menu_order`).

| Order | Tool | Production post ID |
|---|---|---|
| 1 | Microsoft Copilot Chat | 792 |
| 2 | CSU-GPT | 787 |
| 3 | Microsoft 365 Copilot Premium | 791 |
| 4 | Copilot Studio | 785 |
| 5 | Copilot Cowork | 784 |
| 6 | Microsoft Foundry (Azure AI Foundry) | 793 |
| 7 | Microsoft Teams Premium | 794 |
| 8 | Nebula One | 795 |
| 15 | Claude Enterprise (CSU Pilot) | 782 |
| 16 | CSU AI API Gateway | 786 |
| 17 | Google Gemini | 780 |
| 18 | Gemini Notebook | 788 |
| 19 | ChatGPT (OpenAI) | 783 |
| 20 | GitHub Copilot | 789 |
| 21 | Locally Hosted LLMs | 790 |
| 22 | Adobe Firefly | 781 |
