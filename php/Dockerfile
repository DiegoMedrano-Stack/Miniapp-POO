FROM php:8.2-fpm-alpine

# Instalar las extensiones PHP necesarias
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

RUN echo "display_errors = On" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html