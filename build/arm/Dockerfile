FROM resin/rpi-raspbian:stretch as builder

FROM arm32v7/php:7-cli

COPY --from=builder /usr/bin/qemu-arm-static /usr/bin/qemu-arm-static

RUN apt-get update \
    && apt-get install --no-install-recommends -y libevent-dev libssl-dev \
    && docker-php-ext-install sockets \
    && pecl install event \
    && docker-php-ext-enable event \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /app/data && touch /app/.env

ADD vendor /app/vendor
ADD bot.php /app/index.php
ADD autoload.php /app/autoload.php
ADD cli-config.php /app/cli-config.php
ADD src /app/src
ADD config /app/config

WORKDIR /app

CMD ["php", "/app/index.php"]