FROM php:7.4.33-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libsqlite3-dev openssl \
 && docker-php-ext-install pdo_sqlite \
 && rm -rf /var/lib/apt/lists/*

COPY ./src/ /var/www/html/
COPY ./src/php-ctf-security.ini /usr/local/etc/php/conf.d/99-ctf-security.ini

RUN set -e; \
    KEY_HEX="$(openssl rand -hex 32)"; \
    sed -Ei "0,/define\('KEY',\s*'[^']*'\);/s//define('KEY', '${KEY_HEX}');/" /var/www/html/config.php

RUN mkdir /var/www/db \
 && chown www-data:www-data /var/www/db \
 && chmod 0755 /var/www/db

RUN rm -f /bin/su /usr/bin/passwd /usr/bin/chsh /usr/bin/chfn /usr/bin/newgrp \
          /usr/bin/gpasswd /usr/bin/wall /usr/bin/write /usr/bin/mesg

RUN echo "www-data soft nproc 32" >> /etc/security/limits.conf \
 && echo "www-data hard nproc 32" >> /etc/security/limits.conf \
 && echo "www-data soft nofile 256" >> /etc/security/limits.conf \
 && echo "www-data hard nofile 256" >> /etc/security/limits.conf

RUN mkdir -p /var/run/apache2 /var/log/apache2 /var/lock/apache2 \
 && chown -R www-data:www-data /var/run/apache2 /var/log/apache2 /var/lock/apache2

RUN chown -R root:root /var/www/html && chmod -R 0555 /var/www/html

RUN echo "L3AK{flag_test}" > /tmp/flag.txt \
 && chmod 644 /tmp/flag.txt


ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_LOG_DIR=/var/log/apache2
ENV APACHE_PID_FILE=/var/run/apache2.pid
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_LOCK_DIR=/var/lock/apache2