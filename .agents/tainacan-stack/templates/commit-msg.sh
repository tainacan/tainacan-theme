#!/bin/sh
# tainacan-stack: gancho commit-msg (gerado por tnc-stack; reinstale com "tnc-stack gancho .")
# Exige mensagem em Conventional Commits, o padrão do núcleo do Tainacan e dos plugins:
#   <tipo>(<área opcional>)<! opcional>: <assunto>
#   tipos: feat fix docs style refactor perf test build ci chore revert
# Merge, revert automático, fixup e squash passam direto.

MSG_FILE="$1"
FIRST="$(grep -v '^#' "$MSG_FILE" | sed -n '1p')"

case "$FIRST" in
  Merge\ *|Revert\ *|fixup!\ *|squash!\ *|amend!\ *) exit 0 ;;
esac

if ! printf '%s\n' "$FIRST" | grep -Eq '^(feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert)(\([a-zA-Z0-9_./-]+\))?!?: .{1,100}$'; then
  echo "" >&2
  echo "tnc-stack: a mensagem de commit não segue Conventional Commits." >&2
  echo "  Esperado: <tipo>(<área>): <assunto>   ex.: fix(rest): valida include_children no taxquery" >&2
  echo "  Tipos: feat fix docs style refactor perf test build ci chore revert" >&2
  echo "  Recebido: $FIRST" >&2
  exit 1
fi

# gancho que já existia antes da stack
ROOT="$(git rev-parse --show-toplevel)"
if [ -x "$ROOT/.git/hooks/commit-msg.local" ]; then
  "$ROOT/.git/hooks/commit-msg.local" "$@" || exit $?
fi
exit 0
