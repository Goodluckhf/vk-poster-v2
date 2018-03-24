FROM php:5.6-apache

ARG vhost_domain="site.vdev"
ARG web_server_path="/var/www"

#Копируем конфиги
COPY php.ini /usr/local/etc/php/
COPY httpd-vhosts.conf /etc/apache2/sites-available/${vhost_domain}.conf

ENV VHOST_DOMAIN=${vhost_domain}
ENV WEB_SERVER_PATH=${web_server_path}
ENV APACHE_PORT=80

RUN rm /etc/apache2/sites-available/000-default.conf
RUN rm /etc/apache2/sites-enabled/000-default.conf
RUN chown -R www-data:www-data ${web_server_path}
RUN a2ensite ${vhost_domain}.conf
RUN a2enmod rewrite

WORKDIR ${web_server_path}

EXPOSE 80