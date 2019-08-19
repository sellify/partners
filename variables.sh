#!/bin/bash

# Variables
DB_NAME="sellify_partners"
DB_USERNAME="root"
DB_PASSWORD="root"
APP_NAME="sellify-partners"
DOMAIN_NAME="partners.niveshsaharan.com"
REPO_URL="https://github.com/sellify/partners.git"
DEPLOY_PATH="/var/www/html/${APP_NAME}"
RELEASE_NO=${1:-$(date +%s)}
RELEASE_PATH="${DEPLOY_PATH}/releases/${RELEASE_NO}"
SHARED_DIRS=("storage")
SHARED_FILES=(".env")
EMAIL="me@example.com"
INITIAL_DIR=$(pwd)