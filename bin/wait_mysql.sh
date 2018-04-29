#!/bin/bash

#Цвета
GREEN='\033[1;32m'
NC='\033[0m'

echo -e "${GREEN}Waiting for mysql ${NC}"
until docker exec mysql bash -c 'mysqladmin ping -h mysql -u"$MYSQL_USER" -p"$MYSQL_ROOT_PASSWORD"' &> /dev/null
do
  printf "${GREEN}.${NC}"
  sleep 1
done