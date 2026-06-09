#!/bin/bash
# ============================================================
# bundle.sh — Đóng gói theme Elementor thành zip chuẩn Margick
# ============================================================
# Usage:
#   ./bundle.sh <slug> [version]
#
# Ví dụ:
#   ./bundle.sh mgk-edu-elementor
#   ./bundle.sh mgk-edu-elementor v1.4
#
# Kết quả:
#   packages/<slug>/<slug>-<version>.zip   (theme zip cài qua WP Admin)
#
# Trước khi chạy:
#   - Đã sync code mới nhất từ runtime về packages/:
#     cp -a data/wp-content/themes/<slug>/. packages/<slug>/<slug>/
#   - manifest.json đã liệt kê seed_files + required_plugins
# ============================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

SLUG="$1"
VERSION="${2:-}"

if [ -z "$SLUG" ]; then
    echo "Usage: ./bundle.sh <slug> [version]"
    exit 1
fi

SRC="$SCRIPT_DIR/packages/$SLUG/$SLUG"

if [ ! -d "$SRC" ]; then
    echo "ERROR: $SRC không tồn tại. Chạy ./new-template.sh trước, hoặc sync runtime → packages."
    exit 1
fi

# Nếu không truyền version, lấy từ manifest.json (key "version")
if [ -z "$VERSION" ]; then
    VERSION=$(python3 -c "import json;print(json.load(open('$SRC/seed/manifest.json')).get('version','v0.1'))" 2>/dev/null || echo "v0.1")
fi

ZIP="$SCRIPT_DIR/packages/$SLUG/${SLUG}-${VERSION}.zip"

# ─── Kiểm tra files bắt buộc ─────────────────────────────────────────────────
echo "[bundle] Checking required files..."
MISSING=0
for f in "style.css" "functions.php" "seed/manifest.json"; do
    if [ ! -f "$SRC/$f" ]; then
        echo "  ✗ MISSING: $f"
        MISSING=1
    else
        echo "  ✓ $f"
    fi
done

# Hello Elementor child: style.css PHẢI khai báo Template: hello-elementor
if ! grep -qi "^Template:[[:space:]]*hello-elementor" "$SRC/style.css"; then
    echo "  ✗ style.css thiếu 'Template: hello-elementor' (phải là Hello Elementor child)"
    MISSING=1
else
    echo "  ✓ Template: hello-elementor"
fi

# manifest phải khai required_plugins (elementor tối thiểu)
if ! python3 -c "import json,sys; m=json.load(open('$SRC/seed/manifest.json')); sys.exit(0 if any('elementor' in p for p in m.get('required_plugins',[])) else 1)" 2>/dev/null; then
    echo "  ⚠ WARNING: manifest.json chưa khai required_plugins có elementor"
else
    echo "  ✓ required_plugins: elementor"
fi

# Cảnh báo nếu seed_files trong manifest không khớp file thực
python3 - "$SRC" << 'PYEOF'
import json, os, sys
src = sys.argv[1]
m = json.load(open(os.path.join(src, "seed/manifest.json")))
for sf in m.get("seed_files", []):
    p = os.path.join(src, "seed", sf)
    if not os.path.isfile(p):
        print("  ⚠ WARNING: seed_files khai '%s' nhưng file không tồn tại" % sf)
PYEOF

if [ "$MISSING" -eq 1 ]; then
    echo "ERROR: Files bắt buộc bị thiếu."
    exit 1
fi

# ─── Tạo zip (loại trừ rác + Elementor cache) ────────────────────────────────
rm -f "$ZIP"
cd "$SCRIPT_DIR/packages/$SLUG"
zip -r "${SLUG}-${VERSION}.zip" "$SLUG/" \
    --exclude "*.DS_Store" \
    --exclude "*/__pycache__/*" \
    --exclude "*/._*" \
    --exclude "*/node_modules/*" \
    --exclude "*/.git/*"
cd "$SCRIPT_DIR"

SIZE=$(du -sh "$ZIP" | cut -f1)
echo ""
echo "✅ Bundle: packages/$SLUG/${SLUG}-${VERSION}.zip ($SIZE)"

SIZE_BYTES=$(stat -c%s "$ZIP" 2>/dev/null || stat -f%z "$ZIP" 2>/dev/null)
if [ "$SIZE_BYTES" -gt 2097152 ]; then
    echo "⚠ WARNING: File > 2MB — vượt giới hạn Margick API (cân nhắc tách seed/uploads)"
else
    echo "✓ Size OK (< 2MB)"
fi
