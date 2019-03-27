FROM php:7.2-cli

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get install -y git

RUN pecl install ast \
    && docker-php-ext-enable ast

ENV PATH /var/www/html/vendor/bin:$PATH

WORKDIR /var/www/html