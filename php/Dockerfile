FROM php:8-fpm

RUN apt-get update
RUN apt-get install -y zlib1g-dev
RUN apt-get -y install g++
RUN apt-get -y install libicu-dev
RUN apt-get -y install libzip-dev
RUN apt-get -y install zip
RUN apt-get -y install git
RUN apt-get -y install libpng-dev
RUN apt-get -y install libfreetype6-dev
RUN apt-get -y install libjpeg62-turbo-dev
RUN apt-get -y install libgmp-dev
RUN apt-get -y install re2c
RUN apt-get -y install libmhash-dev
RUN apt-get -y install file
RUN apt-get -y install libxslt-dev
RUN apt-get -y install libpq-dev

RUN docker-php-ext-install intl
RUN docker-php-ext-install opcache
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-enable pdo_pgsql
RUN pecl install apcu
RUN docker-php-ext-enable apcu
RUN pecl -v install redis
RUN docker-php-ext-enable redis
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip
RUN docker-php-ext-configure gmp
RUN docker-php-ext-install gmp
RUN docker-php-ext-install xsl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-enable gd

COPY ./php/php.ini /usr/local/etc/php/php.ini
RUN sed -i -e "s#TIMEZONE#$timezone#g" /usr/local/etc/php/php.ini

#COPY xdebug.ini /tmp/xdebug.ini
#RUN cat /tmp/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
#RUN rm /tmp/xdebug.ini

WORKDIR /var/www/project

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony