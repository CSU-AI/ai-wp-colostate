# AI agent directory

Searchable, filterable directory of CSU agents, shown with the `[aicsu_agent_directory]` shortcode from the `aicsu-tool-chooser` plugin (version 0.3.0 and later). It reuses the tool chooser's markup and stylesheet, so the two pages look alike.

## Pieces

| Piece | Source in this repo |
|---|---|
| Shortcode (server-rendered list) | `wordpress/tool-chooser/agent-directory.php` |
| Filtering, search, prefilter links | `wordpress/tool-chooser/agents.js` (tests: `agents.test.cjs`) |
| Styling | `wordpress/tool-chooser/chooser.css` plus `agents.css` |
| Schema: `aicsu_agent`, `aicsu_platform`, `aicsu_agent_tag`, Agent Details fields, `aicsu_role` | `wordpress/tool-chooser/acf-json/` (shared with tools) |
| Records | `content/planned/agents/directory/*.md` |

The first build used an Elementor Loop Grid and an HTML widget. It is in commit `59d619c` if you need it; the shortcode replaced it because it needed fewer overrides and no manual paste.

## How it works

The shortcode renders every published agent on the server, so the list works without JavaScript. `agents.js` adds search and filters (OR within a group, AND across groups). Filter groups: Who Are You? (all four roles), Platform and Topic (only terms an agent uses). Cards show owner and the last-reviewed date (formatted in PHP from the raw `Ymd` value) and link to the agent. Agents are ordered by menu order, then title.

## Linking to a pre-filtered view

Use slugs, comma separated:

```
/tools/agents/?role=student
/tools/agents/?role=student&platform=copilot
/tools/agents/?tag=powerpoint,accessibility&q=alt
```

Parameters: `role`, `platform`, `tag`, `q` (search text). Visitors can untick anything, and the URL updates as filters change. Slugs with no matching checkbox are ignored. Filtering happens in the browser, so the page stays cacheable.

## The page

An Elementor page with the usual heading and intro, then a Shortcode widget containing `[aicsu_agent_directory]`, the same structure as the tool chooser page. The MCP can push this page; there is no HTML widget or super admin step.

## Local workflow

Change schema in Local ACF, then **export first, then sync**:

```sh
source "/Users/david/Local Sites/multisite/local-wpcli-env.sh"
# The env script names PHP 8.4.10; Local has 8.4.18, so put it first on PATH.
export PATH="$HOME/Library/Application Support/Local/lightning-services/php-8.4.18+1/bin/darwin-arm64/bin:$PATH"
W=(--path="/Users/david/Local Sites/multisite/app/public" --url=https://multisite.local/ai --user=cdedward)
wp "${W[@]}" eval-file wordpress/tool-chooser/export-acf-json.php   # Local ACF -> repo
scripts/package-plugin.sh local                                      # repo -> Local plugin (rsync --delete)
```

Syncing first would overwrite Local's newer schema with the repo copy. Run `node wordpress/tool-chooser/agents.test.cjs` and `chooser.test.cjs` after JS changes.

## Known gaps

- Slugs for unused terms are ignored in prefilter links (for example a tag no agent has).
- `contact` is stored but not shown on cards.
- The "Open ..." card links are 22px tall, just under the 24px WCAG 2.2 target size (the tool chooser's links match). A padding change would need a new plugin release.
- Production deployment and QA are recorded in `PRODUCTION-PLAN.md`.
