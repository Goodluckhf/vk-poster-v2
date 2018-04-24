#!/bin/bash
opt="--dev"
if [[ $1 == "prod" ]]
	then opt="--no-dev"
fi

php composer.phar self-update
php composer.phar install --prefer-dist -o -vvv --no-plugins --no-scripts $opt