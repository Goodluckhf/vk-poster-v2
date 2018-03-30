FROM php:5.6-apache

ARG vhost_domain="site.vdev"
ARG web_server_path="/var/www"

RUN apt-get update && apt-get install -y \
	php5.6-bz2 \
	php5.6-curl \
	php5.6-gettext \
	php5.6-mysqli
	
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

RUN useradd just1ce && \
	chown -R just1ce:just1ce /etc/apache2 /var/lock/apache2 /var/run/apache2
	

USER just1ce

WORKDIR ${web_server_path}