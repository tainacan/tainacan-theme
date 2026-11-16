# Núcleo do Tainacan, parte 2: ganchos, REST, ferramental, desempenho e modelos de código

Continuação da referência do núcleo do Tainacan (1.0.3 e 1.4.1). Convenções de citação e versões na [seção 0 da parte 1](nucleo-tainacan-parte-1.md#s0): caminhos relativos a `src/`, prefixo `repo:` para arquivos fora do plugin, linha da [1.4.1] quando não indicado, **Δ** para diferenças 1.0.3 × 1.4.1. Índice geral em [nucleo-tainacan.md](nucleo-tainacan.md).

Partes: [1: fontes e arquitetura](nucleo-tainacan-parte-1.md) · [2: ganchos, REST, ferramental, desempenho, modelos](nucleo-tainacan-parte-2.md) · [3: lições do modelo de dados e da API](nucleo-tainacan-parte-3.md)

## Sumário

- [B. API de ganchos para plugins](#sB)
  - [B.1 `tainacan_register_admin_hook` (formulários do admin)](#b1)
  - [B.2 Ganchos por item da busca facetada (JS, `wp.hooks`)](#b2)
  - [B.3 Filtros `tainacan-item-metadata-get-value-as-html--type-*`](#b3)
  - [B.4 `tainacan-get-the-document` e outros filtros de front-end](#b4)
  - [B.5 Ações e filtros de persistência](#b5)
- [C. API REST](#sC)
  - [C.1 Namespace e controllers](#c1)
  - [C.2 Parâmetros de busca (`prepare_filters`)](#c2)
  - [C.3 Paginação (`perpage`)](#c3)
  - [C.4 `fetch_only` e `fetch_only_meta`](#c4)
  - [C.5 Como plugins acrescentam campos ou rotas](#c5)
  - [C.6 Facetas](#c6)
- [D. Ferramental de desenvolvimento do núcleo](#sD)
  - [D.1 Estrutura do repositório](#d1)
  - [D.2 Build](#d2)
  - [D.3 Testes](#d3)
  - [D.4 Lint](#d4)
  - [D.5 Versões mínimas](#d5)
  - [D.6 CI](#d6)
  - [D.7 Fluxo de branches e PRs](#d7)
  - [D.8 i18n](#d8)
- [E. Armadilhas de desempenho conhecidas](#sE)
- [F. Lista rápida de verificações](#sF)
- [G. Modelos mínimos de código](#sG)
  - [G.1 Criar um item com valor de metadado](#g1)
  - [G.2 Ler a taxonomia de um metadado do tipo Taxonomy](#g2)
  - [G.3 Gancho de formulário no admin](#g3)
  - [G.4 Antes e depois de cada item na busca facetada (JS)](#g4)
  - [G.5 Listagem enxuta pela REST](#g5)

---

<a id="sB"></a>
## B. API de ganchos para plugins

<a id="b1"></a>
### B.1 `tainacan_register_admin_hook` (formulários do admin)

- Assinatura: `function tainacan_register_admin_hook( $context, $callback, $position = 'end-left', $conditional = null )`, que delega para `\Tainacan\Admin_Hooks::get_instance()->register(...)` (`views/admin/classes/hooks/admin-hooks-functions.php:8-11`, 1.0.3 e 1.4.1).
- Quando dispara: `Admin_Hooks::init()` liga `admin_init` a `register_hooks()`, que faz `do_action('tainacan-register-admin-hooks')` (`views/admin/classes/hooks/class-tainacan-admin-hooks.php:12-18`, 1.0.3 e 1.4.1). Roda em todo `admin_init`, inclusive em `admin-ajax.php` e `admin-post.php` (que também disparam `admin_init`; isso é comportamento do WordPress).
- Contextos: `collection`, `metadatum`, `item`, `taxonomy`, `term`, `filter`, `role`, `metadataSection` (filtro `tainacan-admin-hooks-contexts`). Posições: `begin-left`, `begin-right`, `end-left`, `end-right` (filtro `tainacan-admin-hooks-positions`) (`class-tainacan-admin-hooks.php:20-26`). Contexto ou posição inválidos: a função devolve `false` (`:44-46`).
- **O callback roda na hora do registro**: `$result = call_user_func($callback);` (`:48`). **Só é aceito se devolver string** (`is_string($result)`): o HTML vai para `registered_hooks[$context][$position][] = ['form' => $result, 'conditional' => ...]` e a função devolve `true` (`:50-57`); senão, `false` (`:59`).
  - Callback que **imprime** em vez de devolver: a saída vaza no `admin_init`, antes dos cabeçalhos, e o gancho não é registrado. Use `ob_start(); include ...; return ob_get_clean();`.
- Uso do retorno: `Pages::get_admin_js_localization_params()` põe `$settings['form_hooks'] = Admin_Hooks::get_instance()->get_registered_hooks()` (`views/class-tainacan-pages.php:384`), que chega ao JS como `tainacan_plugin['form_hooks']` (`views/admin/js/mixins.js:68`).
- **Injeção por `v-html`: confirmado.** O mixin `formHooks` junta os HTMLs por posição (`getBeginLeftForm` etc., `views/admin/js/mixins.js:85-96`), e os formulários renderizam `<form id="form-{entity}-{posição}" class="form-hook-region" v-html="getBeginLeftForm" />` (por exemplo `views/admin/components/edition/item-edition-form.vue:648-652`, `:202`, `:514`, `:702`). `v-html` usa innerHTML: **`<script>` inline não executa**; o JS vem de arquivo enfileirado.
- `conditional`: forma antiga `['attribute'=>..., 'value'=>...]` ou nova `{atributo: valor, ...}`, comparada com `this.form` (`views/admin/js/mixins.js:165-190`).
- Coleta: ao salvar, `fillExtraFormData()` lê os `elements` de `#form-{entity}-{posição}` e os acrescenta ao corpo enviado (`views/admin/js/mixins.js:108-139`). `updateExtraFormData()` repopula os campos a partir da entidade (`:140-...`). A cada atualização do Vue dispara no `document` o evento `tainacan-{entityName}-hook-reload` (`:98-106`). O plugin grava os campos, por exemplo, em `tainacan-insert-tainacan-collection` (`repo:docs/admin-form-hooks.md:62-65`).

<a id="b2"></a>
### B.2 Ganchos por item da busca facetada (JS, `wp.hooks`)

- São **filtros JavaScript de `wp.hooks`**, não PHP (`views/gutenberg-blocks/blocks/faceted-search/theme-search/js/view-modes-mixin.js:56-80`).
- **Nomes do "after" assimétricos: confirmado** nas duas versões (o bundle [1.0.3] `assets/js/1645.ac186e8f4dd082b25823.js` tem as mesmas strings):
  - `hasBeforeHook()`: `hasFilter('tainacan_faceted_search_item_before') || hasFilter('tainacan_faceted_search_collection_${collectionId}_item_before')` (`:56-60`).
  - `hasAfterHook()`: `hasFilter('tainacan_faceted_search_collection_item_after') || hasFilter('tainacan_faceted_search_collection_${collectionId}_item_after')` (`:62-66`).
  - `getBeforeHook(item)`: aplica `tainacan_faceted_search_collection_${id}_item_before` sobre o resultado de `tainacan_faceted_search_item_before` (`:68-72`).
  - `getAfterHook(item)`: aplica `tainacan_faceted_search_collection_${id}_item_after` sobre o resultado de `tainacan_faceted_search_item_after` (`:74-78`).
  - Consequência: para o "after" global, **registre os dois nomes**. `tainacan_faceted_search_collection_item_after` torna o `v-if` verdadeiro; `tainacan_faceted_search_item_after` produz o HTML. Alternativa: só o nome por coleção, que é igual nos dois métodos.
- Callback: `(html: string, item: object) => string`, injetado por `v-html` (`view-mode-*.vue`, por exemplo `view-mode-cards.vue:37-39`, `:118-120`).

<a id="b3"></a>
### B.3 Filtros `tainacan-item-metadata-get-value-as-html--type-*`

- Cada tipo que implementa `get_value_as_html()` aplica no fim `tainacan-item-metadata-get-value-as-html--type-{tipo}` com `($html, $item_metadata)`:
  - compound: `views/admin/components/metadata-types/compound/class-tainacan-compound.php:269` (`:251` [1.0.3]).
  - control: `.../control/class-tainacan-control.php:314` (`:179` [1.0.3]).
  - description: `.../core-description/class-tainacan-core-description.php:137` (`:124` [1.0.3]).
  - date: `.../date/class-tainacan-date.php:129` (`:110` [1.0.3]).
  - geocoordinate: `.../geocoordinate/class-tainacan-geocoordinate.php:246` (`:223` [1.0.3]).
  - relationship: `.../relationship/class-tainacan-relationship.php:290` (`:259` [1.0.3]).
  - taxonomy: `.../taxonomy/class-tainacan-taxonomy.php:429` (`:408` [1.0.3]).
  - text: `.../text/class-tainacan-text.php:104` (`:91` [1.0.3]), com `force_balance_tags`.
  - textarea: `.../textarea/class-tainacan-textarea.php:101` (`:88` [1.0.3]).
  - url: `.../url/class-tainacan-url.php:127` (`:112` [1.0.3]).
  - user: `.../user/class-tainacan-user.php:194` ([1.4.1]; não conferido na [1.0.3]).
- **Não existe** `--type-` para numeric, selectbox e core-title, que não implementam `get_value_as_html` (grep). Para eles vale o genérico `tainacan-item-metadata-get-value-as-html` (`classes/entities/class-tainacan-item-metadata-entity.php:207`).
  - **O genérico não roda para tipos com método próprio**: o `return $fto->get_value_as_html($this)` sai antes (`:150-155`).
- Genérico de string: `tainacan-item-metadata-get-value-as-string` (`:234`). Também `-get-multivalue-prefix`, `-suffix`, `-separator` (`:95`, `:117`, `:139`).
- Âncoras de termo:
  - `Taxonomy::term_to_html()` (privado, `class-tainacan-taxonomy.php:581-614`) e `Term::_toHtml()` (`classes/entities/class-tainacan-term.php:316-331`, filtro `tainacan-term-to-html`) geram `<a data-linkto='term' data-id='$id' href='$link'>` com **aspas simples** nas duas versões (`class-tainacan-taxonomy.php:605`; `:454` [1.0.3]; `class-tainacan-term.php:324`; `:303` [1.0.3]).
  - O HTML final da página do item sai com **aspas duplas** porque `Item::get_metadata_as_html()` passa tudo por `wp_kses(...)` (`classes/entities/class-tainacan-item.php:776`; `:752` [1.0.3]), que reescreve os atributos (inferência pelo funcionamento do `wp_kses`). Dentro do filtro `--type-taxonomy` o HTML ainda tem aspas simples. Para casar a âncora, use regex que aceite os dois tipos de aspas.
  - Separador da hierarquia: filtro `tainacan-terms-hierarchy-html-separator` (`class-tainacan-taxonomy.php:576`).

<a id="b4"></a>
### B.4 `tainacan-get-the-document` e outros filtros de front-end

- `apply_filters('tainacan-get-the-document', $html, $item)` (`classes/theme-helper/template-tags.php:104`; mesma linha na [1.0.3], com o problema de argumentos **Δ** descrito em [A.14](nucleo-tainacan-parte-1.md#a14)).
- Em `template-tags.php`: `tainacan_get_the_document_raw` (`:123`), `tainacan_get_the_item_document_url` (`:142`), `tainacan_get_the_document_type` (`:161`), `tainacan_get_the_document_mimetype` (`:183`), `tainacan_get_the_item_document_download_link` (`:226`), `tainacan-get-collection-name` (`:486`, `:489`), `tainacan-get-the-attachments` (`:1206`), `tainacan-get-the-mime-type-icon` (`:1444`), `tainacan_single_taxonomy_terms_query` (`:1853`).
- Em `class-tainacan-theme-helper.php`: `tainacan_single_item_content` (`:840`), `tainacan_repository_archive_template_hierarchy` (`:1047`), `tainacan-default-view-mode-for-themes` (`:1161`), `tainacan-enabled-view-modes-for-themes` (`:1182`), `tainacan-get-item-gallery-filter-args` (`:2100`), `tainacan-swiper-main-options` / `-thumbs-options` (`:2385`, `:2407`).
- Em `classes/entities/class-tainacan-item.php`: `tainacan-get-item-metadatum-as-html-filter-args` (`:835`) e a família `tainacan-get-item-metadatum-as-html-{before|after}[--type-X|--id-N|--index-N]` (`:857-892`). Seções: `tainacan-get-metadata-section-as-html-*` (`:1296-1420`).

<a id="b5"></a>
### B.5 Ações e filtros de persistência (os mais úteis)

| Gancho | Tipo | Argumentos | Onde |
|---|---|---|---|
| `tainacan-status-require-validation` | filtro | `$statuses` | `classes/repositories/class-tainacan-repository.php:165` |
| `tainacan-pre-insert` | ação | `$obj` | `classes/repositories/class-tainacan-repository.php:173` |
| `tainacan-pre-insert-{post_type}` (itens: `tainacan-pre-insert-tainacan-item`) | ação | `$obj` | `:176`, `:209` |
| `tainacan-insert` | ação | `$obj, $diffs, $is_update` | `:238` |
| `tainacan-insert-{post_type}` (`tainacan-insert-tainacan-collection`, `-tainacan-metadatum`, `-tainacan-taxonomy`, `-tainacan-item`...) | ação | `$obj` | `:240` |
| `tainacan-pre-insert-Item_Metadata_Entity` / `tainacan-insert-Item_Metadata_Entity` | ação | `$item_metadata` | `classes/repositories/class-tainacan-item-metadata.php:38`, `:117` |
| `tainacan-pre-delete`, `tainacan-pre-delete-{post_type}` | ação | `$entity, $permanent` | `classes/repositories/class-tainacan-repository.php:679-680` |
| `tainacan-deleted`, `tainacan-deleted-{post_type}` | ação | `$entity, $permanent` | `:695-696` |
| `tainacan-get-map-{entidade}` | filtro | mapa de propriedades | `classes/repositories/class-tainacan-metadata.php:47` (exemplo; um por repositório) |
| `tainacan-fetch-args` | filtro | `$args, 'items'\|'logs'` | `classes/repositories/class-tainacan-items.php:395`; `class-tainacan-logs.php:246` |
| `tainacan-taxonomy-added-to-collection` / `-removed-from-collection` | ação | `$tax_id, $collection` | `classes/repositories/class-tainacan-metadata.php:1720-1726` |
| `tainacan-fetch-all-metadatum-values` (curto-circuito das facetas) | filtro | `null, $metadatum, $args` | `classes/repositories/class-tainacan-metadata.php:1261-1264` (`:1247` [1.0.3]) |
| `tainacan-entity-diff` | filtro | `$diff, $unsaved, $old` | `classes/repositories/class-tainacan-logs.php:744` |
| `tainacan_extract_document_content` / `tainacan_item_supports_document_content_extraction` | filtro | | `classes/repositories/class-tainacan-items.php:562`, `:594` |
| `tainacan-get-thumbnail-id-from-document` | filtro | `null, $item` | `classes/repositories/class-tainacan-items.php:686` |
| `tainacan-item-to-array` / `tainacan-item-get-thumbnail` | filtro | | `classes/entities/class-tainacan-item.php:69`, `:156` |
| Registro: `tainacan-register-metadata-type`, `tainacan-register-filter-type`, `tainacan-register-admin-hooks`, `tainacan-register-vuejs-component`, `tainacan-register-vuejs-plugin`, `tainacan-register-importers`, `tainacan-register-exporters`, `tainacan-register-exposers`, `tainacan-register-mappers`, `tainacan-register-generic_process` | ação | | ver A.4, A.7, A.11, A.12, A.15 (parte 1) e B.1 |

Documentação gerada com wp-documentor: `repo:docs/actions.md` (861 linhas) e `repo:docs/filters.md`. Script `repo:generate_docs.sh`, executado pelo CI `generate-docs.yml` a cada push na `develop`.

---

<a id="sC"></a>
## C. API REST

<a id="c1"></a>
### C.1 Namespace e controllers

- `const TAINACAN_REST_NAMESPACE = 'tainacan/v2';` (`classes/api/tainacan-rest-creator.php:12`). Controllers instanciados com `new` nesse arquivo (`:22-43`).
- Base `\Tainacan\API\REST_Controller` (`classes/api/class-tainacan-rest-controller.php`). O construtor define `namespace`, registra `register_routes` em `rest_api_init` e liga `tainacan-api-prepare-items-args` a `add_support_to_tax_query_like` (`:24-28`).
- Rotas (`classes/api/endpoints/*.php`):
  - `items`: `/collection/{cid}/items`, `/items/{id}`, `/items/{id}/attachments`, `/items/{id}/document-content-index/extract`, `/items`, `/collection/{cid}/items/{id}/duplicate`, `/collection/{cid}/items/submission`, `.../submission/{sid}/finish` (`class-tainacan-rest-items-controller.php:56-210`).
  - `item` (valores): `/item/{id}/metadata/{mid}`, `/item/{id}/metadata`, `/item/{id}/metadata-sections/{sid}` (`class-tainacan-rest-item-metadata-controller.php`).
  - `facets`: `/collection/{cid}/facets/{mid}` e `/facets/{mid}` (`class-tainacan-rest-facets-controller.php:41`, `:60`).
  - `collections`, `metadata`, `metadata-sections`, `filters`, `taxonomies`, `terms` (`/taxonomy/{tid}/terms` etc.), `logs`, `bulk-edit`, `sequence-edit`, `importers`, `exporters`, `exposers`, `metadata-types`, `filter-types`, `metadatum-mappers`, `bg-processes`, `roles`, `reports`, `oai` (um arquivo por controller).
- OpenAPI gerada: `repo:docs/openapi.json`, citada em `repo:docs/plugin-development-guidelines.md:66`.

<a id="c2"></a>
### C.2 Parâmetros de busca (`prepare_filters`)

- `prepare_filters($request)` em `classes/api/class-tainacan-rest-controller.php:94-187` (`:83-173` [1.0.3]). URL → `WP_Query` (`:95-131`):
  - `name`/`title` → `title`, `id` → `p`, `authorid` → `author`, `authorname` → `author_name`, `search`/`s` → `s`, `searchterm` → `search`, `status` → `post_status`.
  - `offset`, `metaquery` → `meta_query`, `datequery` → `date_query`, `taxquery` → `tax_query`, `order`, `orderby`, `metakey`, `metavalue`, `metavaluenum`, `metacompare`, `hideempty` → `hide_empty`.
  - **`perpage` → `posts_per_page`**, `number`, `parent`, `paged`, `postin` → `post__in`, `relation`, `metatype` → `meta_type`, `hierarchical`, `exclude` → `post__not_in`, `excludetree`/`exclude_tree`, `include`, `sentence`.
- Chaves aceitas **dentro** de cada cláusula (`:131-161`); qualquer outra é descartada, porque `prepare_meta()` só copia as listadas (`:280-330`):
  - `metaquery`: `key`, `value`, `compare`, `relation` e **`metadatumtype` → `type`** (`:131-137`). `metaquery[0][type]=NUMERIC` vindo da URL **é descartado**; o nome certo é `metadatumtype`.
  - `datequery`: `year`, `month`, `day`, `week`, `hour`, `minute`, `second`, `compare`, `dayofweek`, `inclusive`, `before`, `after`, `column` (`:139-153`).
  - `taxquery`: `taxonomy`, **`metadatum` → `field`**, `terms`, `operator`, `relation` (`:155-161`; `:147-149` [1.0.3]).
  - **`include_children` é descartado: confirmado.** Fora da lista, `prepare_meta()` não o copia; a `WP_Tax_Query` usa o padrão `include_children = true` e o filtro por termo vindo da URL **sempre inclui os filhos** (inferência sobre o padrão do WordPress). O `field` da `WP_Tax_Query` também não entra com esse nome; entra como `metadatum`.
- `value` e `terms` passam por `sanitize_value()`: `wp_kses` com a lista `post` **sem a tag `a`**, mais `trim` (`:259-268`, `:282`).
- `contains_array()` decide se a cláusula é lista: há um array cujo índice não é chave reservada (`:333-343`). A `relation` da raiz é copiada antes (`:286-287`).
- No fim: `$args['perm'] = 'readable'` (`:184`) e o filtro `tainacan-api-prepare-items-args` com `($args, $request)` (`:186`; `:172` [1.0.3]).
- `add_support_to_tax_query_like`: `operator=LIKE`/`NOT LIKE` com `terms` em string vira busca `get_terms(search=...)`, convertida em `IN`/`NOT IN` (`:204-257`).
- Itens (`get_items`, `classes/api/endpoints/class-tainacan-rest-items-controller.php:662-810`):
  - `session_write_close()` logo no início (`:667`).
  - `sentence` padrão vem de `tainacan_option_search_each_word_by_default` ou do filtro `tainacan-default-search-sentence` (`:671-673`, `:1716-1721`).
  - **Curto-circuito**: filtro `tainacan-api-get-items-alternate` com `(false, $request)`; se devolver `WP_REST_Response`, ela é a resposta (`:680-683`; `:656` [1.0.3]). Ponto de entrada para motores de busca externos.
  - `metaquery[n][secondary]=true` exclui a chave dos `filters_arguments` (`:690-696`).
  - **`meta_query[i]['type']` é sobrescrito** pelo `type` do filtro cadastrado para o metadado, só no primeiro nível (`:697-707`).
  - `geoquery` é repassado (`:722-724`).
  - Filtros de resposta: `tainacan-api-items-filters-response` e `tainacan-api-items-filters-arguments-response` logo após o `fetch` (`:729-731`), e `tainacan-api-items-response` (`:796`).

<a id="c3"></a>
### C.3 Paginação (`perpage`)

- Teto: se `posts_per_page > $TAINACAN_API_MAX_ITEMS_PER_PAGE` e o teto é maior que -1, limita ao teto (`class-tainacan-rest-items-controller.php:710-715`; `:686-690` [1.0.3]).
  - **Δ [1.4.1]**: `perpage < 1`, incluindo `-1`, vira `get_minimum_safe_perpage()`, isto é, o teto, com 96 de reserva (`classes/api/class-tainacan-rest-controller.php:173-177`, `:196-202`).
  - **Δ [1.0.3]**: sem essa proteção (`classes/api/class-tainacan-rest-controller.php:155-167` [1.0.3]). **`perpage=-1` passa sem limite**, porque -1 não é maior que o teto.
- Cabeçalhos: `X-WP-Total` (de `found_posts`), `X-WP-TotalPages`, `X-WP-ItemsPerPage`, `X-Tainacan-Query-Time`, `X-Tainacan-Elapsed-Time` (`class-tainacan-rest-items-controller.php:798-807`; `X-WP-Total` em `:779` [1.0.3]).

<a id="c4"></a>
### C.4 `fetch_only` e `fetch_only_meta`

- Declarado em `get_fetch_only_param()` (`classes/api/class-tainacan-rest-controller.php:351-360`).
- **Sem `fetch_only`**: item completo, com `_toArray()`, `document_as_html`, `exposer_urls` e **todos os metadados** (`add_metadata_to_item` com `value`, `value_as_html`, `value_as_string`) (`class-tainacan-rest-items-controller.php:294-311`, `:229-257`).
- **Com `fetch_only`** (CSV ou array): sempre acrescenta `id` e `collection_id` (`:325-333`) e filtra os atributos (`:341`).
  - Metadados só entram por `fetch_only[meta]` (array) ou `fetch_only_meta` (CSV de ids), aplicados como `post__in` no `fetch` dos metadados (`:345-356`).
  - Pedindo `thumbnail`, vêm também `thumbnail_alt`, `thumbnail_id` e `document_mimetype` (`:361-365`).
  - `url` e `exposer_urls` vêm sempre (`:367-368`).
- Com `view_mode` do tipo `template`, a resposta traz `template` (HTML gerado pelo PHP do tema) em vez de `items`, usando os globais `$wp_query` e `$view_mode_displayed_metadata` (`:735-778`).

<a id="c5"></a>
### C.5 Como plugins acrescentam campos ou rotas

- Campos extras de postmeta: filtros `tainacan-api-response-{entidade}-meta` devolvem lista de `meta_key`; o núcleo faz `get_post_meta($id, $key, true)` para cada uma e mescla no array. O filtro recebe `([], $request)`. Comentário no código: "take care to do any permissions verification before exposing the data" (`class-tainacan-rest-items-controller.php:280-292`).
  - Variações: `-item-meta` (`:288`), `-collection-meta` (`class-tainacan-rest-collections-controller.php:435`), `-filter-meta` (`class-tainacan-rest-filters-controller.php:387`), `-metadatum-meta` (`class-tainacan-rest-metadata-controller.php:372`, `:388`), `-metadata-section-meta` (`class-tainacan-rest-metadata-sections-controller.php:211`), `-taxonomy-meta` (`class-tainacan-rest-taxonomies-controller.php:201`), `-term-meta` (`class-tainacan-rest-terms-controller.php:620`).
- Transformar o item: `tainacan-api-items-prepare-for-response` com `($item_arr, $item, $request)` (`class-tainacan-rest-items-controller.php:378`; `:359` [1.0.3]).
- Resposta de item único: `tainacan-rest-response` (`:405`).
- Ações após operações REST: `tainacan-api-item-updated` com `($updated_item, $attributes)` (`:1037`), `tainacan-api-item-duplicated` (`:1238`), `tainacan-submission-item-data` (`:1350`), `tainacan-submission-item-finish` (`:1568`), `tainacan-api-collection-created` (consumido em `classes/mappers/class-tainacan-mappers-handler.php:34`).
- Rotas novas: não há API própria do Tainacan; o caminho é `register_rest_route` do WordPress. Usar o namespace `tainacan/v2` estendendo `\Tainacan\API\REST_Controller`, como os controllers nativos, é possível, mas a documentação não diz que é apoiado: não confirmado. Os exposers são o mecanismo oficial para formatos alternativos de saída ([A.12](nucleo-tainacan-parte-1.md#a12)).

<a id="c6"></a>
### C.6 Facetas

- `GET /tainacan/v2[/collection/{cid}]/facets/{metadatum_id}`, em `classes/api/endpoints/class-tainacan-rest-facets-controller.php:95-175`. Parâmetros:
  - `current_query`: a mesma query da listagem, passada de novo por `prepare_filters`. Ignorada com `TAINACAN_FACETS_DISABLE_FILTER_ITEMS` ou com a opção `tainacan_option_facets_enable_filter_items` desligada (`:100-108`).
  - `hideempty=0` desliga o filtro por itens (`:110-112`).
  - `offset` e `number` (`:114-117`), `search` (`:119-121`), `getSelected=1` (lê termos ou valores selecionados de `current_query`, `:124-146`), `parent` (`:148-151`), `count_items` (desligável com `TAINACAN_FACETS_DISABLE_COUNT_ITEMS` ou com a opção `tainacan_option_facets_enable_count_items`, `:153-167`).
- Tudo termina em `Metadata::fetch_all_metadatum_values($metadatum_id, $args)` (`:171`; `classes/repositories/class-tainacan-metadata.php:1205`).

---

<a id="sD"></a>
## D. Ferramental de desenvolvimento do núcleo

<a id="d1"></a>
### D.1 Estrutura do repositório

- Raiz: `src/` (o plugin), `tests/` (PHPUnit), `docs/` (Docsify, `openapi.json`, `phpdoc`, diagramas); scripts `build.sh`, `build-watch.sh`, `compile-sass.sh`, `run-tests.sh`, `generate_docs.sh`, `verify_compat.sh`; configurações `webpack.{common,dev,prod}.js`, `eslint.config.js`, `phpcs.xml.dist`, `phpunit.xml.dist`, `postcss.config.js`; `composer.json` e `package.json`.
- `repo:docs/setup-local.md:9-14`: "`src` - The actual plugin. Everything outside this folder is not part of the distribution package".
- `vendor` do Composer em `src/vendor` (`repo:composer.json`, `"vendor-dir": "src/vendor"`).

<a id="d2"></a>
### D.2 Build

- Webpack 5 (`webpack ^5.112.0`, `webpack-cli ^7.2.3`) com `vue-loader ^17.4.2`, `babel-loader`, `sass-loader`, `eslint-webpack-plugin` (`repo:package.json`).
- Scripts npm (`repo:package.json`):
  - `"build": "cross-env NODE_ENV=development webpack --config webpack.dev.js --progress --mode development"`
  - `"build-prod": "cross-env NODE_ENV=production webpack --config webpack.prod.js --progress --mode production"`
  - `"lint": "eslint src/views/"`, `"lint:vue": "eslint \"src/views/**/*.vue\""`, `"lint:js": "eslint \"src/views/**/*.js\""`
- Entradas do webpack: páginas comuns, menu, paleta de comandos, painel, configurações, scripts comuns dos blocos e um bundle por bloco (`repo:webpack.common.js:8-37`), mais os modais de seleção em React (`:140-144`). Saída em `src/assets/js/`, `publicPath: './wp-content/plugins/tainacan/assets/js/'` (`:39-41`).
- Sass: `compile-sass.sh` usa `npx sass`; `--prod` minifica (`repo:compile-sass.sh`).
- `build.sh` (`repo:build.sh:1-130`):
  - Lê `build-config.cfg` (modelo `build-config-sample.cfg`), com `wp_plugin_dir`.
  - Usa MD5 para rodar `npm ci`, `composer install` (`--no-dev` em `--prod`), Sass e `npm run build` / `build-prod` só quando algo mudou.
  - Copia `src/*` com `rsync` para o diretório do plugin e **apaga** `.vue`, `.js`, `.scss` e `.sass` de `views/`.
  - Em `--prod`, apaga também `class-tainacan-bulk-edit.php`, os exposers oai-pmh, json-ld, txt e xml, e os `.js.map`.
  - Por isso a instalação 1.0.3 não tem os fontes Vue, e exposers legados podem faltar no pacote de produção.
- Composer: `respect/validation ^1.1`, `smalot/pdfparser`, `kornrunner/blurhash`, `phpoffice/phpspreadsheet ~5.8.1` etc. Desenvolvimento: `phpunit ^9.6.34`, `wpcs ^3.1.0`, `phpcs ^3.13.6`, `yoast/phpunit-polyfills`, `pronamic/wp-documentor`. Scripts `composer phpcs` e `composer phpcbf` (`repo:composer.json`).

<a id="d3"></a>
### D.3 Testes

- PHPUnit: `phpunit.xml.dist` usa o bootstrap `tests/bootstrap.php` e roda `tests/test-*.php` (`repo:phpunit.xml.dist`).
  - Arquivos com prefixo `_` ou `__` (`_test-logs.php`, `__test-bulkedit.php`) ficam de fora pelo padrão.
- `tests/bootstrap.php` (`repo:tests/bootstrap.php:1-40`; `repo:tests/bootstrap-config-sample.php`):
  - Lê `tests/bootstrap-config.php` (cópia de `bootstrap-config-sample.php`, com `tests_dir => '/tmp/wordpress/wordpress-tests-lib'`) ou usa `WP_TESTS_DIR`.
  - Carrega `src/tainacan.php` em `muplugins_loaded` e dispara a ativação em `plugins_loaded`.
  - Inclui fábricas e as bases `tainacan-unit-test-case.php` e `tainacan-unit-api-test-case.php`.
- Como rodar (de `repo:.github/workflows/test.yml` e `repo:run-tests.sh`):
  1. `./tests/bin/install-wp-tests.sh wordpress_test root "" /tmp/wordpress 127.0.0.1:3306 latest`
  2. `mv tests/bootstrap-config-sample.php tests/bootstrap-config.php`
  3. `phpunit` (o que o `run-tests.sh` executa).
- Testes JS ou e2e: **não há** (grep por jest, cypress, playwright e vitest em `package.json` e `tests/` sem resultado).

<a id="d4"></a>
### D.4 Lint

- PHPCS: `phpcs.xml.dist` com `WordPress-Core` e `WordPress-Docs`, excluindo `node_modules` e `vendor` (`repo:phpcs.xml.dist`).
- ESLint flat: `js.configs.recommended` mais `eslint-plugin-vue` `flat/recommended`; globais `tainacan_plugin`, `tainacan_blocks`, `tainacan_extra_components`, `wp`; `no-console` como aviso (`repo:eslint.config.js:1-50`). Roda também no build do webpack (`repo:CONTRIBUTING.md`, "Coding Guidelines").
- Regras mais rígidas em discussão na issue #1052 (`repo:CONTRIBUTING.md`, "Coding Guidelines").

<a id="d5"></a>
### D.5 Versões mínimas

- **Δ** [1.4.1]: WordPress 6.5+, PHP 7.4+, testado até WordPress 7.1 (`tainacan.php:9-11`; `readme.txt:4-6`). [1.0.3]: WordPress 6.0+, PHP 7.0+ (`tainacan.php:9-11` [1.0.3]).
- **Incoerência no CI**: a matriz testa PHP 7.4, 8.0, 8.1, 8.3 e 8.4 contra WordPress `latest` **e `5.9`**, mais 8.2, 8.5 e 8.6 (experimental) só com `latest`, embora o plugin declare WordPress 6.5 como mínimo (`repo:.github/workflows/test.yml`, matriz).

<a id="d6"></a>
### D.6 CI (`.github/workflows`)

- `test.yml`: em push e PR, ignorando mudanças só em `**.md`, `**.txt` e `docs/**`.
  - Serviço MySQL 8.0, `install-wp-tests.sh`, PHPUnit em phar: 9.6.36 para PHP 8.x e 6.5.7 para 7.4.
  - Composer com `--ignore-platform-reqs` para PHP abaixo de 8.3.
  - Teste multisite comentado.
- `generate-docs.yml`: em push na `develop`, regenera a documentação (com permissão de escrita e PR).

<a id="d7"></a>
### D.7 Fluxo de branches e PRs (`repo:CONTRIBUTING.md`)

- GitFlow:
  - Permanentes: `main` (produção) e `develop` (integração).
  - Suporte: `feature/*` (também para correções não urgentes), `release/*` e `hotfix/*` (estes dois só para mantenedores).
  - "Never commit directly to `main` or `develop`".
  - PR sempre contra a `develop`, depois de `git rebase upstream/develop`.
  - Conventional Commits (`feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`, `ci`), assunto com menos de 72 caracteres.
  - Pelo menos uma aprovação de mantenedor; PRs sem atividade por 30 dias podem ser fechados.
- **Divergência**: o CONTRIBUTING diz `main`, mas o branch de produção no GitHub é **`master`**: `origin/main` não existe, `origin/master` existe (`git branch -a`). O branch padrão é a `develop`.
- Modelo de PR (`repo:.github/pull_request_template.md`): issue relacionada, tipo de mudança, ESLint/PHPCS, testes, `deprecated.js` quando o bloco mudar, capturas de tela.

<a id="d8"></a>
### D.8 i18n

- Text domain `tainacan` (`tainacan.php:13`). Scripts usam `wp_set_script_translations(..., 'tainacan')` (`views/class-tainacan-pages.php:490`, `:977`; `views/gutenberg-blocks/class-tainacan-gutenberg-blocks.php:161`, `:194`, `:339`).
- Chunks do webpack: carregador `tainacan_load_script_translation_file_for_chunk` (`classes/tainacan-utils.php:324`).
- Strings do admin em `views/tainacan-i18n.php`, que devolve array filtrável por `tainacan-i18n` (`views/tainacan-i18n.php:5`). Mapeadores acrescentam strings por `tainacan-admin-i18n` (`classes/mappers/class-tainacan-mappers-handler.php:30-32`).
- Sem pasta `languages/` em `src/`. Que as traduções venham do translate.wordpress.org é inferência, não confirmada.
- Textos traduzíveis do papel e das capabilities carregados em `init`, por causa das mudanças de i18n do WordPress 6.7 (`classes/class-tainacan-roles.php:68-73`).

---

<a id="sE"></a>
## E. Armadilhas de desempenho conhecidas

1. **Registro de CPTs em todo `init`**: `Items::register_post_type()` faz `Collections::fetch(['nopaging'=>true])`, busca todas as taxonomias e registra um CPT e uma taxonomia por entidade a cada requisição (`classes/repositories/class-tainacan-items.php:216-252`; "TODO: This can be a problem in large repositories." em `:221`). O CPT de itens é `hierarchical => true` (`classes/entities/class-tainacan-collection.php:133`). Impacto disso nas consultas do admin do WordPress: inferência.
2. **Valores em `wp_postmeta` com `meta_key` numérico**: faceta, filtro e busca avançada viram `meta_query`, com JOINs em `wp_postmeta` por `meta_key` = id do metadado (`classes/repositories/class-tainacan-item-metadata.php:62-109`). O `type` da cláusula é forçado pelo tipo do filtro (`class-tainacan-rest-items-controller.php:697-707`); `NUMERIC` e `DATE` geram `CAST` e anulam índice (efeito do `CAST` sobre o índice é comportamento do MySQL, não confirmado aqui).
3. **Busca textual (`s`)**: o `Search_Engine` troca o `posts_search` por `(título/conteúdo) OR (termos) OR EXISTS(SELECT ... FROM wp_postmeta m WHERE m.post_id = posts.ID AND m.meta_value LIKE '%termo%')`. Para anônimos, acrescenta `INNER JOIN wp_posts pmeta ON m.meta_key = pmeta.ID AND post_status='publish'`, comparando `meta_key` (texto) com `ID` (`classes/class-tainacan-search-engine.php:305-333`, `:335-359`).
   - Também `SELECT DISTINCT` (`:362-370`) e `LEFT JOIN` de relacionamentos (`:373-390`).
   - `LIKE '%x%'` não usa índice: varredura de `wp_postmeta`. Desligável com `TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE` ou com a opção `tainacan_option_enable_default_search_engine` (`:104-110`).
4. **Contagens**: `Items::fetch` não define `no_found_rows` (nenhuma ocorrência em `classes/repositories/class-tainacan-items.php:278-418`) e a REST usa `found_posts` para `X-WP-Total` (`class-tainacan-rest-items-controller.php:798`). Toda listagem paga `SQL_CALC_FOUND_ROWS` ou o `COUNT` equivalente do WordPress (mecanismo do WordPress: inferência).
5. **Facetas (`fetch_all_metadatum_values`)**:
   - Monta a query de itens sem executá-la (`posts_pre_query` com `__return_empty_array`, usando `WP_Query::request`) e a usa como subconsulta `INNER JOIN ({$items_query})` em cada faceta (`classes/repositories/class-tainacan-metadata.php:1267-1273`, `:1325-1360`, `:1485-1495`).
   - Metadados comuns: subconsulta `SELECT DISTINCT post_id, meta_value FROM wp_postmeta WHERE ... ORDER BY meta_value` unida à query de itens, mais um `COUNT(DISTINCT ...)` separado (`:1486-1492`).
   - **Contagem por valor = uma `WP_Query` por valor** com `count_items` ligado (`:1448-1462` taxonomia, `:1549-1561` meta).
   - **Agravante (inferência)**: antes disso, `items_filter` recebe `nopaging = 1` (`:1243`; `:1229` [1.0.3]). `count_items_query` herda `items_filter` e só acrescenta `posts_per_page = 1` (`:1450`, `:1551`); o `nopaging` continua valendo, a `WP_Query` ignora o `posts_per_page` e traz **todos os IDs** para cada valor da faceta.
   - Mitigações: `TAINACAN_FACETS_DISABLE_COUNT_ITEMS` ou a opção `tainacan_option_facets_enable_count_items`; `TAINACAN_FACETS_DISABLE_FILTER_ITEMS` ou a opção `tainacan_option_facets_enable_filter_items` (`classes/api/endpoints/class-tainacan-rest-facets-controller.php:100-108`, `:161-166`); ou responder no filtro `tainacan-fetch-all-metadatum-values` (`classes/repositories/class-tainacan-metadata.php:1261-1264`).
6. **Listagem REST sem `fetch_only`**: cada item roda `add_metadata_to_item()` → `Item::get_metadata()` → `Item_Metadata::fetch()` → `Metadata::fetch_by_collection()` (uma `WP_Query` de metadados com `get_post_ancestors`) e gera `value_as_html` e `value_as_string` de **todos** os metadados (`class-tainacan-rest-items-controller.php:229-257`, `:311`; `classes/repositories/class-tainacan-item-metadata.php:319-341`; `classes/repositories/class-tainacan-metadata.php:426-447`).
   - Sem cache de objeto nos repositórios: grep por `wp_cache_get`, `wp_cache_set` e `set_transient` em `classes/repositories/` sem resultado.
   - Relationship e Taxonomy fazem consultas extras para gerar o HTML. N+1 por item: inferência.
   - **Use sempre `fetch_only` e `fetch_only_meta`** em listagens de plugin.
7. **Δ `perpage=-1` na [1.0.3]**: listagem sem limite ([C.3](#c3)). Corrigido na [1.4.1].
8. **Várias coleções e usuário logado**: `Items::fetch` acrescenta `_filter_where`, uma cláusula por post_type para checar `read_private` (`classes/repositories/class-tainacan-items.php:397-407`, `:447-...`).
9. **`register_post_type` e opções Compound**: `Compound::get_options()` busca os filhos e renderiza `form()` de cada um sempre que as opções são lidas, inclusive na API (`views/admin/components/metadata-types/compound/class-tainacan-compound.php:145-182`).
10. **Logs**: todo `insert()` gera log e diff ([A.8](nucleo-tainacan-parte-1.md#a8)). **Δ** Na [1.0.3] o log é um post, com postmeta; na [1.4.1], uma linha em tabela própria. Em importações em massa, use `disable_logs()` (`classes/repositories/class-tainacan-repository.php:67-89`). Há a constante `TAINACAN_DOING_IMPORT`, efeito não confirmado.
11. **Busca avançada com `tainacan_core_title` sem coleção**: expande para um `OR` com o id do metadado de título de **cada** coleção, com `Collections::fetch([])` (`classes/repositories/class-tainacan-items.php:975-1030`).
12. **Itens relacionados**: `fetch_related_items` consulta metadados por `_option_collection_id` e `_option_display_in_related_items`; com metadado do repositório, percorre **todas** as coleções (`classes/repositories/class-tainacan-items.php:846-880`).

---

<a id="sF"></a>
## F. Lista rápida de verificações (o que costuma quebrar plugins)

- `insert()` sem `validate()` em entidade publicada lança `Exception('Entities must be validated before you can save them')` ([A.3](nucleo-tainacan-parte-1.md#a3)).
- Opções de tipo: ler de `get_metadata_type_options()` ou de `_option_{chave}`, nunca de `get_post_meta($id,'{chave}')`. Para taxonomia, `taxonomy_id` ([A.5](nucleo-tainacan-parte-1.md#a5)).
- Callback de `tainacan_register_admin_hook` deve **devolver** string; o HTML entra por `v-html`, então nada de `<script>` inline ([B.1](#b1)).
- Busca facetada: registrar `tainacan_faceted_search_collection_item_after` **e** `tainacan_faceted_search_item_after` ([B.2](#b2)).
- `taxquery` da URL descarta `include_children` e espera `metadatum` no lugar de `field`; `metaquery` descarta `type` e espera `metadatumtype` ([C.2](#c2)).
- Filtros `--type-*` não existem para numeric, selectbox e core-title, e o genérico não roda para tipos com HTML próprio ([B.3](#b3)).
- Ganchos de registro disparados na inclusão do arquivo do Tainacan (`tainacan-register-metadata-type`, `-filter-type`, `-vuejs-plugin`): ordem de carga ([A.4](nucleo-tainacan-parte-1.md#a4)).
- **Δ** 1.0.3 × 1.4.1: logs em tabela própria, `perpage<1`, `tainacan_get_the_document`, versões mínimas.

---

<a id="sG"></a>
## G. Modelos mínimos de código (derivados das assinaturas acima)

Estes trechos só usam assinaturas confirmadas nas seções anteriores. Não foram executados nesta pesquisa.

<a id="g1"></a>
### G.1 Criar um item com valor de metadado

```php
$collection = tainacan_collections()->fetch( $collection_id );          // A.1 / A.3
$item = new \Tainacan\Entities\Item();
$item->set_title( 'Título' );
$item->set_collection( $collection );
$item->set_status( 'publish' );
if ( $item->validate() ) {                                               // obrigatório p/ publish (A.3)
    $item = tainacan_items()->insert( $item );                           // devolve NOVA instância
}
$metadatum = tainacan_metadata()->fetch( $metadatum_id );
$im = new \Tainacan\Entities\Item_Metadata_Entity( $item, $metadatum );
$im->set_value( 'valor' );
if ( $im->validate() ) {                                                 // sempre obrigatório (A.3)
    tainacan_item_metadata()->insert( $im );
}
```

Métodos conferidos: `Item::set_title()` (`classes/entities/class-tainacan-item.php:355`), `set_collection(Entities\Collection)` e `set_collection_id()` do trait de relação com a coleção (`classes/traits/class-tainacan-entity-collection-relation.php:62`, `:71`) e `Item_Metadata_Entity::set_value()` (`classes/entities/class-tainacan-item-metadata-entity.php:363`).

Observação: se a coleção tem metadados obrigatórios, publicar antes de gravar os valores pode falhar; ver L-NUC-01 na [parte 3](nucleo-tainacan-parte-3.md).

<a id="g2"></a>
### G.2 Ler a taxonomia de um metadado do tipo Taxonomy

```php
$tax_id = get_post_meta( $metadatum_id, '_option_taxonomy_id', true );   // índice (A.5)
// ou: $m->get_metadata_type_options()['taxonomy_id']
$tax_slug = 'tnc_tax_' . (int) $tax_id;                                  // A.2
```

<a id="g3"></a>
### G.3 Gancho de formulário no admin

```php
add_action( 'tainacan-register-admin-hooks', function () {              // B.1, dispara em admin_init
    tainacan_register_admin_hook( 'collection', function () {
        ob_start();
        ?><div class="field"><input type="text" name="meu_plugin_campo"></div><?php
        return ob_get_clean();                                            // DEVE devolver string
    }, 'end-left' );
} );
add_action( 'tainacan-insert-tainacan-collection', function ( $collection ) {
    // ler o campo enviado pelo fillExtraFormData (B.1) e gravar em postmeta
} );
```

Segue o exemplo oficial de `repo:docs/admin-form-hooks.md:62-65`. A forma exata de ler o valor no `save_data` está nesse documento e não foi reproduzida aqui.

<a id="g4"></a>
### G.4 Antes e depois de cada item na busca facetada (JS)

```js
const html = ( html, item ) => html + '<span class="meu-selo"></span>';
wp.hooks.addFilter( 'tainacan_faceted_search_collection_item_after', 'meu-plugin', html ); // liga o v-if
wp.hooks.addFilter( 'tainacan_faceted_search_item_after', 'meu-plugin', html );            // produz o HTML
```

Registrar a mesma função nos dois nomes faz o HTML ser acrescentado só uma vez: `getAfterHook` aplica apenas `tainacan_faceted_search_item_after` e o nome por coleção ([B.2](#b2)).

<a id="g5"></a>
### G.5 Listagem enxuta pela REST

```
GET /wp-json/tainacan/v2/collection/{cid}/items
    ?perpage=24&paged=1
    &fetch_only=title,thumbnail,url
    &fetch_only_meta=123,456
    &taxquery[0][taxonomy]=tnc_tax_9&taxquery[0][terms][]=55&taxquery[0][operator]=IN
    &metaquery[0][key]=123&metaquery[0][value]=1900&metaquery[0][compare]=>=&metaquery[0][metadatumtype]=NUMERIC
```

Cada parâmetro está documentado em [C.2](#c2), [C.3](#c3) e [C.4](#c4). O `include_children` não é aceito e o `type` precisa ir como `metadatumtype`.
