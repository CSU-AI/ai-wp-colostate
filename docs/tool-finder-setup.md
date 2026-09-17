# AI Tool Finder — build sheet

Port of `wireframe.html` to WordPress with **no custom plugin**.

Everything is registered through ACF Pro, so nothing depends on CPT UI being
available. Search & Filter Pro does the filtering. Two static files in this
repo (`aicsu-design-system.css`, `aicsu-tool-finder.js`) carry the styling and
the three behaviours S&F cannot do.

Plugins used, all already installed: **ACF Pro 6.3.12**, **Search & Filter Pro
2.5.14**, **Search & Filter Elementor Extension 1.2.2**, **Elementor** (being
installed).

---

## 1. Post type — ACF › Post Types › Add New

ACF has registered post types since 6.1, so CPT UI is not needed.

| Setting | Value |
|---|---|
| Plural / Singular label | Tools / Tool |
| Post type key | `aicsu_tool` |
| Public | Yes |
| Hierarchical | No |
| Has archive | Yes — `ai-tools` |
| Supports | Title, Excerpt, Custom fields, Page attributes |
| Show in REST API | **Yes** (required — the JS reads `/wp-json/wp/v2/aicsu_tool`) |
| REST base | `aicsu_tool` |
| Menu icon | dashicons-screenoptions |

The **excerpt is the card description**. Keep it to two sentences, and state
the approval status in words inside it. That sentence is the server-rendered
copy of the safety information; the coloured badge is only an upgrade.

## 2. Taxonomies — ACF › Taxonomies › Add New

The active chooser uses four questions. Layers 2 and 3 of the wireframe are
merged: "what data are you working with" and "how is it classified" are the
same question asked twice.

All taxonomies: **Public: Yes**, **Hierarchical: No**, **Show in REST: Yes**,
assigned to `aicsu_tool`.

**`aicsu_role`** — Who are you?
`faculty` · `staff` · `student` · `researcher`

**`aicsu_data`** — What data is involved?
`no-csu-data` · `public-information` · `my-own-files` · `m365-content` ·
`sharepoint-teams` · `ferpa-student-records` · `hipaa-health` ·
`export-controlled` · `confidential-research` · `personnel-hr` · `financial` ·
`sensitive-business`

**`aicsu_task`** — What are you trying to do?
`draft-writing` · `summarize` · `analyze-documents` · `research` ·
`presentations` · `images` · `automation` · `build-agent` · `code` ·
`business-process`

**`aicsu_complexity`** — `beginner` · `intermediate` · `advanced` · `developer`

---

## The safety gate is a tagging rule, not a filter

**Never tag a tool with a sensitive `aicsu_data` term unless it is approved for
that data.**

Do that and the gate needs no meta filter, no conditional logic, and no user
action. Selecting "FERPA student records" cannot return ChatGPT, because
ChatGPT is not tagged with it. S&F enforces it as an ordinary taxonomy query.

The `approved_for_sensitive` field below is not what filters. It drives the
badge and gives the JS a cross-check, so a mis-tagged tool shows up wrong
rather than silently permitted. Both are true or neither is.

Sensitive terms: `ferpa-student-records`, `hipaa-health`, `export-controlled`,
`confidential-research`, `personnel-hr`, `financial`, `sensitive-business`.
This list is mirrored in `CONFIG.sensitiveDataSlugs` in
`aicsu-tool-finder.js` — change one, change the other.

---

## 3. Field group — ACF › Field Groups › "Tool details"

Location rule: Post Type is equal to Tool.
**Settings › Show in REST API: Yes.** Without it the JS gets no field data.

| Label | Name | Type | Config |
|---|---|---|---|
| Approval status | `status` | Select | `approved : Approved for sensitive CSU data`, `pilot : CSU evaluation pilot`, `public_only : Public, non-sensitive data only`. Required. |
| Approved for sensitive CSU data | `approved_for_sensitive` | True / False | Message: "Must match the data classifications tagged on this tool." |
| Cost | `cost` | Text | e.g. "Included with CSU NetID", "~$30/user/mo" |
| Tool URL | `tool_url` | URL | Where the user actually goes |
| Highlights | `highlights` | Repeater → sub-field `label` (Text) | 2–3 short chips. Max 3. |

---

## 4. Search & Filter Pro form

New form, **Post Type: Tool**.

Fields, all **Checkboxes**, all **Operator / Match: Any (OR)**:
`aicsu_role`, `aicsu_data`, `aicsu_task`, `aicsu_complexity`.

Then set the **relationship between taxonomy fields to AND**.

That combination — OR inside a layer, AND across layers — is the logic the
wireframe gets wrong. `filterTools()` in `wireframe.html` uses
`matches.length > 0` across all layers, so ticking "FERPA" and "generate
images" returns tools matching *either*. S&F gives the correct behaviour with
no code. Do not carry the wireframe's logic across.

Other settings: Display Results **via AJAX**, Auto-update results **on**,
Posts per page **-1** (20 tools, no pagination), Order by `menu_order`.

Because every layer ANDs, an over-checked form empties fast. Keep all four
questions visible and do not add secondary filters without a demonstrated
selection need.

## 5. Rendering — Elementor loop

Once Elementor is in:

1. Templates › Theme Builder › **Loop Item**, named "AI Tool Card".
2. Root container gets CSS class `aicsu-finder-card`.
3. Inside: Post Title (H3) · Post Excerpt · a Text widget bound to the `cost`
   dynamic tag · a Text widget per `highlights` row.
4. For the badge, add a Text widget bound to `status` with CSS class
   `aicsu-finder-badge`. Elementor cannot vary the modifier class by value, so
   either accept one badge style, or leave the badge out and let the JS inject
   the colour-coded one — it skips injection if a `.aicsu-finder-badge` is
   already present.
5. Page: a Loop Grid widget pointed at that template, query = Tool. Wrap it in
   a container with class `aicsu-finder-results`. Put the S&F form in a
   container with class `aicsu-finder-filters`, both inside `aicsu-finder`.
6. Connect the S&F form to the Loop Grid through the Search & Filter Elementor
   Extension.

Elementor loop items carry an `e-loop-item-<ID>` class, which is how the JS
maps a card back to its post. Nothing needs to be configured for that.

**Scope note.** This is the only Elementor authoring on the site: one loop
item template. Page layout stays core Gutenberg per `CLAUDE.md`. Worth writing
down as a deliberate exception so it does not become a precedent.

### Fallback if Elementor does not land

S&F Pro → Display Results Method: **Post Type Archive**. The theme's archive
template renders the `ai-tools` archive; `post_class()` emits `post-<ID>`,
which the JS reads the same way, and `decorateMeta()` injects badge and cost
from REST. Put the S&F widget in an archive-scoped sidebar via the Custom
Sidebars plugin. Zero code, no theme edits, plainer cards.

## 6. Load the assets

Same header snippet that already loads the stylesheet:

```html
<link rel="stylesheet" href="https://<pages-url>/aicsu-design-system.css">
<script src="https://<pages-url>/aicsu-tool-finder.js" defer></script>
```

One line, same approved delivery channel, no plugin review. Push to this repo
and both environments update.

The script is progressive enhancement. If it fails to load, filtering still
works; the page loses ranking, the compare tray, and the exclusion notice.

---

## What the JS adds

| Behaviour | Why S&F cannot do it |
|---|---|
| Match scoring | S&F returns a boolean-filtered `WP_Query`, no per-result score. Cards get `data-aicsu-score` and CSS `order`; the strongest get a "Best match" flag, only when 2+ filters are checked. |
| Compare tray | Selection is client state. Persists across AJAX refilters via `sessionStorage`, opens a `<dialog>` comparison table. |
| Exclusion notice | When a sensitive data term is checked, unapproved tools are filtered out server-side and vanish. The JS holds the full set, so it lists what was withheld and why. A tool that disappears reads as a bug; a tool that says why it is unavailable is the point of the page. |

## Accessibility

Fixed from the wireframe, non-negotiable at WCAG 2.1 AA:

- Cards were `<div onclick>` — no keyboard access, no state. Now a real
  `<button aria-pressed>` per card.
- Compare count is `aria-live="polite"`.
- `alert()` is gone; the comparison is a `<dialog>` with managed focus.
- Status is never communicated by colour alone — the badge text says it, and
  the excerpt repeats it.
- Focus rings are explicit on every injected control.

## Palette

The wireframe used `#a855f7`, `#C85A17`, and purple-on-purple cards. None are
CSU colours and the card pairing is not on the accessibility chart. Section 13
of the stylesheet uses approved pairings only: green/white, sunshine/black,
gray/black.

## Migration to production

- **Post type, taxonomies, field group** — ACF › Tools › Export as JSON,
  import on prod. Covers all three in one file.
- **Tools and terms** — WXR export/import, or re-run MCP `create_post`.
- **S&F form** — rebuild by hand, it does not travel.
- **CSS and JS** — nothing to do. Same GitHub Pages URLs.

## Open risk

S&F Pro 2.5.14 is the V2 branch. V3 is a rewrite with a different config
model, and the Elementor integration is the part most likely to need redoing.
Not blocking; worth knowing before the loop template accumulates work.
