FROM just1ce/app-php-base:latest

ARG user='www'	
ENV _USER=${user}

RUN mkdir /home/${user}
    
RUN addgroup -S ${user}
RUN adduser -S -G ${user} ${user} && \
	chown -R ${user}:${user} /home/${user}

USER ${user}