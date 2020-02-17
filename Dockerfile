FROM php:fpm-alpine

LABEL Maintainer Michael Shihjay Chen <shihjay2@gmail.com>

RUN rm -f /etc/apk/repositories &&\
    echo "http://dl-cdn.alpinelinux.org/alpine/v3.9/main" >> /etc/apk/repositories &&\
    echo "http://dl-cdn.alpinelinux.org/alpine/v3.9/community" >> /etc/apk/repositories &&\
    apk add --no-cache --virtual .build-deps \
    zlib-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    php7-dev \
    autoconf \
    gcc \
    g++ \
    make \
    pcre-dev \
    bzip2-dev &&\
    apk add --update --no-cache \
    jpegoptim \
    pngquant \
    optipng \
    supervisor \
    nano \
    icu-dev \
    mariadb-client \
    imagemagick-dev \
    libssh2-dev \
    libzip-dev \
    imap-dev \
    libtool \
    freetype-dev &&\
    docker-php-ext-configure \
    opcache --enable-opcache &&\
    docker-php-ext-configure gd --with-freetype --with-jpeg &&\
    PHP_OPENSSL=yes docker-php-ext-configure imap --with-imap --with-imap-ssl &&\
    docker-php-ext-install \
    opcache \
    mysqli \
    pdo \
    pdo_mysql \
    sockets \
    json \
    intl \
    gd \
    xml \
    zip \
    bz2 \
    pcntl \
    soap \
    imap \
    bcmath &&\
    pecl install imagick &&\
    docker-php-ext-enable imagick &&\
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" &&\
    apk del .build-deps

WORKDIR "/var/www/nosh-lite"

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="./vendor/bin:$PATH"

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/nosh-lite
RUN mkdir /var/www/nosh-lite/vendor &&\
    chmod 777 /var/www/nosh-lite/storage &&\
    chmod 777 /var/www/nosh-lite/public &&\
    chmod 777 /var/www/nosh-lite/vendor

USER www-data

# Install all PHP dependencies
RUN composer install --no-interaction

USER root

COPY docker-entrypoint.sh /usr/local/bin/
RUN ["chmod", "+x", "/usr/local/bin/docker-entrypoint.sh"]

COPY supervisord.conf /etc/supervisord.conf
COPY schedule.sh /usr/local/bin/schedule.sh
RUN ["chmod", "+x", "/usr/local/bin/schedule.sh"]

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

EXPOSE 9000
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
