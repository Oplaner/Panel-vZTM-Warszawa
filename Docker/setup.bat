@echo off
setlocal enabledelayedexpansion

:: Read environment properties
for /f "usebackq tokens=1,* delims==" %%i in (".env") do (
    set %%i=%%j
)

:: Remove containers, images and network
docker rm -f %PHPMYADMIN_APP_NAME% >nul 2>nul
docker rm -f %MARIADB_APPLICATION_NAME% >nul 2>nul
docker rm -f %APACHE_APPLICATION_NAME% >nul 2>nul
docker rmi -f phpmyadmin >nul 2>nul
docker rmi -f mariadb >nul 2>nul
docker rmi -f %APACHE_APPLICATION_NAME% >nul 2>nul
docker network rm %NETWORK_NAME% >nul 2>nul

:: Create network for containers
docker network create %NETWORK_NAME%

:: Build Apache image
docker build ^
    --build-arg PHP_VERSION=%PHP_VERSION% ^
    -t %APACHE_APPLICATION_NAME%:%PHP_VERSION% .

:: Run Apache container
docker run -d --name %APACHE_APPLICATION_NAME% ^
    --network %NETWORK_NAME% ^
    -p 80:80 ^
    -v "%CD%\..":/var/www/html ^
    %APACHE_APPLICATION_NAME%:%PHP_VERSION%

:: Run MariaDB container
docker run -d --name %MARIADB_APPLICATION_NAME% ^
    --network %NETWORK_NAME% ^
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes ^
    mariadb:%MARIADB_VERSION%

:: Wait for MariaDB to be ready
:readiness-check
docker exec %MARIADB_APPLICATION_NAME% mariadb-admin ping -u %DATABASE_USER% --silent
if errorlevel 1 (
    timeout /t 1 >nul
    goto readiness-check
)

:: Create tables
docker exec -i %MARIADB_APPLICATION_NAME% mariadb -u %DATABASE_USER% ^
    -e "CREATE DATABASE %DATABASE_NAME% CHARACTER SET %DATABASE_CHARSET% COLLATE %DATABASE_COLLATION%;"
for %%f in ("..\Source\Database schema\*.sql") do (
    docker exec -i %MARIADB_APPLICATION_NAME% mariadb -u %DATABASE_USER% %DATABASE_NAME% < "%%f"
)

:: Run phpMyAdmin container
docker run -d --name %PHPMYADMIN_APPLICATION_NAME% ^
    --network %NETWORK_NAME% ^
    -e PMA_ABSOLUTE_URI=http://localhost/phpmyadmin ^
    -e PMA_HOST=%MARIADB_APPLICATION_NAME% ^
    -e PMA_USER=%DATABASE_USER% ^
    phpmyadmin:%PHPMYADMIN_VERSION%