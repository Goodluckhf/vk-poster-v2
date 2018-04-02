php composer.phar self-update
php composer.phar install --prefer-dist -o -vvv
php artisan key:generate
php artisan migrate
apache2-foreground