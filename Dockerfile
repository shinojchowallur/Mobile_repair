FROM php:8.3-apache

# mysqli driver for MySQL + Apache rewrite module
RUN docker-php-ext-install mysqli && a2enmod rewrite

WORKDIR /var/www/html
