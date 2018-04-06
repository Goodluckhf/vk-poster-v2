FROM php:5.6-apache


ENV VHOST_DOMAIN="site.vdev"
ENV WEB_SERVER_PATH="/var/www"
ENV APACHE_PORT=8080

RUN apt-get update

RUN apt-get install -y cron curl libcurl4-openssl-dev libmcrypt-dev git unzip zip zlib1g-dev sudo && \
	apt-get clean
	
RUN docker-php-ext-configure zip && \
	docker-php-ext-install zip && \
	docker-php-ext-enable zip
	
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
COPY httpd-vhosts.conf /etc/apache2/sites-available/${VHOST_DOMAIN}.conf

RUN mkdir -p ${WEB_SERVER_PATH}/logs

RUN rm /etc/apache2/sites-available/000-default.conf && \
	rm /etc/apache2/sites-enabled/000-default.conf && \
	echo "" > /etc/apache2/ports.conf

ARG user='www'	
ENV _USER=${user}

RUN a2ensite ${VHOST_DOMAIN}.conf
RUN a2enmod rewrite
RUN mkdir /home/${user}

RUN useradd ${user} && \
	chown -R ${user}:${user} /etc/apache2 /var/lock/apache2 /var/run/apache2 /home/${user}

RUN adduser ${user} sudo

RUN usermod --password `openssl passwd root` ${user}

USER ${user}

RUN (crontab -l ; echo "* * * * * /usr/local/bin/php /var/www/artisan schedule:run >> /dev/null 2>&1\n#empty line") | crontab

WORKDIR ${WEB_SERVER_PATH}