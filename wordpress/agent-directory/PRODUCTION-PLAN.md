# Agent directory: production build plan

Moves the agent directory from Local to `ai.colostate.edu`. It follows `wordpress/build-notes/production-workflow.md`: drafts first, one release, nothing on the shared network touched.

Status of Local: schema, three agent records, the "AI Agent Card" Loop Item, and the test page (ID 84) are built and checked. Nothing exists on production yet.

## Decisions needed before starting

| # | Decision | Recommendation |
|---|---|---|
| 1 | Page URL | `/tools/agents/`, a child of Tools. Add it to `sitemap.yml` and write `content/planned/tools/agents.md` so site status tracking sees it. |
| 2 | How `aicsu_role` gains agents on production | Ship a tiny tool-chooser plugin update (see phase 2). The role taxonomy is defined by the plugin's JSON there, so the plugin is the only clean place to change it. |
| 3 | Show owner and contact on cards? | Cards show owner. `contact` is stored but not displayed. Confirm. Owners are set for all three agents; contact is still blank for the two new ones. |
| 4 | Who pastes the filter widget | A super admin, by hand, in the browser. |

## Hard rules for this release

- Production MCP stays read-only until you say, in that session, that writes are allowed for a named phase.
- Everything is created as a draft. Publishing happens only in phase 8.
- Do not edit `csu-theme`, network settings, or network-activate anything. Do not use super admin credentials for MCP.
- Never click ACF "Sync available" on production.
- This plan needs `acf-write` on production **off**. Schema comes from a JSON import, not from recreating items.

## Phase 0: Parity check (read-only)

1. Turn the production MCP on, authenticate through `/mcp`, and confirm the connection.
2. Run `detect-elementor-version`. Compare Elementor, Elementor Pro, ACF Pro, CSU Theme, and tool-chooser plugin versions with Local (Local: Elementor 4.3.4, Pro 4.2.2, theme 3.2.1, ACF Pro 6.3.12).
3. Confirm production has the Elementor Pro Loop Grid and Loop Item type. Without Pro's Loop Builder, stop and revisit the design.
4. `list-post-types` and `list-taxonomies` on production: confirm `aicsu_tool` and `aicsu_role` exist and `aicsu_agent` does not.

**Gate:** all versions compatible, Loop Builder present.

## Phase 1: Capture the schema from Local (repo only, no production)

1. Write `wordpress/agent-directory/export-agent-schema.php` (Local only) that exports the `aicsu_agent` post type, `aicsu_platform`, `aicsu_agent_tag`, and the Agent Details field group in ACF's import format. Output goes to `wordpress/agent-directory/acf-schema/agents.json`, outside the plugin's `acf-json/` folder so the plugin never loads it.
2. Export the Loop Item: Local, Templates, export "AI Agent Card" to `wordpress/agent-directory/exports/ai-agent-card.json`.
3. Confirm the exported field keys match what `build-loop-item.php` references: `field_6ae4e654663b7` (agent_url), `field_6ae4e654663c0` (owner), `field_6ae4e654663ca` (last_reviewed). The card reads fields by key, so recreating the field group by hand would break it.

## Phase 2: Schema on production

Needs a site admin (you) in wp-admin; no MCP writes.

**2a. Plugin update for the shared taxonomy**
1. On Local, run `export-acf-json.php` so `taxonomy_6a861b2a7a176.json` (`aicsu_role`) lists both `aicsu_tool` and `aicsu_agent`.
2. Bump the plugin `Version` (0.2.2 to 0.2.3), commit, run `scripts/package-plugin.sh zip`, and upload as super admin per the existing process. Plugin stays activated on the ai subsite only.
3. Verify: the tool chooser page still lists every tool, and Roles terms are unchanged. Role terms on production already exist (faculty, researcher, staff, student).

**2b. Agent schema import**
1. ACF, Tools, Import JSON on the ai subsite, using `agents.json`. Do not touch the plugin's items.
2. Verify with production MCP reads: `acf-read list-post-types`, `list-taxonomies`, `list-field-groups`. Field keys must equal Local's.
3. Verify `aicsu_role` now reports `aicsu_agent` among its object types.

**Gate:** keys identical to Local, tool chooser unaffected.

## Phase 3: Curated terms

Create the terms by name (slugs derive from them) so the filter has what the records need. Platforms: Copilot, NebulaOne. Tags: Accessibility, PowerPoint, Word, Excel, Curriculum, Learning Outcomes, Assessment. After creation, confirm slugs match `content/planned/agents/directory/README.md`. Unused terms stay (the filter only shows terms that appear on cards).

## Phase 4: Agent records as drafts

Writes allowed for this phase only.

1. For each file in `content/planned/agents/directory/`, create the `aicsu_agent` post as a draft with the same slug, excerpt, content, terms, and fields (`agent_url`, `owner`, `contact`, `last_reviewed`).
2. Read each record back with `get-post` and `acf-read get-fields`; confirm values match.
3. Record `production_post_id` and `production_modified` in each file's front matter. (Same drift guard as pages.)

## Phase 5: Loop Item

1. Import `ai-agent-card.json` through Elementor, Templates, Import.
2. Record the new template ID (it will differ from Local's 83).
3. Open the template and confirm the preview shows owner, date, and the Open button; if a field is blank, the field keys do not match, so return to phase 2b.

## Phase 6: Directory page as draft

1. Export the Local page structure (`export-page` on ID 84, or run `build-directory-page.php` logic as the reference).
2. Rewrite the Loop Grid `template_id` to the production template ID from phase 5. Keep the container classes `aicsu-agent-layout`, `aicsu-agent-sidebar`, `aicsu-agent-main`, and `aicsu-agent-results`.
3. Create the page as a draft at the chosen slug and parent through the production MCP. The HTML widget will not save through MCP (no `unfiltered_html`), so leave a placeholder.
4. Record `production_post_id` and `production_modified` in the planned page's front matter.
5. **Manual step, super admin:** edit the draft in Elementor, add an HTML widget in the sidebar container, paste the full text of `wordpress/tool-chooser/agent-filter.html`, update. If the paste is stripped, check for `DISALLOW_UNFILTERED_HTML` in `wp-config.php` and stop; that needs server access.

## Phase 7: QA on the production draft (under the real theme)

- Desktop, tablet, mobile layouts. Panel stacks above cards on mobile.
- Keyboard: every filter, reset, and card button reachable, focus visible.
- Screen reader pass: the result count announces changes, labels and legends read correctly.
- Color contrast on card text, chips, and buttons.
- JavaScript off: all cards listed, no broken filter UI.
- Prefilter links: `?role=student`, `?role=faculty&tag=curriculum`, `?platform=nebulaone&tag=assessment&q=plo`.
- Term names appear properly ("NebulaOne", "PowerPoint", "Learning Outcomes") and not as prettified slugs; if they do, the REST name lookup failed, so check the REST base.
- Page caching: confirm a newly added agent appears after publish without a manual purge, or note the purge step.
- Every agent link opens the correct agent for a signed-in CSU user.

**Gate:** `qa` passed on production draft.

## Phase 8: Release

One release, following "Section release" in the production workflow.

1. Add the directory page to `sitemap.yml` and mark it `ready-production`.
2. Publish the agent records, then the page.
3. Add the page to the production menu (or the Tools submenu).
4. Add links from audience pages as they ship, for example a Students page linking to `/tools/agents/?role=student`.
5. Run `find-broken-links`, spot check the prefilter URLs on the live page.
6. Write `wordpress/releases/YYYY-MM-DD-agents.md` listing the plugin version, imported schema, template ID, page ID, and record IDs.
7. Set front matter lifecycle to `published`.

## Rollback

- Page or records misbehave: set them back to draft. Nothing else depends on them.
- Plugin update misbehaves: reinstall the 0.2.2 zip.
- Schema import: leave in place; unused post types and taxonomies are harmless.
- Filter widget misbehaves: remove the HTML widget; the page still lists every agent.

## After launch

- Adding an agent: write the Markdown record, use only curated terms, push as a draft through the production MCP, review, publish. Ask before adding a term; update the README term list when one is added.
- Review dates: filter nothing on `last_reviewed` yet. Consider a quarterly pass that lists records older than 6 months.
- If the HTML widget changes, a super admin must re-paste it; the repo copy is the source.

## Known risks

| Risk | Mitigation |
|---|---|
| Recreated field group gets new field keys, so the card shows blanks | Import JSON; never recreate by hand |
| Plugin and DB both define `aicsu_role` | Only the plugin defines it on production; no DB copy there |
| MCP strips the HTML widget | Planned manual paste |
| Loop Grid template ID points at Local's ID | Rewrite in phase 6 and check the draft |
| Production Elementor or Pro version drift | Phase 0 comparison |
| Agent goes live with no contact on file | `contact` is blank for the two newest agents; fill it in or accept that cards show owner only |
