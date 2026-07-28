#!/usr/bin/env bash
#
# Ambiente local — Quiz em Família (Laravel Sail)
#
# Uso:
#   bash scripts/dev.sh           # sobe containers + migrate + Vite
#   bash scripts/dev.sh --build   # rebuild das imagens antes de subir
#   bash scripts/dev.sh --fresh   # migrate:fresh --seed
#   bash scripts/dev.sh --seed    # migrate + db:seed
#   bash scripts/dev.sh --stop    # para os containers
#   bash scripts/dev.sh --down    # para e remove containers/rede
#   bash scripts/dev.sh --shell   # abre shell no container da app
#
# Requisitos: Docker + Docker Compose
#
set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()  { echo -e "${BLUE}[dev]${NC} $*"; }
ok()   { echo -e "${GREEN}[ok]${NC} $*"; }
warn() { echo -e "${YELLOW}[aviso]${NC} $*"; }
err()  { echo -e "${RED}[erro]${NC} $*" >&2; }
die()  { err "$*"; exit 1; }

usage() {
  sed -n '2,22p' "$0" | sed 's/^# \{0,1\}//'
  exit 0
}

DO_BUILD=0
DO_FRESH=0
DO_STOP=0
DO_DOWN=0
DO_SHELL=0
DO_SEED=0
SKIP_MIGRATE=0
SKIP_NPM=0

for arg in "$@"; do
  case "$arg" in
    --build)        DO_BUILD=1 ;;
    --fresh)        DO_FRESH=1 ;;
    --seed)         DO_SEED=1 ;;
    --stop)         DO_STOP=1 ;;
    --down)         DO_DOWN=1 ;;
    --shell)        DO_SHELL=1 ;;
    --skip-migrate) SKIP_MIGRATE=1 ;;
    --skip-npm)     SKIP_NPM=1 ;;
    -h|--help)      usage ;;
    *)              die "Opção desconhecida: $arg (use --help)" ;;
  esac
done

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "$APP_DIR"

[[ -f "$APP_DIR/artisan" ]] || die "artisan não encontrado em $APP_DIR"
[[ -f "$APP_DIR/compose.yaml" ]] || die "compose.yaml não encontrado (Sail)"

command -v docker >/dev/null 2>&1 || die "Docker não encontrado. Instale o Docker Desktop / Engine."
docker info >/dev/null 2>&1 || die "Docker não está rodando. Inicie o Docker e tente de novo."

sail() {
  if [[ -x "$APP_DIR/vendor/bin/sail" ]]; then
    "$APP_DIR/vendor/bin/sail" "$@"
  else
    die "vendor/bin/sail ausente. Rode o bootstrap (o script tenta instalar o Composer automaticamente)."
  fi
}

bootstrap_vendor() {
  if [[ -x "$APP_DIR/vendor/bin/sail" ]]; then
    return 0
  fi

  log "vendor/ ausente — instalando dependências PHP via Composer (Docker)..."
  docker run --rm \
    --user "$(id -u):$(id -g)" \
    -v "$APP_DIR:/var/www/html" \
    -w /var/www/html \
    composer:2 \
    composer install --ignore-platform-reqs --no-interaction --prefer-dist

  [[ -x "$APP_DIR/vendor/bin/sail" ]] || die "Falha ao instalar vendor/bin/sail"
  ok "Composer OK"
}

ensure_env() {
  if [[ ! -f "$APP_DIR/.env" ]]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    ok ".env criado a partir do .env.example (Sail/MySQL)"
  else
    log ".env já existe"
  fi
}

wait_for_mysql() {
  log "Aguardando MySQL ficar saudável..."
  local i
  local pass
  pass="$(grep -E '^DB_PASSWORD=' "$APP_DIR/.env" | head -n1 | cut -d= -f2- | tr -d '"' | tr -d "'")"
  pass="${pass:-password}"

  for i in $(seq 1 60); do
    if sail exec -T mysql mysqladmin ping -h 127.0.0.1 -p"${pass}" --silent 2>/dev/null; then
      ok "MySQL pronto"
      return 0
    fi
    sleep 2
  done
  warn "MySQL ainda não respondeu — migrate pode falhar; tente de novo em alguns segundos."
}

# --- comandos curtos ---
if [[ "$DO_STOP" -eq 1 ]]; then
  bootstrap_vendor
  log "Parando containers..."
  sail stop
  ok "Containers parados"
  exit 0
fi

if [[ "$DO_DOWN" -eq 1 ]]; then
  bootstrap_vendor
  log "Derrubando containers (sail down)..."
  sail down
  ok "Ambiente derrubado"
  exit 0
fi

if [[ "$DO_SHELL" -eq 1 ]]; then
  bootstrap_vendor
  sail shell
  exit 0
fi

# --- subir ambiente ---
ensure_env
bootstrap_vendor

if [[ "$DO_BUILD" -eq 1 ]]; then
  log "Build das imagens Sail..."
  sail build --no-cache
fi

log "Subindo containers (sail up -d)..."
sail up -d
ok "Containers no ar"

# APP_KEY
if ! grep -qE '^APP_KEY=base64:' "$APP_DIR/.env"; then
  log "Gerando APP_KEY..."
  sail artisan key:generate --force
  ok "APP_KEY gerada"
fi

wait_for_mysql

# Storage link
sail artisan storage:link >/dev/null 2>&1 || true

# Migrations
if [[ "$SKIP_MIGRATE" -eq 0 ]]; then
  if [[ "$DO_FRESH" -eq 1 ]]; then
    warn "migrate:fresh --seed (apaga dados locais do banco)"
    sail artisan migrate:fresh --seed --force
  else
    log "Rodando migrations..."
    sail artisan migrate --force
    if [[ "$DO_SEED" -eq 1 ]]; then
      log "Rodando seeders..."
      sail artisan db:seed --force
    fi
  fi
  ok "Banco OK"
fi

# Frontend
APP_PORT="${APP_PORT:-80}"
VITE_PORT="${VITE_PORT:-5173}"
if [[ "$APP_PORT" == "80" ]]; then
  APP_URL_HINT="http://localhost"
else
  APP_URL_HINT="http://localhost:${APP_PORT}"
fi

if [[ "$SKIP_NPM" -eq 0 ]]; then
  log "Instalando dependências Node no container..."
  sail npm install --ignore-scripts
  echo
  ok "Ambiente local pronto"
  log "App:  ${APP_URL_HINT}"
  log "Vite: http://localhost:${VITE_PORT}"
  echo
  log "Iniciando Vite (Ctrl+C para parar o Vite; containers continuam no ar)..."
  echo "  Para parar tudo:  bash scripts/dev.sh --stop"
  echo
  sail npm run dev
else
  echo
  ok "Containers no ar (sem Vite)."
  log "App: ${APP_URL_HINT}"
  log "Vite depois: ./vendor/bin/sail npm run dev"
fi
