# AI tool directory records

Each Markdown file mirrors one local WordPress `aicsu_tool` record.

- Edit the JSON-compatible front matter for ACF fields, highlights, taxonomy term slugs, and order.
- Edit the paragraph below the front matter for the WordPress excerpt shown on the card.
- Set `demo` to `false` after replacing sample claims with reviewed content.
- Keep existing `local_post_id` values unchanged. New records use `null` and start as local drafts; the importer writes back their new ID.
- `wordpress_status: draft` moves an existing local record to draft. `publish` preserves its current state; the importer never publishes.
- Run the local-only importer with:

```sh
source "/Users/david/Local Sites/multisite/local-wpcli-env.sh"
wp --path="/Users/david/Local Sites/multisite/app/public" --url=https://multisite.local/ai eval-file wordpress/tool-chooser/import-tools.php
```

The importer validates known taxonomies and prevents sensitive-data tags unless both approval fields agree. It does not publish tools. It is restricted to Local; production records are pushed through the production MCP per `wordpress/build-notes/production-workflow.md`, and their IDs go in `production_post_id`.

Set `AICSU_TOOL_IMPORT_DRY_RUN=1` before the `wp` command to validate every file without changing WordPress.

## Data classification

`aicsu_data` now carries only CSU data-classification levels, which is what the chooser filters on:

| Slug | Filter label | Level |
|---|---|---|
| `level-1-public` | Public Information | Level 1 |
| `level-2-internal` | Internal CSU work | Level 2 |
| `level-3-confidential` | Confidential records | Level 3 |
| `level-4-restricted` | Restricted or regulated | Level 4 |

Levels follow the [CSU System Data Governance Policy](https://csusystem.edu/wp-content/uploads/sites/7/2025/05/CSUS-Data-Governance-Policy-ACS.pdf) (effective 2025-05-12). The importer refuses `level-4-restricted` on any tool, and requires `status: approved` plus `approved_for_sensitive: true` before it will accept `level-3-confidential`. Tool approval does not grant data access; users must follow applicable data-steward and access requirements. The policy separately addresses agents outside the System and exempts Research Data as defined there.

The older regulation-flavored labels (`ferpa-student-records`, `my-own-files`, `sharepoint-teams`, and so on) moved to a non-filtering `data_examples` array. They are editorial examples and are not rendered by the current chooser.

## Status values

`approved`, `coming_soon`, `pilot`, `public_only`, `unsupported`, `informational`.

- `coming_soon` is for planned tools that are not yet available.
- `unsupported` is for tools someone may install or use on their own, with no CSU review, agreement, or support behind them.
- `informational` is for tools listed only because people ask about them, where the answer is that CSU does not license or provide them.
