FROM php:8.3-apache

# mysqli driver for MySQL + Apache rewrite module
RUN docker-php-ext-install mysqli && a2enmod rewrite

WORKDIR /var/www/html
COPY src/ /var/www/html/

# On every start: use only ONE Apache MPM, and listen on Railway's $PORT (80 locally).
# The sed commands replace whatever port is there, so restarts are safe.
CMD ["sh", "-c", "a2dismod mpm_event mpm_worker >/dev/null 2>&1; a2enmod mpm_prefork >/dev/null 2>&1; sed -i -E \"s/^Listen [0-9]+/Listen ${PORT:-80}/\" /etc/apache2/ports.conf; sed -i -E \"s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${PORT:-80}>/\" /etc/apache2/sites-available/000-default.conf; apache2-foreground"]
