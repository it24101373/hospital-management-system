#!/bin/sh
set -e

# Support dynamic PORT from Render, Railway, Fly.io, or default to 80
HTTP_PORT="${PORT:-80}"

# Update Apache listening port dynamically
sed -i "s/Listen 80/Listen ${HTTP_PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${HTTP_PORT}>/" /etc/apache2/sites-available/000-default.conf

# Ensure permissions
# Ensure permissions
mkdir -p /var/www/html/sessions /var/www/html/backups /var/www/html/public/uploads
touch /var/www/html/database.sqlite
chown -R www-data:www-data /var/www/html/sessions /var/www/html/backups /var/www/html/public/uploads /var/www/html/database.sqlite
chmod -R 777 /var/www/html/sessions /var/www/html/backups /var/www/html/public/uploads
chmod 666 /var/www/html/database.sqlite

echo "Hospital Management System starting on port ${HTTP_PORT}..."
exec apache2-foreground
