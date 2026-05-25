FROM php:8.4-fpm-bookworm

ARG UID=1000
ARG GID=1000

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y \
        git \
        unzip \
        curl \
        zip \
        libpq-dev \
        libzip-dev \
        libonig-dev \
        ca-certificates \
        gnupg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        bcmath \
        zip \
        pcntl \
        mbstring \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --gid ${GID} app \
    && useradd --uid ${UID} --gid app --shell /bin/bash --create-home app \
    && sed -i 's/user = www-data/user = app/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = app/g' /usr/local/etc/php-fpm.d/www.conf

