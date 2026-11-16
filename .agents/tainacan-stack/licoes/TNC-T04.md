# TNC-T04 · Opção de tipo de metadado lida sem o prefixo _option_

**Severidade:** aviso (sempre) · **Categoria:** tainacan · **Arquivos:** .php

## Sintoma
O plugin tenta descobrir a configuração de um metadado (a taxonomia de um metadado do tipo Taxonomy, a coleção de um Relationship, o provedor de mapa de um GeoCoordinate) e recebe vazio, sem erro nem aviso. Telas e painéis que dependem disso renderizam incompletos, por exemplo uma lista com uma linha só. Gravações nessa chave não têm efeito nenhum no Tainacan.

## Causa
As opções de tipo de metadado ficam num único postmeta serializado, `metadata_type_options`, no post do metadado (`classes/repositories/class-tainacan-metadata.php:160-167`). Depois de cada `insert`, `update_metadata_type_index()` grava cada chave também como postmeta separado, com prefixo `_option_` (`classes/repositories/class-tainacan-metadata.php:1733-1757`), e o próprio núcleo consulta esse índice, como `_option_taxonomy_id` (`classes/api/endpoints/class-tainacan-rest-items-controller.php:519`). Não existe postmeta com a chave crua, então `get_post_meta( $id, 'taxonomy_id', true )` devolve sempre `''`. O índice só é atualizado, nunca limpo, e `_option_taxonomy` pode estar vazio ou desatualizado; a fonte preferencial é `_option_taxonomy_id`, com o slug montado como `tnc_tax_{id}` (`classes/entities/class-tainacan-taxonomy.php:51`).

## O que a regra detecta
- `get_post_meta`, `update_post_meta`, `add_post_meta` e `delete_post_meta` (segundo argumento) e `get_metadata`, `metadata_exists` (terceiro argumento) com chave literal igual a uma opção de tipo nativo: `taxonomy_id`, `allow_new_terms`, `link_filtered_by_current_collection`, `link_filtered_by_collections`, `hide_hierarchy_path`, `do_not_dispaly_term_as_link` (com a grafia do núcleo), `visible_options_list`, `display_related_item_metadata`, `display_in_related_items`, `accept_draft_items`, `accept_only_items_authored_by_current_user`, `default_author`, `map_provider`, `initial_zoom`, `maximum_zoom`, `initial_latitude`, `initial_longitude`, `control_metadatum`, `control_metadatum_options`, `only_repository`, `children_order`, `link-as-button`, `force-iframe`, `iframe-min-height`, `is-image`.
- Não dispara para chaves genéricas que também existem como meta comum, como `collection_id`, `input_type` ou `search`.
- Não dispara para leitura pela entidade (`$metadatum->get_metadata_type_options()['taxonomy_id']`) nem para `_option_<chave>`.
- Não sabe se o post consultado é de fato um metadado: uma chave dessas usada como meta própria em outro tipo de post também é acusada.

## Como corrigir
1. Para ler, use a entidade ou o índice com prefixo.
2. Para gravar, altere as opções pela entidade e salve pelo repositório, que refaz o índice no `insert`.

```php
// errado
$tax_id = get_post_meta( $metadatum_id, 'taxonomy_id', true ); // sempre ''

// certo
$tax_id   = get_post_meta( $metadatum_id, '_option_taxonomy_id', true );
// ou: $tax_id = $metadatum->get_metadata_type_options()['taxonomy_id'];
$tax_slug = 'tnc_tax_' . (int) $tax_id;
```

## Quando suprimir
Quando a chave é meta própria do plugin, gravada num post que não é metadado do Tainacan: prefira trocar o nome da chave por um com prefixo do plugin. Se não der: `// tnc-check-ignore TNC-T04: meta própria do plugin em post de outro tipo, não é opção de metadado`.

## Origem
Num painel público de indicadores, a descoberta da taxonomia do metadado Autoria por `get_post_meta( $id, 'taxonomy_id' )` devolvia vazio em silêncio e o painel inteiro renderizava uma linha só. O `_option_taxonomy` também estava vazio naquela instalação, por isso a lição recomenda o `_option_taxonomy_id`.
