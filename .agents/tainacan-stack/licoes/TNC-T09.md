# TNC-T09 · Gancho ou consulta do Tainacan com uso que falha em silêncio

**Severidade:** aviso; callback de gancho de entidade com primeiro parâmetro de tipo escalar é bloqueio · **Categoria:** tainacan · **Arquivos:** .php

## Sintoma
Três situações sem erro claro na tela: uma importação ou coleta grava zero itens, com "TypeError" no log e posts órfãos pela metade; um filtro de HTML de metadado transforma o valor quando chamado à mão, mas a página do item continua mostrando o valor cru; uma busca de itens por `WP_Query` não encontra itens que existem.

## Causa
- Os ganchos de persistência entregam a entidade como primeiro argumento: `tainacan-pre-insert` (`classes/repositories/class-tainacan-repository.php:173`), `tainacan-insert` com `($obj, $diffs, $is_update)` (`:238`), `tainacan-insert-{post_type}` (`:240`), `tainacan-pre-delete*` e `tainacan-deleted*` com `($entity, $permanent)` (`:679-680`, `:695-696`). Um callback tipado como `int`, `bool` etc. lança `TypeError` no PHP 8 dentro da gravação, e o fatal derruba a operação de quem chamou. Logs também são entidades e passam pelos mesmos ganchos.
- `Item_Metadata_Entity::get_value_as_html()` usa o método do tipo quando ele existe e retorna antes de aplicar o filtro genérico `tainacan-item-metadata-get-value-as-html` (`classes/entities/class-tainacan-item-metadata-entity.php:146-156`, filtro em `:207`). Text, Textarea, Date, URL, Taxonomy, Relationship, Compound, User, Control, GeoCoordinate e Core_Description têm método próprio e aplicam só `tainacan-item-metadata-get-value-as-html--type-{tipo}`. O genérico vale apenas para Numeric, Selectbox e Core_Title.
- O CPT de itens de cada coleção (`tnc_col_{id}_item`) é registrado com `exclude_from_search => true` (`classes/entities/class-tainacan-collection.php:131-162`), e `'post_type' => 'any'` no `WP_Query` só inclui tipos com `exclude_from_search` falso (WordPress 6.9.1, `wp-includes/class-wp-query.php:2600-2601`).

## O que a regra detecta
- `add_action`/`add_filter` com gancho literal que casa com `tainacan-insert*`, `tainacan-pre-insert*`, `tainacan-delete*`, `tainacan-deleted*`, `tainacan-pre-delete*` (e `tainacan-update*`, `tainacan-pre-update*`), cujo callback do mesmo arquivo tem o primeiro parâmetro tipado como `int`, `string`, `float`, `bool`, `array`, `iterable`, `callable`, `null`, `false` ou `true` (também com `?`): bloqueio.
- `add_filter( 'tainacan-item-metadata-get-value-as-html', ... )` num arquivo sem nenhum registro de `tainacan-item-metadata-get-value-as-html--type-*`: aviso em cada registro do genérico.
- `'post_type' => 'any'` em arquivo que menciona `tnc_col_`, `Tainacan\`, `tainacan_items(` ou `tainacan-item`: aviso.
- Não dispara para callback sem tipo, com classe de entidade (`\Tainacan\Entities\Item $item`) ou com tipo união (`int|Item`).
- Basta um `--type-*` qualquer no arquivo para o genérico não ser acusado, mesmo que faltem os tipos que interessam.

## Como corrigir
1. Gancho de entidade: tire o tipo ou use a classe da entidade, e confira com `instanceof` (ignorando `Log` quando não interessa). Se o callback não precisa do argumento, registre com `accepted_args = 0`.
2. HTML de metadado: registre também as variantes `--type-<tipo>` que interessam.
3. Busca de itens: liste os post types das coleções (`$collection->get_db_identifier()`) ou use `tainacan_items()->fetch()`.

```php
// errado
add_action( 'tainacan-insert', function ( bool $forcar ) { /* ... */ } );
get_posts( [ 'post_type' => 'any', 'meta_key' => '123', 'meta_value' => $codigo ] );

// certo
add_action( 'tainacan-insert', function ( $entidade ) {
    if ( ! $entidade instanceof \Tainacan\Entities\Item ) {
        return;
    }
    // ...
} );
get_posts( [ 'post_type' => $colecao->get_db_identifier(), 'meta_key' => '123', 'meta_value' => $codigo ] );
```

## Quando suprimir
`'post_type' => 'any'` num arquivo que fala de Tainacan mas consulta outros conteúdos de propósito: `// tnc-check-ignore TNC-T09: busca páginas e posts, não itens do Tainacan`. Filtro genérico usado de propósito só para Numeric, Selectbox ou título: cite isso no motivo. Não suprima callback de entidade com tipo escalar.

## Origem
Num painel de indicadores, `add_action( 'tainacan-insert', [ Cache::class, 'bump' ] )` com `bump( bool $force )` fez uma coleta de 13.446 itens terminar com 0 criados, 950 fatais e 947 posts órfãos. Um plugin de dados interligados descobriu que o filtro genérico de HTML quase nunca rodava, e um importador não achava itens existentes com `'post_type' => 'any'`.
