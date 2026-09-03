#!/bin/sh
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
exec apache2-foreground
