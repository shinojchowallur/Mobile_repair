FROM php:8.3-apache

RUN docker-php-ext-install mysqli && a2enmod rewrite

WORKDIR /var/www/html
COPY src/ /var/www/html/

RUN echo "DirectoryIndex repair.php index.php" > /etc/apache2/conf-available/dir-index.conf \
    && a2enconf dir-index

CMD sed -i "s/80/${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground
