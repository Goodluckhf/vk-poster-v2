#!/bin/bash

#Цвета
GREEN='\033[1;32m'
NC='\033[0m'

if [[ $1 == "build" ]]
then
	docker build -t registry.gitlab.com/just1ce/poster/php-base:dev --cache-from registry.gitlab.com/just1ce/poster/php-base:dev -f ./docker-services/php-base/Dockerfile ./
	docker build -t registry.gitlab.com/just1ce/poster/php-base:prod --cache-from registry.gitlab.com/just1ce/poster/php-base:prod -f ./docker-services/php-base/Dockerfile.prod ./
	docker-compose -f compose.base.yml -f compose.dev.yml build
	docker-compose -f compose.base.yml -f compose.prod.yml build
else
	docker-compose -f compose.base.yml -f compose.prod.yml pull
	docker-compose -f compose.base.yml -f compose.prod.yml up -d
    # Ждем пока мускул оклимается
    bash ./bin/wait_mysql.sh
    docker exec poster php artisan migrate --seed
fi


for service in "poster" "php-cron"
do
	echo -e "${GREEN}Start deploying [$service]${NC}"
	docker exec $service bash ./bin/deploy.sh
	echo -e "${GREEN}deploying [$service] is successfull!${NC}"
done
