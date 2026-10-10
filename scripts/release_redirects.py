#!/usr/bin/env -S uv run --script
# /// script
# requires-python = ">=3.11"
# dependencies = ["pyyaml"]
# ///
"""Build a Redirection plugin import CSV for one section release.

    uv run scripts/release_redirects.py /tools/ > wordpress/releases/2026-10-15-tools-redirects.csv

Includes rows from data/redirects.csv whose destination is in the section and whose
status is `approved`. Refuses to redirect to a page that is not ready-production or
published, so a release never points visitors at a missing page. Import the output
in Tools > Redirection > Import on the ai subsite, then mark the rows `live`.
"""

from __future__ import annotations

import csv
import sys

from sitemap_lib import LIVE, pages, redirects


def main() -> int:
    if len(sys.argv) != 2:
        print(__doc__, file=sys.stderr)
        return 2
    section = sys.argv[1]
    by_path = {p.path: p for p in pages()}
    rows, errors = [], []
    for r in redirects():
        dest = r["destination_url"]
        target = by_path.get(dest)
        if not target or target.section != section:
            continue
        if r["status"] != "approved":
            print(f"skip ({r['status']}): {r['source_url']} -> {dest}", file=sys.stderr)
            continue
        if target.status not in LIVE:
            errors.append(f"{r['source_url']} -> {dest}: destination is '{target.status}'")
            continue
        rows.append([r["source_url"], dest, "0", "301"])

    if errors:
        print("Refusing to write redirects to pages that are not live:", *errors, sep="\n  ", file=sys.stderr)
        return 1
    out = csv.writer(sys.stdout, lineterminator="\n")
    out.writerow(["source", "target", "regex", "code"])
    out.writerows(rows)
    print(f"{len(rows)} redirect(s) for {section}", file=sys.stderr)
    return 0


if __name__ == "__main__":
    sys.exit(main())
