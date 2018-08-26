FROM php:7-cli

RUN apt-get update \
    && apt-get install --no-install-recommends -y libicu-dev libevent-dev libssl-dev \
    && docker-php-ext-install sockets \
    && pecl install event \
    && docker-php-ext-configure intl \
    && docker-php-ext-enable event \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /app/data && touch /app/.env

ADD vendor /app/vendor
ADD bot.php /app/bot.php
ADD autoload.php /app/autoload.php
ADD src /app/src
ADD config /app/config

WORKDIR /app

CMD ["php", "/app/bot.php"]