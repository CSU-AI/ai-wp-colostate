# AI Tool Chooser proof of concept

Built locally on 2026-09-11 and rechecked on 2026-09-17. The chooser page and all 12 tool records remain drafts; rendering is verified for both draft previews and published pages.

- Preview: https://multisite.local/ai/?page_id=52&preview=true (requires WordPress login).
- Elementor editor: https://multisite.local/ai/wp-admin/post.php?post=52&action=elementor
- Sample tools: post IDs 40 through 51, all drafts, marked with `aicsu_demo=1`.
- Page copy: `content/planned/ai-tool-chooser-poc.md`.
- Plugin source: `wordpress/tool-chooser/`.

## ACF verification and repairs

The existing `aicsu_tool` post type was correctly registered with REST support, excerpts, custom fields, and page order. All eight taxonomy definitions existed, and seven were attached correctly. An archive is disabled, which is fine for a chooser on a separate page.

Corrected the Tool Details field group (22, `group_6a861cf88c190`) location from ordinary posts to `aicsu_tool` and enabled its REST output. Attached Outputs (17, `taxonomy_6a861b4f4fd26`) to `aicsu_tool`. Created the missing output terms while creating samples. Existing fields, taxonomies, and unrelated content were preserved.

Sample posts and scalar metadata were created through emcp-multisite-local. The MCP post tools write raw post meta rather than ACF field references, so a guarded local WP-CLI helper completed ACF references and the Highlights repeater via `update_field`. ACF readback confirmed the resulting values.

## Implementation

The new page uses native Elementor containers, heading and text widgets, and a Shortcode widget. A small three-file plugin, **AI @ CSU Tool Chooser**, is activated only on the AI subsite. It registers no post types, fields, or taxonomies. All tool content remains editable in ACF and updates on the next page load.

The plugin renders cards on the server and adds client-side search, filtering, sorting, and comparison. Search & Filter Pro 3.2.3 is installed; the older build sheet targets V2. For this small proof of concept, the plugin avoids a separate filter configuration and implements the custom comparison and classification checks together. The original wireframe, design system CSS, and tool-finder JS are untouched.

- OR within each taxonomy group; AND across groups.
- For sensitive data, all selected sensitive classifications must match, and both the approval status and boolean must agree. A public-data choice cannot bypass this gate.
- Directory order, name ascending/descending, or match score. DOM order follows sort order.
- Three primary filter groups and five under More Filters. Mobile filters start collapsed.
- Up to three selections, retained while filtering, with a modal comparison table. Two selections enable comparison. Escape closes the dialog and restores focus.
- No-JavaScript fallback shows all server-rendered cards with a notice; interactive controls remain hidden.
- Tool records render whether the chooser page is published or shown as a draft preview.
- No outbound data requests, tracking, or external asset dependencies.

The active CSU theme disables Elementor Canvas and wraps its templates. The page therefore uses the supported default template. Page-scoped CSS hides `.page-header` on page 52 to prevent the repeated page title. The chooser itself has one H1, followed by H2/H3 headings. The theme's pre-existing logo H1 remains outside chooser content; no theme files were changed. Its footer widgets also remain unchanged.

## Checks performed

- PHP lint and `node wordpress/tool-chooser/chooser.test.cjs` passed.
- `verify-local.php` passed: ACF attachment/REST settings, highlights, live URL rendering, 12 cards on published and draft page views, and server-rendered fallback.
- Browser: 12 initial cards; Draft Writing + FERPA returned three; adding HIPAA returned two. A no-match search showed the empty state and reset recovered the directory.
- Alphabetical ordering, three-selection limit, comparison table, Escape close and focus return passed.
- Desktop and mobile visual review completed. Mobile used a 390px viewport with no document overflow. Its comparison table scrolls within the dialog.
- Tablet review at 900px confirmed one card column, no overflow, and best-match ranking across two task selections.

This is targeted functional/accessibility verification, not a full WCAG audit. The inherited theme heading structure is noted above.

## Editing and migration

Replace sample data in `content/planned/tools/directory/`, then run the guarded local importer documented there. Edit headings and introductory copy in Elementor and the planned Markdown source. Tool URLs may be empty, in which case there is no visit link. Fields, taxonomy choices, excerpts, and page order are read directly. Set `demo` to `false` when replacing a sample with reviewed content. Do not publish sample approval claims as real guidance.

For a bulk migration after approval, export ACF definitions with ACF Tools and records/terms with WordPress WXR. Install only `aicsu-tool-chooser.php`, `chooser.js`, and `chooser.css` from the source folder. Activate the plugin on the destination subsite, import the saved Elementor element tree, and recreate page settings. Adjust the page-specific title CSS to the destination page ID and recheck links and access behavior. The seeding helper is explicitly local-only and must not be installed with the plugin.

The client filter is intended for a small directory; pagination/server queries should be reconsidered for hundreds of tools. The local verification helper expects these 12 original draft fixtures and must be updated when they are replaced.
