#!/bin/env bash

set -Eeuo pipefail

trap catch_err SIGINT SIGTERM ERR

catch_err() {
    echo "Something went wrong"
    exit 255
}

uid=$(id -u)
gid=$(id -g)

RED="\e[31m"
GREEN="\e[32m"
ENDCOLOR="\e[0m"

echo -e "${GREEN}Developer enviroment initialization script${ENDCOLOR}"

if test -r docker-compose.yml 
then
    echo -e "${GREEN}STOP CURRENT STACK${ENDCOLOR}"
    docker compose down
    docker compose down
fi

#####
echo -e "${GREEN}Prepare dockerfile${ENDCOLOR}"

for DOCKERFILE in $(ls -1 ${PWD}/dev/dockerfile | grep default | sed -e 's/\..*$//');
do
    cp -vf ${PWD}/dev/dockerfile/${DOCKERFILE}.default ${PWD}/dev/dockerfile/${DOCKERFILE}.dockerfile;
    sed -i "s|<UID>|${uid}|gi" ${PWD}/dev/dockerfile/${DOCKERFILE}.dockerfile;
    sed -i "s|<GID>|${gid}|gi" ${PWD}/dev/dockerfile/${DOCKERFILE}.dockerfile;
done

echo -e "${GREEN}Prepare docker-compose.yml${ENDCOLOR}"

cp -vf ${PWD}/dev/docker-compose.yml.default ${PWD}/docker-compose.yml

sed -i "s|<UID>|${uid}|gi" ${PWD}/docker-compose.yml
sed -i "s|<GID>|${gid}|gi" ${PWD}/docker-compose.yml
sed -i "s|<PROJECT_DIR>|${PWD}|gi" ${PWD}/docker-compose.yml

while true; do
    echo -e "${GREEN}Enable Xdebug? Y(es) or N(o)?${ENDCOLOR}"
    read -p "? " xdebug
    case ${xdebug} in
        [Yy]* ) 
            echo -e "${GREEN}Xdebug enabled${ENDCOLOR}";
            cp -vf ${PWD}/dev/config/php82debug.ini ${PWD}/dev/config/php82.ini
            break;;
        [Nn]* )
            echo -e "${GREEN}Xdebug disabled${ENDCOLOR}";
            cp -vf ${PWD}/dev/config/php82nodebug.ini ${PWD}/dev/config/php82.ini
            break;;
        * )
            echo -e "${RED}Please answer Y(es) or N(o)${ENDCOLOR}";;
    esac
done

sudo rm -rvf ${PWD}/.env.*
cp -vf ${PWD}/dev/config/.env ${PWD}/.env.local

echo -e "${GREEN}Cleanup Symfony vendors${ENDCOLOR}"
sudo rm -rf ${PWD}/vendor

echo -e "${GREEN}Cleanup Symfony cache${ENDCOLOR}"
sudo rm -rf ${PWD}/var/cache/*

echo -e "${GREEN}Install Symfony vendors${ENDCOLOR}"
docker compose run --rm tour_price_cli php composer.phar install

docker compose down

echo -e "\n${GREEN}Now run 'docker compose up' to start stack${ENDCOLOR}"

exit 0
