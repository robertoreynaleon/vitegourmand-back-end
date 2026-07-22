# Image PHP et Apache utilisée pour le développement local du backend.
FROM php:8.2-apache

ARG LOCAL_UID=1000
ARG LOCAL_GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1

# Installation des bibliothèques nécessaires à Symfony, MySQL, MongoDB et WebP.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j4 gd intl mbstring opcache pdo_mysql zip \
    && pecl install mongodb-1.21.2 \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers \
    && groupmod --gid "${LOCAL_GID}" www-data \
    && usermod --uid "${LOCAL_UID}" --gid "${LOCAL_GID}" www-data \
    && rm -rf /var/lib/apt/lists/*

# Composer reste disponible dans le conteneur pour les commandes Symfony.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Installation reproductible des dépendances PHP depuis composer.lock.
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

# Copie du projet pour permettre aussi un démarrage sans bind mount.
COPY . .

# Configuration Apache et préparation du script de démarrage.
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/vitegourmand-entrypoint
RUN chmod +x /usr/local/bin/vitegourmand-entrypoint \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["vitegourmand-entrypoint"]
CMD ["apache2-foreground"]
