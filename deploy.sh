php composer.phar self-update
php composer.phar install --prefer-dist -o -vvv
php composer.phar dump-autoload
php artisan key:generate
php artisan migrate --seed

service cron start
apache2-foreground