# AI agent directory

Searchable, filterable directory of CSU agents. No plugin: the schema is stored in ACF Pro, cards are a native Elementor Pro Loop Grid, and the filter is one Elementor HTML widget.

## Pieces

| Piece | Where | Source in this repo |
|---|---|---|
| Records | `aicsu_agent` posts | `content/planned/agents/directory/*.md` |
| Schema | ACF Pro, database only | Described in `content/planned/agents/directory/README.md` |
| Card | Elementor Pro Loop Item "AI Agent Card" | `build-loop-item.php` (Local only) |
| Filter, styling, and script | One HTML widget in the left column | `../tool-chooser/agent-filter.html` |
| Test page | Local page 84, "AI Agents (test build)" | `build-directory-page.php` rebuilds the layout and places the filter |

## How the filter works

The Loop Grid renders every agent on the server, so the list works without JavaScript. Each card wrapper carries WordPress term classes such as `aicsu_role-student`, `aicsu_platform-copilot`, and `aicsu_agent_tag-powerpoint`. `agent-filter.html` reads those classes, builds the checkbox groups from the terms that appear on cards, and hides cards that do not match. Choices combine with OR inside a group and AND across groups. Term display names come from the public REST API.

Adding a term to an agent adds it to the filter automatically. Nothing in `agent-filter.html` changes.

## Linking to a pre-filtered view

Use slugs, comma separated:

```
/ai-agents/?role=student
/ai-agents/?role=student&platform=copilot
/ai-agents/?tag=powerpoint,accessibility&q=alt
```

Parameters: `role`, `platform`, `tag`, `q` (search text). Visitors can untick anything. The URL updates as filters change, so a filtered view can be shared. Unknown slugs are ignored.

## Page requirements

The look matches the tool chooser (`chooser.css`): gray filter panel with a green top rule on the left, two-column card grid on the right.

- A row container with class `aicsu-agent-layout` holds two children: `aicsu-agent-sidebar` (270px) with the HTML widget, and `aicsu-agent-main`.
- `aicsu-agent-main` holds the "Explore Your Options" heading and a container with class `aicsu-agent-results` that wraps the Loop Grid.
- The Loop Grid uses the "AI Agent Card" Loop Item and queries post type `aicsu_agent`.
- `build-directory-page.php` builds exactly this structure on Local. Copy it when building the production page.
- The HTML widget also carries the card and panel CSS (kept in one place, using the chooser's tokens), and the script injects the result count and the empty-state message into the results container.

## Putting the filter on a page (production)

The production MCP account lacks `unfiltered_html`, so it cannot save an HTML widget. A super admin does this step by hand:

1. Log in to the ai subsite as a super admin. Super admins have `unfiltered_html` on multisite unless `DISALLOW_UNFILTERED_HTML` is set in `wp-config.php`.
2. Edit the directory page in Elementor and add an HTML widget above the results container.
3. Paste the full contents of `wordpress/tool-chooser/agent-filter.html`, update, and view the page.
4. Re-paste whenever `agent-filter.html` changes. This repo is the source of truth, not the widget.

## Local commands

```sh
source "/Users/david/Local Sites/multisite/local-wpcli-env.sh"
# The env script names PHP 8.4.10; Local now has 8.4.18, so put it first on PATH.
export PATH="$HOME/Library/Application Support/Local/lightning-services/php-8.4.18+1/bin/darwin-arm64/bin:$PATH"
W=(--path="/Users/david/Local Sites/multisite/app/public" --url=https://multisite.local/ai --user=cdedward)
wp "${W[@]}" eval-file wordpress/agent-directory/build-loop-item.php
wp "${W[@]}" eval-file wordpress/agent-directory/build-directory-page.php
```

`--user` is required. Elementor will not save documents without a logged-in user.

## Known gaps

- The "Last reviewed" date shows as `YYYY-MM-DD`.
- Production still needs the ACF schema (ACF Tools export and import), the Loop Item, and a directory page.
