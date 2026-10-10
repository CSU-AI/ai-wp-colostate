"""Shared loaders for sitemap.yml, planned-page front matter, and redirects."""

from __future__ import annotations

import csv
from dataclasses import dataclass
from pathlib import Path

import yaml

ROOT = Path(__file__).resolve().parents[1]
LIFECYCLE = ["captured", "drafting", "content-review", "approved", "built-local", "qa", "ready-production", "published"]
LIVE = {"ready-production", "published"}


@dataclass
class Page:
    path: str
    file: str
    priority: str
    section: str
    meta: dict

    @property
    def status(self) -> str:
        return str(self.meta.get("status") or "")


def front_matter(file: Path) -> dict:
    text = file.read_text(encoding="utf-8")
    if not text.startswith("---"):
        return {}
    block = text.split("---", 2)[1]
    return yaml.safe_load(block) or {}


def pages() -> list[Page]:
    sitemap = yaml.safe_load((ROOT / "sitemap.yml").read_text(encoding="utf-8"))
    found: list[Page] = []

    def walk(items: list[dict], section: str | None) -> None:
        for item in items:
            path = item["path"]
            sec = section or path
            file = ROOT / item["file"]
            meta = front_matter(file) if file.exists() else {"status": "missing-file"}
            found.append(Page(path, item["file"], item.get("priority", ""), sec, meta))
            walk(item.get("children", []), sec)

    walk(sitemap["pages"], None)
    return found


def redirects() -> list[dict]:
    with (ROOT / "data" / "redirects.csv").open(newline="", encoding="utf-8") as f:
        return list(csv.DictReader(f))
