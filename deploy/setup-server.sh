#!/usr/bin/env bash
# =============================================================================
# Muscat Fragrances – Lightsail Server Bootstrap
# Run once on a fresh Ubuntu 22.04 instance as: sudo bash setup-server.sh
# =============================================================================
set -euo pipefail

APP_DIR="/var/www/muscatfr"
PHP_VERSION="8.3"
NGINX_CONF="/etc/nginx/sites-available/muscatfr"
DB_NAME="muscat_fragrances"
DB_USER="muscatapp"

echo "======================================================="
echo " Muscat Fragrances – Server Setup"
echo "======================================================="

# ── 1. System update ─────────────────────────────────────────────────────────
apt-get update -y
apt-get upgrade -y

# ── 2. Install required packages ─────────────────────────────────────────────
apt-get install -y \
    nginx \
    mysql-server \
    software-properties-common \
    curl \
    unzip \
    git \
    ufw

# ── 3. PHP 8.3 ───────────────────────────────────────────────────────────────
add-apt-repository ppa:ondrej/php -y
apt-get update -y
apt-get install -y \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-redis

# ── 4. Composer ──────────────────────────────────────────────────────────────
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# ── 5. Node.js 20 (for Vite asset build) ─────────────────────────────────────
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# ── 6. MySQL database + user ─────────────────────────────────────────────────
echo " → Setting up MySQL database..."
read -rsp "Enter a secure DB password for '${DB_USER}': " DB_PASS
echo

mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "DB_USER=${DB_USER}"   >> /root/.db_credentials
echo "DB_PASS=${DB_PASS}"   >> /root/.db_credentials
chmod 600 /root/.db_credentials
echo " → DB credentials saved to /root/.db_credentials (keep safe!)"

# ── 7. App directory ─────────────────────────────────────────────────────────
mkdir -p "${APP_DIR}"
chown -R www-data:www-data /var/www
chmod -R 755 /var/www

# ── 8. Firewall ───────────────────────────────────────────────────────────────
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo ""
echo "======================================================="
echo " Server bootstrap complete!"
echo " Next: run  deploy/deploy.sh  to push your app."
echo "======================================================="
