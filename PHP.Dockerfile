FROM php:fpm

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN apt-get update && apt-get upgrade -y && apt-get install -y git zip unzip
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
