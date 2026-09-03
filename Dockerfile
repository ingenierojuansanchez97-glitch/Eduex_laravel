FROM php:8.2-cli

# Instalar extensiones requeridas por Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libpq-dev zip \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring gd

# Copiar Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Dar permisos de ejecución al script entrypoint en /app
RUN chmod +x /app/entrypoint.sh

# Definir el entrypoint que ejecutará las migraciones y luego iniciará el servidor
ENTRYPOINT ["/app/entrypoint.sh"]
