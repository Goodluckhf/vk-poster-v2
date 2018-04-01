FROM php:5.6-apache

ARG vhost_domain="site.vdev"
ARG web_server_path="/var/www"
RUN apt-get update

RUN apt-get install -y curl libcurl4-openssl-dev libmcrypt-dev
	
RUN docker-php-ext-configure pdo && \
	docker-php-ext-install pdo && \
	docker-php-ext-enable pdo

RUN docker-php-ext-configure mysql && \
	docker-php-ext-install mysql && \
	docker-php-ext-enable mysql

RUN docker-php-ext-configure pdo_mysql && \
	docker-php-ext-install pdo_mysql && \
	docker-php-ext-enable pdo_mysql

RUN docker-php-ext-configure mcrypt && \
	docker-php-ext-install mcrypt && \
	docker-php-ext-enable mcrypt

RUN docker-php-ext-configure curl && \
	docker-php-ext-install curl && \
	docker-php-ext-enable curl
	
#Копируем конфиги
COPY php.ini /usr/local/etc/php/
COPY httpd-vhosts.conf /etc/apache2/sites-available/${vhost_domain}.conf

ENV VHOST_DOMAIN=${vhost_domain}
ENV WEB_SERVER_PATH=${web_server_path}
ENV APACHE_PORT=8080

RUN mkdir -p ${web_server_path}/logs

RUN rm /etc/apache2/sites-available/000-default.conf && \
	rm /etc/apache2/sites-enabled/000-default.conf && \
	echo "" > /etc/apache2/ports.conf
	
RUN a2ensite ${vhost_domain}.conf
RUN a2enmod rewrite

RUN useradd www && \
	chown -R www:www /etc/apache2 /var/lock/apache2 /var/run/apache2

USER www

WORKDIR ${web_server_path}