#!/usr/bin/env bash
# =============================================================================
# Muscat Fragrances – Reset admin password (run on the Lightsail server)
# Usage:
#   sudo bash deploy/reset-admin-password.sh admin@example.com
# =============================================================================
set -euo pipefail

APP_DIR="/var/www/muscatfr"
PHP="php8.3"
EMAIL="${1:-}"

if [[ -z "${EMAIL}" ]]; then
    echo "Usage: sudo bash reset-admin-password.sh <admin-email>"
    exit 1
fi

read -rsp "New password (min 14 chars): " NEW_PASS
echo
read -rsp "Confirm password: "           CONFIRM
echo

if [[ "${NEW_PASS}" != "${CONFIRM}" ]]; then
    echo "Passwords do not match. Aborting."
    exit 1
fi

if [[ ${#NEW_PASS} -lt 14 ]]; then
    echo "Password must be at least 14 characters. Aborting."
    exit 1
fi

cd "${APP_DIR}"

${PHP} artisan tinker --execute="
\$user = App\Models\User::where('email', '${EMAIL}')->firstOrFail();
\$user->password = '${NEW_PASS}';
\$user->save();
echo 'Password updated for: ' . \$user->email . PHP_EOL;
"

echo "Done — you can now log in at https://muscatfragrances.com/admin/login"
