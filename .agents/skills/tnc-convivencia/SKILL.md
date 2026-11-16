---
name: tnc-convivencia
description: Garante que plugins Tainacan/WordPress convivam no mesmo site: colisões de nomes, handles, rotas, opções e shortcodes; ganchos disputados na página do item e na busca facetada; CSS e JS vazando; plugins irmãos que leem dados uns dos outros. Use quando um plugin quebra outro, ao ativar plugin novo num site com vários, na revisão conjunta de vários plugins, ou ao criar API entre plugins irmãos.
---

# tnc-convivencia

Referência: `.agents/tainacan-stack/referencias/convivencia-entre-plugins.md`.

## 1. Inventário e colisões (automático)

Na pasta de plugins do site (ou numa pasta com os repositórios lado a lado):
```
php .agents/tainacan-stack/bin/tnc-check --conflicts=<pasta-dos-plugins>
php .agents/tainacan-stack/bin/tnc-check --inventory=<pasta-dos-plugins> --format=md
```
- **Quebra**: função ou classe global repetida (erro fatal), shortcode, tipo de post, taxonomia, rota REST, página de admin ou objeto JS global repetidos, handle com origem diferente, `remove_all_*` num gancho que outro usa.
- **Revisar**: opção ou transient gravados por dois plugins, mesmo evento de cron.
- **Atenção**: dois plugins no mesmo gancho sensível (`tainacan-*`, `the_content`, `pre_get_posts`, `posts_*`, `rest_*`, `template_include`) com a mesma prioridade; a ordem depende de quem carrega primeiro.

## 2. Regras de convivência

- Prefixo próprio em tudo que é global; namespace PHP para classes.
- Nunca desfazer o que é dos outros: sem `remove_all_filters`, sem desregistrar jQuery/Vue/assets do Tainacan, sem mexer em `$wp_filter` (TNC-C01, W10). Para suspender um callback de terceiro (ex.: impressão), guarde e restaure pela API do WP_Hook, em `try/finally`.
- Prioridade explícita quando a ordem importa, documentada no `AGENTS.md` do projeto.
- CSS sempre sob a classe raiz do plugin; nada de seletor de elemento solto (TNC-C02). Cuidado com `.button` do Bulma × `.wp-core-ui .button`, `[hidden]` reaparecendo, `z-index` do lightbox do Tainacan, ancestral com `transform` quebrando `position: fixed`.
- Assets só nas telas que usam (cheque `hook_suffix` ou a presença do bloco/shortcode). Menu movido de lugar muda o `hook_suffix`.
- E-mail: `phpmailer_init`/`wp_mail_from` só em volta do seu `wp_mail()` (TNC-W10).
- Reescrita de URL: não chame `flush_rewrite_rules()` em toda carga; na ativação, só depois de registrar os seus tipos.
- Plugin que conversa com outro usa a **API pública** dele (funções, filtros, métodos `is_*()`/getters), nunca a opção crua nem a tabela interna. Quem expõe API publica número de versão de contrato e mantém compatibilidade.
- Dois plugins sobre o mesmo dado (taxonomia, meta, índice): defina quem é o dono e como o outro lê.
- Renome de plugin: desative o antigo antes de ativar o novo (ganchos duplicados).

## 3. Revisão conjunta de vários plugins

Para N plugins que vivem juntos:
1. Rode `--conflicts` e resolva os "Quebra".
2. Monte o mapa de ganchos sensíveis com prioridade (do `--inventory`) e decida a ordem.
3. Ative todos no ambiente de testes com dados reais e percorra: página do item, busca facetada, admin de item e de coleção, REST, impressão, importação. Console do navegador limpo e `debug.log` sem avisos novos.
4. Registre as decisões de convivência no `AGENTS.md` de cada plugin envolvido.

## Relato

Colisões encontradas (com o comando), decisões de ordem e de dono, o que foi testado junto e o resultado.
