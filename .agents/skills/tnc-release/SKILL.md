---
name: tnc-release
description: Publica uma versão de plugin Tainacan/WordPress sem esquecer nada: versão igual em todos os lugares, changelog, readme.txt, migrações, build de assets, pacote ZIP, tag e verificação em ambiente de testes. Use para "lance a versão", "suba a release", "gere o ZIP", "bump de versão", ou antes de publicar no WordPress.org ou no servidor.
---

# tnc-release

## 1. Conferências automáticas

```
php .agents/tainacan-stack/bin/tnc-check --release
php .agents/tainacan-stack/bin/tnc-check --diff=<tag-anterior> --fail-on=block
```
- **TNC-R01**: versão do cabeçalho, constantes `*_VERSION`, `Stable tag`, `package.json` e `composer.json` iguais.
- **TNC-R02**: campos do `readme.txt`.
- **TNC-R03**: o changelog cita a versão.
- **TNC-R04**: Text Domain igual ao slug.
- **TNC-R05**: `Requires Plugins: tainacan`.
- **TNC-R06**: eventos de cron limpos na desativação.

## 2. Checklist

- [ ] Versão nova segue o semver do projeto (correção = patch; recurso = minor; quebra de contrato = major) → verificação: `--release` sem TNC-R01.
- [ ] Changelog descreve o que muda para quem usa (não a lista de commits).
- [ ] Migração de dados: idempotente, versionada (opção com a versão do esquema), testada sobre cópia de produção; e o que fazer para reverter.
- [ ] Mapa de configuração cacheado em opção tem número de versão: mudar a regra sem invalidar o cache não tem efeito em instalação existente.
- [ ] Assets: `npm run build` (produção) e `composer install --no-dev` quando o pacote leva `vendor/`. ZIP montado a partir do git (`git archive`) mais os artefatos de build, nunca de um diretório de trabalho sujo; sem `node_modules`, testes, `.git`, `.agents/` nem arquivos de desenvolvimento.
- [ ] Instalação limpa do ZIP num WordPress de testes ativa sem aviso, e a atualização sobre a versão anterior preserva os dados.
- [ ] Plugin renomeado: instrução para desativar o antigo antes de ativar o novo.
- [ ] Cache de borda (Varnish, CDN): plano de purga depois do deploy.
- [ ] Plugin que vai ao WordPress.org: `tnc-wordpress-org` com erro zero no Plugin Check; `readme.txt` com External services, se houver.
- [ ] Revisão em duas camadas sobre o diff da release: `tnc-revisao-tecnica` (no Claude Code, `/code-review high`; `xhigh` só quando pedirem).
- [ ] Mensagens de commit em Conventional Commits, em inglês (`fix(area): ...`); o gancho `commit-msg` da stack confere.
- [ ] Fluxo git-flow: `release/X.Y.Z` a partir de `develop`, versões ajustadas, build de produção, `merge --no-ff` em `master`/`main`, tag anotada `vX.Y.Z`, merge de volta em `develop`. Nunca `push` em branch de repositório upstream (tainacan/tainacan) sem confirmação explícita de quem pediu.
- [ ] Tag no git (`vX.Y.Z`) e push da tag; release com o changelog e o ZIP enxuto.
- [ ] `AGENTS.md` do projeto atualizado com o estado da versão.

## 3. Relato

Versão, o que entrou, resultado das verificações, onde foi testada, artefato gerado (caminho e hash) e o passo de reversão.
