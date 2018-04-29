#!/bin/bash

#Цвета
GREEN='\033[1;32m'
NC='\033[0m'

if [[ $1 == "build" ]]
then
	docker-compose -f compose.base.yml -f compose.dev.yml build
	docker-compose -f compose.base.yml -f compose.prod.yml up -d --build
else
	docker-compose -f compose.base.yml -f compose.prod.yml pull
	docker-compose -f compose.base.yml -f compose.prod.yml up -d
fi

echo -e "${GREEN}Waiting 25 sec for [mysql]${NC}"
sleep 25

docker exec poster php artisan migrate --seed

for service in "poster" "php-cron"
do
	echo -e "${GREEN}Start deploying [$service]${NC}"
	docker exec $service bash ./bin/deploy.sh
	echo -e "${GREEN}deploying [$service] is successfull!${NC}"
done