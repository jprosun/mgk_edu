#!/bin/bash
# run.sh — Khởi động workspace mgk_edu_elementor (image production Margick)
# Port: 8091  ·  Builder: Elementor (FREE)  ·  Theme: mgk-edu-elementor (Hello Elementor child)
# Credentials: test / changeme (DB)
#
# Usage:
#   ./run.sh          # foreground
#   ./run.sh -d       # detached, sau đó ./restore-db.sh nếu cần nạp lại DB
#
# data/ đã chứa sẵn full WP core + wp-content (đồng bộ từ bản thử nghiệm).
# DB nạp từ db.sql qua ./restore-db.sh khi cần khôi phục state.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DB_PASS="${DATABASE_PASSWORD:-changeme}"

if [ ! -d "$SCRIPT_DIR/data" ]; then
    echo "[run.sh] ERROR: ./data không tồn tại. Workspace chưa được dựng đầy đủ."
    exit 1
fi

echo "[run.sh] Starting mgk-edu-el on http://localhost:8091 ..."
DATABASE_PASSWORD="$DB_PASS" docker compose -f "$SCRIPT_DIR/docker-compose.yml" up "$@"

# Detached: nhắc restore DB
case " $* " in
    *" -d "*|*" --detach "*)
        echo ""
        echo "[run.sh] Container đang chạy nền. Nếu DB trống, nạp lại bằng:"
        echo "         ./restore-db.sh"
        ;;
esac
