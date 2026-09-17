# AI tool directory records

Each Markdown file mirrors one local WordPress `aicsu_tool` record.

- Edit the JSON-compatible front matter for ACF fields, highlights, taxonomy term slugs, and order.
- Edit the paragraph below the front matter for the WordPress excerpt shown on the card.
- Set `demo` to `false` after replacing sample claims with reviewed content.
- Keep `local_post_id` unchanged. `wordpress_status` is documentary; the importer preserves the current publication state.
- Run the local-only importer with:

```sh
source "/Users/david/Local Sites/multisite/local-wpcli-env.sh"
wp --path="/Users/david/Local Sites/multisite/app/public" --url=https://multisite.local/ai eval-file wordpress/tool-chooser/import-tools.php
```

The importer validates known taxonomies and prevents sensitive-data tags unless both approval fields agree. It does not publish tools. Production transfer remains a reviewed bulk export/import step.

Set `AICSU_TOOL_IMPORT_DRY_RUN=1` before the `wp` command to validate every file without changing WordPress.

## Data classification

`aicsu_data` now carries only CSU data-classification levels, which is what the chooser filters on:

| Slug | Filter label | Level |
|---|---|---|
| `level-1-public` | Public information | Level 1 |
| `level-2-internal` | Internal CSU work | Level 2 |
| `level-3-confidential` | Confidential records | Level 3 |
| `level-4-restricted` | Restricted or regulated | Level 4 |

Levels follow the CSU System Data Governance Policy (effective 2025-05-12). The importer refuses `level-4-restricted` on any tool, and requires `status: approved` plus `approved_for_sensitive: true` before it will accept `level-3-confidential`. Level 3 also requires Data Governance Steering Committee approval, which is a human step the importer cannot check.

The older regulation-flavored labels (`ferpa-student-records`, `my-own-files`, `sharepoint-teams`, and so on) moved to a non-filtering `data_examples` array. They give people the words they actually recognize on the detail page without implying CSU has issued a per-regulation approval.

## Status values

`approved`, `coming_soon`, `pilot`, `public_only`, `unsupported`, `informational`.

- `coming_soon` is for tools with a committed availability date but no way to request them yet.
- `unsupported` is for tools someone may install or use on their own, with no CSU review, agreement, or support behind them.
- `informational` is for tools listed only because people ask about them, where the answer is that CSU does not license or provide them.
