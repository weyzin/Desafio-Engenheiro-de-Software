FROM php:8.2-fpm-alpine

ARG UID=1000
ARG GID=1000

# libs
RUN apk add --no-cache \
    bash git curl unzip icu-dev libzip-dev libpng-dev libjpeg-turbo-dev oniguruma-dev \
    freetype-dev libxml2-dev postgresql-dev \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    bcmath intl pcntl zip gd pdo pdo_mysql pdo_pgsql

# Redis (pecl)
RUN pecl install redis \
 && docker-php-ext-enable redis

# >>> AQUI o ajuste: usa diretamente composer:2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# user não-root
RUN addgroup -g ${GID} app && adduser -D -G app -u ${UID} app

WORKDIR /var/www/html

RUN set -eux; \
    sed -i 's|^user = www-data|user = app|g' /usr/local/etc/php-fpm.d/www.conf; \
    sed -i 's|^group = www-data|group = app|g' /usr/local/etc/php-fpm.d/www.conf; \
    { echo "ping.path = /ping"; echo "pm.status_path = /status"; } >> /usr/local/etc/php-fpm.d/www.conf; \
    mkdir -p storage bootstrap/cache; \
    chown -R app:app /var/www/html

USER app

EXPOSE 9000
CMD ["php-fpm"]
