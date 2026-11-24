FROM php:8.3-apache

# mysqli driver for MySQL + Apache rewrite module
RUN docker-php-ext-install mysqli && a2enmod rewrite

WORKDIR /var/www/html
COPY src/ /var/www/html/

# On start: use only ONE Apache MPM (fixes "More than one MPM loaded"),
# and listen on the port Railway gives in $PORT (80 locally).
CMD ["sh", "-c", "a2dismod mpm_event mpm_worker >/dev/null 2>&1; a2enmod mpm_prefork >/dev/null 2>&1; sed -i \"s/80/${PORT:-80}/g\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf; apache2-foreground"]
