FROM php:7-cli

ADD composer.json /app/composer.json

RUN apt-get update \
    && apt-get install --no-install-recommends -y wget git libevent-dev libssl-dev \
    && wget https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer -O - -q | php -- --quiet \
    && mv composer.phar /bin/composer \
    && docker-php-ext-install sockets \
    && pecl install event \
    && docker-php-ext-enable event \
    && cd /app && composer install \
    && apt-get purge -y wget git  \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/* \
    && touch /app/.env

ADD bot.php /app/index.php
ADD autoload.php /app/autoload.php
ADD src /app/src
ADD config /app/config

CMD ["php", "/app/index.php"]