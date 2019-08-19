#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

source "variables.sh"

sudo apt-get update
export LC_ALL="en_US.UTF-8"
export LC_CTYPE="en_US.UTF-8"

echo -e "********* Installing common *********"
sudo apt-get install -y curl software-properties-common zip unzip openssl acl

echo -e "********* Installing git *********"
sudo apt install -y git

echo -e "********* Installing nginx *********"
sudo apt install -y nginx

echo -e "********* Node js *********"
curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash -
sudo apt-get install -y nodejs

echo -e "********* Redis server *********"
sudo apt-get install -y redis-server && update-rc.d redis-server enable && update-rc.d redis-server defaults

echo -e "********* Supervisor *********"
sudo apt-get install -y supervisor

echo -e "********* Adding PPA *********"
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update

echo -e "********* Installing php *********"
sudo apt-get install -y php7.2 php7.2-common php7.2-cli php7.2-fpm php7.2-curl php7.2-json php7.2-tidy php7.2-mysql php7.2-gd php7.2-xml php7.2-zip php7.2-mbstring php7.2-dom php7.2-mysql php7.2-imap php7.2-bcmath php-apcu

# Fixes php.ini
sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.2/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /etc/php/7.2/fpm/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 120/g' /etc/php/7.2/fpm/php.ini

echo -e "********* Installing mysql server *********"
echo "mysql-server-5.7 mysql-server/root_password password ${DB_PASSWORD}" | sudo debconf-set-selections
echo "mysql-server-5.7 mysql-server/root_password_again password ${DB_PASSWORD}" | sudo debconf-set-selections
sudo apt-get -y install mysql-server-5.7

mysql -u${DB_USERNAME} -p${DB_PASSWORD} -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME}"

# Download and install composer
echo -e "********* Installing composer *********"
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

echo -e "********* Installing certbot *********"
sudo add-apt-repository ppa:certbot/certbot
sudo apt-get update
sudo apt-get install -y certbot python-certbot-nginx
# Requires press enter // TODO
sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048

# SWAP memory for composer
/bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=2048
/sbin/mkswap /var/swap.1
/sbin/swapon /var/swap.1

sudo apt -y autoremove

# Clean up cache
sudo apt-get clean