#!/usr/bin/env -S uv run --script
# /// script
# requires-python = ">=3.11"
# dependencies = ["pyyaml"]
# ///
"""Report lifecycle status and WordPress IDs for every sitemap page.

    uv run scripts/site_status.py            # all pages
    uv run scripts/site_status.py /tools/    # one section
"""

from __future__ import annotations

import sys
from collections import Counter

from sitemap_lib import LIFECYCLE, LIVE, ROOT, front_matter, pages


def problems(meta: dict) -> list[str]:
    status = str(meta.get("status") or "")
    found = []
    if status not in LIFECYCLE:
        found.append(f"unknown status '{status}'")
        return found
    stage = LIFECYCLE.index(status)
    if stage >= LIFECYCLE.index("built-local") and not meta.get("local_post_id"):
        found.append("no local_post_id")
    if status in LIVE and not meta.get("production_post_id"):
        found.append("no production_post_id")
    if meta.get("production_post_id") and not meta.get("production_modified"):
        found.append("no production_modified (drift guard)")
    return found


def main() -> int:
    section = sys.argv[1] if len(sys.argv) > 1 else None
    rows = [p for p in pages() if not section or p.section == section]
    issues = 0
    print(f"{'path':34} {'pri':4} {'status':17} {'local':>6} {'prod':>6}  notes")
    for p in rows:
        notes = problems(p.meta)
        issues += bool(notes)
        print(f"{p.path:34} {p.priority:4} {p.status:17} {str(p.meta.get('local_post_id') or '-'):>6} "
              f"{str(p.meta.get('production_post_id') or '-'):>6}  {'; '.join(notes)}")

    tools = [front_matter(f) for f in sorted((ROOT / "content/planned/tools/directory").glob("*.md")) if f.name != "README.md"]
    on_prod = sum(1 for t in tools if t.get("production_post_id"))
    print(f"\nTool records: {len(tools)} total, {on_prod} with production_post_id")
    print("By status: " + ", ".join(f"{s} {n}" for s, n in sorted(Counter(p.status for p in rows).items(), key=lambda x: LIFECYCLE.index(x[0]) if x[0] in LIFECYCLE else 99)))
    if issues:
        print(f"{issues} page(s) need attention.")
    return 1 if issues else 0


if __name__ == "__main__":
    sys.exit(main())
