FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip intl \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

# Configure PHP upload limits for video files
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 105M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# mod_php requires the prefork MPM (it isn't thread-safe).  apt-get and
# a2enmod can silently re-enable mpm_event on top of mpm_prefork, causing
# "AH00534: More than one MPM loaded" at runtime.  We fix this in a
# SEPARATE layer, AFTER every apt/a2enmod call, so nothing can undo it.
# The final `apache2ctl -t` + count check fails the build loudly if the
# fix didn't stick.
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && echo "--- mods-enabled mpm symlinks ---" \
    && ls -la /etc/apache2/mods-enabled/mpm_* \
    && apache2ctl -t \
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
