#!/bin/sh
set -eu

# Prépare les dossiers écrits par Symfony et la gestion des images.
mkdir -p var/cache var/log config/jwt public/uploads/menus
chown -R www-data:www-data var config/jwt public/uploads/menus

# Installe les dépendances si le volume vendor vient d'être créé.
if [ ! -f vendor/autoload_runtime.php ]; then
    composer install --no-interaction --prefer-dist --no-progress --no-scripts
fi

# Génère une paire de clés locale au premier démarrage du volume JWT.
if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
    php bin/console lexik:jwt:generate-keypair --no-interaction
    chown www-data:www-data config/jwt/private.pem config/jwt/public.pem
fi

# Nettoie le cache afin de prendre en compte les variables du conteneur.
php bin/console cache:clear --no-warmup
chown -R www-data:www-data var

exec "$@"
