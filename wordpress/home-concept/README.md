# Home page concept (not live)

**Status: not live.** This is the redesigned AI @ CSU home page, built on Local only (post 390, `/home-concept/`). It is not on production. The configured front page is unchanged, and the home page swap is its own release (see `wordpress/build-notes/production-workflow.md`).

This folder exists so the page is not lost when only the Local database holds it.

## Files

- `home-concept.elementor.json`: the Elementor element tree (a hero shortcode container and the Responsible AI section). Page-specific URLs are tokens, listed below.
- `home-concept.page-settings.json`: page settings, including the page CSS (`custom_css`) that production needs too.

## Tokens to resolve at push time

| Token | Local value | Production target |
|---|---|---|
| `{{CHOOSER_URL}}` | `/ai-tool-chooser-poc/` | The Help Me Choose page (sitemap `/tools/help-me-choose/`, production post 796) |
| `{{TEACHING_URL}}` | `/teaching/` | `/teaching/` |
| `{{TRAINING_URL}}` | `/training/` | `/training/` |
| `{{RESEARCH_URL}}` | `/research/` | `/research/` |
| `{{RESPONSIBLE_USE_URL}}` | `/resources/responsible-use/` | `/resources/responsible-use/` |
| `{{MENTAL_HEALTH_URL}}` | `/resources/responsible-use/mental-health/` | `/resources/responsible-use/mental-health/` |
| `{{WHITEBOARD_URL}}` | personal SharePoint whiteboard | Ask David. Kept out of Git on purpose (private URL). |

The CSU-GPT hero button link (`primary_url`) is the tool's own URL, also in the tree. The Responsible AI links to outside sources came from the shared mockup and need a link check.

## Before pushing

1. Production must run plugin `aicsu-tool-chooser` 0.4.0 or later. The page uses `[aicsu_home_hero]` and `[aicsu_quick_match]`. Release it per the workflow (zip, upload as super admin, activate on the ai subsite only). Without it the shortcodes print as plain text.
2. Production MCP: enable writes for the session, authenticate through `/mcp` in an interactive session, run `detect-elementor-version`. Production is a draft push only.
3. Create the page as a **draft**. Keep the front page unchanged. Apply `home-concept.page-settings.json` with `update-page-settings`.
4. The hero container needs CSS classes `wc-container-wide wc-container-gap-none`; the Responsible AI container needs `wc-container-wide aicsu-rai` (already set in the tree).
5. Apply the tool order below, then check the Quick Match list on the draft.

## Tool directory order (apply on production)

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
