#!/bin/sh

# Exit immediately if a command exits with a non-zero status.
set -e

# Create the symbolic link every time the container starts
php artisan storage:link

# Run migrations (force for production)
php artisan migrate --force

# Start the actual web server (usually php-fpm or apache)
# This depends on what your original CMD was. Examples:
# exec php-fpm
# OR
# exec apache2-foreground