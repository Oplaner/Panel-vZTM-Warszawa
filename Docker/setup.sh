#!/bin/bash
set -e
source .env

# Remove containers, images and network
docker rm -f ${PHPMYADMIN_APPLICATION_NAME} 2>/dev/null || true
docker rm -f ${MARIADB_APPLICATION_NAME} 2>/dev/null || true
docker rm -f ${APACHE_APPLICATION_NAME} 2>/dev/null || true
docker rmi -f phpmyadmin 2>/dev/null || true
docker rmi -f mariadb 2>/dev/null || true
docker rmi -f ${APACHE_APPLICATION_NAME} 2>/dev/null || true
docker network rm ${NETWORK_NAME} 2>/dev/null || true

# Create network for containers
docker network create ${NETWORK_NAME}

# Build Apache image
docker build \
    --build-arg PHP_VERSION=${PHP_VERSION} \
    -t ${APACHE_APPLICATION_NAME}:${PHP_VERSION} .

# Run Apache container
docker run -d --name ${APACHE_APPLICATION_NAME} \
    --network ${NETWORK_NAME} \
    -p 80:80 \
    -v "$(cd .. && pwd)":/var/www/html \
    ${APACHE_APPLICATION_NAME}:${PHP_VERSION}

# Run MariaDB container
docker run -d --name ${MARIADB_APPLICATION_NAME} \
    --network ${NETWORK_NAME} \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    mariadb:${MARIADB_VERSION}

# Wait for MariaDB to be ready
until docker exec ${MARIADB_APPLICATION_NAME} mariadb-admin ping -u ${DATABASE_USER} --silent; do
    sleep 1
done

# Create tables
docker exec -i ${MARIADB_APPLICATION_NAME} mariadb -u ${DATABASE_USER} \
    -e "CREATE DATABASE ${DATABASE_NAME} CHARACTER SET ${DATABASE_CHARSET} COLLATE ${DATABASE_COLLATION};"
for file in "../Source/Database schema/*.sql"; do
    docker exec -i ${MARIADB_APPLICATION_NAME} mariadb -u ${DATABASE_USER} ${DATABASE_NAME} < "$file"
done

# Run phpMyAdmin container
docker run -d --name ${PHPMYADMIN_APPLICATION_NAME} \
    --network ${NETWORK_NAME} \
    -e PMA_ABSOLUTE_URI=http://localhost/phpmyadmin \
    -e PMA_HOST=${MARIADB_APPLICATION_NAME} \
    -e PMA_USER=${DATABASE_USER} \
    phpmyadmin:${PHPMYADMIN_VERSION}