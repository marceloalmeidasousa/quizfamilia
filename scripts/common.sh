#!/usr/bin/env bash
# Funções compartilhadas pelos scripts de produção (aaPanel).
# shellcheck disable=SC2034

ensure_runtime_env() {
  local app_dir="${1:-}"

  # Cron / tarefas do aaPanel frequentemente não definem HOME
  if [[ -z "${HOME:-}" || "$HOME" == "/" ]]; then
    if [[ "$(id -u)" -eq 0 ]]; then
      export HOME="/root"
    else
      export HOME
      HOME="$(getent passwd "$(id -un)" 2>/dev/null | cut -d: -f6 || true)"
      export HOME="${HOME:-/tmp}"
    fi
  fi

  export COMPOSER_HOME="${COMPOSER_HOME:-${HOME}/.composer}"
  mkdir -p "$COMPOSER_HOME" 2>/dev/null || true

  # Node.js instalado pelo aaPanel (Version Manager)
  if ! command -v npm >/dev/null 2>&1; then
    local node_bin=""
    for candidate in \
      /www/server/nodejs/v22.*/bin \
      /www/server/nodejs/v22/bin \
      /www/server/nodejs/v20.*/bin \
      /www/server/nodejs/v20/bin \
      /www/server/nodejs/v*/bin
    do
      # shellcheck disable=SC2086
      for dir in $candidate; do
        if [[ -x "${dir}/npm" ]]; then
          node_bin="$dir"
          break 2
        fi
      done
    done
    if [[ -n "$node_bin" ]]; then
      export PATH="${node_bin}:${PATH}"
    fi
  fi

  # Git: repositório de outro usuário (ex.: www) executado como root
  if [[ -n "$app_dir" && -d "${app_dir}/.git" ]] && command -v git >/dev/null 2>&1; then
    git config --global --add safe.directory "$app_dir" 2>/dev/null || true
  fi
}

detect_php() {
  if [[ -n "${PHP_BIN:-}" && -x "$PHP_BIN" ]]; then
    echo "$PHP_BIN"
    return 0
  fi

  local candidates=(
    /www/server/php/84/bin/php
    /www/server/php/83/bin/php
    /www/server/php/85/bin/php
    /usr/bin/php
    "$(command -v php 2>/dev/null || true)"
  )

  local bin major minor
  for bin in "${candidates[@]}"; do
    [[ -z "$bin" || ! -x "$bin" ]] && continue
    major="$("$bin" -r 'echo PHP_MAJOR_VERSION;')"
    minor="$("$bin" -r 'echo PHP_MINOR_VERSION;')"
    if (( major > 8 || (major == 8 && minor >= 3) )); then
      echo "$bin"
      return 0
    fi
  done

  return 1
}

resolve_composer() {
  local php_bin="$1"
  local app_dir="$2"
  local composer_bin="${COMPOSER_BIN:-$(command -v composer 2>/dev/null || true)}"

  if [[ -n "$composer_bin" ]]; then
    echo "$composer_bin"
    return 0
  fi

  if [[ -f "${app_dir}/composer.phar" ]]; then
    echo "${php_bin} ${app_dir}/composer.phar"
    return 0
  fi

  return 1
}
