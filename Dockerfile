FROM php:7.3-cli

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get install -y git

RUN apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) zip

RUN pecl install ast \
    && docker-php-ext-enable ast

RUN php -r "copy('https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

ENV PATH /var/www/html/vendor/bin:$PATH

WORKDIR /var/www/html