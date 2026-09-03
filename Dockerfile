FROM php:8.2-cli

# Instalar extensiones requeridas por Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip \
    && docker-php-ext-install pdo_mysql mbstring gd

# Copiar Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Ejecutar la aplicación exponiendo el puerto dinámico de Render
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
