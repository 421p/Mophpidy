FROM arm32v7/php:7-cli

RUN apt-get update \
    && apt-get install --no-install-recommends -y libevent-dev libssl-dev \
    && docker-php-ext-install sockets \
    && pecl install event \
    && docker-php-ext-enable event \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir /app
RUN touch /app/.env

ADD bot.php /app/index.php
ADD autoload.php /app/autoload.php
ADD vendor /app/vendor
ADD src /app/src
ADD config /app/config

CMD ["php", "/app/index.php"]