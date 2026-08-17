#!/usr/bin/env bash
#
# Deploy via webhook do aaPanel — Quiz em Família
#
# Cole este script (ou chame este arquivo) no campo de comando do
# Website → Git → Webhook / Post-Receive Hook do aaPanel.
#
# Exemplo no painel (comando do webhook):
#   bash /www/wwwroot/quizemfamilia.com.br/scripts/aapanel-webhook-deploy.sh
#
set -euo pipefail

APP_DIR="${APP_DIR:-/www/wwwroot/quizemfamilia.com.br}"
BRANCH="${BRANCH:-main}"
PHP_BIN="${PHP_BIN:-/www/server/php/84/bin/php}"
COMPOSER_BIN="${COMPOSER_BIN:-/usr/local/bin/composer}"
WEB_USER="${WEB_USER:-www}"
WEB_GROUP="${WEB_GROUP:-www}"
LOG_DIR="${LOG_DIR:-${APP_DIR}/storage/logs}"
LOG_FILE="${LOG_DIR}/deploy-webhook.log"
LOCK_FILE="${APP_DIR}/storage/logs/deploy-webhook.lock"

# Node do aaPanel (ajuste v22 → v20 se necessário)
export PATH="/www/server/nodejs/v22/bin:/www/server/nodejs/v20/bin:${PATH}"
export HOME="${HOME:-/home/${WEB_USER}}"
export COMPOSER_HOME="${COMPOSER_HOME:-${HOME}/.composer}"
export PHP_BIN COMPOSER_BIN WEB_USER WEB_GROUP BRANCH

mkdir -p "$LOG_DIR"
touch "$LOG_FILE" 2>/dev/null || true

exec >>"$LOG_FILE" 2>&1

echo "========== $(date '+%Y-%m-%d %H:%M:%S') webhook deploy =========="

cd "$APP_DIR"

# Evita dois deploys ao mesmo tempo
exec 9>"$LOCK_FILE"
if ! flock -n 9; then
  echo "[aviso] Deploy já em andamento — ignorando este webhook."
  exit 0
fi

# safe.directory (git como root/www)
git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true

# Ajusta PATH do Composer se o do sistema for antigo
if [[ -x /usr/local/bin/composer ]]; then
  COMPOSER_BIN=/usr/local/bin/composer
  export COMPOSER_BIN
fi

if [[ ! -x "$PHP_BIN" ]]; then
  echo "[erro] PHP não encontrado em $PHP_BIN"
  exit 1
fi

if [[ ! -f "$APP_DIR/scripts/deploy.sh" ]]; then
  echo "[erro] scripts/deploy.sh não encontrado em $APP_DIR"
  exit 1
fi

echo "[info] PHP: $($PHP_BIN -v | head -n1)"
echo "[info] Composer: $($PHP_BIN "$COMPOSER_BIN" --version 2>/dev/null || $COMPOSER_BIN --version)"
echo "[info] Node: $(command -v node && node -v || echo 'não encontrado')"
echo "[info] Branch: $BRANCH"

# Garante dono www nos logs/cache antes do deploy
if id "$WEB_USER" &>/dev/null; then
  chown -R "${WEB_USER}:${WEB_GROUP}" \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache" 2>/dev/null || true
fi

# Deploy completo (pull + composer + npm build + migrate + cache)
export PATH="/www/server/php/84/bin:/usr/local/bin:${PATH}"
bash "$APP_DIR/scripts/deploy.sh" --pull

echo "[ok] Deploy webhook concluído em $(date '+%Y-%m-%d %H:%M:%S')"
echo
