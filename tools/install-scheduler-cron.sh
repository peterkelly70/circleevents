#!/usr/bin/env bash

set -euo pipefail

APP_DIR="/var/www/html/events.computer-wizard.com.au"
CRON_LINE="* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"

current_crontab="$(crontab -l 2>/dev/null || true)"

if grep -Fqx "${CRON_LINE}" <<< "${current_crontab}"; then
    echo "Scheduler cron entry already exists."
    exit 0
fi

{
    if [[ -n "${current_crontab}" ]]; then
        printf '%s\n' "${current_crontab}"
    fi
    printf '%s\n' "${CRON_LINE}"
} | crontab -

echo "Installed scheduler cron entry:"
echo "${CRON_LINE}"
