# Planned content

Create one Markdown file per proposed page. Use this front matter:

```yaml
---
title: Page title
slug: page-slug
status: drafting
parent:
source_urls: []
redirect_from: []
template: standard
owner:
reviewers: []
last_reviewed:
local_post_id:
production_post_id:
production_modified:
---
```

Page copy below the front matter is the editable source used to build Elementor drafts.

`production_modified` is the production page's modified time (GMT) after the last push. The push compares it before overwriting so edits made directly on production are not lost. Check progress with `uv run scripts/site_status.py`.
