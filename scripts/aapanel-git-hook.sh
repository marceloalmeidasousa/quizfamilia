#!/usr/bin/env bash
#
# Cole este arquivo no Git Hook / comando de deploy do aaPanel:
#   bash /www/wwwroot/quizemfamilia.com.br/scripts/aapanel-git-hook.sh
#
# Vite 8 não roda no Node 18. Este script força o Node 20/22 do aaPanel.
#
set -euo pipefail

APP_DIR="${APP_DIR:-/www/wwwroot/quizemfamilia.com.br}"
cd "$APP_DIR"

echo "Iniciando Deploy..."

# Procura Node 20.19+ / 22.12+ instalado pelo aaPanel (não usa o Node 18 do sistema).
NODE_DIR=""
shopt -s nullglob
for bin in /www/server/nodejs/*/bin/node; do
  [[ -x "$bin" ]] || continue
  dir="$(dirname "$bin")"
  [[ -x "${dir}/npm" ]] || continue
  ver="$("$bin" -v 2>/dev/null | sed 's/^v//')"
  major="${ver%%.*}"
  minor="$(printf '%s' "$ver" | cut -d. -f2)"
  if (( major > 22 || (major == 22 && minor >= 12) || (major == 20 && minor >= 19) )); then
    NODE_DIR="$dir"
  fi
done
shopt -u nullglob

if [[ -z "$NODE_DIR" ]]; then
  echo "ERRO: Node 20.19+ ou 22.12+ não encontrado."
  echo "No aaPanel: App Store → Node.js Version Manager → instale o 22 LTS."
  echo "Pastas atuais:"
  ls -la /www/server/nodejs 2>/dev/null || echo "(sem /www/server/nodejs)"
  echo "Node do sistema: $(command -v node) $(node -v 2>/dev/null || true)"
  exit 1
fi

export PATH="${NODE_DIR}:${PATH}"
echo "Node: $(command -v node) $(node -v)"
echo "npm:  $(command -v npm) $(npm -v)"

git pull origin main

if [[ -x "$APP_DIR/scripts/deploy.sh" ]]; then
  export PATH
  bash "$APP_DIR/scripts/deploy.sh"
else
  composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
  npm ci --ignore-scripts --no-audit --no-fund
  npm run build
  php artisan migrate --force
  php artisan optimize:clear
  php artisan optimize
  php artisan storage:link --force || php artisan storage:link || true
fi

echo "Deploy finalizado com sucesso em $(date)"
