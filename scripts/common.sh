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

  # Vite 8 / Rolldown exige Node ^20.19 || >=22.12. Prefere o Node do aaPanel
  # mesmo quando um Node 18 já está no PATH (erro: styleText em node:util).
  local node_bin=""
  for candidate in \
    /www/server/nodejs/v22.*/bin \
    /www/server/nodejs/v22/bin \
    /www/server/nodejs/v20.*/bin \
    /www/server/nodejs/v20/bin
  do
    # shellcheck disable=SC2086
    for dir in $candidate; do
      if [[ -x "${dir}/node" && -x "${dir}/npm" ]]; then
        node_bin="$dir"
        break 2
      fi
    done
  done
  if [[ -n "$node_bin" ]]; then
    export PATH="${node_bin}:${PATH}"
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

# Vite 8 precisa de Node ^20.19.0 || >=22.12.0 (styleText em node:util).
node_meets_vite() {
  local bin="${1:-node}"
  local ver major minor
  [[ -x "$bin" ]] || return 1
  ver="$("$bin" -v 2>/dev/null | sed 's/^v//')"
  major="${ver%%.*}"
  minor="$(printf '%s' "$ver" | cut -d. -f2)"
  major="${major:-0}"
  minor="${minor:-0}"
  if (( major > 22 )); then
    return 0
  fi
  if (( major == 22 && minor >= 12 )); then
    return 0
  fi
  if (( major == 20 && minor >= 19 )); then
    return 0
  fi
  return 1
}
