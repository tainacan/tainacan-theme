---
name: tnc-contribuir-nucleo
description: Trabalho no repositório do núcleo do Tainacan (github.com/tainacan/tainacan): fluxo GitFlow, build com webpack e Sass, PHPUnit com banco de testes, ESLint/PHPCS, modelo de PR, blocos Gutenberg e compatibilidade com plugins existentes. Use ao alterar o núcleo, preparar PR para a develop, rodar os testes do núcleo ou avaliar se uma mudança no núcleo quebra plugins.
---

# tnc-contribuir-nucleo

Confira os detalhes no próprio repositório (`CONTRIBUTING.md`, `package.json`, `phpunit.xml.dist`, `.github/`), que prevalecem sobre este resumo. Referência: `.agents/tainacan-stack/referencias/ambiente-de-desenvolvimento.md` e `referencias/nucleo-tainacan.md` (seção D).

## Estrutura e build

- O plugin distribuído é `src/`; tudo fora dele é ferramental. `vendor` do Composer fica em `src/vendor`.
- `build.sh` (lê `build-config.cfg`) instala dependências, compila Sass, roda webpack e copia `src/` para o `wp_plugin_dir`, apagando os fontes `.vue/.js/.scss` de `views/`. Por isso a instalação não tem os fontes Vue.
- Scripts npm: `build` (desenvolvimento), `build-prod`, `lint`, `lint:vue`, `lint:js`. O ESLint também roda no build.
- PHPCS: `composer phpcs` (WordPress-Core + WordPress-Docs).

## Testes

```
./tests/bin/install-wp-tests.sh wordpress_test root "" 127.0.0.1:3306 latest
cp tests/bootstrap-config-sample.php tests/bootstrap-config.php
phpunit                       # ou ./run-tests.sh
```
Arquivos `tests/test-*.php`; os prefixados com `_` ficam fora. Não há testes JS nem e2e: mudança em Vue exige roteiro de teste manual no PR.

## Fluxo

- Trabalhe num **fork** (`origin` = fork, `upstream` = tainacan/tainacan). Qualquer `git push` para branch do upstream, `merge` em `develop`/`master` do upstream, tag que vai ao upstream, `push --force` ou abertura de PR em nome de outra pessoa exige **confirmação explícita** de quem pediu antes de executar.

- GitFlow: trabalho em `feature/<tema>` a partir da `develop`; PR contra a `develop`, depois de `git rebase upstream/develop`. Nunca commit direto em `develop` nem na branch de produção (no GitHub ela se chama `master`, embora o CONTRIBUTING diga `main`).
- Commits em Conventional Commits (`feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `perf`, `ci`), assunto com menos de 72 caracteres.
- Modelo de PR: issue relacionada, tipo de mudança, ESLint/PHPCS, testes, capturas de tela; **bloco alterado (`block.json` ou `save.js`) exige atualizar `deprecated.js`**.

## Compatibilidade com plugins

O núcleo é base de muitos plugins. Antes de mudar assinatura, gancho, chave de opção, rota REST, nome de evento JS, classe CSS usada por tema, formato de `metadata_type_options` ou comportamento de repositório:
- Procure o uso nos plugins conhecidos (`tnc-check --inventory` numa pasta com plugins; `grep` pelo nome do gancho).
- Mantenha o antigo funcionando com aviso de depreciação (`_deprecated_hook`, `_deprecated_function`) por pelo menos uma versão minor.
- Registre no changelog a mudança e o caminho de migração para plugins.
- Gancho novo: nome com prefixo `tainacan-`, argumentos documentados no docblock (a documentação de ações e filtros é gerada pelo wp-documentor a cada push na `develop`).

## Adoção da stack no núcleo

O núcleo tem código legado: instale com `--baseline` para registrar os achados atuais e bloquear só os novos. O CI da stack usa `--diff=origin/develop`, então só as linhas alteradas no PR são avaliadas.
