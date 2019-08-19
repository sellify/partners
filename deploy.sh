#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

source "variables.sh"

echo -e "********* Check if there's a .env file in current directory *********"

if ! test -f $INITIAL_DIR/.env; then
    echo "There must be a .env file in your current directory."
    exit 1
fi

echo -e "********* Create required directories if doesn't exists already *********"
mkdir -p "${RELEASE_PATH}"
mkdir -p $DEPLOY_PATH/storage
mkdir -p $DEPLOY_PATH/storage/app/public
mkdir -p $DEPLOY_PATH/storage/framework/cache
mkdir -p $DEPLOY_PATH/storage/framework/session
mkdir -p $DEPLOY_PATH/storage/framework/views
mkdir -p $DEPLOY_PATH/storage/logs

echo -e "********* Go to release directory *********"
cd $RELEASE_PATH

echo -e "********* Fetch files from git *********"
git init
git remote add origin $REPO_URL
git pull origin master

echo -e "********* Install laravel dependencies with composer *********"
composer install -o --no-interaction --no-dev

echo -e "********* Copy .env file to project dir *********"
cp $INITIAL_DIR/.env $DEPLOY_PATH/.env

echo -e "********* Create symlinks to storage and .env *********"
ln -sf $DEPLOY_PATH/.env ./
rm -rf storage && ln -sf $DEPLOY_PATH/storage ./

echo -e "********* NPM *********"
npm install
npm run production

echo -e "********* Generate key *********"
php artisan key:generate --no-interaction --force

echo -e "********* Run database migrations *********"
php artisan migrate --no-interaction --force
php artisan db:seed --no-interaction --force

echo -e "********* Run optimization commands for laravel *********"
php artisan optimize
php artisan cache:clear
php artisan route:cache
php artisan view:clear
php artisan config:cache

php artisan passport:install

echo -e "********* Remove existing directory or symlink for the release and create a new one. *********"
rm -f $RELEASE_PATH/current
ln -sf $RELEASE_PATH $DEPLOY_PATH/current

echo -e "********* SSL configs *********"
SSL_PARAMS="$(sudo cat "${RELEASE_PATH}/ssl-params.conf")"
LETS_ENCRYPT="$(sudo cat "${RELEASE_PATH}/letsencrypt.conf")"

sudo test -f /etc/nginx/snippets/ssl-params.conf || echo "${SSL_PARAMS}" | sudo tee /etc/nginx/snippets/ssl-params.conf
sudo test -f /etc/nginx/snippets/letsencrypt.conf || echo "${LETS_ENCRYPT}" | sudo tee /etc/nginx/snippets/letsencrypt.conf

echo -e "********* Supervisor config *********"
SUPERVISOR_CONF="$(sudo cat "${RELEASE_PATH}/supervisor.conf")"
SUPERVISOR_CONF="${SUPERVISOR_CONF//_APP_NAME_/$APP_NAME}"
SUPERVISOR_CONF="${SUPERVISOR_CONF//_DEPLOY_PATH_/$DEPLOY_PATH}"
sudo test -f /etc/supervisor/conf.d/${APP_NAME}.conf || echo "${SUPERVISOR_CONF}" | sudo tee /etc/supervisor/conf.d/${APP_NAME}.conf

echo -e "********* Setup cron *********"
CRON="* * * * * www-data cd ${DEPLOY_PATH}/current && php artisan schedule:run >> /dev/null 2>&1"
sudo test -f /etc/cron.d/${APP_NAME}|| echo "${CRON}" | sudo tee /etc/cron.d/${APP_NAME}

echo -e "********* Generate SSL certificate *********"
sudo certbot --nginx -d ${DOMAIN_NAME} --non-interactive --agree-tos -m ${EMAIL}

echo -e "********* Nginx host *********"
VHOST="$(sudo cat "${RELEASE_PATH}/host.conf")"
VHOST="${VHOST//_APP_NAME_/$APP_NAME}"
VHOST="${VHOST//_DOMAIN_NAME_/$DOMAIN_NAME}"
VHOST="${VHOST//_DEPLOY_PATH_/$DEPLOY_PATH}"

sudo test -f /etc/nginx/sites-available/${DOMAIN_NAME}.conf || echo "${VHOST}" | sudo tee /etc/nginx/sites-available/${DOMAIN_NAME}.conf
sudo test -f /etc/nginx/sites-enabled/${DOMAIN_NAME}.conf || sudo ln -s /etc/nginx/sites-available/${DOMAIN_NAME}.conf /etc/nginx/sites-enabled/${DOMAIN_NAME}.conf

echo -e "********* Change owner *********"
sudo chown -R www-data:www-data "${RELEASE_PATH}"

echo -e "********* Restart queue *********"
php artisan queue:restart

echo -e "********* Reload nginx *********"
sudo service nginx reload