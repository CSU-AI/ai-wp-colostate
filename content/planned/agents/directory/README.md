# AI agent directory records

Each Markdown file mirrors one `aicsu_agent` record in WordPress. Same layout as `content/planned/tools/directory/`.

- Edit the JSON-compatible front matter for fields and taxonomy term slugs.
- The paragraph below the front matter is the WordPress excerpt, shown on the card.
- Keep existing `local_post_id` values. New records use `null` and start as drafts.
- Record `production_post_id` after every production push.
- Local and production writes go through the MCP connections described in `wordpress/build-notes/production-workflow.md`. There is no importer script for agents yet.

## Scope

List only agents any CSU person can open. Agents limited to a group stay private and are not listed here. The directory has no access, approval, or data-level fields.

## Fields

| Front matter | WordPress |
|---|---|
| `title`, `slug` | Post title and slug |
| first paragraph | Excerpt (two sentences or fewer) |
| `agent_url` | ACF `agent_url`, required |
| `owner` | ACF `owner`, a person or team |
| `contact` | ACF `contact`, email or URL |
| `last_reviewed` | ACF `last_reviewed`, ISO date; WordPress stores `YYYYMMDD` |
| `taxonomies` | Term slugs per taxonomy |

## Curated terms

Terms are curated. Pick only from this list. If nothing fits, ask before adding a term, and update this list when one is added.

**`aicsu_role`** (shared with tools): `faculty`, `staff`, `student`, `researcher`. Tick every group that may want the agent.

**`aicsu_platform`**: `copilot`, `nebulaone`

**`aicsu_agent_tag`**: `accessibility`, `powerpoint`, `word`, `excel`, `curriculum`, `learning-outcomes`, `assessment`, `procurement`, `governance`, `security`

## Schema

Ships as ACF JSON in the `aicsu-tool-chooser` plugin (`wordpress/tool-chooser/acf-json/`): post type `aicsu_agent`, taxonomies `aicsu_platform` and `aicsu_agent_tag`, field group "Agent Details", and `aicsu_role` shared with tools. See `wordpress/agent-directory/README.md` for the workflow.
