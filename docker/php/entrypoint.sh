#!/bin/sh
set -e

# Auto-run composer install if composer.json exists and vendor is missing or outdated
if [ -f "composer.json" ]; then
    if [ ! -d "vendor" ] || [ "composer.json" -nt "vendor" ] || [ "composer.lock" -nt "vendor" ]; then
        echo "Running composer install..."
        composer install --no-interaction --optimize-autoloader
    fi
fi

# Execute the main container command
exec "$@"
