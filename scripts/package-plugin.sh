#!/usr/bin/env bash
# Package the AI @ CSU Tool Chooser plugin. Ships only runtime files; local-only
# helpers (import, export, verify, samples, tests) never leave the repo.
#
#   scripts/package-plugin.sh zip     -> dist/aicsu-tool-chooser-<version>.zip for production
#   scripts/package-plugin.sh local   -> sync into the Local by Flywheel plugins folder
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/wordpress/tool-chooser"
SLUG="aicsu-tool-chooser"
LOCAL_PLUGINS="${LOCAL_PLUGINS:-$HOME/Local Sites/multisite/app/public/wp-content/plugins}"
FILES=(aicsu-tool-chooser.php chooser.css chooser.js acf-json)

version="$(sed -n 's/^ \* Version: *//p' "$SRC/aicsu-tool-chooser.php")"
stage="$(mktemp -d)"
trap 'rm -rf "$stage"' EXIT
mkdir "$stage/$SLUG"
for f in "${FILES[@]}"; do cp -R "$SRC/$f" "$stage/$SLUG/"; done

case "${1:-}" in
  zip)
    mkdir -p "$ROOT/dist"
    out="$ROOT/dist/$SLUG-$version.zip"
    rm -f "$out"
    (cd "$stage" && zip -qr "$out" "$SLUG")
    echo "$out"
    ;;
  local)
    rsync -a --delete "$stage/$SLUG/" "$LOCAL_PLUGINS/$SLUG/"
    echo "Synced $SLUG $version to $LOCAL_PLUGINS/$SLUG"
    ;;
  *)
    echo "usage: $0 zip|local" >&2
    exit 2
    ;;
esac
