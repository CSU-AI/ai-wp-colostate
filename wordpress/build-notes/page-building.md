# Page building notes

Lessons from building the Resources pages on Local with the EMCP tools, verified against the CSU theme in November 2026. Read this before building or pushing any page. Production has no disk access, so everything here works through the MCP only unless a step says "manual".

## Theme behavior

| Behavior | What to do |
|---|---|
| The theme shows a title banner above the page, which duplicates the page's H1. | Hide it per page with the theme's layout option (stored as `_wc_post_layout_head = hide`). The MCP refuses to write that meta key, so set it by hand in wp-admin on Local **and** on production for every new page. Check that the page has one H1 afterward. Elementor's `hide_title` page setting does not do this. |
| Widget `text_color` is ignored for paragraph text. The theme's own paragraph color wins. | Don't rely on white or custom text on dark backgrounds. Use dark text on light backgrounds. |
| The Button widget is re-rendered with the theme's own green `btn` style. Its background and text colors are overridden. | Use the theme's default button look. Don't set button colors. |
| Widget font sizes do not reach paragraph text (it renders at the theme size). | Treat paragraph size settings as decorative. |
| Heading-to-text spacing in cards is large. | Accepted as part of the brand for now. Revisit later. |
| A top-level Elementor container is inset from the viewport edges (about 5% each side). The production home slider is edge to edge. | Add the theme's own classes to the outer container's CSS Classes field (`css_classes`): `wc-container-wide wc-container-gap-none`. Wide makes it full width and keeps 2rem side padding on its children (768px and up). Add `wc-container-gap-none` only for edge-to-edge media such as a hero or slider, because it also strips that padding and content then touches the screen edge below about 1250px. Under 768px the theme applies neither class and containers stay inset. The theme's `stretch` and `wc-container-width-wide` classes do similar work. Used on production page 6 and on the home concept (hero: both classes; tan Responsible AI band: wide only). |
| Sibling top-level or nested parent containers get a 4rem bottom margin from the theme (`:is(.e-con.e-parent + .e-con.e-parent, ...)`), so stacked rows float apart. | Give the section's outer container a class (for example `aicsu-rai`) and add page CSS `.elementor-<post id> .e-con.e-parent, .aicsu-rai .e-con { margin-bottom: 0 !important; }`. Do not scope it with `body.page-id-<id>`: the theme removes that body class on the front page, then space rows with padding. The theme rule outranks a plain override, hence `!important`. |
| The main menu supports two levels (parent and child). A third-level item is saved but never shows. | Keep menus two levels deep. |

## MCP quirks

- `build-page` takes no parent or slug. After building, call `update-post` with `slug` and `parent`.
- `build-page` rejects a missing `title`, and widgets need `widget_type`.
- New widgets added later with `add-free-widget` regenerate CSS correctly. If a page looks stale, reload with a changed query string.
- `build-page` leaves the outer container's `flex_align_items` at Elementor's default (center), so headings and text shrink and center. After building, call `update-container` on the outer container with `flex_align_items: stretch` and `flex_justify_content: flex-start`. The heading `align` setting alone does not fix it.
- The CSU theme sets `--flex-direction: column` on `.footer .e-con-inner`, so a boxed container in a Theme Builder footer cannot be a row. Use `content_width: full` containers in footer templates.
- `reorder-elements` takes `container_id` and `element_ids`.
- `menu-write` operations: `add-item`, `delete-item`, `reorder-items`. A menu write can fail once with `ECONNRESET`. Check the menu with `get-menu` before retrying, because it may have saved.
- Protected meta (for example `_wc_*`) cannot be written through `update-post`.
- `export-page` returns only the element tree. Page settings such as `custom_css` (the Glossary's table borders and alignment) are not included. When pushing, also read `_elementor_page_settings` from Local (`wp_14_postmeta`) and apply it with `update-page-settings`.

## Layout recipes

### Card grid

- Grid container: flex row, wrap, `flex_align_items: stretch`, 20px gap.
- Card container: flex column, `flex_align_items: stretch` (Elementor's default centers children and shrinks them), `flex_justify_content: flex-start`.
- Card widths: 31.5% for three across, 48.5% for two across. Tablet 48%, mobile 100%.
- Card style: white background, 1px Colorado State Green border with a 6px top border, 8px radius, 24px 22px padding.
- Card contents, in order: icon, H3 (green, 22px, bold, left), one or two sentences, optional Button.

### Icons

- Use Elementor's built-in Font Awesome through the icon widget's `selected_icon`, for example `{"value":"fas fa-leaf","library":"fa-solid"}`. They render as inline SVG with no upload, so they push to production unchanged.
- Style: `view: stacked`, `shape: circle`, `primary_color: #C8C372` (circle), `secondary_color: #1E4D2B` (glyph), `size: 30px`, `icon_padding: 0.6em`, `align: start`.
- Uploaded SVGs need the media library and, on production, probably `unfiltered_html`, which the MCP account lacks. Avoid them unless a needed icon does not exist in Font Awesome.
- Avoid AI-generated photo-realistic imagery. Icons and diagrams are fine. Real CSU photography is at https://www.flickr.com/photos/coloradostateuniversity/ (check each photo's license). The CSU photo library is paid.

### Callout

- Container, full width, flex column, 14px gap, 28px padding (18px mobile), 8px radius.
- Background: Colorado State Gold tinted to 25% strength, `#F1F0DC`. Border: Colorado State Green, uniform 2px (no thicker side edge).
- Contents: H2 in green, dark paragraph, then Buttons. Used for the crisis box on the mental health page.

### Long pages: jump links, bands, accordion

Pattern used on the FAQ, Glossary, Mental Health, hub and index pages (November 2026).

- Under the intro, a row of small Buttons linking to section anchors (`_element_id` on each section container). Row is flex, wrap, 12px gap.
- Each section is a full-width flex column container with an anchor id. Alternate sections get the Gold Tint band (`#F1F0DC`, 8px radius, 28px padding). Outer container gap is 28px.
- Section header: flex row, 14px gap, icon widget (26px, gold circle, green glyph) plus H2.
- FAQ questions use the native Accordion widget: H3 titles, `faq_schema: yes`, green title color, 1px green border, white backgrounds. Elementor opens the first item by default and the widget has no control to collapse it.
- Keep heading order: H1, H2 sections, H3 items. Do not jump from H1 to H3.
- Rebuilding a page: `import-template` the new container tree, then `remove-element` the old top-level container. Imported element ids are regenerated, so look them up with `find-element` or `get-page-structure` before updating.

### Rosters and long lists (governance pages, November 2026)

- Tint panel: put `_css_classes: csu-panel` on a text-editor widget with `_background_color: #F1F0DC`, 24px 28px padding and an 8px radius. Page `custom_css` (`.csu-panel ul, .csu-panel ol { margin: 0; }`) removes the list margin so columns line up. Repeat the CSS on each page that uses panels. It is page settings, so on production apply it with `update-page-settings`.
- Columns: the text-editor widget has native `text_columns` (3 desktop, 2 tablet, 1 mobile for long rosters) and `column_gap`. Use it only on single plain lists. Mixed bold labels and lists split badly across columns.
- Group grids (subgroups, member groups): flex row wrap, 31.5% (three across) or 48.5% (two across) children, 3px Gold top border, H3 or H4 at 22px green. No card chrome.
- Theme H3 is very large. Set `typography_font_size` 22 to 24px and `title_color` green on H3s inside grids and panels.
- Jump links: a row of small Buttons linking to `#id`, with `_element_id` set on each H2.
- Build order for new blocks in an existing page: `import-template` appends at the top level, so `move-element` it into the outer container (`position` -1 or an index).

## Brand colors

Source: https://brand.colostate.edu/color/ (Find Your Energy palette). Older secondary and tertiary palettes are retired. Only that color page of the brand site reflects the current brand.

Primary: Colorado State Green `#1E4D2B`, Colorado State Gold `#C8C372`, Aggie Orange `#D9782D`, 80% Black `#59595B`, White `#FFFFFF`.

Energy palette: Oval Green `#006144`, Lovers Lane `#82C503`, Energy Green `#CFFC00`, Flower Trial Red `#E56A54`, Powered Purple `#7E5475`, Horsetooth Blue `#008FB3`, Stalwart Slate `#105456`, Sunshine `#FFC038`. Neutrals: Black `#000000`, Gray `#CCCCCC`, Tan `#E3CDB1`.

Accessibility rule from the brand page: pairings not listed are not approved. AA18 pairings are for large type only (18pt regular or 14pt bold).

Approved pairings used here:

| Background | Text | Rating |
|---|---|---|
| White | Primary Green | AAA |
| White | 80% Black | AA |
| Primary Green | White | AAA |
| Primary Green | Gold | AA |
| Gold | Primary Green | AA |
| Tan | Primary Green | AA |

Tints of a brand color (for example the callout's 25% Gold) are acceptable to David as lighter versions of the same color. Dark body text on a tint is not on the brand pairing list, so keep text dark and high contrast.

## Style guide

Writers' style guide: https://marcomm.colostate.edu/style-guide-az/. Project rules in `AGENTS.md` take priority: no em dashes, Title Case headings, sentence case body, one H1.

## Checklist for a new page

1. Draft the Markdown in `content/planned/` and add it to `sitemap.yml`.
2. Build on Local (`build-page`), then set `slug` and `parent` with `update-post`.
3. Hide the theme title banner by hand in wp-admin. Confirm there is one H1.
4. Add the page to the menu if it belongs there.
5. Check desktop, tablet and mobile in a browser.
6. Record `local_post_id` in the front matter.
7. For production: repeat step 3 by hand, and keep the page as a draft until its section release.
