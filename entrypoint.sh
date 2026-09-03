#!/bin/sh
set -e

# Correr migraciones y limpiar cachés
php artisan migrate --force
php artisan config:cache
php artisan route:cache

# Arrancar el servidor integrado de PHP usando la variable PORT de Render
exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
