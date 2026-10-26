# Image de production de CrazyDev : PHP 8.3 + Apache (gère le .htaccess du projet)
FROM php:8.3-apache

# Configuration PHP "production" (pas d'affichage d'erreurs) + réglages du projet
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-crazydev.ini"

# Apache : active la réécriture d'URL (.htaccess) et la vraie IP client derrière Caddy
COPY docker/apache.conf /etc/apache2/conf-available/crazydev.conf
RUN a2enmod rewrite remoteip && a2enconf crazydev

# Le code appartient à root (lecture seule pour Apache) ;
# seuls les dossiers de données et de logs sont modifiables par www-data.
COPY . /var/www/html/
RUN mkdir -p config/data log upload \
    && chown -R www-data:www-data config/data log upload

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD curl -fsS -o /dev/null http://localhost/ || exit 1
