FROM php:8.3-fpm

USER root

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

EXPOSE 9000

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]