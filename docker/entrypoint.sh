#!/bin/sh
set -e

# Write Apache SetEnv directives so mod_php can read them via getenv()
cat > /etc/apache2/conf-available/app-env.conf <<EOF
SetEnv DB_PATH "${DB_PATH}"
SetEnv APP_ENV "${APP_ENV}"
SetEnv APP_DEBUG "${APP_DEBUG}"
EOF

a2enconf app-env > /dev/null 2>&1

# Initialize SQLite database on first boot
DB_PATH="${DB_PATH:-/var/www/html/data/database.sqlite}"
if [ ! -f "${DB_PATH}" ]; then
    mkdir -p "$(dirname "${DB_PATH}")"
    sqlite3 "${DB_PATH}" < /var/www/html/docker/schema.sql
    sqlite3 "${DB_PATH}" < /var/www/html/docker/seed.sql
    chown www-data:www-data "${DB_PATH}"
fi

# Enable Xdebug coverage config only when COVERAGE_ENABLED is set
if [ "$COVERAGE_ENABLED" = "1" ]; then
    cp /var/www/html/docker/coverage.ini /usr/local/etc/php/conf.d/coverage.ini
    mkdir -p /tmp/coverage
    chmod 777 /tmp/coverage
fi

exec apache2-foreground
