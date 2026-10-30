# Agent directory: production build plan

Moves the agent directory to `ai.colostate.edu`. Follows `wordpress/build-notes/production-workflow.md`: drafts first, nothing on the shared network touched, one release.

Everything ships in one release of the existing `aicsu-tool-chooser` plugin (0.3.0): schema, shortcode, and assets. Field keys match Local automatically because the schema is JSON in the plugin.

## Decisions before starting

| # | Decision | Recommendation |
|---|---|---|
| 1 | Page URL | **Decided: `/tools/agents/`**, planned in `sitemap.yml` and `content/planned/tools/agents.md`. The live site still serves `/ai-tools/` until the `/tools/` section is released, so this page is built to publish with that release, not before. |
| 2 | Contact on cards | Not shown today. Owners are set for all three agents; contact is blank for the two newest. |

## Rules for this release

- Production MCP stays read-only until you say, in that session, that writes are allowed for a named phase.
- Everything is created as a draft. Publishing happens in phase 6.
- Do not edit `csu-theme`, network settings, or network-activate the plugin. Do not use super admin credentials for MCP.
- Never click ACF "Sync available" on production. The plugin JSON is the schema.

## Phase 0: Parity check (read-only)

1. Turn on the production MCP, authenticate through `/mcp`.
2. `detect-elementor-version`; compare Elementor, Elementor Pro, ACF Pro, CSU Theme, and plugin versions with Local (Elementor 4.3.4, Pro 4.2.2, theme 3.2.1, ACF Pro 6.3.12, plugin 0.2.2 on production).
3. `list-post-types` and `list-taxonomies`: confirm `aicsu_tool` and `aicsu_role` exist and `aicsu_agent` does not.

**Gate:** versions compatible.

## Phase 1: Plugin release 0.3.0

1. Confirm the repo is at 0.3.0 and `scripts/package-plugin.sh zip` lists `agent-directory.php`, `agents.js`, and `agents.css` along with the existing files.
2. Run `node wordpress/tool-chooser/chooser.test.cjs` and `agents.test.cjs`.
3. A super admin uploads the zip over the installed plugin (it stays activated on the ai subsite only; do not network-activate).
4. Verify on production: the tool chooser page still lists every tool and Compare works; Roles terms are unchanged; `list-post-types` shows `aicsu_agent`; `list-taxonomies` shows `aicsu_platform` and `aicsu_agent_tag`, and `aicsu_role` is attached to both Tools and Agents.

**Gate:** tool chooser unaffected; agent schema present.
**Rollback:** reinstall the 0.2.2 zip.

## Phase 2: Curated terms

Create the terms by name: Platforms Copilot and NebulaOne. Tags Accessibility, PowerPoint, Word, Excel, Curriculum, Learning Outcomes, Assessment. Confirm slugs match `content/planned/agents/directory/README.md`. Unused terms stay; the filter only lists platforms and tags that agents use.

## Phase 3: Agent records as drafts

Writes allowed for this phase only.

1. For each file in `content/planned/agents/directory/`, create the `aicsu_agent` post as a draft with the same slug, excerpt, content, terms, and fields (`agent_url`, `owner`, `contact`, `last_reviewed` as `Ymd`).
2. Read each record back (`get-post`, `acf-read get-fields`) and confirm values match.
3. Record `production_post_id` and `production_modified` in each file's front matter.

## Phase 4: Directory page as draft

1. Find the production parent page for `/tools/` (`list-pages`). If the `/tools/` landing page is not there yet, build the page as a draft with no parent and set the parent when the Tools section is built. Create the page with slug `agents` through the production MCP: heading, intro text, and a Shortcode widget containing `[aicsu_agent_directory]` (same structure and styling as the tool chooser page, including its breadcrumb, heading, and intro settings: copy them from page 52 on Local).
2. Record the page ID and modified time in the planned page's front matter.

## Phase 5: QA on the production draft (under the real theme)

- Desktop, tablet, mobile. Filter panel collapses on mobile.
- Keyboard: every filter, reset, and link reachable, focus visible.
- Screen reader: count announces changes; labels and legends read correctly.
- Contrast on card text, chips, and links.
- JavaScript off: all cards listed.
- Prefilter links: `?role=student`, `?role=faculty&tag=curriculum`, `?platform=nebulaone&tag=assessment&q=plo`, and one that matches nothing (empty state fills the column).
- Equal-height cards per row; links aligned at the bottom.
- Page caching: publish a throwaway agent and confirm it appears without editing the page, or note the purge step.
- Each agent link opens the correct agent for a signed-in CSU user.

**Gate:** QA passed on the draft.

## Phase 6: Release

One release, following "Section release" in the production workflow.

1. Mark `content/planned/tools/agents.md` `ready-production` (it is already in `sitemap.yml`).
2. Publish the agent records, then the page, as part of the `/tools/` section release (page order in that release is not fixed: the page can go out last).
3. Add the page to the production menu.
4. Link from audience pages as they ship, for example `/tools/agents/?role=student`.
5. Run `find-broken-links`; spot check the prefilter URLs on the live page.
6. Write `wordpress/releases/YYYY-MM-DD-agents.md` (plugin version, page ID, record IDs).
7. Set lifecycle to `published`.

## Rollback

- Page or records misbehave: set them back to draft.
- Plugin misbehaves: reinstall 0.2.2. The agent post type, taxonomies, and records then disappear from the front end until 0.3.0 returns; stored data stays in the database.

## After launch

- Adding an agent: write the Markdown record from curated terms, push as a draft through the production MCP, review, publish. Ask before adding a term; update the README term list when one is added.
- Plugin changes (CSS, JS, schema) need a new plugin release by a super admin.
- Review dates: consider a quarterly pass listing records older than six months.

## Risks

| Risk | Mitigation |
|---|---|
| Plugin upload overwrites production with a broken build | Run both JS tests and PHP lint first; keep the 0.2.2 zip |
| Terms missing when records are pushed | Phase 2 before phase 3 |
| Agent goes live with no contact on file | Fill `contact`, or accept owner-only cards |
| Elementor or Pro version drift | Phase 0 comparison |
