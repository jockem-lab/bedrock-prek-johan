FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libjpeg-dev libwebp-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli zip exif \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -sS https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp \
    && chmod +x /usr/local/bin/wp

WORKDIR /var/www/html
