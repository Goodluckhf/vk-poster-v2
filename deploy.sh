php composer.phar self-update
php composer.phar install --prefer-dist -o -vvv
php composer.phar dump-autoload
php artisan config:cache
php artisan key:generate
php artisan migrate --seed


echo 'root' | sudo -S service cron start
apache2-foreground