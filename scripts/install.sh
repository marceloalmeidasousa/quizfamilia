#!/usr/bin/env bash
#
# Instalação inicial — Quiz em Família (Laravel) no aaPanel
#
# Uso (primeira vez no servidor):
#   cd /www/wwwroot/quizemfamilia.com.br
#   git pull   # se o código ainda não estiver atualizado
#   bash scripts/install.sh
#
# Ou com variáveis (não interativo):
#   APP_URL=https://quizemfamilia.com.br \
#   DB_DATABASE=quizfamilia \
#   DB_USERNAME=quizfamilia \
#   DB_PASSWORD='sua_senha' \
#   bash scripts/install.sh --yes
#
# Opções:
#   --yes             Não pergunta confirmações (usa variáveis / defaults)
#   --fresh           migrate:fresh (APAGA TODOS OS DADOS) + seed
#   --no-seed         Não roda seeders
#   --force-env       Recria .env a partir do .env.example (faz backup do atual)
#   --skip-npm        Não instala deps nem faz build do frontend
#   --skip-composer   Não executa composer install
#   -h, --help        Mostra esta ajuda
#
# Variáveis de ambiente (opcionais):
#   PHP_BIN  COMPOSER_BIN  WEB_USER  WEB_GROUP
#   APP_URL  DB_HOST  DB_PORT  DB_DATABASE  DB_USERNAME  DB_PASSWORD
#
set -euo pipefail

# ---------------------------------------------------------------------------
# Cores / helpers
# ---------------------------------------------------------------------------
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()  { echo -e "${BLUE}[install]${NC} $*"; }
ok()   { echo -e "${GREEN}[ok]${NC} $*"; }
warn() { echo -e "${YELLOW}[aviso]${NC} $*"; }
err()  { echo -e "${RED}[erro]${NC} $*" >&2; }

die() {
  err "$*"
  exit 1
}

usage() {
  sed -n '2,36p' "$0" | sed 's/^# \{0,1\}//'
  exit 0
}

ask() {
  # ask "Pergunta" "default"
  local prompt="$1"
  local default="${2:-}"
  local reply
  if [[ -n "$default" ]]; then
    read -r -p "$prompt [$default]: " reply
    echo "${reply:-$default}"
  else
    read -r -p "$prompt: " reply
    echo "$reply"
  fi
}

confirm() {
  # confirm "mensagem" — retorna 0 se sim
  local msg="$1"
  local reply
  read -r -p "$msg [s/N]: " reply
  [[ "$reply" =~ ^[sSyY]$ ]]
}

set_env_key() {
  # set_env_key KEY value  — atualiza ou adiciona chave no .env
  local key="$1"
  local value="$2"
  local file="$APP_DIR/.env"
  local quoted="$value"

  # Aspas se houver caracteres sensíveis no .env
  case "$value" in
    *[[:space:]]*|*[\#\"\'\$\`\\=]*)
      quoted="\"${value//\"/\\\"}\""
      ;;
  esac

  if grep -qE "^${key}=" "$file"; then
    # Reescreve o arquivo sem depender de sed + delimitadores frágeis
    local tmp
    tmp="$(mktemp)"
    awk -v k="$key" -v v="$quoted" '
      BEGIN { done=0 }
      $0 ~ ("^" k "=") { print k "=" v; done=1; next }
      { print }
      END { if (!done) print k "=" v }
    ' "$file" > "$tmp"
    mv "$tmp" "$file"
  else
    printf '\n%s=%s\n' "$key" "$quoted" >> "$file"
  fi
}

# ---------------------------------------------------------------------------
# Flags
# ---------------------------------------------------------------------------
ASSUME_YES=0
MIGRATE_FRESH=0
RUN_SEED=1
FORCE_ENV=0
SKIP_NPM=0
SKIP_COMPOSER=0

for arg in "$@"; do
  case "$arg" in
    --yes|-y)          ASSUME_YES=1 ;;
    --fresh)           MIGRATE_FRESH=1 ;;
    --no-seed)         RUN_SEED=0 ;;
    --force-env)       FORCE_ENV=1 ;;
    --skip-npm)        SKIP_NPM=1 ;;
    --skip-composer)   SKIP_COMPOSER=1 ;;
    -h|--help)         usage ;;
    *)                 die "Opção desconhecida: $arg (use --help)" ;;
  esac
done

# ---------------------------------------------------------------------------
# Paths e binários
# ---------------------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=common.sh
source "${SCRIPT_DIR}/common.sh"

APP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "$APP_DIR"

ensure_runtime_env "$APP_DIR"

WEB_USER="${WEB_USER:-www}"
WEB_GROUP="${WEB_GROUP:-www}"

PHP_BIN="$(detect_php)" || die "PHP >= 8.3 não encontrado. Defina PHP_BIN=/www/server/php/83/bin/php"
COMPOSER_BIN="$(resolve_composer "$PHP_BIN" "$APP_DIR")" \
  || die "Composer não encontrado. Instale globalmente ou coloque composer.phar na raiz do projeto."

NODE_BIN="$(command -v node 2>/dev/null || true)"
NPM_BIN="$(command -v npm 2>/dev/null || true)"

[[ -f "$APP_DIR/artisan" ]] || die "artisan não encontrado em $APP_DIR"
[[ -f "$APP_DIR/.env.example" ]] || die ".env.example não encontrado"

STARTED_AT="$(date '+%Y-%m-%d %H:%M:%S')"
log "Instalação inicial em $APP_DIR"
log "PHP: $($PHP_BIN -v | head -n1)"

# ---------------------------------------------------------------------------
# 1) .env
# ---------------------------------------------------------------------------
ENV_CREATED=0

if [[ -f "$APP_DIR/.env" && "$FORCE_ENV" -eq 1 ]]; then
  backup="$APP_DIR/.env.backup.$(date +%Y%m%d%H%M%S)"
  cp "$APP_DIR/.env" "$backup"
  warn "Backup do .env atual: $backup"
  cp "$APP_DIR/.env.example" "$APP_DIR/.env"
  ENV_CREATED=1
  ok ".env recriado a partir do .env.example"
elif [[ ! -f "$APP_DIR/.env" ]]; then
  cp "$APP_DIR/.env.example" "$APP_DIR/.env"
  ENV_CREATED=1
  ok ".env criado a partir do .env.example"
else
  log ".env já existe — mantendo (use --force-env para recriar)"
fi

# Defaults de produção no .env novo
if [[ "$ENV_CREATED" -eq 1 ]]; then
  set_env_key "APP_ENV" "production"
  set_env_key "APP_DEBUG" "false"
  set_env_key "LOG_LEVEL" "error"
  set_env_key "DB_HOST" "127.0.0.1"
  set_env_key "DB_PORT" "3306"
  set_env_key "APP_URL" "https://quizemfamilia.com.br"
fi

# Coleta / aplica configuração
APP_URL_VAL="${APP_URL:-}"
DB_HOST_VAL="${DB_HOST:-}"
DB_PORT_VAL="${DB_PORT:-}"
DB_DATABASE_VAL="${DB_DATABASE:-}"
DB_USERNAME_VAL="${DB_USERNAME:-}"
DB_PASSWORD_VAL="${DB_PASSWORD:-}"

if [[ "$ASSUME_YES" -eq 0 ]]; then
  echo
  log "Configure os valores de produção (Enter mantém o sugerido):"
  APP_URL_VAL="$(ask "APP_URL" "${APP_URL_VAL:-https://quizemfamilia.com.br}")"
  DB_HOST_VAL="$(ask "DB_HOST" "${DB_HOST_VAL:-127.0.0.1}")"
  DB_PORT_VAL="$(ask "DB_PORT" "${DB_PORT_VAL:-3306}")"
  DB_DATABASE_VAL="$(ask "DB_DATABASE" "${DB_DATABASE_VAL:-quizfamilia}")"
  DB_USERNAME_VAL="$(ask "DB_USERNAME" "${DB_USERNAME_VAL:-quizfamilia}")"
  DB_PASSWORD_VAL="$(ask "DB_PASSWORD" "${DB_PASSWORD_VAL:-}")"
  echo
else
  APP_URL_VAL="${APP_URL_VAL:-https://quizemfamilia.com.br}"
  DB_HOST_VAL="${DB_HOST_VAL:-127.0.0.1}"
  DB_PORT_VAL="${DB_PORT_VAL:-3306}"
  DB_DATABASE_VAL="${DB_DATABASE_VAL:-quizfamilia}"
  DB_USERNAME_VAL="${DB_USERNAME_VAL:-quizfamilia}"
  [[ -n "$DB_PASSWORD_VAL" ]] || die "Com --yes, defina DB_PASSWORD (e preferencialmente DB_DATABASE / DB_USERNAME)."
fi

[[ -n "$DB_PASSWORD_VAL" ]] || die "DB_PASSWORD é obrigatório."

set_env_key "APP_ENV" "production"
set_env_key "APP_DEBUG" "false"
set_env_key "LOG_LEVEL" "error"
set_env_key "APP_URL" "$APP_URL_VAL"
set_env_key "DB_CONNECTION" "mysql"
set_env_key "DB_HOST" "$DB_HOST_VAL"
set_env_key "DB_PORT" "$DB_PORT_VAL"
set_env_key "DB_DATABASE" "$DB_DATABASE_VAL"
set_env_key "DB_USERNAME" "$DB_USERNAME_VAL"
set_env_key "DB_PASSWORD" "$DB_PASSWORD_VAL"
ok ".env configurado para produção"

# ---------------------------------------------------------------------------
# Confirmação
# ---------------------------------------------------------------------------
echo
log "Resumo:"
echo "  APP_URL      = $APP_URL_VAL"
echo "  DB_HOST      = $DB_HOST_VAL:$DB_PORT_VAL"
echo "  DB_DATABASE  = $DB_DATABASE_VAL"
echo "  DB_USERNAME  = $DB_USERNAME_VAL"
echo "  migrate      = $([[ "$MIGRATE_FRESH" -eq 1 ]] && echo 'migrate:fresh (DESTRUTIVO)' || echo 'migrate')"
echo "  seed         = $([[ "$RUN_SEED" -eq 1 ]] && echo 'sim' || echo 'não')"
echo

if [[ "$MIGRATE_FRESH" -eq 1 ]]; then
  warn "ATENÇÃO: --fresh apaga TODAS as tabelas e dados do banco '$DB_DATABASE_VAL'."
fi

if [[ "$ASSUME_YES" -eq 0 ]]; then
  confirm "Continuar com a instalação?" || die "Instalação cancelada."
fi

# ---------------------------------------------------------------------------
# 2) Composer
# ---------------------------------------------------------------------------
if [[ "$SKIP_COMPOSER" -eq 0 ]]; then
  log "Instalando dependências PHP (composer --no-dev)..."
  $COMPOSER_BIN install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-ansi
  ok "Composer OK"
else
  log "Pulando composer (--skip-composer)"
fi

# ---------------------------------------------------------------------------
# 3) APP_KEY
# ---------------------------------------------------------------------------
if grep -qE '^APP_KEY=$' "$APP_DIR/.env" || ! grep -qE '^APP_KEY=base64:' "$APP_DIR/.env"; then
  log "Gerando APP_KEY..."
  $PHP_BIN artisan key:generate --force --no-interaction
  ok "APP_KEY gerada"
else
  log "APP_KEY já definida — mantendo"
fi

# ---------------------------------------------------------------------------
# 4) Frontend
# ---------------------------------------------------------------------------
if [[ "$SKIP_NPM" -eq 0 ]]; then
  [[ -n "$NPM_BIN" ]] || die "npm não encontrado. Instale Node.js no aaPanel ou use --skip-npm."
  log "Node: $($NODE_BIN -v) | npm: $($NPM_BIN -v)"
  log "Instalando dependências Node e gerando assets..."
  if [[ -f "$APP_DIR/package-lock.json" ]]; then
    $NPM_BIN ci --ignore-scripts --no-audit --no-fund
  else
    $NPM_BIN install --ignore-scripts --no-audit --no-fund
  fi
  $NPM_BIN run build
  ok "Assets (Vite) gerados"
else
  log "Pulando build frontend (--skip-npm)"
fi

# ---------------------------------------------------------------------------
# 5) Storage
# ---------------------------------------------------------------------------
log "Criando symlink public/storage..."
$PHP_BIN artisan storage:link --force 2>/dev/null || $PHP_BIN artisan storage:link || true

mkdir -p \
  "$APP_DIR/storage/framework/cache" \
  "$APP_DIR/storage/framework/sessions" \
  "$APP_DIR/storage/framework/views" \
  "$APP_DIR/storage/logs" \
  "$APP_DIR/bootstrap/cache"

# ---------------------------------------------------------------------------
# 6) Banco
# ---------------------------------------------------------------------------
log "Testando conexão com o banco..."
$PHP_BIN artisan db:show --no-interaction >/dev/null 2>&1 \
  || $PHP_BIN artisan migrate:status --no-interaction >/dev/null 2>&1 \
  || warn "Não foi possível validar o banco via artisan (seguindo mesmo assim)."

if [[ "$MIGRATE_FRESH" -eq 1 ]]; then
  if [[ "$ASSUME_YES" -eq 0 ]]; then
    confirm "Confirma migrate:fresh (apaga tudo)?" || die "Cancelado."
  fi
  log "Rodando migrate:fresh..."
  if [[ "$RUN_SEED" -eq 1 ]]; then
    $PHP_BIN artisan migrate:fresh --seed --force --no-interaction
  else
    $PHP_BIN artisan migrate:fresh --force --no-interaction
  fi
  ok "Banco recriado"
else
  log "Rodando migrate..."
  $PHP_BIN artisan migrate --force --no-interaction
  ok "Migrations OK"
  if [[ "$RUN_SEED" -eq 1 ]]; then
    log "Rodando seeders..."
    $PHP_BIN artisan db:seed --force --no-interaction
    ok "Seeders OK"
  fi
fi

# ---------------------------------------------------------------------------
# 7) Caches de produção
# ---------------------------------------------------------------------------
log "Gerando caches de produção..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache 2>/dev/null || true
ok "Caches prontos"

# ---------------------------------------------------------------------------
# 8) Permissões (aaPanel)
# ---------------------------------------------------------------------------
if id "$WEB_USER" &>/dev/null; then
  log "Ajustando permissões para ${WEB_USER}:${WEB_GROUP}..."
  chown -R "${WEB_USER}:${WEB_GROUP}" \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/public/build" 2>/dev/null || true
  chmod -R ug+rwx "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
  find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \; 2>/dev/null || true
  find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \; 2>/dev/null || true
  ok "Permissões OK"
else
  warn "Usuário '$WEB_USER' não existe — ajuste WEB_USER se necessário."
fi

FINISHED_AT="$(date '+%Y-%m-%d %H:%M:%S')"
echo
ok "Instalação concluída."
log "Início: $STARTED_AT"
log "Fim:    $FINISHED_AT"
log "URL:    $APP_URL_VAL"
echo
log "Próximos passos no aaPanel:"
echo "  • Document Root → ${APP_DIR}/public"
echo "  • PHP do site → 8.3+"
echo "  • SSL (Let's Encrypt) para $APP_URL_VAL"
echo "  • Deploys seguintes:"
echo "      cd ${APP_DIR}"
echo "      git pull"
echo "      bash scripts/deploy.sh"
