#!/bin/bash
# Enable strict error checking
set -euo pipefail

# Configuration
APP_NAME="zemwa-erp"
APP_BASE="/home/zemwa/apps/${APP_NAME}"
REPO_DIR="${APP_BASE}/repo"
RELEASES_DIR="${APP_BASE}/releases"
SHARED_DIR="${APP_BASE}/shared"
CURRENT_LINK="${APP_BASE}/current"
BRANCH="main" # Change this to your deploy branch (e.g. "staging" or "production")

# PHP binary configuration (especially for cPanel/EasyPHP paths)
# Modify this to match the PHP version assigned to your cPanel shell/user
PHP_BIN="/opt/cpanel/ea-php82/root/usr/bin/php"

LOG_DIR="${SHARED_DIR}/logs/deploy"
TIMESTAMP="$(date +%Y%m%d%H%M%S)"
LOG_FILE="${LOG_DIR}/deploy-${TIMESTAMP}.log"

# Create log directory if not exists
mkdir -p "${LOG_DIR}"

# Redirect stdout and stderr to log file and console
exec > >(tee -a "${LOG_FILE}") 2>&1

echo "=================================================="
echo "==> Starting deployment"
echo "==> App: ${APP_NAME}"
echo "==> Branch: ${BRANCH}"
echo "==> Time: ${TIMESTAMP}"
echo "==> Log file: ${LOG_FILE}"
echo "=================================================="

NEW_RELEASE="${RELEASES_DIR}/${TIMESTAMP}"

echo "==> Updating repo from branch: ${BRANCH}"
git -C "${REPO_DIR}" fetch --tags origin
git -C "${REPO_DIR}" checkout "${BRANCH}"
git -C "${REPO_DIR}" reset --hard "origin/${BRANCH}"

# Read version number from Laravel version.txt file
APP_VERSION=$(cat "${REPO_DIR}/version.txt")
GIT_COMMIT=$(git -C "${REPO_DIR}" rev-parse --short HEAD)
GIT_TAG=$(git -C "${REPO_DIR}" tag --points-at HEAD | head -n 1 || true)

echo "==> Application version: ${APP_VERSION}"
echo "==> Git commit: ${GIT_COMMIT}"
if [ -n "${GIT_TAG}" ]; then
  echo "==> Git tag: ${GIT_TAG}"
else
  echo "==> Git tag: none"
fi

echo "==> Checking required files in shared directory"
[ -f "${SHARED_DIR}/.env" ] || { echo "ERROR: Missing ${SHARED_DIR}/.env"; exit 1; }
[ -d "${SHARED_DIR}/storage" ] || { echo "ERROR: Missing ${SHARED_DIR}/storage"; exit 1; }

# Initialize storage subdirectories in shared if they don't exist
mkdir -p "${SHARED_DIR}/storage/app/public"
mkdir -p "${SHARED_DIR}/storage/framework/cache/data"
mkdir -p "${SHARED_DIR}/storage/framework/sessions"
mkdir -p "${SHARED_DIR}/storage/framework/testing"
mkdir -p "${SHARED_DIR}/storage/framework/views"
mkdir -p "${SHARED_DIR}/storage/logs"

echo "==> Creating release directory: ${TIMESTAMP}"
mkdir -p "${NEW_RELEASE}"

echo "==> Copying source to new release"
rsync -a --delete \
  --exclude=".git" \
  --exclude="node_modules" \
  --exclude="vendor" \
  --exclude="storage" \
  "${REPO_DIR}/" "${NEW_RELEASE}/"

echo "==> Linking shared .env"
ln -sfn "${SHARED_DIR}/.env" "${NEW_RELEASE}/.env"

echo "==> Linking shared storage folder"
rm -rf "${NEW_RELEASE}/storage"
ln -sfn "${SHARED_DIR}/storage" "${NEW_RELEASE}/storage"

echo "==> Writing deploy metadata info"
cat > "${NEW_RELEASE}/DEPLOY_INFO" <<EOF
APP_NAME=${APP_NAME}
BRANCH=${BRANCH}
APP_VERSION=${APP_VERSION}
GIT_COMMIT=${GIT_COMMIT}
GIT_TAG=${GIT_TAG}
DEPLOYED_AT=${TIMESTAMP}
LOG_FILE=${LOG_FILE}
EOF

# Move to new release directory to run composer and npm
cd "${NEW_RELEASE}"

echo "==> Installing PHP Dependencies (Composer)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing Node Dependencies (NPM)"
npm ci

echo "==> Building frontend assets (Laravel Mix / Vite)"
npm run prod

echo "==> Clearing Application and Cache files"
"${PHP_BIN}" artisan optimize:clear

# Parse database credentials from the shared .env file to create a pre-deploy backup
clean_env_val() {
  local val="$1"
  val="${val//[$'\r\n']/}"
  val="${val#\"}"
  val="${val%\"}"
  val="${val#\'}"
  val="${val%\'}"
  echo "$val"
}

echo "==> Extracting database credentials from .env"
DB_HOST=$(clean_env_val "$(grep "^DB_HOST=" "${SHARED_DIR}/.env" | cut -d '=' -f 2- || echo '127.0.0.1')")
DB_DATABASE=$(clean_env_val "$(grep "^DB_DATABASE=" "${SHARED_DIR}/.env" | cut -d '=' -f 2- || echo '')")
DB_USERNAME=$(clean_env_val "$(grep "^DB_USERNAME=" "${SHARED_DIR}/.env" | cut -d '=' -f 2- || echo '')")
DB_PASSWORD=$(clean_env_val "$(grep "^DB_PASSWORD=" "${SHARED_DIR}/.env" | cut -d '=' -f 2- || echo '')")

if [ -n "${DB_DATABASE}" ] && [ -n "${DB_USERNAME}" ]; then
  BACKUP_DIR="${SHARED_DIR}/backups"
  mkdir -p "${BACKUP_DIR}"
  BACKUP_FILE="${BACKUP_DIR}/pre-deploy-${DB_DATABASE}-${TIMESTAMP}.sql"
  
  echo "==> Backing up database to ${BACKUP_FILE} before running migrations"
  export MYSQL_PWD="${DB_PASSWORD}"
  if mysqldump -h "${DB_HOST}" -u "${DB_USERNAME}" --single-transaction --quick --routines --triggers --events "${DB_DATABASE}" > "${BACKUP_FILE}"; then
    echo "==> Database backup created successfully"
    gzip "${BACKUP_FILE}"
    echo "==> Database backup compressed: ${BACKUP_FILE}.gz"
  else
    echo "ERROR: Database backup failed! Aborting deployment to protect production data."
    exit 1
  fi
else
  echo "ERROR: Could not parse database credentials from .env. Aborting deployment."
  exit 1
fi

echo "==> Running Database Migrations"
"${PHP_BIN}" artisan migrate --force

# Run module migrations if the command is supported
if "${PHP_BIN}" artisan list --raw | grep -q '^module:migrate'; then
  echo "==> Running Module Migrations"
  "${PHP_BIN}" artisan module:migrate --force
fi

echo "==> Linking Storage directory"
rm -rf "${NEW_RELEASE}/public/storage"
ln -sfn "${SHARED_DIR}/storage/app/public" "${NEW_RELEASE}/public/storage"

echo "==> Setting folder permissions"
chmod -R 775 "${SHARED_DIR}/storage"
chmod -R 775 "${NEW_RELEASE}/bootstrap/cache"

echo "==> Switching current release (atomic)"
rm -f "${APP_BASE}/.current_tmp"
ln -s "${NEW_RELEASE}" "${APP_BASE}/.current_tmp"
mv -Tf "${APP_BASE}/.current_tmp" "${CURRENT_LINK}"

# Cache config, route and views for production performance
echo "==> Generating optimization caches"
"${PHP_BIN}" artisan config:cache

if "${PHP_BIN}" artisan route:cache; then
  echo "==> Route cache created successfully"
else
  echo "==> WARNING: Route caching failed; continuing without route cache"
  "${PHP_BIN}" artisan route:clear
fi

"${PHP_BIN}" artisan view:cache

echo "==> Cleaning old releases (keeping last 5)"
cd "${RELEASES_DIR}"
ls -1dt */ 2>/dev/null | tail -n +6 | xargs -r rm -rf

echo "=================================================="
echo "==> Deployment completed successfully"
echo "==> Current release: ${NEW_RELEASE}"
echo "==> Deploy info: ${NEW_RELEASE}/DEPLOY_INFO"
echo "==> Log saved to: ${LOG_FILE}"
echo "=================================================="
