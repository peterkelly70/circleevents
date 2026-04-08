#!/usr/bin/env bash

set -euo pipefail

APP_ROOT="/var/www/html/events.computer-wizard.com.au"

chgrp -R www-data \
    "$APP_ROOT/database" \
    "$APP_ROOT/storage" \
    "$APP_ROOT/bootstrap/cache"

chmod 2770 \
    "$APP_ROOT/database" \
    "$APP_ROOT/storage" \
    "$APP_ROOT/storage/framework" \
    "$APP_ROOT/storage/logs" \
    "$APP_ROOT/bootstrap/cache"

chmod 660 "$APP_ROOT/database/database.sqlite"

stat -c '%U %G %a %n' \
    "$APP_ROOT/database" \
    "$APP_ROOT/database/database.sqlite" \
    "$APP_ROOT/storage" \
    "$APP_ROOT/storage/framework" \
    "$APP_ROOT/storage/logs" \
    "$APP_ROOT/bootstrap/cache"
