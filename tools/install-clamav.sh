#!/usr/bin/env bash

set -euo pipefail

APP_ROOT="/var/www/html/events.computer-wizard.com.au"
ENV_FILE="${APP_ROOT}/.env"

if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this script as root."
    exit 1
fi

if command -v apt-get >/dev/null 2>&1; then
    export DEBIAN_FRONTEND=noninteractive

    apt-get update
    apt-get install -y clamav clamav-freshclam

    systemctl stop clamav-freshclam >/dev/null 2>&1 || true
    freshclam
    systemctl enable --now clamav-freshclam >/dev/null 2>&1 || true

elif command -v dnf >/dev/null 2>&1; then
    dnf install -y clamav clamav-update
    freshclam

elif command -v yum >/dev/null 2>&1; then
    yum install -y clamav clamav-update
    freshclam

else
    echo "Unsupported package manager. Install ClamAV manually and set CLAMSCAN_BINARY in ${ENV_FILE}."
    exit 1
fi

CLAMSCAN_PATH="$(command -v clamscan || true)"

if [[ -z "${CLAMSCAN_PATH}" ]]; then
    echo "ClamAV install completed, but clamscan was not found on PATH."
    exit 1
fi

if grep -q '^CLAMSCAN_BINARY=' "${ENV_FILE}" 2>/dev/null; then
    sed -i "s#^CLAMSCAN_BINARY=.*#CLAMSCAN_BINARY=${CLAMSCAN_PATH}#" "${ENV_FILE}"
else
    printf '\nCLAMSCAN_BINARY=%s\n' "${CLAMSCAN_PATH}" >> "${ENV_FILE}"
fi

echo "ClamAV installed."
echo "Configured CLAMSCAN_BINARY=${CLAMSCAN_PATH} in ${ENV_FILE}."
echo "Next step:"
echo "  cd ${APP_ROOT} && php artisan optimize:clear"
