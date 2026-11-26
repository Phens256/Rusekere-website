#!/bin/sh
# Starts the site with PHP's built-in server. MySQL/MariaDB must be running and database/rusekere_school.sql imported.
cd "$(dirname "$0")"
echo "Open http://localhost:8000/setup-check.php to verify, then http://localhost:8000/"
php -S localhost:8000
