#!/usr/bin/env bash
#
# Readscope Booking Build & Packaging Utility
# Usage: ./bin/build-zip.sh
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"

echo "==> Executing Readscope Booking Distribution Builder..."
php "$SCRIPT_DIR/build-package.php"
