FROM php:7.2.6-cli-alpine3.7

RUN apk add --update \
		autoconf \
		g++ \
		libtool \
		make \
	&& apk add --update icu-dev \
	&& docker-php-ext-install intl \
	&& docker-php-ext-install sockets \
	&& touch /usr/local/etc/php/bogus.ini \
	&& pear config-set php_ini /usr/local/etc/php/bogus.ini \
	&& pecl config-set php_ini /usr/local/etc/php/bogus.ini \
	&& apk add --update \
		libevent-dev \
		openssl-dev \
	&& pecl install event \
	&& pecl install lua \
	&& docker-php-ext-enable event lua \
	&& mv /usr/local/etc/php/conf.d/docker-php-ext-event.ini \
		/usr/local/etc/php/conf.d/docker-php-ext-zz-event.ini \
	&& rm /usr/local/etc/php/bogus.ini \
	&& apk del \
		autoconf \
		bash \
		binutils \
		binutils-libs \
		db \
		expat \
		file \
		g++ \
		gcc \
		gdbm \
		gmp \
		isl \
		libatomic \
		libbz2 \
		libc-dev \
		libffi \
		libgcc \
		libgomp \
		libldap \
		libltdl \
		libmagic \
		libsasl \
		libstdc++ \
		libtool \
		m4 \
		make \
		mpc1 \
		mpfr3 \
		musl-dev \
		perl \
		pkgconf \
		pkgconfig \
		python \
		re2c \
		readline \
		sqlite-libs \
		zlib-dev \
	&& rm -rf /tmp/* /var/cache/apk/*

RUN mkdir /app

ADD bot.php /app/index.php
ADD autoload.php /app/autoload.php
ADD vendor /app/vendor

CMD ["php", "/app/index.php"]