#!/bin/bash

#--env COLUMNS=`tput cols` --env LINES=`tput lines`

#Цвета
GREEN='\033[1;32m'
NC='\033[0m'

docker build -t registry.gitlab.com/just1ce/poster/php-base:dev --cache-from registry.gitlab.com/just1ce/poster/php-base:dev -f ./docker-services/php-base/Dockerfile ./
docker-compose -f compose.base.yml -f compose.dev.yml up -d --build

# Ждем пока мускул оклимается
bash ./bin/wait_mysql.sh

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