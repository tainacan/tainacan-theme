---
name: tnc-validacao
description: Roda as verificações de entrega de um projeto Tainacan/WordPress e relata o resultado com comandos e saídas. Use depois de qualquer alteração de código, antes de commit, ao pedir "valide", "rode os testes", "lint", "verifique antes de subir", ou quando um gancho pre-commit ou o CI falhar.
---

# tnc-validacao

## Ordem

Rode na raiz do repositório e guarde a saída de cada passo para a resposta.

1. **tnc-check nas alterações**
   ```
   php .agents/tainacan-stack/bin/tnc-check --changed
   ```
   Para o que já está no índice: `--staged`. Para a branch inteira contra a base: `--diff=origin/develop` (ou `origin/main`).
   - `BLOQUEIO`: corrija. Não suprima bloqueio de segurança sem a demonstração do porquê é seguro.
   - `aviso`: corrija ou justifique na linha: `// tnc-check-ignore TNC-XNN: motivo concreto`.
   - Dúvida sobre um achado: `php .agents/tainacan-stack/bin/tnc-check --explain TNC-XNN`.
2. **Sintaxe**: o `tnc-check` já roda `php -l` (TNC-B01) com o PHP do PATH. Se o projeto declara PHP mínimo menor que o local, evite sintaxe nova (match, enum, readonly, argumentos nomeados, `?->`) e diga na resposta que não deu para testar na versão mínima.
3. **Padrões do projeto**, quando existirem:
   - `composer phpcs` ou `vendor/bin/phpcs` (configuração `phpcs.xml.dist`).
   - `vendor/bin/phpstan` (configuração `phpstan.neon*`).
   - `vendor/bin/phpunit` ou o script de testes do projeto (no núcleo do Tainacan: `run-tests.sh`, que exige o banco de testes do WordPress).
   - JS/Vue alterado: `npm run lint` e `npm run build` (o build do núcleo roda ESLint).
4. **Plugin Check**, quando o plugin vai ao WordPress.org ou quando o diff toca saída, entrada, SQL, i18n ou enqueue: `wp plugin check <slug> --exclude-directories=.agents,.claude,.claude-plugin,.codex,.cursor,node_modules,vendor,tests` num WordPress local com o plugin Plugin Check, ou `vendor/bin/phpcs` com o `phpcs.xml.dist` estrito (o instalador da stack cria um). Detalhes e tratamento dos achados: skill `tnc-wordpress-org`.
5. **Antes de commit que vai para deploy**: revisão em duas camadas sobre o diff (`tnc-revisao-tecnica`; no Claude Code, `/code-review medium` uma vez por entrega, `high` se a mudança mexe em segurança, migração de dados ou transação). Achados confirmados se corrigem antes do commit; plausíveis se relatam.
6. **Release**, se a mudança muda a versão: `php .agents/tainacan-stack/bin/tnc-check --release`.

Ferramenta ausente (sem `vendor/`, sem banco de testes): não instale nada sem pedir. Registre como pendente, com o comando que a pessoa precisa rodar.

## PHP fora do PATH

No Windows com XAMPP, aponte para o executável: `C:/xampp/php/php.exe .agents/tainacan-stack/bin/tnc-check --changed`. Os hooks do agente usam a variável `TNC_PHP` quando definida.

## Formato do relato

```
- [x] tnc-check --changed → PASS: 0 bloqueio(s), 0 aviso(s) em 4 arquivo(s)
- [x] phpunit --filter Fixture → OK (12 tests, 30 assertions)
- [ ] teste manual no admin → pendente: abrir Itens > Editar e conferir o campo novo
```

Nunca declare entrega concluída com bloqueio aberto ou com verificação obrigatória não executada.
