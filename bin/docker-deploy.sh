#!/bin/bash

#-env COLUMNS=`tput cols` --env LINES=`tput lines`

#Цвета
GREEN='\033[1;32m'
NC='\033[0m'

docker-compose -f compose.base.yml -f compose.dev.yml up -d --build

echo -e "${GREEN}Waiting 25 sec for [mysql]${NC}"
sleep 25

docker exec poster php artisan migrate --seed

echo -e "${GREEN}Start install dependencies ${NC}"
docker exec poster bash ./bin/installDeps.sh

for service in "poster" "php-cron"
do
	echo -e "${GREEN}Start deploying [$service]${NC}"
	docker exec $service bash ./bin/deploy.sh
	docker exec $service php artisan config:clear
	echo -e "${GREEN}deploying [$service] is successfull!${NC}"
done