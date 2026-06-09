#!/bin/bash
# restore-db.sh — Nạp db.sql vào container đang chạy + chỉnh siteurl về port hiện tại
# Chạy sau ./run.sh -d khi muốn load lại state đã lưu.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DB_PASS="${DATABASE_PASSWORD:-changeme}"
APP_CONTAINER="mgk-edu-el"
SITE_URL="http://localhost:8091"

if [ ! -f "$SCRIPT_DIR/db.sql" ]; then
    echo "[restore-db] Không có db.sql."
    exit 1
fi

if ! docker inspect "$APP_CONTAINER" --format "{{.State.Running}}" 2>/dev/null | grep -q "true"; then
    echo "[restore-db] ERROR: Container $APP_CONTAINER chưa chạy. Chạy ./run.sh -d trước."
    exit 1
fi

echo "[restore-db] Restoring db.sql → magicaklocaldb..."
docker exec -i "$APP_CONTAINER" mariadb -utest -p"$DB_PASS" magicaklocaldb < "$SCRIPT_DIR/db.sql"

# db.sql gốc dump từ sandbox cũ (port 8090) — chỉnh URL về 8091 + flush cache
echo "[restore-db] Đồng bộ siteurl → $SITE_URL ..."
docker exec "$APP_CONTAINER" wp --allow-root --path=/var/www/html option update home    "$SITE_URL" >/dev/null 2>&1 || true
docker exec "$APP_CONTAINER" wp --allow-root --path=/var/www/html option update siteurl "$SITE_URL" >/dev/null 2>&1 || true
docker exec "$APP_CONTAINER" wp --allow-root --path=/var/www/html search-replace "http://localhost:8090" "$SITE_URL" --all-tables --skip-columns=guid >/dev/null 2>&1 || true
docker exec "$APP_CONTAINER" wp --allow-root --path=/var/www/html cache flush >/dev/null 2>&1 || true

echo "[restore-db] Done. Site: $SITE_URL"
