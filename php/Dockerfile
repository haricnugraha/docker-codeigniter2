FROM php:5.6-fpm

RUN apt-get update && apt-get install -y \
    # gd
    libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng12-dev \
    # intl
    # zlib1g-dev libicu-dev \
    # imageick
    # libmagickwand-dev \
    # memcached
    # libz-dev libmemcached-dev \
    # libmemcached11 libmemcachedutil2 build-essential \
    # vim
    # vim \
    # curl sendmail \ 
    # supervisor \
    # && pecl install memcached-2.2.0 \
    && docker-php-ext-install mysql pdo_mysql mbstring \
    && echo date.timezone=Asia/Jakarta >> /usr/local/etc/php/conf.d/php.ini \
    # && echo extension=memcached.so >> /usr/local/etc/php/conf.d/memcached.ini \
    # && docker-php-ext-enable memcached \    
    && apt-get autoremove -y \
    && apt-get clean
    # && rm -rf /tmp/pear