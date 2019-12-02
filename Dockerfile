FROM php:7.3-apache

#TODO копировать только нужные файлы (без composer, out/, тестовых ресурсов и Dockerfile)
COPY . .

RUN chmod -R 777 /var/www/html/*

RUN apt-get update && \
    apt-get install -y -qq \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libmcrypt-dev \
        libpng-dev \
        antiword

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd

EXPOSE 80
