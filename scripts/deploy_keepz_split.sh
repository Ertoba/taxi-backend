#!/usr/bin/env bash
set -Eeuo pipefail

CONTAINER="${CONTAINER:-rideon-app-1}"
APP_ROOT="${APP_ROOT:-/var/www/html}"
SOURCE_COMMIT="${SOURCE_COMMIT:-46c6a039cfe04e83a61784ed4810ffcf7572766c}"
BACKUP_ROOT="${BACKUP_ROOT:-/opt/rideon/backups}"
STAMP="$(date -u +%Y%m%d-%H%M%S)"
BACKUP_DIR="${BACKUP_ROOT}/keepz-split-${STAMP}"
STAGE="/tmp/keepz-split-${STAMP}"
TMP_DIR="$(mktemp -d)"
SUCCESS=0
FILES_INSTALLED=0

FILES=(
  app/Console/Commands/ReconcileKeepzSplitPayments.php
  app/Console/Kernel.php
  app/Http/Controllers/Admin/Driver/KeepzPayoutController.php
  app/Http/Controllers/Admin/KeepzSplitSettingsController.php
  app/Http/Controllers/Api/V1/Admin/KeepzSettlementApiController.php
  app/Http/Controllers/Api/V1/Admin/PayoutMethodApiController.php
  app/Http/Controllers/Front/PaymentFrontController.php
  app/Models/KeepzSplitSettlement.php
  app/Providers/RouteServiceProvider.php
  app/Services/KeepzSplitService.php
  app/Strategies/KeepzSplitStrategy.php
  app/Support/KeepzReceiver.php
  database/migrations/2026_07_23_000001_add_keepz_split_driver_payout_settings.php
  database/migrations/2026_07_23_000002_create_keepz_split_settlements_table.php
  resources/views/admin/appUsers/driver/keepz_payout_method.blade.php
  resources/views/admin/appUsers/driver/payout_method_link.blade.php
  resources/views/admin/generalSettings/payment-methods/keepz-split.blade.php
  resources/views/admin/generalSettings/payment-methods/payment-links.blade.php
  routes/api.php
  routes/keepz_split.php
)

log() {
  printf '\n[%s] %s\n' "$(date -u +%H:%M:%S)" "$*"
}

restore_code() {
  log "Restoring application files from ${BACKUP_DIR}"

  if [[ -d "${BACKUP_DIR}/files" ]]; then
    while IFS= read -r -d '' backup_file; do
      relative="${backup_file#${BACKUP_DIR}/files/}"
      docker exec "$CONTAINER" mkdir -p "${APP_ROOT}/$(dirname "$relative")"
      docker cp "$backup_file" "${CONTAINER}:${APP_ROOT}/${relative}" >/dev/null
    done < <(find "${BACKUP_DIR}/files" -type f -print0)
  fi

  if [[ -f "${BACKUP_DIR}/missing-before.txt" ]]; then
    while IFS= read -r relative; do
      [[ -n "$relative" ]] || continue
      docker exec "$CONTAINER" rm -f "${APP_ROOT}/${relative}" || true
    done < "${BACKUP_DIR}/missing-before.txt"
  fi

  docker exec "$CONTAINER" sh -lc "cd '${APP_ROOT}' && php artisan optimize:clear" || true
}

cleanup() {
  rc=$?

  if [[ $rc -ne 0 && $FILES_INSTALLED -eq 1 && $SUCCESS -eq 0 ]]; then
    restore_code || true
    printf '\nDeployment failed. Code was restored. Additive migrations/settings may remain, but Keepz Split was forced Inactive.\n' >&2
  fi

  docker exec "$CONTAINER" rm -rf "$STAGE" >/dev/null 2>&1 || true
  rm -rf "$TMP_DIR"
  exit "$rc"
}
trap cleanup EXIT

for command in docker curl tar find; do
  command -v "$command" >/dev/null 2>&1 || {
    echo "Required command is missing: $command" >&2
    exit 1
  }
done

docker inspect "$CONTAINER" >/dev/null 2>&1 || {
  echo "Docker container was not found: $CONTAINER" >&2
  exit 1
}

docker exec "$CONTAINER" test -f "${APP_ROOT}/artisan" || {
  echo "Laravel application was not found at ${APP_ROOT}" >&2
  exit 1
}

log "Downloading audited backend commit ${SOURCE_COMMIT}"
curl -fsSL \
  "https://github.com/Ertoba/taxi-backend/archive/${SOURCE_COMMIT}.tar.gz" \
  -o "${TMP_DIR}/source.tar.gz"
tar -xzf "${TMP_DIR}/source.tar.gz" -C "$TMP_DIR"
SOURCE_DIR="$(find "$TMP_DIR" -mindepth 1 -maxdepth 1 -type d -name 'taxi-backend-*' | head -n 1)"

[[ -n "$SOURCE_DIR" && -d "$SOURCE_DIR" ]] || {
  echo "Downloaded source directory could not be located." >&2
  exit 1
}

for relative in "${FILES[@]}"; do
  [[ -f "${SOURCE_DIR}/${relative}" ]] || {
    echo "Required source file is missing: ${relative}" >&2
    exit 1
  }
done

log "Staging and syntax-checking files inside ${CONTAINER}"
docker exec "$CONTAINER" rm -rf "$STAGE"
docker exec "$CONTAINER" mkdir -p "$STAGE"

for relative in "${FILES[@]}"; do
  docker exec "$CONTAINER" mkdir -p "${STAGE}/$(dirname "$relative")"
  docker cp "${SOURCE_DIR}/${relative}" "${CONTAINER}:${STAGE}/${relative}" >/dev/null
done

for relative in "${FILES[@]}"; do
  if [[ "$relative" == *.php && "$relative" != resources/views/* ]]; then
    docker exec "$CONTAINER" php -l "${STAGE}/${relative}" >/dev/null
  fi
done

log "Creating rollback backup at ${BACKUP_DIR}"
mkdir -p "${BACKUP_DIR}/files"
: > "${BACKUP_DIR}/missing-before.txt"
printf '%s\n' "$SOURCE_COMMIT" > "${BACKUP_DIR}/source-commit.txt"

for relative in "${FILES[@]}"; do
  if docker exec "$CONTAINER" test -f "${APP_ROOT}/${relative}"; then
    mkdir -p "${BACKUP_DIR}/files/$(dirname "$relative")"
    docker cp "${CONTAINER}:${APP_ROOT}/${relative}" "${BACKUP_DIR}/files/${relative}" >/dev/null
  else
    printf '%s\n' "$relative" >> "${BACKUP_DIR}/missing-before.txt"
  fi
done

APP_OWNER="$(docker exec "$CONTAINER" stat -c '%u:%g' "${APP_ROOT}/app")"

log "Installing Keepz Split runtime files"
for relative in "${FILES[@]}"; do
  docker exec "$CONTAINER" mkdir -p "${APP_ROOT}/$(dirname "$relative")"
  docker exec "$CONTAINER" cp "${STAGE}/${relative}" "${APP_ROOT}/${relative}"
  docker exec "$CONTAINER" chown "$APP_OWNER" "${APP_ROOT}/${relative}"
done
FILES_INSTALLED=1

log "Applying only the two additive Keepz Split migrations"
docker exec "$CONTAINER" sh -lc \
  "cd '${APP_ROOT}' && php artisan migrate --force --path=database/migrations/2026_07_23_000001_add_keepz_split_driver_payout_settings.php"
docker exec "$CONTAINER" sh -lc \
  "cd '${APP_ROOT}' && php artisan migrate --force --path=database/migrations/2026_07_23_000002_create_keepz_split_settlements_table.php"

log "Forcing Keepz Split to Inactive until TEST verification is completed"
docker exec "$CONTAINER" sh -lc \
  "cd '${APP_ROOT}' && php artisan tinker --execute='\\App\\Models\\GeneralSetting::updateOrCreate([\"meta_key\" => \"keepz_split_status\"], [\"meta_value\" => \"Inactive\", \"module\" => 2]); echo \"KEEPZ_SPLIT=Inactive\".PHP_EOL;'"

log "Clearing Laravel caches"
docker exec "$CONTAINER" sh -lc "cd '${APP_ROOT}' && php artisan optimize:clear"

log "Verifying routes and database records"
docker exec "$CONTAINER" sh -lc \
  "cd '${APP_ROOT}' && php artisan route:list --path=keepz-split --columns=Method,URI,Name,Action"
docker exec "$CONTAINER" sh -lc \
  "cd '${APP_ROOT}' && php artisan tinker --execute='echo \"PAYOUT_METHODS=\".\\App\\Models\\PayoutMethod::where(\"name\", \"keepz split receiver\")->where(\"status\", 1)->count().PHP_EOL; echo \"SETTLEMENT_TABLE=\".(\\Illuminate\\Support\\Facades\\Schema::hasTable(\"keepz_split_settlements\") ? \"yes\" : \"no\").PHP_EOL;'"

SUCCESS=1
log "KEEPZ SPLIT BACKEND DEPLOYED SAFELY"
echo "Source commit: ${SOURCE_COMMIT}"
echo "Rollback backup: ${BACKUP_DIR}"
echo "Keepz Split status: Inactive"
echo "Next step: configure platform BRANCH-to-IBAN mapping and test with Keepz TEST mode."
