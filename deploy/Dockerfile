FROM php:8.1.34-apache-bookworm

ARG IONCUBE_URL=https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz

ENV APACHE_DOCUMENT_ROOT=/var/www/droptaxi/public

COPY apache-droptaxi.conf /etc/apache2/conf-available/droptaxi.conf
COPY php-production.ini /usr/local/etc/php/conf.d/99-droptaxi.ini

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        procps; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" curl exif gd mbstring mysqli pdo_mysql zip; \
    pecl install redis-6.3.0; \
    docker-php-ext-enable redis; \
    curl -fsSL "$IONCUBE_URL" -o /tmp/ioncube.tar.gz; \
    tar -xzf /tmp/ioncube.tar.gz -C /tmp; \
    test -f /tmp/ioncube/ioncube_loader_lin_8.1.so; \
    extension_dir="$(php-config --extension-dir)"; \
    install -m 0644 /tmp/ioncube/ioncube_loader_lin_8.1.so "$extension_dir/ioncube_loader_lin_8.1.so"; \
    printf 'zend_extension=%s/ioncube_loader_lin_8.1.so\n' "$extension_dir" > /usr/local/etc/php/conf.d/00-ioncube.ini; \
    sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf; \
    sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf; \
    a2enmod expires headers remoteip rewrite; \
    a2enconf droptaxi; \
    mkdir -p /var/www/droptaxi; \
    php -r 'foreach (["curl", "exif", "gd", "mbstring", "mysqli", "openssl", "redis"] as $extension) { if (!extension_loaded($extension)) { fwrite(STDERR, "Missing PHP extension: {$extension}\\n"); exit(1); } }'; \
    php -r 'if (!extension_loaded("ionCube Loader")) { fwrite(STDERR, "Missing ionCube Loader\\n"); exit(1); }'; \
    rm -rf /var/lib/apt/lists/* /tmp/ioncube /tmp/ioncube.tar.gz

WORKDIR /var/www/droptaxi
