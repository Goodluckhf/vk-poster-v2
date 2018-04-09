FROM php:7.2.4-fpm-alpine3.7

RUN apk update && \
	apk add \
		bash \
		busybox-suid \
		cron
		
	

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY php.conf /usr/local/etc/php-fpm.d/docker.conf

ARG user='www'	
ENV _USER=${user}

RUN mkdir /home/${user}

RUN (echo "* * * * * /usr/local/bin/php /var/www/artisan schedule:run >> /dev/null 2>&1") >> /var/spool/cron/crontabs/root

RUN addgroup -S ${user}
RUN adduser -S -G ${user} ${user} && \
	chown -R ${user}:${user} /home/${user}

USER ${user}


WORKDIR /var/www