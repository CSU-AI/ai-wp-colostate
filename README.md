# AI @ CSU website rebuild

This repository supports a Local-first rebuild of [ai.colostate.edu](https://ai.colostate.edu/). Markdown is the editable content source. Approved content is built as Elementor drafts on the Local by Flywheel site, reviewed there, and transferred to production in bulk.

## Key locations

- `archive/`: raw snapshots and inventories from the current live site
- `content/legacy/`: readable Markdown extracted from the current live site
- `content/planned/`: editable replacement content
- `sitemap.yml`: tentative navigation structure
- `design/`: reusable page and writing guidance
- `wordpress/`: exports and migration/build notes
- `docs/`: existing AI tool chooser assets and implementation notes
- `scripts/archive_live_site.py`: repeatable live-site archive script

See `AGENTS.md` for operating rules.
