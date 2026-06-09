#!/bin/bash
# rebuild.sh — Lưu state hiện tại của workspace để bàn giao / version bằng Git.
#   1. Dump DB sống → db.sql
#   2. Sync theme từ runtime (data/.../themes/mgk-edu-elementor) → packages/ (source)
#
# Dùng khi muốn "đóng băng" state đang chạy trước khi commit hoặc bundle.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DB_PASS="${DATABASE_PASSWORD:-changeme}"
APP_CONTAINER="mgk-edu-el"
THEME="mgk-edu-elementor"

if ! docker inspect "$APP_CONTAINER" --format "{{.State.Running}}" 2>/dev/null | grep -q "true"; then
    echo "[rebuild] ERROR: Container $APP_CONTAINER chưa chạy. Chạy ./run.sh -d trước."
    exit 1
fi

# ─── 1. Dump DB ──────────────────────────────────────────────────────────────
echo "[rebuild] Dumping DB → db.sql ..."
docker exec "$APP_CONTAINER" sh -c "mariadb-dump -utest -p$DB_PASS --no-tablespaces --single-transaction magicaklocaldb" > "$SCRIPT_DIR/db.sql"
echo "[rebuild]   $(du -h "$SCRIPT_DIR/db.sql" | cut -f1)  ($(grep -c 'CREATE TABLE' "$SCRIPT_DIR/db.sql") tables)"

# ─── 2. Sync theme runtime → packages (source of truth) ──────────────────────
RUNTIME_THEME="$SCRIPT_DIR/data/wp-content/themes/$THEME"
PKG_THEME="$SCRIPT_DIR/packages/$THEME/$THEME"
if [ -d "$RUNTIME_THEME" ] && [ -d "$PKG_THEME" ]; then
    echo "[rebuild] Syncing theme runtime → packages ..."
    rsync -a --delete \
        --exclude '.git' --exclude 'node_modules' \
        "$RUNTIME_THEME/" "$PKG_THEME/"
    echo "[rebuild]   diff check:"
    diff -rq "$RUNTIME_THEME" "$PKG_THEME" && echo "[rebuild]   ✓ runtime == packages"
else
    echo "[rebuild] ⚠ Bỏ qua sync theme (thiếu $RUNTIME_THEME hoặc $PKG_THEME)"
fi

echo "[rebuild] Done. State đã lưu — sẵn sàng commit / bundle."
