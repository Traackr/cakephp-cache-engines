FROM php:7.1-fpm-alpine
RUN apk update && apk add build-base
RUN apk add zlib-dev git zip libmcrypt-dev \
  && docker-php-ext-install zip \
  && docker-php-ext-install mcrypt \
  && docker-php-ext-enable mcrypt
RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer
COPY . /app
WORKDIR /app
ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"
