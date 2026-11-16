# TNC-T06 · Gancho da busca facetada registrado com um nome só

**Severidade:** bloqueio (sempre) · **Categoria:** tainacan · **Arquivos:** .js, .jsx, .ts, .tsx, .vue, .php

## Sintoma
O selo, contador ou bloco que o plugin acrescenta "depois de cada item" na busca facetada do Tainacan nunca aparece; ou o espaço aparece vazio. O filtro está registrado e não há erro no console.

## Causa
Os ganchos por item da busca facetada são filtros JavaScript de `wp.hooks`, e os nomes do "depois" são assimétricos no núcleo (`views/gutenberg-blocks/blocks/faceted-search/theme-search/js/view-modes-mixin.js:56-80`): `hasAfterHook()` consulta `tainacan_faceted_search_collection_item_after` (ou o nome por coleção) para decidir se desenha o lugar (`:62-66`), enquanto `getAfterHook()` aplica `tainacan_faceted_search_item_after` e o nome por coleção para gerar o HTML (`:74-78`). Com só o primeiro, o lugar é desenhado vazio; com só o segundo, o `v-if` fica falso e o lugar nunca aparece. O "antes" é simétrico e não tem o problema (`:56-60`, `:68-72`).

## O que a regra detecta
- Arquivo com `addFilter( 'tainacan_faceted_search_item_after', ... )` e sem `addFilter( 'tainacan_faceted_search_collection_item_after', ... )`, ou o contrário.
- Só nomes literais (aspas simples, duplas ou crase) logo após `addFilter(`.
- A verificação é por arquivo: registrar cada nome num arquivo diferente gera achado nos dois.
- Não dispara para o nome por coleção `tainacan_faceted_search_collection_<id>_item_after`, que é o mesmo nos dois métodos, nem para os ganchos de "antes".

## Como corrigir
Registre a mesma função nos dois nomes (o HTML é acrescentado uma vez só, porque `getAfterHook()` só aplica um deles), ou use apenas o nome por coleção.

```js
// errado
wp.hooks.addFilter( 'tainacan_faceted_search_item_after', 'meu-plugin', selo );

// certo
const selo = ( html, item ) => html + '<span class="meu-selo"></span>';
wp.hooks.addFilter( 'tainacan_faceted_search_collection_item_after', 'meu-plugin', selo ); // liga o v-if
wp.hooks.addFilter( 'tainacan_faceted_search_item_after', 'meu-plugin', selo );            // produz o HTML
```

## Quando suprimir
Quando os dois nomes são registrados em arquivos diferentes do mesmo plugin: `// tnc-check-ignore TNC-T06: o par está registrado em busca-depois.js`. Melhor ainda é juntar os dois registros no mesmo arquivo.

## Origem
Num plugin de busca segmentada, o lugar reservado por item nunca era desenhado porque só `tainacan_faceted_search_item_after` estava registrado. É um comportamento do núcleo, confirmado no Tainacan 1.4.1 e também no 1.0.3 e no 1.1.
