FROM php:5.6-apache

#Копируем конфиги
COPY php.ini /usr/local/etc/php/
COPY vhost-macro.conf /etc/httpd/conf/extra
COPY httpd-vhosts.conf /etc/httpd/conf/extra
COPY httpd.conf /etc/httpd/conf

ARG vhost_domain="site.vdev"
ARG web_server_path="/var/www"

ENV VHOST_DOMAIN=$vhost_domain
ENV WEB_SERVER_PATH=$web_server_path

RUN mkdir -p $web_server_path"/"$vhost_domain"/public"



VOLUME [$web_server_path"/"$vhost_domain]

WORKDIR $web_server_path"/"$vhost_domain

EXPOSE 80