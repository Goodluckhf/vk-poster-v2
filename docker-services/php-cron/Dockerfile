FROM just1ce/app-php-base:latest

RUN touch /cron.log
RUN touch crontab.tmp \
    && echo '* * * * * /usr/local/bin/php /var/www/artisan schedule:run >&1' > crontab.tmp \
    && crontab crontab.tmp \
    && rm -rf crontab.tmp
    
CMD crond -f