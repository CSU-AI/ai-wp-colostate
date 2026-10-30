# 2026-10-30: Tools slug change and redirects

Production page changes, made through the production MCP:

| Page | ID | Before | After |
|---|---|---|---|
| Choosing an AI Tool (the AI Tools menu item) | 800 | `/ai-tools/` | `/tools/` (slug `tools`) |
| Which AI Tool Should I Use? | 796 | `/ai-tools/help-me-choose/` | `/tools/help-me-choose/` (still a child of 800) |
| AI Agents (draft) | 814 | no parent | child of 800, will publish at `/tools/agents/` |

The "AI Tools" menu item links to page 800 by ID, so it follows the new slug.

## Redirects (`2026-10-30-tools-redirects.csv`)

Imported under Tools > Redirection > Import on the ai subsite on 2026-10-30 and verified with HTTP requests: `/ai-tools/`, `/ai-tools/help-me-choose/`, and `/ai-tools/anything-else/` all return 301 to the matching `/tools/` paths. The MCP `list-redirects` tool returns nothing on this site even with live rules (it does not read Redirection's rules), so verify with HTTP requests instead.

1. `/ai-tools/` to `/tools/`
2. `/ai-tools/help-me-choose/` to `/tools/help-me-choose/`
3. `^/ai-tools/(.*)$` to `/tools/$1` (regex catch-all for any other path under `/ai-tools/`; keep it last so the exact rows win)

All are 301.

## Checks after import

- `/ai-tools/` and `/ai-tools/help-me-choose/` return 301 to the new URLs.
- `/tools/` and `/tools/help-me-choose/` return 200.
- If the old URLs still return 200, purge the WP Engine cache (Clear WPE Cache in the admin bar).
- Page 800 still links to `/ai-tools/help-me-choose/` in its button and two text links, and page 796's breadcrumb links to `/ai-tools/`. They redirect correctly now; update them to the new paths when convenient to avoid the extra hop.
