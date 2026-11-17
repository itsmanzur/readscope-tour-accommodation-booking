#!/usr/bin/env bash
#
# Readscope Booking Version Bump Utility
# Usage: ./bin/bump-version.sh <new_version>
# Example: ./bin/bump-version.sh 1.3.0
#

set -e

NEW_VERSION=$1

if [ -z "$NEW_VERSION" ]; then
    echo "Error: Version number required."
    echo "Usage: $0 <new_version>"
    exit 1
fi

# Validate semantic version format (e.g., 1.3.0 or 1.3.0-beta1)
if ! [[ "$NEW_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9.]+)?$ ]]; then
    echo "Error: Invalid version format '$NEW_VERSION'. Expected format like 1.3.0 or 1.3.0-beta1"
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"

echo "==> Bumping Readscope Booking version to: $NEW_VERSION"

# 1. Update the-readscope-tour-and-accommodation-booking.php header
sed -i -E "s/(\* Version:[[:space:]]+)[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9.]+)?/\1$NEW_VERSION/" "$ROOT_DIR/the-readscope-tour-and-accommodation-booking.php"

# 2. Update define('READSCOPE_VERSION', 'x.y.z') in the-readscope-tour-and-accommodation-booking.php
sed -i -E "s/(define\('READSCOPE_VERSION',[[:space:]]*')[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9.]+)?('\);)/\1$NEW_VERSION\3/" "$ROOT_DIR/the-readscope-tour-and-accommodation-booking.php"

# 3. Update readme.txt Stable tag
sed -i -E "s/(Stable tag:[[:space:]]+)[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9.]+)?/\1$NEW_VERSION/" "$ROOT_DIR/readme.txt"

# 4. Update README.md version badge
sed -i -E "s/(badge\/version-)[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9.]+)?(-blue\.svg)/\1$NEW_VERSION\3/" "$ROOT_DIR/README.md"

echo "==> Successfully updated version strings in:"
echo "    - the-readscope-tour-and-accommodation-booking.php (Plugin Header & READSCOPE_VERSION constant)"
echo "    - readme.txt (Stable tag)"
echo "    - README.md (Version badge)"
echo "==> Done!"
