---
name: tnc-estender-tainacan
description: Como estender o núcleo do Tainacan a partir de um plugin, com as assinaturas confirmadas no código e as armadilhas de cada ponto. Use para criar tipo de metadado ou de filtro, gancho de formulário no admin, conteúdo antes/depois do item na busca facetada, alterar a página do item, decorar valores de metadado, criar importador, exportador, exposer, mapeador, processo em segundo plano, página de admin ou modo de visualização.
---

# tnc-estender-tainacan

Referência completa com arquivo:linha: `.agents/tainacan-stack/referencias/nucleo-tainacan.md` e `referencias/ganchos-e-extensao.md`. Confira sempre no código do núcleo da versão que o projeto suporta; as linhas abaixo são da 1.4.1 (`src/` do repositório tainacan/tainacan).

## Antes de começar

- Declare `Requires Plugins: tainacan` no cabeçalho do plugin (WordPress 6.5+).
- Ordem de carga: `tainacan-register-metadata-type`, `tainacan-register-filter-type` e `tainacan-register-vuejs-plugin` disparam **enquanto o arquivo do Tainacan é incluído**. Registre o `add_action` no nível de cima do arquivo do plugin, não dentro de `plugins_loaded`. (Plugins cuja pasta vem antes de `tainacan/` na ordem alfabética carregam antes.)
- Acesso a repositórios: `tainacan_items()`, `tainacan_metadata()`, `tainacan_collections()` etc., ou `\Tainacan\Repositories\X::get_instance()`. Nunca `new`.

## Pontos de extensão

**Formulário do admin** (`views/admin/classes/hooks/class-tainacan-admin-hooks.php:12-59`)
```php
add_action( 'tainacan-register-admin-hooks', function () {
    tainacan_register_admin_hook( 'item', function () {   // contextos: collection, metadatum, item, taxonomy, term, filter, role, metadataSection
        ob_start();
        include __DIR__ . '/views/campo.php';               // sem <script>: o HTML entra por v-html
        return (string) ob_get_clean();                     // DEVE devolver string
    }, 'end-left' );                                        // begin-left, begin-right, end-left, end-right
} );
add_action( 'tainacan-insert-tainacan-item', function ( $item ) { /* gravar o campo enviado */ } );
```
O JS do campo vai em arquivo enfileirado no `admin_enqueue_scripts` e escuta `tainacan-<entidade>-hook-reload` no `document`. Regras: TNC-T01, T02, T03.

**Busca facetada, antes/depois de cada item** (JS, `wp.hooks`; `view-modes-mixin.js:56-80`)
```js
const selo = ( html, item ) => html + '<span class="meu-plugin-selo"></span>';
wp.hooks.addFilter( 'tainacan_faceted_search_collection_item_after', 'meu-plugin', selo ); // liga o v-if
wp.hooks.addFilter( 'tainacan_faceted_search_item_after', 'meu-plugin', selo );            // gera o HTML
```
O laço de itens usa `:key="index"`: o elemento é reaproveitado entre páginas; não guarde estado no DOM. Regra: TNC-T06.

**Valor de metadado na página do item** (`classes/entities/class-tainacan-item-metadata-entity.php:146-207`)
- Tipos com HTML próprio aplicam `tainacan-item-metadata-get-value-as-html--type-<tipo>` (compound, control, description, date, geocoordinate, relationship, taxonomy, text, textarea, url, user).
- O filtro genérico `tainacan-item-metadata-get-value-as-html` só roda para numeric, selectbox e core-title. Regra: TNC-T09.
- Âncoras de termo saem com aspas simples dentro do filtro e duplas na página final (o `wp_kses` reescreve): casar com regex que aceite as duas.
- Antes/depois de cada metadado: `tainacan-get-item-metadatum-as-html-{before|after}[--type-X|--id-N|--index-N]`.

**Documento do item**: `tainacan-get-the-document` com `($html, $item)`. Na 1.0.3 `tainacan_get_the_document()` passa o id como tamanho de imagem; compare com a 1.4.1 antes de depender dele.

**Tipo de metadado**: estenda `\Tainacan\Metadata_Types\Metadata_Type`; registre em `add_action( 'tainacan-register-metadata-type', fn( $helper ) => $helper->register_metadata_type( $handle, $classe, $script ) )`. Opções ficam em `metadata_type_options` e no índice `_option_<chave>` (TNC-T04). O índice nunca é limpo: chave removida deixa `_option_` antigo.

**Tipo de filtro**: estenda `Filter_Type`; `tainacan-register-filter-type` + `register_filter_type()`. Opções em `filter_type_options` (sem índice `_option_`).

**Página de admin**: subclasse de `\Tainacan\Pages` com o trait `Singleton_Instance`, `get_page_slug(): string`, `render_page_content()`, e `add_action( 'load-' . $page_suffix, [ $this, 'load_page' ] )` depois do `add_submenu_page()`. Regra: TNC-T08. Tela completa, campo de formulário com persistência, drawer ao lado do item e escrita de metadado pela REST com recarga do Vue: skill `tnc-ui-admin` (`referencias/ui-admin-e-drawer.md`).

**Processo em segundo plano**: `do_action( 'tainacan-register-generic_process' )` (underscore no fim) e `Generic_Process_Handler::get_instance()->register_generic_process( [ 'name', 'slug', 'class_name' ] )`; a classe estende `\Tainacan\GenericProcess\Generic_Process`. A fila é global (`tnc_bg_process`): nunca apague a fila inteira.

**Importador / exportador / exposer / mapeador**: `tainacan-register-importers`, `-exporters`, `-exposers`, `-mappers`, com `register_importer( [...] )`, `register_exporter( [...] )`, `register_exposer( $classe )`, `register_mapper( $classe )`. O HTML/XML de exposer passa por `wp_kses` do núcleo: teste a saída real.

**Gravação de entidades**: ganchos `tainacan-pre-insert`, `tainacan-insert` (`$obj, $diffs, $is_update`), `tainacan-insert-<post_type>`, `tainacan-deleted`. Callbacks sem tipo escalar no primeiro parâmetro (TNC-T09). Todo `insert()` gera log; em massa, `$repo->disable_logs()` e reative depois.

## Verificação

Teste no admin real (formulário, busca facetada, página do item) com o tema em uso e com outros plugins ativos. Rode `tnc-validacao`.
