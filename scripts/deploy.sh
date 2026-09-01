#!/bin/bash
# Enable strict error checking
set -euo pipefail

# Ensure database password variable is always cleared on exit
trap 'unset MYSQL_PWD 2>/dev/null || true' EXIT

# Runtime binaries
PHP_BIN="/opt/cpanel/ea-php83/root/usr/bin/php"
NODE_BIN="/opt/cpanel/ea-nodejs18/bin/node"
NPM_BIN="/opt/cpanel/ea-nodejs18/bin/npm"

# Ensure PHP binary and Node.js are available in PATH for composer and child processes
PHP_DIR="$(dirname "${PHP_BIN}")"
export PATH="${PHP_DIR}:/opt/cpanel/ea-nodejs18/bin:$PATH"

# Configuration
APP_NAME="zemwa-erp-in"
APP_BASE="/home/mithuntc/apps/${APP_NAME}"
REPO_DIR="${APP_BASE}/repo"
RELEASES_DIR="${APP_BASE}/releases"
SHARED_DIR="${APP_BASE}/shared"
CURRENT_LINK="${APP_BASE}/current"
BRANCH="prod" # Deploying from the stable production branch

LOG_DIR="${SHARED_DIR}/logs/deploy"
TIMESTAMP="$(date +%Y%m%d%H%M%S)"
LOG_FILE="${LOG_DIR}/deploy-${TIMESTAMP}.log"
LOCK_FILE="${APP_BASE}/deploy.lock"

# Prevent concurrent deployments using file descriptor locking
exec 9>"${LOCK_FILE}"
if ! flock -n 9; then
  echo "ERROR: Another deployment is already running. Aborting."
  exit 1
fi

# Pre-flight directory creation
mkdir -p "${RELEASES_DIR}"
mkdir -p "${SHARED_DIR}/backups"
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

echo "==> Checking runtime versions and system utilities"
for cmd in git rsync gzip flock; do
  command -v "$cmd" >/dev/null 2>&1 || {
    echo "ERROR: Required system command not found: $cmd"
    exit 1
  }
done

[ -x "${PHP_BIN}" ] || { echo "ERROR: PHP binary not found or not executable: ${PHP_BIN}"; exit 1; }
[ -x "${NODE_BIN}" ] || { echo "ERROR: Node binary not found or not executable: ${NODE_BIN}"; exit 1; }
[ -x "${NPM_BIN}" ] || { echo "ERROR: NPM binary not found or not executable: ${NPM_BIN}"; exit 1; }

MYSQLDUMP_BIN="$(command -v mysqldump || true)"
[ -n "${MYSQLDUMP_BIN}" ] && [ -x "${MYSQLDUMP_BIN}" ] || { echo "ERROR: mysqldump binary not found or not executable"; exit 1; }

[ -d "${REPO_DIR}/.git" ] || { echo "ERROR: Git repository missing at: ${REPO_DIR}"; exit 1; }

"${PHP_BIN}" -v | head -n 1
"${NODE_BIN}" -v
"${NPM_BIN}" -v

NEW_RELEASE="${RELEASES_DIR}/${TIMESTAMP}"

echo "==> Updating repo from branch: ${BRANCH}"
git -C "${REPO_DIR}" fetch origin "${BRANCH}" --tags
git -C "${REPO_DIR}" checkout "${BRANCH}"
git -C "${REPO_DIR}" reset --hard "origin/${BRANCH}"

if [ -f "${REPO_DIR}/version.txt" ]; then
  APP_VERSION="$(cat "${REPO_DIR}/version.txt")"
else
  APP_VERSION="unknown"
fi

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
  --exclude="/.git" \
  --exclude="/node_modules" \
  --exclude="/vendor" \
  --exclude="/storage" \
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

# Ensure bootstrap/cache directory exists and is writable for composer package discovery
mkdir -p "${NEW_RELEASE}/bootstrap/cache"
chmod -R u+rwX,g+rwX "${NEW_RELEASE}/bootstrap/cache"

# Move to new release directory for builds and commands
cd "${NEW_RELEASE}"

echo "==> Installing PHP Dependencies via Composer"
[ -f "${NEW_RELEASE}/composer.phar" ] || {
  echo "ERROR: composer.phar not found in release: ${NEW_RELEASE}/composer.phar"
  exit 1
}

"${PHP_BIN}" "${NEW_RELEASE}/composer.phar" install \
  --no-dev \
  --prefer-dist \
  --optimize-autoloader \
  --no-interaction

echo "==> Installing Node Dependencies (NPM)"
"${NPM_BIN}" ci

echo "==> Building frontend assets"
"${NPM_BIN}" run prod

echo "==> Removing Node build dependencies to save disk space"
rm -rf "${NEW_RELEASE}/node_modules"

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
  BACKUP_FILE="${BACKUP_DIR}/pre-deploy-${DB_DATABASE}-${TIMESTAMP}.sql"
  
  echo "==> Backing up database to ${BACKUP_FILE} before running migrations"
  export MYSQL_PWD="${DB_PASSWORD}"
  if "${MYSQLDUMP_BIN}" -h "${DB_HOST}" -u "${DB_USERNAME}" --single-transaction --quick --routines --triggers --events "${DB_DATABASE}" > "${BACKUP_FILE}"; then
    :
  else
    echo "==> Full mysqldump with routines/events failed (likely user privileges); retrying with standard triggers"
    if ! "${MYSQLDUMP_BIN}" -h "${DB_HOST}" -u "${DB_USERNAME}" --single-transaction --quick --triggers "${DB_DATABASE}" > "${BACKUP_FILE}"; then
      unset MYSQL_PWD
      rm -f "${BACKUP_FILE}"
      echo "ERROR: Database backup failed! Aborting deployment to protect production data."
      exit 1
    fi
  fi
  unset MYSQL_PWD
  echo "==> Database backup created successfully"
  gzip -f "${BACKUP_FILE}"
  echo "==> Database backup compressed: ${BACKUP_FILE}.gz"
else
  echo "ERROR: Could not parse database credentials from .env. Aborting deployment."
  exit 1
fi

echo "==> Running Database Migrations"
"${PHP_BIN}" artisan migrate --force

# Run module migrations (Nwidart Laravel Modules)
if "${PHP_BIN}" artisan list --raw | grep -q '^module:migrate'; then
  echo "==> Running Module Migrations"
  "${PHP_BIN}" artisan module:migrate --force
fi

echo "==> Linking Storage directory"
rm -rf "${NEW_RELEASE}/public/storage"
ln -sfn "${SHARED_DIR}/storage/app/public" "${NEW_RELEASE}/public/storage"

echo "==> Setting folder permissions"
chmod -R u+rwX,g+rwX "${SHARED_DIR}/storage"
chmod -R u+rwX,g+rwX "${NEW_RELEASE}/bootstrap/cache"

# Generate optimization caches BEFORE switching symlink so release is 100% warmed up
echo "==> Generating optimization caches"
"${PHP_BIN}" artisan config:cache

if "${PHP_BIN}" artisan route:cache; then
  echo "==> Route cache created successfully"
else
  echo "==> WARNING: Route caching failed; continuing without route cache"
  "${PHP_BIN}" artisan route:clear
fi

"${PHP_BIN}" artisan view:cache

# Atomic switch happens LAST after everything has succeeded without errors
echo "==> Switching current release (atomic)"
rm -f "${APP_BASE}/.current_tmp"
ln -s "${NEW_RELEASE}" "${APP_BASE}/.current_tmp"

# If 'current' is an existing physical directory (e.g. created by cPanel domain creation), remove it once
if [ -d "${CURRENT_LINK}" ] && [ ! -L "${CURRENT_LINK}" ]; then
  echo "==> Removing initial physical directory at ${CURRENT_LINK} to convert to symlink"
  rm -rf "${CURRENT_LINK}"
fi

mv -Tf "${APP_BASE}/.current_tmp" "${CURRENT_LINK}"

echo "==> Cleaning old releases (keeping last 5)"
cd "${RELEASES_DIR}"
ls -1dt */ 2>/dev/null | tail -n +6 | xargs -r rm -rf

echo "=================================================="
echo "==> Deployment completed successfully"
echo "==> Current release: ${NEW_RELEASE}"
echo "==> Deploy info: ${NEW_RELEASE}/DEPLOY_INFO"
echo "==> Log saved to: ${LOG_FILE}"
echo "=================================================="
