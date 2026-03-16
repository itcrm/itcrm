#!/bin/sh
set -e

# Write Apache SetEnv directives so mod_php can read them via getenv()
cat > /etc/apache2/conf-available/app-env.conf <<EOF
SetEnv DB_HOST "${DB_HOST}"
SetEnv DB_PORT "${DB_PORT}"
SetEnv DB_USER "${DB_USER}"
SetEnv DB_PASSWORD "${DB_PASSWORD}"
SetEnv DB_DATABASE "${DB_DATABASE}"
SetEnv APP_ENV "${APP_ENV}"
SetEnv APP_DEBUG "${APP_DEBUG}"
EOF

a2enconf app-env > /dev/null 2>&1

# Enable Xdebug coverage config only when COVERAGE_ENABLED is set
if [ "$COVERAGE_ENABLED" = "1" ]; then
    cp /var/www/html/docker/coverage.ini /usr/local/etc/php/conf.d/coverage.ini
    mkdir -p /tmp/coverage
    chmod 777 /tmp/coverage
fi

exec apache2-foreground
