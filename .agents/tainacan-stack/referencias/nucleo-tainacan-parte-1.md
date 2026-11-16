# Núcleo do Tainacan, parte 1: fontes e arquitetura

Referência do núcleo do Tainacan (1.0.3 e 1.4.1) com citações `arquivo:linha`, para agentes que escrevem ou revisam plugins. Esta parte cobre as fontes (seção 0) e a arquitetura (seção A). Índice geral em [nucleo-tainacan.md](nucleo-tainacan.md).

Partes: [1: fontes e arquitetura](nucleo-tainacan-parte-1.md) · [2: ganchos, REST, ferramental, desempenho, modelos](nucleo-tainacan-parte-2.md) · [3: lições do modelo de dados e da API](nucleo-tainacan-parte-3.md)

## Sumário

- [0. Fontes, versões e como ler as citações](#s0)
- [A. Arquitetura](#sA)
  - [A.1 Carregamento do plugin](#a1)
  - [A.2 Entidades](#a2)
  - [A.3 Repositórios](#a3)
  - [A.4 Tipos de metadado](#a4)
  - [A.5 Opções de tipo de metadado (prefixo `_option_`)](#a5)
  - [A.6 Valores dos itens (item metadata)](#a6)
  - [A.7 Tipos de filtro](#a7)
  - [A.8 Logs](#a8)
  - [A.9 Capabilities e permissões](#a9)
  - [A.10 CPTs e taxonomias](#a10)
  - [A.11 Processos em segundo plano](#a11)
  - [A.12 Exposers e mapeadores](#a12)
  - [A.13 Blocos Gutenberg](#a13)
  - [A.14 Front-end: página do item e template tags](#a14)
  - [A.15 Busca facetada (Vue)](#a15)
  - [A.16 Páginas de admin (`\Tainacan\Pages`)](#a16)

---

<a id="s0"></a>
## 0. Fontes, versões e como ler as citações

Data da pesquisa: 21/10/2026.

| Rótulo | O que é |
|---|---|
| **[1.4.1]** | Repositório oficial do Tainacan no GitHub, branch padrão `develop` (commit `ab94a76`, "Merge tag '1.4.1' into develop", 20/10/2026). `master` está no commit `5e5394a` ("Merge branch 'release/1.4.1'"). Lido a partir de um clone raso. |
| **[1.0.3]** | Plugin 1.0.3 instalado a partir do pacote de distribuição, já construído: sem `.vue`/`.js` de origem, só `assets/js` compilado. |

- Citações **relativas à raiz do plugin**, que no repositório é `src/`: `classes/x.php:10` corresponde a `src/classes/x.php:10`. Arquivos de fora do plugin (build, testes, CI, docs) levam o prefixo `repo:`.
- Sem versão indicada, a linha é da **[1.4.1]**. Se a [1.0.3] tem o mesmo trecho, aparecem as duas linhas. "(1.0.3 e 1.4.1)" quer dizer linhas iguais nas duas.
- Mudanças entre 1.0.3 e 1.4.1 que afetam plugins: logs saíram de CPT para tabela própria, `perpage<1` passou a ser limitado, a chamada de `tainacan_get_the_document` foi corrigida e as versões mínimas subiram. Diferenças marcadas com **Δ**.
- "Não confirmado": o trecho não foi achado no código. "Inferência": dedução a partir do código, sem teste em execução.
- Citações marcadas "[1.0.3, conferido fora da pesquisa]" foram verificadas depois, na instalação 1.0.3, para checar as lições da parte 3.

---

<a id="sA"></a>
## A. Arquitetura

<a id="a1"></a>
### A.1 Carregamento do plugin

- Cabeçalho [1.4.1]: `Version: 1.4.1`, `Requires at least: 6.5`, `Tested up to: 7.1`, `Requires PHP: 7.4`, `Text Domain: tainacan` (`tainacan.php:8-13`). **Δ** [1.0.3]: `Requires at least: 6.0`, `Tested up to: 6.9`, `Requires PHP: 7.0` (`tainacan.php:9-11` [1.0.3]).
- Constantes `TAINACAN_VERSION`, `TAINACAN_BASE_DIR`, `TAINACAN_CLASSES_DIR` (`tainacan.php:22-24`).
- Global `$TAINACAN_API_MAX_ITEMS_PER_PAGE`: constante `TAINACAN_API_MAX_ITEMS_PER_PAGE` (mínimo 12) ou, sem ela, a opção `tainacan_option_search_results_per_page` (padrão 96) (`tainacan.php:27`; `tainacan.php:27` [1.0.3]).
- **Δ** `TAINACAN_API_MAX_BATCH_TERMS` vale 200 por padrão (`tainacan.php:32-34`). Só existe na [1.4.1].
- Carregamento por `classes/tainacan-creator.php`; migrações em `init` (`tainacan.php:37-41`).
- Ordem dentro do criador [1.4.1]:
  - Utilitários e autoload do Composer (`classes/tainacan-creator.php:42-44`).
  - Gerenciadores de segundo plano (heartbeat, importadores, exportadores, genéricos) instanciados **na inclusão do arquivo** (`classes/tainacan-creator.php:56-71`).
  - `spl_autoload_register('tainacan_autoload')` (`:73`).
  - Repositórios (`:161-177`), `Metadata_Type_Helper` (`:165`), `Filter_Type_Helper` (`:169`).
  - Exposers e mapeadores (`:179-181`), `Search_Engine` (`:183`), integrações (`:185-187`), `Roles` (`:189`), CLI (`:194`).
  - Rotas REST (`:197`), páginas do admin (`:199`), `Admin_Hooks` (`:203`), `Component_Hooks` (`:206`), `Plugin_Hooks` (`:209`), mídia (`:211-215`).
  - [1.0.3]: `Metadata_Type_Helper` em `:162`, `Filter_Type_Helper` em `:166`, `Admin_Hooks` em `:202`, `Plugin_Hooks` em `:208`.
- Funções globais dos repositórios: `tainacan_collections()`, `tainacan_filters()`, `tainacan_item_metadata()`, `tainacan_items()`, `tainacan_logs()`, `tainacan_metadata()`, `tainacan_taxonomies()`, `tainacan_terms()`, `tainacan_metadata_sections()` (`classes/tainacan-loaders.php:9-82`).

<a id="a2"></a>
### A.2 Entidades (`classes/entities/`)

- Arquivos: `class-tainacan-entity.php` (base), `collection`, `item`, `item-metadata-entity`, `metadatum`, `metadata-section`, `filter`, `taxonomy`, `term`, `log`.
- Tipo de post de cada entidade:
  - `Collection::$post_type = 'tainacan-collection'` (`classes/entities/class-tainacan-collection.php:71`).
  - `Metadatum::$post_type = 'tainacan-metadatum'` (`classes/entities/class-tainacan-metadatum.php:41`).
  - `Filter::$post_type = 'tainacan-filter'` (`classes/entities/class-tainacan-filter.php:31`).
  - `Taxonomy::$post_type = 'tainacan-taxonomy'` (`classes/entities/class-tainacan-taxonomy.php:30`).
  - `Metadata_Section::$post_type = 'tainacan-metasection'` (`classes/entities/class-tainacan-metadata-section.php:19`).
  - `Log::$post_type = 'tainacan-log'` (`classes/entities/class-tainacan-log.php:32`).
  - `Item_Metadata_Entity` e `Term`: `$post_type = false` (`class-tainacan-item-metadata-entity.php:16`, `class-tainacan-term.php:29`).
- API comum (`classes/entities/class-tainacan-entity.php`):
  - `get_mapped_property()` aplica `tainacan-entity-get-property` (`:194-203`); `set_mapped_property()` aplica `tainacan-entity-set-property` (`:215-217`).
  - `set_status()` passa por `tainacan-set-post-status` (`:260-261`); `get_status()` por `tainacan-get-post-status` (`:347-350`).
  - `validate()` (`:272-292`) percorre o mapa do repositório, chama `validate_prop()` (`:299`) e, se tudo passar, `set_as_valid()` (`:393`). `get_validated()` em `:385`, `get_errors()` em `:335`.
  - `_toArray()` aplica `{$hook_prefix}-to-array` (`:399-410`).
  - `can_read()`, `can_edit()`, `can_delete()`, `can_publish()` (`:422-452`).
- `get_db_identifier()`:
  - Base: devolve o `post_type` (`classes/entities/class-tainacan-entity.php:360-362`).
  - Coleção: `'tnc_col_' . id . '_item'`, de `$db_identifier_prefix = 'tnc_col_'` e `$db_identifier_sufix = '_item'` (grafado `sufix` no código) (`classes/entities/class-tainacan-collection.php:84`, `:91`, `:506-507`).
  - Taxonomia: `'tnc_tax_' . id` (`classes/entities/class-tainacan-taxonomy.php:51`, `:197-198`).

<a id="a3"></a>
### A.3 Repositórios (`classes/repositories/`)

- Base abstrata `classes/repositories/class-tainacan-repository.php`:
  - Construtor `protected`; registra `register_post_type` e `init_objects` em `init` e liga `tainacan-get-map-$name` a `get_default_properties` (`:96-101`).
  - Abstratos: `_get_map()` (`:135`), `fetch($args, $output = null)` (`:731`), `update($object, $new_values = null)` (`:738`), `register_post_type()` (`:743`).
  - Concretos: `insert()` (`:161`), `insert_metadata()` (`:257`), `fetch_output()` (`:324`; saída `'WP_Query'`, a padrão, ou `'OBJECT'`, array de entidades), `parse_fetch_args()` (`:376`), `fetch_one()` (`:643`), `trash()` (`:666`), `delete($entity, $permanent = true)` (`:676`), `can_edit/can_read/can_delete/can_publish` (`:754`, `:784`, `:823`, `:847`) e os estáticos `get_entity_by_post()` (`:516`), `get_entity_by_post_type()` (`:540`), `get_repository()` (`:594`).
- **Singleton**: subclasses usam o trait `\Tainacan\Traits\Singleton_Instance` (`get_instance()` estático, construtor `private` que chama `parent::__construct()` e depois `$this->init()`) (`classes/traits/class-tainacan-singleton-instance.php:12-28`). Use `Repositório::get_instance()`; `new` falha porque o construtor é privado.
- **Validação antes de gravar**, com a exceção exata:
  - `Repository::insert()`: se o status está em `['publish','future','private','pending']` (lista alterável pelo filtro `tainacan-status-require-validation`) e `! $obj->get_validated()`, faz `throw new \Exception( 'Entities must be validated before you can save them' );` (`classes/repositories/class-tainacan-repository.php:164-166`, 1.0.3 e 1.4.1).
  - Consequência: rascunho e `auto-draft` gravam sem `validate()`.
  - `Item_Metadata::insert()` exige validação **sempre**, sem olhar status, com a mesma mensagem (`classes/repositories/class-tainacan-item-metadata.php:30-34`).
  - `save_core_metadatum_value()` lança `'Item metadata should be validated beforehand'` (`classes/repositories/class-tainacan-item-metadata.php:152`).
  - `Metadata::insert_array_metadatum()` lança `\ErrorException('The entity wasn\'t validated.' ...)` (`classes/repositories/class-tainacan-metadata.php:1155-1166`).
- Fluxo de `Repository::insert()` (`classes/repositories/class-tainacan-repository.php:161-246`):
  1. Valida, como acima.
  2. Dispara `tainacan-pre-insert` e `tainacan-pre-insert-{post_type}` (`:173-176`).
  3. Copia as propriedades nativas para `WP_Post`.
  4. Para `Item`, o post_type vira o identificador da coleção e o gancho específico é `tainacan-pre-insert-tainacan-item` (`:198-210`). Item sem coleção: devolve `false` (`:204-206`).
  5. `wp_insert_post()` (`:221`); se falhar, devolve `false` (`:223-225`).
  6. Grava propriedades mapeadas como `meta`/`meta_multi` via `insert_metadata()` (`:231-235`) e `update_post_meta($id,'_user_edit_lastr',...)` (`:236`).
  7. Dispara `tainacan-insert` com `($obj, $diffs, $is_update)` e `tainacan-insert-{post_type}` (`:238-241`); para itens, `{post_type}` é `tainacan-item`.
  8. Devolve **nova instância** da entidade (`:244`).
- `parse_fetch_args()`: propriedades do mapa viram argumentos de `fetch`; as mapeadas para meta viram `meta_query`, as nativas viram argumentos de `WP_Query` (exceções `ID→p`, `post_title→title`); também converte `orderby` (`classes/repositories/class-tainacan-repository.php:376-424`).
- `Items::fetch($args = [], $collections = [], $output = null)` (`classes/repositories/class-tainacan-items.php:278-418`):
  - `$args` numérico: busca um item; devolve `[]` se a coleção não for legível (`:282-299`).
  - Sem coleção: consulta todos os `post_types` de coleções e filtra pelas legíveis pelo usuário (`:311-361`).
  - `orderby` padrão `post_date DESC` com `ID DESC` de desempate (`:373-386`).
  - Opcionais: `parse_relationship_metaquery` (com `TAINACAN_ENABLE_RELATIONSHIP_METAQUERY`) e `parse_core_metadata_for_advanced_search` (chaves `tainacan_core_title`/`tainacan_core_description`, desligável com `TAINACAN_DISABLE_CORE_METADATA_ON_ADVANCED_SEARCH`) (`:387-393`).
  - Filtro `tainacan-fetch-args` com `($args, 'items')` (`:395`; `:376` [1.0.3]).
  - Com mais de um CPT e usuário logado, injeta o `posts_where` `_filter_where` para checar permissão por coleção (`:397-407`).
  - `fetch_ids()` força `fields => ids` (`:427-432`).
- `Metadata::fetch_by_collection()` sem `post_status` nos argumentos: monta os status por coleção, com os públicos (`get_post_stati(['public'=>true])`) e só acrescenta os privados se o usuário atual tem `tnc_col_{id}_read_private_metadata` (ou `tnc_rep_read_private_metadata` para o repositório) (`classes/repositories/class-tainacan-metadata.php:445-456` [1.0.3, conferido fora da pesquisa]).

<a id="a4"></a>
### A.4 Tipos de metadado

- Base: `abstract class Metadata_Type` (`views/admin/components/metadata-types/metadata-type/class-tainacan-metadata-type.php:10`).
- Propriedades:
  - `primitive_type`: `string`, `item`, `term`, `compound`, `date` etc.; define os tipos de filtro compatíveis (`:13-24`).
  - `repository`: repositório da entidade referenciada (`:26-35`).
  - `options` e `default_options` (`:37-47`).
  - `core`, `related_mapped_prop`, `component` (exibição), `form_component` (formulário de opções), `name`, `description`, `preview_template`, `sortable` (`:49-96`).
  - `manage_multiple_input` (`:98-107`).
- Métodos: `validate(Item_Metadata_Entity)` devolve `true` por padrão (`:113`). `set_options()` passa por `tainacan_maybe_unserialize_array` (`:188-191`). `get_options()` faz `array_merge(default_options, options)` (`:202-204`). `get_option($key)` devolve `''` se a chave não existe (`:214-217`). `validate_options(Metadatum)` (`:267`), `get_slug()` (nome da classe em minúsculas, `:309-312`), `_toArray()` (`:314-332`).
- `get_value_as_html()` **não** é declarado na base. `Item_Metadata_Entity::get_value_as_html()` usa o método do tipo se existir (`method_exists`) e só cai na formatação genérica quando o tipo não o define (`classes/entities/class-tainacan-item-metadata-entity.php:146-156`).
- Nativos registrados em `Metadata_Type_Helper::init()`: Text, Textarea, Date, Numeric, Selectbox, Relationship, Taxonomy, Compound, User, Control, GeoCoordinate, URL (`views/admin/components/metadata-types/metadata-type-helper/class-tainacan-metadata-type-helper.php:24-35`). Core_Title e Core_Description são os metadados núcleo (`classes/repositories/class-tainacan-metadata.php:26-29`).
- **Registrar um tipo novo**:
  - `do_action('tainacan-register-metadata-type', $this)`, disparado **dentro de `Metadata_Type_Helper::init()`**, isto é, quando o singleton é criado durante a inclusão do arquivo do Tainacan (`class-tainacan-metadata-type-helper.php:41`, `:40` [1.0.3]; instanciação em `classes/tainacan-creator.php:165`).
  - O callback recebe o helper e chama `$helper->register_metadata_type($handle, $class_name, $script_path, $args = [])`, que registra a classe no repositório e agenda o script para `admin_enqueue_scripts` com prioridade 80 (`class-tainacan-metadata-type-helper.php:37`, `:43-53`, `:62-77`).
  - Exemplo oficial: `add_action("tainacan-register-metadata-type", ...)` no nível de cima do arquivo do plugin (`repo:docs/creating-metadata-type.md:31-41`); o componente Vue do formulário vai por `tainacan-register-vuejs-component` (`repo:docs/creating-metadata-type.md:44`).
  - Registro direto, sem script: `\Tainacan\Repositories\Metadata::get_instance()->register_metadata_type($class)` (`classes/repositories/class-tainacan-metadata.php:311-322`). Há `unregister_metadata_type` (`:329-338`) e o gancho legado `do_action('register_metadata_types')` em `fetch_metadata_types` (`:807`).
  - **Armadilha de ordem de carga (inferência)**: o gancho dispara na inclusão do `tainacan.php`. Um `add_action('tainacan-register-metadata-type')` feito dentro de `plugins_loaded` chega tarde, embora a documentação recomende `plugins_loaded` para chamar APIs do Tainacan (`repo:docs/plugin-development-guidelines.md:32`). Pela ordem alfabética de `active_plugins`, `tainacan-xyz/` vem antes de `tainacan/` (o `-` antecede o `/`), por isso plugins com prefixo `tainacan-` costumam funcionar com `add_action` no nível de cima. Para plugins carregados depois, chamar `Metadata_Type_Helper::get_instance()->register_metadata_type(...)` direto em `plugins_loaded` deve funcionar, porque o enfileiramento só ocorre em `admin_enqueue_scripts`. Não testado. Vale o mesmo para `tainacan-register-filter-type` (A.7) e `tainacan-register-vuejs-plugin` (`views/admin/classes/hooks/class-tainacan-plugin-hooks.php:24`, também em `init()` do singleton). `tainacan-register-vuejs-component` não tem o problema: dispara em `init`, prioridade 80 (`views/admin/classes/hooks/class-tainacan-component-hooks.php:23-27`).

<a id="a5"></a>
### A.5 Opções de tipo de metadado (prefixo `_option_`: confirmado)

- Fonte canônica: propriedade `metadata_type_options` de `Metadatum`, `'map' => 'meta'`, guardada como **um único** postmeta `metadata_type_options` (array serializado) no post do metadado (`classes/repositories/class-tainacan-metadata.php:160-167`).
- Índice explodido: após cada `insert`, `update_metadata_type_index()` grava cada chave como postmeta separado `'_option_' . $option` (`classes/repositories/class-tainacan-metadata.php:1733-1757`; chamada em `insert()` `:749-755`; [1.0.3] `:1698-1705`). O comentário diz que o índice existe para consultas como `meta_query` com `_option_taxonomy_id = 4` (`:1733-1746`).
  - **O índice só é atualizado, nunca limpo** (inferência): só há `update_post_meta` no laço (`:1754-1756`). Chave removida das opções deixa `_option_<chave>` antigo no banco.
  - Por isso `get_post_meta($id, 'taxonomy_id')` devolve vazio. Formas certas: `get_post_meta($id, '_option_taxonomy_id', true)` ou `$metadatum->get_metadata_type_options()['taxonomy_id']`.
- O núcleo consulta o índice: `_option_taxonomy_id` (`classes/api/endpoints/class-tainacan-rest-items-controller.php:519`, `classes/api/endpoints/class-tainacan-rest-taxonomies-controller.php:218`, `classes/api/endpoints/class-tainacan-rest-reports-controller.php:673`, `:700`, `classes/repositories/class-tainacan-taxonomies.php:358`, `classes/repositories/class-tainacan-metadata.php:1804`, `:1843`), `_option_allow_new_terms` (`classes/repositories/class-tainacan-metadata.php:1847`), `_option_collection_id` e `_option_display_in_related_items` (`classes/repositories/class-tainacan-items.php:857`, `:861`).
- **Taxonomy** (`views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php`):
  - Padrões: `allow_new_terms` ('no'), `link_filtered_by_current_collection` ('no'), `link_filtered_by_collections` ([]), `input_type` ('tainacan-taxonomy-radio'), `hide_hierarchy_path` ('no'), `do_not_dispaly_term_as_link` ('no', grafia do original) (`:22-29`).
  - Rótulos no formulário: `taxonomy_id`, `input_type`, `visible_options_list`, `allow_new_terms`, `link_filtered_by_current_collection`, `link_filtered_by_collections`, `hide_hierarchy_path`, `do_not_dispaly_term_as_link` (`:87-105`).
  - `_toArray()` acrescenta as **derivadas** `options['taxonomy']` (slug `tnc_tax_{id}` a partir de `taxonomy_id`) e `options['hierarchical']` (`:618-626`). O formulário Vue devolve `taxonomy` ao salvar (`views/admin/components/metadata-types/taxonomy/FormTaxonomy.vue:579`), o que explica um `_option_taxonomy` no banco, possivelmente vazio ou desatualizado. Fonte preferencial: `_option_taxonomy_id`, com slug `tnc_tax_{id}`. A relação "vazio = vindo do formulário" é inferência.
  - Legado: `allow_new_terms` pode estar salvo como `false` (`:329`).
- **Relationship** (`views/admin/components/metadata-types/relationship/class-tainacan-relationship.php`):
  - Sem `set_default_options`. Rótulos e validação definem `collection_id` (obrigatório, numérico), `search` (id do metadado usado como rótulo e critério de busca), `display_related_item_metadata`, `display_in_related_items` (yes/no), `accept_draft_items` (yes/no), `accept_only_items_authored_by_current_user` (yes/no) (`:54-83`, `:164-200`).
  - `get_options()` acrescenta as derivadas `related_primitive_type` e, se o metadado de busca é taxonomia, `search_by_tax` (`:467-480`).
  - `primitive_type = 'item'`, repositório Items (`:15-16`).
- **Outros tipos**:
  - Selectbox: `input_type` ('tainacan-selectbox') (`views/admin/components/metadata-types/selectbox/class-tainacan-selectbox.php:20-22`).
  - User: `default_author` ('no') (`views/admin/components/metadata-types/user/class-tainacan-user.php:23-25`).
  - URL: `link-as-button`, `force-iframe`, `iframe-min-height`, `iframe-allowfullscreen`, `is-image`, com hífen (`views/admin/components/metadata-types/url/class-tainacan-url.php:20-26`).
  - GeoCoordinate: `map_provider`, `attribution`, `initial_zoom`, `maximum_zoom`, `initial_latitude`, `initial_longitude` (`views/admin/components/metadata-types/geocoordinate/class-tainacan-geocoordinate.php:26-33`).
  - Control: `control_metadatum_options`, `control_metadatum` ('document_type' | 'collection_id' | 'has_thumbnail'), `only_repository` (`views/admin/components/metadata-types/control/class-tainacan-control.php:87-91`).
  - Compound: `get_options()` lê `parent`, `children_order` e devolve `children_objects`, montado na hora com `fetch` dos filhos, `form()` e `ob_start` para cada filho. Opção pesada (`views/admin/components/metadata-types/compound/class-tainacan-compound.php:145-182`).
  - Text, Textarea, Numeric, Date: sem `set_default_options` (grep sem resultado).

<a id="a6"></a>
### A.6 Valores dos itens (item metadata)

- Ficam em `wp_postmeta` do post do item, com **`meta_key` igual ao ID numérico do metadado** (`classes/repositories/class-tainacan-item-metadata.php:62-109`: `update_post_meta($item_id, $metadatum->get_id(), ...)`).
  - Metadados núcleo (título, descrição) são gravados **também** como meta comum, para `meta_query` funcionar (`:44-49`).
  - Taxonomia: valor em `wp_term_relationships` via `wp_set_object_terms`; termos ausentes são criados com `Terms::insert` quando permitido (`:162-204`).
  - Composto: registro pai com `meta_value = ''`; filhos ligados por `parent_meta_id` (`:53-67`, `:250`).
  - Multivalorado: `delete_post_meta` e depois vários `add_post_meta`. Relationship remove duplicatas (`:94-110`).
- Ganchos: `tainacan-pre-insert-Item_Metadata_Entity` e `tainacan-insert-Item_Metadata_Entity`, além dos genéricos (`:37-38`, `:116-117`).
- `Item_Metadata::fetch(Item)` chama `Metadata::fetch_by_collection()` (que sobe a hierarquia de coleções com `get_post_ancestors`) e cria um `Item_Metadata_Entity` por metadado (`classes/repositories/class-tainacan-item-metadata.php:319-341`; `classes/repositories/class-tainacan-metadata.php:426-447`).
- Propriedades do metadado relevantes para consultas diretas [1.0.3, conferido fora da pesquisa]: `parent` mapeia para `post_parent` (é o metadado composto pai, padrão 0) (`classes/repositories/class-tainacan-metadata.php:72-77`), e `collection_id` é `'map' => 'meta'` (`:168-173`), com `'default'` representando o repositório (`$default_metadata = 'default'`, `:22`).
- Separador de multivalor padrão: `<span class="multivalue-separator"> | </span>`, filtrável por `tainacan-item-metadata-get-multivalue-separator` (`classes/entities/class-tainacan-item-metadata-entity.php:125`, `:136` [1.0.3, conferido fora da pesquisa]; filtro em `:139` [1.4.1]).

<a id="a7"></a>
### A.7 Tipos de filtro

- Base: `abstract class Filter_Type` (`views/admin/components/filter-types/filter-type/class-tainacan-filter-type.php:11`). O construtor faz `add_action('register_filter_types', ...)` (`:67-69`). API: `set_supported_types/get_supported_types` (tipos primitivos aceitos), `set_component`, `set_form_component`, `set_options/get_option`, `validate_options(Filter)`, `_toArray` (`:81-270`).
- Nativos: Date, Numeric, Taginput, Checkbox, Selectbox, Autocomplete, Date_Interval, Dates_Intersection, Numeric_Interval, TaxonomyTaginput, TaxonomyCheckbox, TaxonomySelectbox, Numeric_List_Interval, Numerics_Intersection (`views/admin/components/filter-types/filter-type-helper/class-tainacan-filter-type-helper.php:23-36`).
- **Registrar um tipo novo**: `do_action('tainacan-register-filter-type', $this)` em `Filter_Type_Helper::init()` (`:41`, 1.0.3 e 1.4.1), e então `$helper->register_filter_type($handle, $class_name, $script_path, $args = [])` (`:63-77`). Instanciado na inclusão (`classes/tainacan-creator.php:169`): mesma armadilha de ordem de carga de A.4. No repositório: `Filters::register_filter_type()` (`classes/repositories/class-tainacan-filters.php:279`) e o legado `do_action('register_filter_types')` (`:319`).
- Opções do filtro em `filter_type_options` (`classes/repositories/class-tainacan-filters.php:55`). **Não existe** índice `_option_` para filtros (grep sem resultado).

<a id="a8"></a>
### A.8 Logs

- **Δ [1.4.1]**: logs em tabela própria `{$wpdb->prefix}tainacan_logs` (`classes/repositories/class-tainacan-logs.php:451-454`; criação em `migrations.php:553-560`).
  - `insert()` usa `$wpdb->insert` e dispara `tainacan-pre-insert*` e `tainacan-insert*` (`classes/repositories/class-tainacan-logs.php:468-523`).
  - `fetch()` e `fetch_count()` aplicam `tainacan-fetch-args` com `'logs'` (`:230-300`).
  - Há `class-tainacan-logs-deprecated.php` e a constante `TAINACAN_USE_DEPRECATED_LOGS`. Comportamento exato da constante não confirmado.
- **[1.0.3]**: log é o CPT `tainacan-log`, registrado com `register_post_type` (`classes/repositories/class-tainacan-logs.php:144-180` [1.0.3]), portanto um post (com postmeta) por registro de log.
- Logs gerados pelos ganchos `tainacan-insert`, `tainacan-pre-insert`, `tainacan-deleted`, `tainacan-pre-delete` e de anexos (`classes/repositories/class-tainacan-logs.php:36-48`). O diff passa por `tainacan-entity-diff` (`:744`, `:771`).
- Todo `insert()` de plugin gera log. Desligar com `$repo->disable_logs()`, religar com `enable_logs()` (`classes/repositories/class-tainacan-repository.php:67-89`).

<a id="a9"></a>
### A.9 Capabilities e permissões

- Capabilities de item por coleção (`classes/entities/class-tainacan-collection.php:186-202`):
  - `edit_post`, `read_post`, `delete_post`: `tnc_col_{id}_edit_item`, `tnc_col_{id}_read_item`, `tnc_col_{id}_delete_item` (singular).
  - `publish_posts`: `tnc_col_{id}_publish_items`.
  - `read_private_posts`: `tnc_col_{id}_read_private_items`.
  - `create_posts` e `edit_posts`: `tnc_col_{id}_edit_items`.
  - `edit_private_posts`, `edit_published_posts`, `edit_others_posts`: `tnc_col_{id}_edit_private_items`, `_edit_published_items`, `_edit_others_items`.
  - `delete_posts`, `delete_private_posts`, `delete_published_posts`, `delete_others_posts`: `tnc_col_{id}_delete_items`, `_delete_private_items`, `_delete_published_items`, `_delete_others_items`.
- Catálogo em `Roles::populate_tainacan_capabilities()` (`classes/class-tainacan-roles.php:92-512`), executado em `init` (`:73`):
  - Repositório: `manage_tainacan`, `tnc_rep_edit_users`, `tnc_rep_edit_collections`, `tnc_rep_delete_collections`, `tnc_rep_edit_taxonomies`, `tnc_rep_edit_others_taxonomies`, `tnc_rep_delete_taxonomies`, `tnc_rep_delete_others_taxonomies`, `tnc_rep_edit_metadata`, `tnc_rep_edit_filters`, `tnc_rep_delete_metadata`, `tnc_rep_delete_filters`, `tnc_rep_read_private_collections`, `tnc_rep_read_private_taxonomies`, `tnc_rep_read_private_metadata`, `tnc_rep_read_private_filters`, `tnc_rep_read_logs` (`:95-228`).
  - Por coleção (`%d` = id ou `all`): `manage_tainacan_collection_%d`, `tnc_col_%d_edit_users`, `_bulk_edit`, `_edit_metadata`, `_edit_metasection`, `_edit_filters`, `_delete_metadata`, `_delete_metasection`, `_delete_filters`, `_read_private_metadata`, `_read_private_metasection`, `_read_private_filters`, `_read_private_items`, `_edit_items`, `_publish_items`, `_edit_others_items`, `_edit_private_items`, `_edit_published_items`, `_delete_items`, `_delete_others_items`, `_delete_private_items`, `_delete_published_items` (`:238-505`).
  - Cada uma tem `supercaps`, por exemplo `tnc_col_all_edit_items`, `manage_tainacan_collection_all`, `manage_tainacan`.
- Resolução: filtros `user_has_cap` (`user_has_cap_filter`) e `map_meta_cap` (`classes/class-tainacan-roles.php:75-76`, `:654`, `:783`). Quem tem `manage_tainacan` ganha toda cap `tnc_*` (`:686-690`); as `tnc_col_*` são resolvidas pelas supercaps (`:692-...`). Items tem `map_meta_cap` próprio (`classes/repositories/class-tainacan-items.php:37`, `:782`).
- Como checar:
  - `current_user_can("tnc_col_{$id}_edit_items")`.
  - `$collection->user_can('edit_items')`, que monta `'tnc_col_' . id . '_' . $capability` (`classes/entities/class-tainacan-collection.php:1267-1279`).
  - `$entity->can_edit()`, via `user_can($user, $entity_cap->edit_post, $id)` (`classes/repositories/class-tainacan-repository.php:754-773`).
- Papéis próprios: `tainacan-administrator`, `tainacan-editor` e outros (`classes/class-tainacan-roles.php:516-...`).

<a id="a10"></a>
### A.10 CPTs e taxonomias

- CPT de itens por coleção: `tnc_col_{id}_item`, registrado por `Collection::register_collection_item_post_type()` (`classes/entities/class-tainacan-collection.php:119-169`).
  - Argumentos: `hierarchical => true`, `public => true`, `show_in_rest => true`, `exclude_from_search => true`, `has_archive => true`, `rewrite.slug` = slug da coleção, `map_meta_cap => true`, `capabilities = get_items_capabilities()` (`:131-162`).
  - Interface nativa do WordPress só com `tainacan_enable_dev_wp_interface()` (`:138-139`; `classes/tainacan-utils.php:100`).
- `Items::register_post_type()` roda em todo `init`: busca **todas** as coleções com `nopaging` e todas as taxonomias, registra os CPTs e taxonomias e liga as taxonomias às coleções. Comentário no código: "TODO: This can be a problem in large repositories." (`classes/repositories/class-tainacan-items.php:216-252`, TODO em `:221`; `:202` [1.0.3]).
- Taxonomias registradas como `tnc_tax_{id}` em `Taxonomy::tainacan_register_taxonomy()` (`classes/entities/class-tainacan-taxonomy.php:62-100`).

<a id="a11"></a>
### A.11 Processos em segundo plano

- Tabela própria `{$wpdb->prefix}tnc_bg_process` (`classes/background-process/class-tainacan-background-process.php:62`; `migrations.php:32`).
- Classes:
  - `Background_Process_Base`, com cron de saúde: `{identifier}_cron`, `{identifier}_cron_check`, intervalo de 5 minutos, trava em transient `{identifier}_process_lock` (`classes/background-process/class-tainacan-background-process-base.php:106-123`, `:285-311`).
  - `abstract class Background_Process extends Background_Process_Base`, `$action = 'process'`, métodos `save()`, `update()`, `open()`, `close()`, `delete()`, `handle()` (`classes/background-process/class-tainacan-background-process.php:14-505`).
  - Heartbeat em `heartbeat_send` (`classes/background-process/class-tainacan-background-process-heartbeat.php:11`).
- **Processo genérico novo**:
  - Gancho `do_action('tainacan-register-generic_process')` (com underscore no fim), em `init` (`classes/background-process/generic-process/class-tainacan-generic-handler.php:22`, `:34`).
  - `Generic_Process_Handler::get_instance()->register_generic_process(['name'=>..., 'slug'=>..., 'class_name'=>...])`. Os três campos são obrigatórios; sem eles, devolve `false` (`:37-46`).
  - A classe estende `\Tainacan\GenericProcess\Generic_Process` (abstrata; `run()`, passos, transients, logs) (`classes/background-process/generic-process/class-tainacan-generic-process.php:7-315`).
  - Enfileirar: `add_to_queue($process_object)` (`class-tainacan-generic-handler.php:48-60`). Executor: `Background_Generic_Process`, `$action = 'generic_process'` (`classes/background-process/generic-process/class-tainacan-bg-generic.php:7-40`).
  - Exemplo nativo: Bulk edit (`class-tainacan-generic-handler.php:27-32`).
- **Importadores**: `do_action('tainacan-register-importers')` em `init` (`classes/background-process/importer/class-tainacan-importer-handler.php:24`, `:83`); `register_importer(['name','slug','class_name', 'manual_mapping'=>false, 'manual_collection'=>true, ...])` (`:128-146`).
- **Exportadores**: `do_action('tainacan-register-exporters', $this)` em `init` (`classes/background-process/exporter/class-tainacan-exporter-handler.php:22`, `:54`); `register_exporter([...])` (`:63-79`).

<a id="a12"></a>
### A.12 Exposers e mapeadores

- `Exposers_Handler`: `do_action('tainacan-register-exposers', $this)` em `init`; `register_exposer($class_name)` / `unregister_exposer` (`classes/exposers/class-tainacan-exposers-handler.php:31`, `:47`, `:55`, `:73`). Atua na REST por `rest_request_after_callbacks` e por `tainacan-api-get-items-alternate` (`:33-35`).
- Base `abstract class Exposer` com o abstrato `rest_request_after_callbacks($response, $handler, $request)` (`classes/exposers/class-tainacan-exposer.php:13`, `:81`).
- `Mappers_Handler`: `do_action('tainacan-register-mappers', $this)` e `register_mapper($class_name)` (`classes/mappers/class-tainacan-mappers-handler.php:46`, `:54`); usa também `tainacan-api-items-prepare-for-response` (`:36`).

<a id="a13"></a>
### A.13 Blocos Gutenberg

- `views/gutenberg-blocks/class-tainacan-gutenberg-blocks.php`:
  - Categoria por `block_categories_all` (`:66`); blocos em `init`, prioridade 11 (`:78`).
  - Cada bloco: `register_block_type($this->blocks_dir . '/' . $block_slug, ...)` a partir do `block.json` (`:213-214`).
  - Lista filtrável por `tainacan-blocks-available-blocks` (`:249-260`) e pela opção `tainacan_option_enabled_blocks` (`:128-132`).
- Blocos (pastas em `views/gutenberg-blocks/blocks/`): carousel-collections-list, carousel-items-list, carousel-terms-list, collections-list, dynamic-items-list, faceted-search, facets-list, geocoordinate-item-metadatum, item-gallery, item-metadata, item-metadata-section, item-metadata-sections, item-metadatum, item-submission-form, items-gallery, metadata-section-description, metadata-section-name, related-items-list, search-bar, terms-list. Exemplo: `"name": "tainacan/faceted-search"`, `apiVersion: 3` (`views/gutenberg-blocks/blocks/faceted-search/block.json:2-5`).
- O modelo de PR exige atualizar `deprecated.js` quando `block.json` ou `save.js` mudam (`repo:.github/pull_request_template.md`).

<a id="a14"></a>
### A.14 Front-end: página do item e template tags

- Página do item, por duas vias:
  1. Tema com `tainacan/single-items.php`: o arquivo entra na hierarquia (`classes/theme-helper/class-tainacan-theme-helper.php:627-644`; `:604` [1.0.3]).
  2. Senão, com a opção `tainacan_option_override_item_single_template` (ligada por padrão) ou sem `TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER`, um filtro em `the_content` troca o conteúdo por `get_tainacan_item_single_content()` (`:32-37`, `:167-182`).
  - `get_tainacan_item_single_content()` monta documento (`get_tainacan_item_gallery`), `$item->get_metadata_sections_as_html()` e anexos, e aplica `tainacan_single_item_content` (`:813-841`; `:808` [1.0.3]).
- Template tags (`classes/theme-helper/template-tags.php`):
  - `tainacan_get_the_metadata($args, $item_id)` (`:59`), `tainacan_the_metadata` (`:77`).
  - `tainacan_get_the_document($item_id = 0, $img_size = 'large')`, que aplica `tainacan-get-the-document` com `($html, $item)` (`:96-105`).
    - **Δ [1.0.3]**: chama `$item->get_document_as_html($item_id, $img_size)` (`:104` [1.0.3]), mas a assinatura é `get_document_as_html($img_size = 'large')` (`classes/entities/class-tainacan-item.php:887` [1.0.3]): o `$item_id` acaba usado como tamanho de imagem. A [1.4.1] corrigiu para `get_document_as_html($img_size)` (`:104`).
  - `tainacan_get_the_document_raw` (`:116`), `tainacan_get_the_item_document_url` (`:135`), `tainacan_get_the_document_type` (`:154`), `tainacan_get_the_document_mimetype` (`:174`), `tainacan_the_document` (`:394`), `tainacan_get_attachment_as_html` (`:427`), `tainacan_has_document` (`:449`).
  - `tainacan_get_collection_id` (`:464`), `tainacan_get_collection` (`:474`), `tainacan_get_adjacent_items` (`:497`).
  - `tainacan_the_faceted_search($args)` (`:1093`), `tainacan_register_view_mode($slug, $args)` (`:1231`), `tainacan_get_item($post_id = 0)` (`:1244`), `tainacan_current_view_displays` (`:1261`).
  - `tainacan_the_related_items` (`:1529`), `tainacan_the_item_gallery` (`:1629`), `tainacan_get_the_metadata_sections` / `tainacan_the_metadata_sections` (`:1683`, `:1695`).
- HTML de metadados:
  - `Item::get_metadata_as_html()` passa o resultado inteiro por `wp_kses($return, wp_kses_allowed_html('tainacan_content'))` (`classes/entities/class-tainacan-item.php:639-776`; `:618-752` [1.0.3]).
  - `get_item_metadatum_as_html()` oferece filtros `before/after`, por tipo (`--type-{tipo}`), por id (`--id-{id}`) e por índice (`classes/entities/class-tainacan-item.php:813-893`).
  - Documento e anexos passam por `wp_kses` com `tainacan_content` e pelos filtros `tainacan-item-get-document-as-html` e `tainacan-item-get-attachment-as-html` (`:949`, `:980`).
- Anexos: `Item::get_attachments($exclude = null)` lista os anexos com `post_parent` = item e, sem `$exclude`, exclui a imagem destacada e, quando `document_type == 'attachment'`, o próprio documento; o resultado passa por `tainacan-item-get-attachments` (`classes/entities/class-tainacan-item.php:90-113` [1.0.3, conferido fora da pesquisa]).
- Modos de visualização: `Theme_Helper::register_view_mode($slug, $args)`. Padrões: `type='template'`, template em `{tema}/tainacan/view-mode-{slug}.php`, componente `view-mode-{slug}` (`classes/theme-helper/class-tainacan-theme-helper.php:1111-1134`).

<a id="a15"></a>
### A.15 Busca facetada (Vue)

- Bloco `tainacan/faceted-search`; app de tema em `views/gutenberg-blocks/blocks/faceted-search/theme.js` e `theme.vue`; modos de visualização em `theme-search/components/view-mode-*.vue`.
- Mixin comum `views/gutenberg-blocks/blocks/faceted-search/theme-search/js/view-modes-mixin.js`. Os ganchos de antes e depois de cada item entram por `v-html` (por exemplo `view-mode-cards.vue:37-39`, `:118-120`).
- `:key="index"` no laço de itens (`view-mode-cards.vue:25`, `view-mode-list.vue:26`, `view-mode-masonry.vue:31`, `view-mode-mosaic.vue:28`, `view-mode-records.vue:27`): ao trocar de página o Vue reaproveita o elemento DOM pelo índice. No mapa a chave é `item.id` (`view-mode-map.vue:15`).
- **Δ** Vue: a [1.4.1] usa `vue ^3.5.44`, `vuex ^4.1.0`, `vue-router ^5.4.0`, `buefy ^3.1.0`, `bulma ^1.0.4` (`repo:package.json`). Na [1.0.3] os bundles contêm as strings `"3.5.25"` e `"3.5.27"` (`assets/js/4367.*.js` [1.0.3]): Vue 3.5.x, por inferência a partir das strings.
- Componentes e plugins Vue extras: `window.tainacan_extra_components` e `window.tainacan_extra_plugins` (`views/admin/js/admin-main.js:105-107`, `:218-220`), registrados pelos ganchos PHP `tainacan-register-vuejs-component` e `tainacan-register-vuejs-plugin` (`views/admin/classes/hooks/class-tainacan-component-hooks.php:27`, `:60`; `views/admin/classes/hooks/class-tainacan-plugin-hooks.php:24`, `:46`).

<a id="a16"></a>
### A.16 Páginas de admin (`\Tainacan\Pages`)

- `abstract class Pages` exige `abstract protected function get_page_slug(): string` (`views/class-tainacan-pages.php:10`, `:17`; também na [1.0.3]) e `abstract public function render_page_content()` (`:447`).
- Construtor `protected` que chama `$this->init()` (`:36-38`); `init()` liga `admin_menu` e `admin_head` (`:47-52`).
- `load_page()` **não é chamado sozinho**: a subclasse faz `add_action('load-' . $page_suffix, [$this, 'load_page'])` depois de `add_submenu_page()`, como as páginas nativas (`views/admin/class-tainacan-admin.php:48`, `views/settings/class-tainacan-settings.php:46` etc.). Só então CSS e JS são enfileirados (`views/class-tainacan-pages.php:63-70`).
- Subclasses nativas usam o trait `Singleton_Instance` (`views/admin/class-tainacan-admin.php:7-8`). Guia oficial: `repo:docs/creating-tainacan-admin-pages.md:113-146`.
