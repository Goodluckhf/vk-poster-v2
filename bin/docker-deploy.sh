#!/bin/bash

#Цвета
GREEN='\033[1;32m'
NC='\033[0m'

docker-compose -f compose.base.yml -f compose.dev.yml up -d --build
sleep 3

docker exec poster php artisan migrate --seed
for service in "poster" "php-cron"
do
	echo -e "${GREEN}Start deploying [$service]${NC}"
	docker exec $service bash ./bin/deploy.sh
	echo -e "${GREEN}Start install dependencies [$service]${NC}"
	docker exec $service bash ./bin/installDeps.sh
	docker exec $service php artisan config:clear
	echo -e "${GREEN}deploying [$service] is successfull!${NC}"
done