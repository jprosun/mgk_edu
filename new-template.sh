#!/bin/bash
# ============================================================
# new-template.sh — Tạo package folder cho 1 template Elementor mới
# ============================================================
# Tư duy: master template (mgk-edu-elementor) = Hello Elementor child,
# PHP-native shell + Locked Core + Editable Shell. Mỗi biến thể = 1 slug
# clone từ master, đổi category + schema, generator seed lúc activate.
#
# Usage:
#   ./new-template.sh <slug> <category>
#
# Ví dụ:
#   ./new-template.sh mgk-fashion-001 fashion
#   ./new-template.sh mgk-fnb-001 fnb
#
# Kết quả:
#   packages/<slug>/<slug>/                ← source theme (edit ở đây)
#   packages/<slug>/<slug>/seed/manifest.json  ← đã điền slug + category
#   packages/<slug>/<slug>/schemas/<category>.php  ← schema rỗng nếu chưa có
#
# Sau khi tạo xong:
#   1. cp -a packages/<slug>/<slug>/. data/wp-content/themes/<slug>/
#   2. docker exec mgk-edu-el wp --allow-root --path=/var/www/html theme activate <slug>
#   3. Build/seed trong WP Admin (http://localhost:8091/wp-admin)
#   4. ./bundle.sh <slug>
# ============================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

SLUG="$1"
CATEGORY="$2"

# Master template được clone ra (Hello Elementor child đã hoàn chỉnh)
MASTER="$SCRIPT_DIR/packages/mgk-edu-elementor/mgk-edu-elementor"

# ─── Validate args ────────────────────────────────────────────────────────────
if [ -z "$SLUG" ] || [ -z "$CATEGORY" ]; then
    echo "Usage: ./new-template.sh <slug> <category>"
    echo ""
    echo "  slug      : tên duy nhất, dấu gạch ngang (vd: mgk-fashion-001)"
    echo "  category  : edu | fashion | fnb | beauty | retail ... (vd: fashion)"
    exit 1
fi

if [ ! -d "$MASTER" ]; then
    echo "ERROR: Không tìm thấy master template tại $MASTER"
    echo "  Cần có packages/mgk-edu-elementor/mgk-edu-elementor/ (Hello Elementor child gốc)."
    exit 1
fi

DEST="$SCRIPT_DIR/packages/$SLUG/$SLUG"

if [ -d "$DEST" ]; then
    echo "ERROR: $DEST đã tồn tại. Chọn slug khác hoặc xóa folder cũ."
    exit 1
fi

# ─── Clone master → package mới ──────────────────────────────────────────────
echo "[new-template] Cloning master → packages/$SLUG/$SLUG/"
mkdir -p "$DEST"
cp -a "$MASTER/." "$DEST/"

# Đổi Theme Name + Description trong style.css (giữ Template: hello-elementor)
sed -i "s/^Theme Name:.*/Theme Name: ${SLUG}/"                                  "$DEST/style.css"
sed -i "s/^Description:.*/Description: Margick template — ${SLUG} (${CATEGORY}). Elementor build, child of Hello Elementor./" "$DEST/style.css"

# Điền slug + category vào manifest.json (giữ required_plugins + version + seed_files)
python3 - "$DEST/seed/manifest.json" "$SLUG" "$CATEGORY" << 'PYEOF'
import json, sys
path, slug, category = sys.argv[1], sys.argv[2], sys.argv[3]
with open(path) as f:
    m = json.load(f)
m["slug"] = slug
m["category"] = category
with open(path, "w") as f:
    json.dump(m, f, indent=2)
print("[new-template] manifest.json: slug=%s category=%s" % (slug, category))
PYEOF

# ─── Schema rỗng cho category mới (đa ngành) ─────────────────────────────────
if [ ! -f "$DEST/schemas/${CATEGORY}.php" ]; then
    cat > "$DEST/schemas/${CATEGORY}.php" << EOF
<?php
/**
 * schemas/${CATEGORY}.php — CPT + ACF + taxonomy cho category: ${CATEGORY}
 * Template: ${SLUG}
 *
 * Đa ngành: file này định nghĩa DATA CORE riêng cho ngành ${CATEGORY}.
 * Widget Elementor + partial trong template-parts/ tái dùng; chỉ data đổi.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// TODO: register_post_type(), register_taxonomy(), acf_add_local_field_group()
EOF
    echo "[new-template] Created schemas/${CATEGORY}.php"
fi

echo ""
echo "✅ Template package created: packages/$SLUG/$SLUG/"
echo ""
echo "Bước tiếp theo:"
echo "  1. Copy vào runtime:"
echo "     cp -a packages/$SLUG/$SLUG/. data/wp-content/themes/$SLUG/"
echo ""
echo "  2. Activate (container phải đang chạy):"
echo "     docker exec mgk-edu-el wp --allow-root --path=/var/www/html theme activate $SLUG"
echo ""
echo "  3. Sinh layout Elementor + seed:"
echo "     docker exec mgk-edu-el wp --allow-root --path=/var/www/html mgk gen-layouts"
echo ""
echo "  4. WP Admin để chỉnh giao diện: http://localhost:8091/wp-admin"
echo ""
echo "  5. Khi xong: ./bundle.sh $SLUG"
