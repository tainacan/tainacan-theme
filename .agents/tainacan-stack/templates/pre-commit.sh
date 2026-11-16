#!/bin/sh
# tainacan-stack: gancho pre-commit (gerado por tnc-stack; reinstale com "tnc-stack gancho .")
# Verifica só o que está no índice. Bloqueio impede o commit; aviso não.

ROOT="$(git rev-parse --show-toplevel)"
CHECK="$ROOT/.agents/tainacan-stack/bin/tnc-check"

# Onde está o PHP.
#
# TNC_PHP manda. Sem ele, tenta o PATH e, no Windows, os lugares onde o PHP
# costuma estar sem entrar no PATH: XAMPP, Laragon, WAMP, o PHP do Herd e um
# instalado solto em C:\php. Antes disto, quem desenvolve no Windows via o
# gancho pular a verificação em silêncio e descobrir o achado só no CI.
php_bin() {
  if [ -n "$TNC_PHP" ]; then
    echo "$TNC_PHP"
    return
  fi
  if command -v php >/dev/null 2>&1; then
    echo "php"
    return
  fi
  for candidate in \
    /c/xampp-tainacan/php/php.exe \
    /c/xampp/php/php.exe \
    /c/xampp8-2/php/php.exe \
    /c/laragon/bin/php/php.exe \
    /c/wamp64/bin/php/php.exe \
    /c/php/php.exe \
    "$LOCALAPPDATA/Herd/bin/php.exe"
  do
    if [ -x "$candidate" ]; then
      echo "$candidate"
      return
    fi
  done
  # Última tentativa: qualquer php.exe dentro de uma pasta de versão do XAMPP/Laragon.
  for candidate in /c/laragon/bin/php/php-*/php.exe /c/wamp64/bin/php/php*/php.exe; do
    if [ -x "$candidate" ]; then
      echo "$candidate"
      return
    fi
  done
  echo ""
}

if [ -f "$CHECK" ]; then
  PHP_BIN="$(php_bin)"
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" "$CHECK" --staged --fail-on=block || {
      echo ""
      echo "tnc-check bloqueou o commit. Corrija ou justifique na linha com: // tnc-check-ignore TNC-XNN: motivo"
      exit 1
    }
  else
    echo "tnc-check: PHP não encontrado. Defina TNC_PHP com o caminho do php (ex.: TNC_PHP=C:/xampp/php/php.exe). Verificação pulada." >&2
  fi
fi

# gancho que já existia antes da stack
if [ -x "$ROOT/.git/hooks/pre-commit.local" ]; then
  "$ROOT/.git/hooks/pre-commit.local" "$@" || exit $?
fi
exit 0
