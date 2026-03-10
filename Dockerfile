FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    nginx
 
# Install PHP extensions
# ADDED: pdo_pgsql to the list below
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# This will now succeed because pdo_pgsql is installed above
RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www

EXPOSE 80

# Update your CMD to this:
CMD php artisan config:clear && php artisan storage:link && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT