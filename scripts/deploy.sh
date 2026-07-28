#!/usr/bin/env bash
#
# Deploy de produção — Quiz em Família (Laravel) no aaPanel
#
# Uso (no servidor):
#   cd /www/wwwroot/quizemfamilia.com.br
#   # Configure o .env antes (APP_ENV=production, DB_HOST=127.0.0.1, etc.)
#   git pull
#   bash scripts/deploy.sh
#
# Opções:
#   --pull            Executa git pull dentro do script (padrão: não; faça o pull antes)
#   --skip-migrate    Não executa migrate
#   --skip-npm        Não instala deps nem faz build do frontend
#   --skip-composer   Não executa composer install
#   --seed            Roda db:seed após migrate
#   --no-maintenance  Não entra em modo manutenção
#   -h, --help        Mostra esta ajuda
#
# Variáveis de ambiente (opcionais):
#   PHP_BIN=/www/server/php/83/bin/php
#   COMPOSER_BIN=composer
#   WEB_USER=www
#   WEB_GROUP=www
#   BRANCH=main
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

log()  { echo -e "${BLUE}[deploy]${NC} $*"; }
ok()   { echo -e "${GREEN}[ok]${NC} $*"; }
warn() { echo -e "${YELLOW}[aviso]${NC} $*"; }
err()  { echo -e "${RED}[erro]${NC} $*" >&2; }

die() {
  err "$*"
  exit 1
}

usage() {
  sed -n '2,32p' "$0" | sed 's/^# \{0,1\}//'
  exit 0
}

# ---------------------------------------------------------------------------
# Flags
# ---------------------------------------------------------------------------
# Pull fica de fora por padrão: rode `git pull` antes do deploy.
DO_PULL=0
SKIP_MIGRATE=0
SKIP_NPM=0
SKIP_COMPOSER=0
RUN_SEED=0
NO_MAINTENANCE=0

for arg in "$@"; do
  case "$arg" in
    --pull)            DO_PULL=1 ;;
    --skip-pull)       DO_PULL=0 ;; # compatível com uso antigo
    --skip-migrate)    SKIP_MIGRATE=1 ;;
    --skip-npm)        SKIP_NPM=1 ;;
    --skip-composer)   SKIP_COMPOSER=1 ;;
    --seed)            RUN_SEED=1 ;;
    --no-maintenance)  NO_MAINTENANCE=1 ;;
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

BRANCH="${BRANCH:-main}"
WEB_USER="${WEB_USER:-www}"
WEB_GROUP="${WEB_GROUP:-www}"

PHP_BIN="$(detect_php)" || die "PHP >= 8.3 não encontrado. Defina PHP_BIN=/www/server/php/83/bin/php"
COMPOSER_BIN="$(resolve_composer "$PHP_BIN" "$APP_DIR")" \
  || die "Composer não encontrado. Instale globalmente ou coloque composer.phar na raiz do projeto."

NODE_BIN="$(command -v node 2>/dev/null || true)"
NPM_BIN="$(command -v npm 2>/dev/null || true)"

MAINTENANCE_ON=0

cleanup() {
  if [[ "$MAINTENANCE_ON" -eq 1 && "$NO_MAINTENANCE" -eq 0 ]]; then
    warn "Saindo do modo manutenção após falha..."
    $PHP_BIN artisan up || true
  fi
}
trap cleanup EXIT

# ---------------------------------------------------------------------------
# Pré-checagens
# ---------------------------------------------------------------------------
log "App: $APP_DIR"
log "PHP: $($PHP_BIN -v | head -n1)"
log "Composer: $COMPOSER_BIN"

[[ -f "$APP_DIR/artisan" ]] || die "artisan não encontrado em $APP_DIR"
[[ -f "$APP_DIR/.env" ]] || die ".env não encontrado. Copie .env.example e configure antes do deploy."

if ! $PHP_BIN artisan --version >/dev/null 2>&1; then
  # vendor pode ainda não existir — ok até o composer install
  warn "Laravel ainda não está instalado (vendor ausente). Continuando..."
fi

# ---------------------------------------------------------------------------
# Deploy
# ---------------------------------------------------------------------------
STARTED_AT="$(date '+%Y-%m-%d %H:%M:%S')"
log "Iniciando deploy em $STARTED_AT"

# 1) Maintenance
if [[ "$NO_MAINTENANCE" -eq 0 ]]; then
  if [[ -d "$APP_DIR/vendor" ]]; then
    log "Entrando em modo manutenção..."
    $PHP_BIN artisan down --retry=60 --secret="deploy-$(date +%s)" || warn "artisan down falhou (seguindo)"
    MAINTENANCE_ON=1
  else
    warn "Primeiro deploy: pulando maintenance mode (sem vendor)."
  fi
fi

# 2) Git pull (opcional — fluxo padrão: rode `git pull` antes deste script)
if [[ "$DO_PULL" -eq 1 ]]; then
  if [[ -d "$APP_DIR/.git" ]]; then
    log "Atualizando código (git pull origin $BRANCH)..."
    git fetch origin "$BRANCH"
    git pull --ff-only origin "$BRANCH"
    ok "Código atualizado"
  else
    warn "Não é um repositório git — pulando pull"
  fi
else
  log "Pulando git pull (já deve ter sido feito antes; use --pull se quiser)"
fi

# 3) Composer
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

# 4) Frontend (Vite)
if [[ "$SKIP_NPM" -eq 0 ]]; then
  if [[ -z "$NPM_BIN" ]]; then
    die "npm não encontrado. Instale Node.js no aaPanel (App Store → Node.js) ou use --skip-npm."
  fi
  log "Node: $($NODE_BIN -v) | npm: $($NPM_BIN -v)"
  log "Instalando dependências Node e gerando assets..."
  $NPM_BIN ci --ignore-scripts --no-audit --no-fund
  $NPM_BIN run build
  ok "Assets (Vite) gerados em public/build"
else
  log "Pulando build frontend (--skip-npm)"
fi

# 5) Storage link
log "Garantindo symlink public/storage..."
$PHP_BIN artisan storage:link --force 2>/dev/null || $PHP_BIN artisan storage:link || true

# 6) Migrações
if [[ "$SKIP_MIGRATE" -eq 0 ]]; then
  log "Rodando migrations..."
  $PHP_BIN artisan migrate --force --no-interaction
  ok "Migrations OK"
  if [[ "$RUN_SEED" -eq 1 ]]; then
    log "Rodando seeders..."
    $PHP_BIN artisan db:seed --force --no-interaction
    ok "Seeders OK"
  fi
else
  log "Pulando migrations (--skip-migrate)"
fi

# 7) Caches de produção
log "Limpando e reconstruindo caches..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache 2>/dev/null || true
ok "Caches prontos"

# 8) Permissões (aaPanel: usuário www)
if id "$WEB_USER" &>/dev/null; then
  log "Ajustando permissões para ${WEB_USER}:${WEB_GROUP}..."
  # Donos gerais
  chown -R "${WEB_USER}:${WEB_GROUP}" \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/public/build" 2>/dev/null || true

  # Escrita necessária para Laravel
  chmod -R ug+rwx "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
  find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \; 2>/dev/null || true
  find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \; 2>/dev/null || true
  ok "Permissões OK"
else
  warn "Usuário '$WEB_USER' não existe neste servidor — ajuste WEB_USER se necessário."
fi

# 9) Recarregar PHP-FPM (opcional, se root)
reload_php_fpm() {
  local services=(
    php-fpm-84
    php-fpm-83
    php-fpm-85
    php84-php-fpm
    php83-php-fpm
  )

  if [[ "$(id -u)" -ne 0 ]]; then
    warn "Sem root: não foi possível reiniciar PHP-FPM automaticamente."
    return
  fi

  # aaPanel init scripts
  for ver in 84 83 85 82; do
    if [[ -x "/etc/init.d/php-fpm-${ver}" ]]; then
      log "Recarregando php-fpm-${ver}..."
      "/etc/init.d/php-fpm-${ver}" reload && ok "PHP-FPM ${ver} recarregado" && return
    fi
  done

  for svc in "${services[@]}"; do
    if systemctl list-unit-files 2>/dev/null | grep -q "^${svc}"; then
      systemctl reload "$svc" && ok "$svc recarregado" && return
    fi
  done

  warn "PHP-FPM não recarregado automaticamente (ok se o painel já gerencia)."
}
reload_php_fpm

# 10) Sair da manutenção
if [[ "$MAINTENANCE_ON" -eq 1 ]]; then
  log "Saindo do modo manutenção..."
  $PHP_BIN artisan up
  MAINTENANCE_ON=0
  ok "Aplicação online"
fi

trap - EXIT

FINISHED_AT="$(date '+%Y-%m-%d %H:%M:%S')"
echo
ok "Deploy concluído com sucesso."
log "Início:  $STARTED_AT"
log "Fim:     $FINISHED_AT"
log "URL:     verifique APP_URL no .env"
echo
log "Lembretes aaPanel:"
echo "  • Document Root do site deve apontar para: ${APP_DIR}/public"
echo "  • PHP do site: 8.3+ (recomendado 8.3 ou 8.4)"
echo "  • Extensões: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, curl"
echo "  • Filas (QUEUE_CONNECTION=database): configure Supervisor/cron se necessário"
echo "  • Scheduler (se usar): * * * * * ${PHP_BIN} ${APP_DIR}/artisan schedule:run >> /dev/null 2>&1"
