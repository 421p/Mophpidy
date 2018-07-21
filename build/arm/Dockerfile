FROM resin/rpi-raspbian:stretch as builder

FROM arm32v7/php:7-cli

COPY --from=builder /usr/bin/qemu-arm-static /usr/bin/qemu-arm-static

RUN apt-get update \
    && apt-get install --no-install-recommends -y zlib1g-dev libpq-dev git libicu-dev libxml2-dev libpcre3-dev libevent-dev libssl-dev \
    && docker-php-ext-install sockets \
    && pecl install event \
    && pecl install ds \
    && pecl install redis \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable event ds redis \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /app/data && touch /app/.env

ADD vendor /app/vendor
ADD doctrine /app/doctrine

RUN ln -s /app/doctrine /usr/bin/doctrine && chmod +x /app/doctrine

ADD migrations /app/migrations
ADD bot.php /app/bot.php
ADD autoload.php /app/autoload.php
ADD cli-config.php /app/cli-config.php
ADD src /app/src
ADD config /app/config

WORKDIR /app

CMD ["php", "/app/bot.php"]