#!/usr/bin/env bash
#
# Readscope Booking Release Distribution Builder
# Usage: ./bin/build-release.sh
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"

echo "==> Building Readscope Booking release package..."
php "$SCRIPT_DIR/build-package.php"

echo "==> Release build completed successfully."
