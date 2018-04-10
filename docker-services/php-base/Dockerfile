FROM php:7.2.4-fpm-alpine3.7

RUN apk update && \
	apk add \
		bash
		
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY php.conf /usr/local/etc/php-fpm.d/docker.conf

WORKDIR /var/www