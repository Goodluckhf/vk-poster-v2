FROM registry.gitlab.com/just1ce/poster/php-app:dev

RUN touch crontab.tmp \
    && echo '* * * * * /usr/local/bin/php /var/www/artisan schedule:run' > crontab.tmp \
    && crontab crontab.tmp \
    && rm -rf crontab.tmp
    
CMD crond -f