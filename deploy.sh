php composer.phar self-update
php composer.phar install --prefer-dist -o -vvv --no-plugins --no-scripts
php composer.phar dump-autoload
php artisan key:generate
php artisan config:cache