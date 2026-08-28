#!/usr/bin/env bash
set -euo pipefail

APP_USER="${APP_USER:-$(id -un)}"
WEB_GROUP="${WEB_GROUP:-www-data}"
TARGETS=("storage" "bootstrap/cache")

if ! getent group "$WEB_GROUP" >/dev/null; then
    echo "Group '$WEB_GROUP' tidak ditemukan. Set WEB_GROUP sesuai web server, misalnya nginx atau apache."
    exit 1
fi

for target in "${TARGETS[@]}"; do
    if [[ ! -d "$target" ]]; then
        echo "Direktori '$target' tidak ditemukan."
        exit 1
    fi
done

chown -R "$APP_USER:$WEB_GROUP" "${TARGETS[@]}"
find "${TARGETS[@]}" -type d -exec chmod 2775 {} +
find "${TARGETS[@]}" -type f -exec chmod 664 {} +

echo "Permission Laravel storage/cache sudah diset untuk $APP_USER:$WEB_GROUP."
