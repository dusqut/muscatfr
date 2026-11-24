#!/usr/bin/env bash
# =============================================================================
# Muscat Fragrances – Deploy / Update script
# Run on the Lightsail server inside the app directory:
#   cd /var/www/muscatfr && sudo -u www-data bash deploy/deploy.sh
# Or run remotely from your local machine (adjust SSH alias as needed):
#   ssh ubuntu@<SERVER_IP> "cd /var/www/muscatfr && sudo -u www-data bash deploy/deploy.sh"
# =============================================================================
set -euo pipefail

APP_DIR="/var/www/muscatfr"
PHP="php8.3"
ARTISAN="${APP_DIR}/artisan"

cd "${APP_DIR}"

echo "──────────────────────────────────────────"
echo " Muscat Fragrances – Deployment started"
echo " $(date)"
echo "──────────────────────────────────────────"

# ── 1. Enable maintenance mode ───────────────────────────────────────────────
${PHP} "${ARTISAN}" down --retry=10 --render="errors::503" || true

# ── 2. Pull latest code ──────────────────────────────────────────────────────
git pull origin main

# ── 3. PHP dependencies ──────────────────────────────────────────────────────
composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# ── 4. Build front-end assets ────────────────────────────────────────────────
npm ci --omit=dev
npm run build

# ── 5. Migrate database ──────────────────────────────────────────────────────
${PHP} "${ARTISAN}" migrate --force

# ── 6. Clear & rebuild caches ────────────────────────────────────────────────
${PHP} "${ARTISAN}" config:cache
${PHP} "${ARTISAN}" route:cache
${PHP} "${ARTISAN}" view:cache
${PHP} "${ARTISAN}" event:cache

# ── 7. Fix storage permissions ───────────────────────────────────────────────
chown -R www-data:www-data "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

# ── 8. Restart PHP-FPM ───────────────────────────────────────────────────────
systemctl reload php8.3-fpm

# ── 9. Disable maintenance mode ──────────────────────────────────────────────
${PHP} "${ARTISAN}" up

echo ""
echo "──────────────────────────────────────────"
echo " Deployment complete ✓"
echo "──────────────────────────────────────────"
