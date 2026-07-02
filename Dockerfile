# Build the Go FTP server binary
FROM docker.io/golang:1.26-bookworm AS ftp-builder

WORKDIR /build
COPY ./ftpserver-v0.16.0.tar.gz /tmp/ftpserver.tar.gz
RUN tar xzf /tmp/ftpserver.tar.gz --strip-components=1 -C /build \
    && rm /tmp/ftpserver.tar.gz

RUN go build -o /usr/local/bin/ftpserver .

# Use FrankenPHP image — Caddy + PHP in one process, no php-fpm needed
FROM docker.io/dunglas/frankenphp:php8.4 AS app

# Install Supervisor
RUN apt-get update && apt-get install -y cron supervisor

# WordPress-required + useful PHP extensions (ftpext for WordPress FTP uploads)
RUN install-php-extensions \
    @composer \
    mysqli \
    pdo_mysql \
    gd \
    bcmath \
    bz2 \
    gettext \
    sockets \
    sodium \
    zip \
    ldap \
    redis \
    exif \
    imagick \
    opcache \
    ftp

# Custom php.ini
COPY ./php.ini /usr/local/etc/php/php.ini

# Caddyfile for WordPress (replaces default Symfony-oriented one)
COPY ./Caddyfile /etc/caddy/Caddyfile
COPY ./Caddyfile /etc/frankenphp/Caddyfile

WORKDIR /app

# Extract WordPress 7.0 into /app (strip the top-level WordPress-7.0/ dir)
COPY ./7.0.tar.gz /tmp/wordpress.tar.gz
RUN tar xzf /tmp/wordpress.tar.gz --strip-components=1 -C /app \
    && rm /tmp/wordpress.tar.gz

# Ensure wp-content dirs exist and are writable by www-data
RUN mkdir -p /app/wp-content/uploads /app/wp-content/upgrade /app/wp-content/cache \
    && chown -R www-data:www-data /app

# Copy wp-config.php with FTP constants pre-configured
COPY ./wp-config.php /app/wp-config.php
RUN chown www-data:www-data /app/wp-config.php

# Patched WP_Filesystem_FTPext — falls back to FTP_HOST/FTP_USER/FTP_PASS constants
# when WP_Filesystem() is called with no args (e.g. during theme rendering)
COPY ./class-wp-filesystem-ftpext.php /app/wp-admin/includes/class-wp-filesystem-ftpext.php

# Copy the compiled FTP server binary from the Go builder
COPY --from=ftp-builder /usr/local/bin/ftpserver /usr/local/bin/ftpserver

# FTP server config — localhost only, serves /app as www-data
COPY ./ftpserver.json /app/ftpserver.json

# Add supervisor configs
COPY ./ftpserver.conf /etc/supervisor/conf.d/

# Add supervisor to the entry point
RUN sed -i '/set -e/a \\n/usr/bin/supervisord -c /etc/supervisor/supervisord.conf \&\n' /usr/local/bin/docker-php-entrypoint

# Default env — allows overriding at runtime
ENV SERVER_NAME=localhost
ENV SERVER_ROOT=/app
