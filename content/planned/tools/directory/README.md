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
