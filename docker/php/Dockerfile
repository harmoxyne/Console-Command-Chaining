FROM php:8.2-fpm
LABEL maintainer="harmoxyne@gmail.com"

RUN mkdir ~/.ssh

RUN buildDeps=" \
        default-libmysqlclient-dev \
        libbz2-dev \
        libmemcached-dev \
        libsasl2-dev \
    " \
    runtimeDeps=" \
        curl \
        git \
        zip \
        unzip \
        vim \
        wget \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libjpeg-dev \
        libmemcachedutil2 \
        libpng-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libevent-dev \
        librabbitmq-dev \
        libcurl4-openssl-dev \
        pkg-config \
        libssl-dev \
        openssh-client \
    " \
    && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y $buildDeps $runtimeDeps \
    && docker-php-ext-install bcmath bz2 curl calendar iconv intl mbstring opcache pdo_mysql soap zip \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/  \
    && docker-php-ext-install pcntl gd exif\
    && docker-php-ext-enable opcache \
    && pecl install memcached redis \
    && docker-php-ext-enable memcached.so redis.so \
    && apt-get purge -y --auto-remove $buildDeps \
    && apt-get install -y procps \
    && rm -r /var/lib/apt/lists/*

RUN ln -sf /dev/stdout /var/log/access.log && ln -sf /dev/stderr /var/log/error.log

RUN set -xe \
    && pecl install \
        redis \
        amqp \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable \
        redis \
        amqp
RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends
RUN pecl install imagick && docker-php-ext-enable imagick

RUN wget http://pecl.php.net/get/amqp-1.11.0 -O amqp.tar.gz \
    && tar -zxvf amqp.tar.gz \
    && cd amqp-1.11.0 \
    && phpize \
    && ./configure --with-amqp \
    && make \
    && make install

COPY php.ini /usr/local/etc/php/

## Install Composer
RUN echo "Install composer globally"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && ln -s $(composer config --global home) /root/composer
RUN composer self-update --2
ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1

RUN mkdir /.cache -m 777

#USER www-data

CMD ["php-fpm"]
