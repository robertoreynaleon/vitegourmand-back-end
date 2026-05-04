#!/bin/bash
set -e

echo "=== [post-build] Démarrage ==="

# ── 1. Clés JWT ─────────────────────────────────────────────────────────────
# Les fichiers .pem sont dans .gitignore. On les recrée depuis les variables
# d'env base64 JWT_PRIVATE_KEY_B64 et JWT_PUBLIC_KEY_B64 (définies dans la
# console Clever Cloud).
echo "[1/3] Décodage des clés JWT..."
mkdir -p config/jwt
printf '%s' "$JWT_PRIVATE_KEY_B64" | base64 -d > config/jwt/private.pem
printf '%s' "$JWT_PUBLIC_KEY_B64"  | base64 -d > config/jwt/public.pem
chmod 600 config/jwt/private.pem
echo "  Clés JWT OK"

# ── 2. DATABASE_URL ──────────────────────────────────────────────────────────
# Clever Cloud injecte MYSQL_ADDON_URI (mysql://user:pass@host:port/db).
# Doctrine a besoin de serverVersion et charset dans l'URL.
if [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_ADDON_URI" ]; then
    BASE_URI=$(echo "$MYSQL_ADDON_URI" | cut -d'?' -f1)
    export DATABASE_URL="${BASE_URI}?serverVersion=8.0&charset=utf8mb4"
    echo "  DATABASE_URL construit depuis MYSQL_ADDON_URI"
fi

# ── 3. Migrations Doctrine ───────────────────────────────────────────────────
echo "[2/3] Lancement des migrations Doctrine..."
APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
echo "  Migrations OK"

# ── 4. Cache prod ────────────────────────────────────────────────────────────
echo "[3/3] Vidage du cache prod..."
APP_ENV=prod php bin/console cache:clear --no-warmup
echo "  Cache OK"

echo "=== [post-build] Terminé ==="
