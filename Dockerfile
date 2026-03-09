FROM php:8.4-fpm-alpine

# System dependencies (alpine = намного меньше размер)
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Сначала только composer файлы (кэш слоя)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-dev --optimize-autoloader \
    || composer update --no-interaction --no-scripts --no-dev --optimize-autoloader

# Копируем проект
COPY . .
RUN composer dump-autoload --optimize

# Node (только prod зависимости)
COPY package.json package-lock.json* ./
RUN npm ci --prefer-offline && npm run build && rm -rf node_modules

# Права
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]