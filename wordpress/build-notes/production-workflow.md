# Production workflow

How work moves from this repo and the Local by Flywheel site to `ai.colostate.edu`, a single subsite on a shared CSU multisite network.

## Principles

- Local is where pages are built and reviewed. Production receives finished work.
- The site launches section by section. Old pages stay live until their replacements are published and redirected.
- Nothing in this workflow touches the shared network: no theme files, no network-activated plugins, no network settings.
- Every production change is repeatable from this repo.

## Shared network boundaries

`ai.colostate.edu` shares its theme, plugins, and core with hundreds of sites.

| Never | Why |
|---|---|
| Edit, upload, or update `csu-theme` files | Shared by every site. Updates belong to the network's normal theme process. |
| Network-activate `aicsu-tool-chooser` | It registers the `aicsu_tool` post type and taxonomies. Network activation would add them to every site. Activate it on the ai subsite only. |
| Change the ai subsite's active theme or network settings | Out of scope for content work. |
| Use super admin credentials for MCP or scripts | A leaked super admin password reaches every site. |
| Sync ACF JSON into the database through "Sync available" on production | Not harmful, but makes the database a second source of truth. Leave the JSON as the schema. |

Per-site storage is safe: pages, posts, media in `uploads/sites/<id>/`, Elementor kit and Theme Builder templates, menus, Redirection rules, and ACF data all live in the ai subsite's own tables. ACF's default JSON save path on this network is `csu-theme/acf-json`; the plugin overrides it for its own keys so schema never lands in the theme.

## What moves, and how

| Layer | Source of truth | Path to production |
|---|---|---|
| Code: tool chooser plugin | `wordpress/tool-chooser/` | `scripts/package-plugin.sh zip`, then upload as super admin in the browser and activate on the ai subsite only |
| Schema: `aicsu_tool`, taxonomies, Tool Details fields | `wordpress/tool-chooser/acf-json/` | Ships inside the plugin zip |
| Design system: Elementor kit, loop templates, reusable sections | Local Elementor | Production MCP (`update-global-colors`, `update-global-typography`, `import-template`) |
| Pages and tool records | `content/planned/` Markdown, built in Local Elementor | Production MCP push, as drafts |
| Redirects | `data/redirects.csv` | `scripts/release_redirects.py`, imported into Redirection |
| News posts (after launch) | Production | Edited directly in wp-admin; not mirrored in Markdown |

### Schema changes

1. Edit the field group, post type, or taxonomy in Local wp-admin under ACF.
2. Run the exporter so the repo matches Local:

   ```sh
   source "/Users/david/Local Sites/multisite/local-wpcli-env.sh"
   wp --path="/Users/david/Local Sites/multisite/app/public" --url=https://multisite.local/ai eval-file wordpress/tool-chooser/export-acf-json.php
   ```

3. Bump the plugin `Version`, commit, then `scripts/package-plugin.sh local` to keep Local's copy in step.
4. Ship with the next plugin release.

## Production MCP

- Connection name: `emcp-prod-ai`. Never reuse the Local connection name.
- Account: a dedicated `ai-mcp` user, Administrator on the ai subsite only, no role on any other site, not a super admin. Without `unfiltered_html`, it cannot save script or iframe markup; add embeds yourself.
- Ask that `read-file`, `search-files`, `list-directory`, and `add-custom-js` be disabled on production. File tools on a shared network can expose `wp-config.php` and other sites.
- Keep it read-only by default. Enable writes only for a push session, and say so explicitly in that session.
- Before the first push, run `detect-elementor-version` and compare plugin and theme versions with Local.

## Page lifecycle

`drafting` → `content-review` → `approved` → `built-local` → `qa` → `ready-production` → `published`

1. **Draft** the Markdown in `content/planned/`.
2. **Approve** when the copy is final (`approved`).
3. **Build** an Elementor draft on Local. Record `local_post_id` (`built-local`).
4. **Review** desktop, tablet, and mobile on Local. Run accessibility checks (`qa`).
5. **Push** to production as a draft (see below). Record `production_post_id` and `production_modified` (`ready-production`).
6. **Publish** during the section's release (`published`).

Check progress with:

```sh
uv run scripts/site_status.py           # whole site
uv run scripts/site_status.py /tools/   # one section
```

## Pushing a page

1. Read the Local page with `export-page` or `get-page-structure`.
2. Upload new media to production. Record each mapping in `wordpress/media-map.csv` (`local_id,production_id,production_url`) so it is uploaded once.
3. Rewrite the element tree: `https://multisite.local/ai` becomes `https://ai.colostate.edu`, and every image `id` and `url` uses the production values from the media map.
4. **Drift guard.** If the page already has a `production_post_id`, read its modified time on production. If it differs from `production_modified` in the front matter, someone changed it there. Stop, pull that change into Markdown and Local, then continue.
5. Create or update the production page as a **draft** with the same slug and parent as the sitemap.
6. Record the new `production_modified`. Preview the draft on production under the real theme.

Tool records follow the same path: create the `aicsu_tool` post, set terms, and set ACF fields with their field keys. The keys match because the schema ships as JSON.

## Section release

One release per top-level sitemap section, for example `/tools/`.

1. Confirm every page in the section is `ready-production`: `uv run scripts/site_status.py /tools/`.
2. Mark the section's rows in `data/redirects.csv` as `approved` once reviewed.
3. Publish the section's production drafts.
4. Update the production menu. Mixed old and new navigation is expected during the transition.
5. Generate and import redirects:

   ```sh
   uv run scripts/release_redirects.py /tools/ > wordpress/releases/YYYY-MM-DD-tools-redirects.csv
   ```

   Import in Tools > Redirection > Import on the ai subsite. Confirm with the MCP `list-redirects`. Mark the rows `live`.
6. Unpublish or draft the legacy pages that now redirect. Do not delete them until the section has been stable for a few weeks.
7. Run `find-broken-links`, check media URLs, forms, and the chooser.
8. Set the section's pages to `published` and write `wordpress/releases/YYYY-MM-DD-<section>.md` with what changed.

The homepage swap is its own release, once enough sections are live to support the new home.

## Keeping Local in parity

- Same CSU Theme, Elementor, Elementor Pro, ACF Pro, and Redirection versions as production. Atomic elements were enabled on production on 2026-10-09; confirm with `detect-elementor-version` and an editor check on the first production MCP connection.
- Compare versions before each release.
- Keep a separate `ai-live` reference subsite on Local for the current production content. Do not import it over the `ai` build subsite.

## Future editors

Production is push-only from this repo for structural pages. The drift guard catches outside edits. If other editors are added later, either restrict them to news posts or accept that their page edits must be pulled back into Markdown before the next push.
