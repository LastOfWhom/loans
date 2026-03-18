#!/bin/sh
set -e

cd /var/www/app

echo "Starting PHP-FPM..."
exec "$@"
