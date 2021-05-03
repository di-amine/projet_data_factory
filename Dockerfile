FROM  trafex/alpine-nginx-php7:1.10.0

USER root

RUN apk add --no-cache php7-pdo php7-sqlite3 php7-pdo_sqlite php7-pdo_mysql php7-tokenizer php7-iconv php7-pcntl

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

ADD . /var/www/html

ADD nginx.conf /etc/nginx/nginx.conf

# Run composer install to install the dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress

USER nobody