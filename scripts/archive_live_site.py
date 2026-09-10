#!/usr/bin/env python3
"""Archive the public AI @ CSU WordPress site and extract editable Markdown."""

from __future__ import annotations

import csv
import html
import json
import re
import shutil
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urljoin, urlparse


ROOT = Path(__file__).resolve().parents[1]
SITE = "https://ai.colostate.edu/"
API = urljoin(SITE, "wp-json/wp/v2/")
CAPTURED_AT = datetime.now(timezone.utc).replace(microsecond=0).isoformat()
CURL = shutil.which("curl") or "/usr/bin/curl"
PANDOC = shutil.which("pandoc") or "/opt/homebrew/bin/pandoc"


def fetch(url: str) -> bytes:
    return subprocess.run(
        [CURL, "-L", "--fail", "--silent", "--show-error", url],
        check=True,
        capture_output=True,
    ).stdout


def api_collection(kind: str) -> list[dict]:
    items: list[dict] = []
    page = 1
    fields = "id,slug,link,title,content,excerpt,date,modified,parent,status"
    while True:
        url = f"{API}{kind}?status=publish&per_page=100&page={page}&_fields={fields}"
        try:
            batch = json.loads(fetch(url))
        except subprocess.CalledProcessError:
            break
        if not batch:
            break
        items.extend(batch)
        if len(batch) < 100:
            break
        page += 1
    return items


def media_collection() -> list[dict]:
    items: list[dict] = []
    page = 1
    fields = "id,slug,link,source_url,title,alt_text,caption,mime_type,date,modified"
    while True:
        url = f"{API}media?per_page=100&page={page}&_fields={fields}"
        try:
            batch = json.loads(fetch(url))
        except subprocess.CalledProcessError:
            break
        if not batch:
            break
        items.extend(batch)
        if len(batch) < 100:
            break
        page += 1
    return items


def relative_path(url: str) -> str:
    path = urlparse(url).path.strip("/")
    return path or "home"


def archive_path(url: str) -> Path:
    path = urlparse(url).path.strip("/")
    return ROOT / "archive" / "html" / path / "index.html"


def markdown_path(url: str) -> Path:
    path = relative_path(url)
    return ROOT / "content" / "legacy" / f"{path}.md"


def rendered(value: object) -> str:
    if isinstance(value, dict):
        return html.unescape(str(value.get("rendered", ""))).strip()
    return html.unescape(str(value or "")).strip()


def markdown_from_html(source: str) -> str:
    source = re.sub(r"<(script|style|noscript|form)\b.*?</\1>", "", source, flags=re.I | re.S)
    source = re.sub(r"<(?:div|section)\b[^>]*>", "", source, flags=re.I)
    source = re.sub(r"</(?:div|section)>", "\n", source, flags=re.I)
    source = re.sub(r"</?span\b[^>]*>", "", source, flags=re.I)
    source = re.sub(
        r"<a\b[^>]*\bhref=([\"'])(.*?)\1[^>]*>",
        lambda match: f'<a href="{match.group(2)}">',
        source,
        flags=re.I | re.S,
    )
    source = re.sub(r"<a\b(?![^>]*href)[^>]*>", "<a>", source, flags=re.I)
    result = subprocess.run(
        [PANDOC, "--from=html", "--to=gfm", "--wrap=none"],
        input=source.encode(),
        check=True,
        capture_output=True,
    ).stdout.decode()
    result = re.sub(r"\n{3,}", "\n\n", result).strip()
    return result + "\n" if result else ""


def yaml_string(value: object) -> str:
    return json.dumps(str(value or ""), ensure_ascii=False)


def write_legacy(item: dict, post_type: str) -> None:
    target = markdown_path(item["link"])
    target.parent.mkdir(parents=True, exist_ok=True)
    title = rendered(item.get("title"))
    body = markdown_from_html(rendered(item.get("content")))
    front_matter = [
        "---",
        f"title: {yaml_string(title)}",
        f"slug: {yaml_string(item.get('slug'))}",
        f"source_url: {yaml_string(item.get('link'))}",
        f"source_post_id: {item.get('id')}",
        f"source_post_type: {post_type}",
        f"source_modified: {yaml_string(item.get('modified'))}",
        f"captured_at: {yaml_string(CAPTURED_AT)}",
        "status: captured",
        "---",
        "",
        f"# {title}",
        "",
    ]
    target.write_text("\n".join(front_matter) + body, encoding="utf-8")


def extract_links(source: str, source_url: str) -> list[str]:
    links = re.findall(r"\bhref=[\"']([^\"'#]+)", source, flags=re.I)
    return sorted({urljoin(source_url, html.unescape(link)) for link in links})


def main() -> None:
    pages = api_collection("pages")
    posts = api_collection("posts")
    records = [(item, "page") for item in pages] + [(item, "post") for item in posts]
    if not records:
        raise RuntimeError("WordPress returned no published pages or posts")

    crawl_rows: list[dict] = []
    link_rows: list[dict] = []
    for item, post_type in records:
        url = item["link"]
        raw = fetch(url)
        html_target = archive_path(url)
        html_target.parent.mkdir(parents=True, exist_ok=True)
        html_target.write_bytes(raw)
        write_legacy(item, post_type)
        crawl_rows.append(
            {
                "url": url,
                "post_type": post_type,
                "wordpress_id": item["id"],
                "title": rendered(item.get("title")),
                "slug": item.get("slug", ""),
                "parent_id": item.get("parent", ""),
                "published": item.get("date", ""),
                "modified": item.get("modified", ""),
                "html_file": str(html_target.relative_to(ROOT)),
                "markdown_file": str(markdown_path(url).relative_to(ROOT)),
                "captured_at": CAPTURED_AT,
            }
        )
        for destination in extract_links(raw.decode(errors="replace"), url):
            link_rows.append(
                {
                    "source_url": url,
                    "destination_url": destination,
                    "internal": urlparse(destination).netloc == urlparse(SITE).netloc,
                }
            )

    archive_dir = ROOT / "archive"
    archive_dir.mkdir(exist_ok=True)
    with (archive_dir / "crawl.csv").open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=list(crawl_rows[0].keys()))
        writer.writeheader()
        writer.writerows(sorted(crawl_rows, key=lambda row: row["url"]))

    data_dir = ROOT / "data"
    data_dir.mkdir(exist_ok=True)
    with (data_dir / "links.csv").open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=["source_url", "destination_url", "internal"])
        writer.writeheader()
        writer.writerows(sorted(link_rows, key=lambda row: (row["source_url"], row["destination_url"])))

    media = media_collection()
    media_rows = [
        {
            "wordpress_id": item["id"],
            "source_url": item.get("source_url", ""),
            "title": rendered(item.get("title")),
            "alt_text": item.get("alt_text", ""),
            "caption": rendered(item.get("caption")),
            "mime_type": item.get("mime_type", ""),
            "published": item.get("date", ""),
            "modified": item.get("modified", ""),
        }
        for item in media
    ]
    with (archive_dir / "media-inventory.csv").open("w", newline="", encoding="utf-8") as handle:
        fieldnames = ["wordpress_id", "source_url", "title", "alt_text", "caption", "mime_type", "published", "modified"]
        writer = csv.DictWriter(handle, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(media_rows)

    print(f"Archived {len(pages)} pages, {len(posts)} posts, and {len(media)} media records.")


def self_check() -> None:
    assert relative_path(SITE) == "home"
    assert relative_path(urljoin(SITE, "csu-ai-hub/csu-gpt/")) == "csu-ai-hub/csu-gpt"
    converted = markdown_from_html("<div class='elementor'><h2>Hello</h2><script>bad()</script><p>World</p></div>")
    assert converted == "## Hello\n\nWorld\n"
    print("Self-check passed.")


if __name__ == "__main__":
    self_check() if "--self-check" in sys.argv else main()
