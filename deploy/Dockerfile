FROM php:8.3-apache-bookworm

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        bcmath \
        gd \
        intl \
        mbstring \
        mongodb \
        mysqli \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers expires remoteip

RUN install-php-extensions grpc

COPY apache-rideon.conf /etc/apache2/sites-available/000-default.conf
COPY docker-entrypoint-rideon.sh /usr/local/bin/docker-entrypoint-rideon

RUN chmod 0755 /usr/local/bin/docker-entrypoint-rideon

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint-rideon"]
CMD ["apache2-foreground"]
