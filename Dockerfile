FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip intl \
    # mod_php requires the prefork MPM (it isn't thread-safe); make this
    # explicit and deterministic rather than trusting whatever the apt
    # dependency resolution happened to leave enabled — that's what caused
    # "More than one MPM loaded" (mpm_event alongside mpm_prefork).
    && (a2dismod mpm_event || true) \
    && (a2dismod mpm_worker || true) \
    && a2enmod mpm_prefork rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Laravel serves from public/, not the repo root.
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
