FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip intl \
    && rm -rf /var/lib/apt/lists/*

# mod_php requires the prefork MPM (it isn't thread-safe). Installing/upgrading
# packages above can make apache2's own postinst re-enable a different default
# MPM (mpm_event) on top of whatever was already enabled, causing
# "AH00534: More than one MPM loaded" at runtime. a2dismod/a2enmod trust the
# same symlink bookkeeping that caused this, so instead: wipe every mpm_*
# symlink and recreate only prefork directly, then verify at BUILD time
# (not runtime) that exactly one MPM is loaded — a regression here fails the
# build loudly instead of shipping a container that 502s.
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite \
    && test "$(apache2ctl -M 2>/dev/null | grep -c 'mpm_')" = "1"

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
