---
name: tnc-rest-api
description: Criar rotas REST em plugin WordPress/Tainacan com permissão, validação e schema corretos, e consumir a REST do Tainacan (tainacan/v2) sem armadilhas de parâmetros, paginação e desempenho. Use para "crie um endpoint", "API para o front", "buscar itens pela REST", "metaquery/taxquery", "fetch_only", ou quando a REST devolve dado errado, lento ou 401/403.
---

# tnc-rest-api

## Rota própria

```php
add_action( 'rest_api_init', function () {
    register_rest_route( 'meu-plugin/v1', '/itens/(?P<id>\d+)', [
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => [ $this, 'salvar' ],
        'permission_callback' => [ $this, 'pode_editar' ],   // método public
        'args'                => [
            'id'    => [ 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'absint' ],
            'texto' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
        ],
    ] );
} );
```
- `permission_callback` sempre, e nunca `'__return_true'` (TNC-S04). Leitura pública: um callback que confere que o recurso existe e é público (`'publish' === get_post_status( $id )`, `$item->can_read()`, coleção pública); senão rascunho e item privado vazam. Escrita pública (webhook, formulário): justifique com `tnc-check-ignore` e proteja de outro jeito (assinatura HMAC conferida com `hash_equals`, captcha, limite por IP).
- `args` com `validate_callback` e `sanitize_callback` no registro (como acima), não dentro do handler: falha cedo, fica ao lado da declaração e vira esquema da API.
- O `permission_callback` só decide: sem gravação, contador ou cache dentro dele (TNC-W10).
- Nada de `@` no padrão da rota: derruba a REST do site inteiro (TNC-W10).
- Namespace próprio e prefixado. Não registre a mesma rota em dois lugares (procedural e controller).
- No navegador, mande o nonce `wp_rest` (`X-WP-Nonce`) para a sessão valer.
- Permissão por coleção do Tainacan: `current_user_can( "tnc_col_{$id}_edit_items" )` ou `$collection->user_can( 'edit_items' )`. Item: `$item->can_edit()`.
- Cache de resposta que depende do usuário (`can_read`, itens privados) leva o usuário ou o papel na chave, senão o anônimo herda dado privado.

## Consumir a REST do Tainacan

Base: `/wp-json/tainacan/v2/`. Listagem: `collection/<cid>/items` ou `items`.

```
?perpage=24&paged=1
&fetch_only=title,thumbnail,url&fetch_only_meta=123,456
&taxquery[0][taxonomy]=tnc_tax_9&taxquery[0][terms][]=55&taxquery[0][operator]=IN
&metaquery[0][key]=123&metaquery[0][value]=1900&metaquery[0][compare]=>=&metaquery[0][metadatumtype]=NUMERIC
```
- Sem `fetch_only`, cada item vem com todos os metadados renderizados (`value_as_html`): lento em listagem. Use sempre `fetch_only` + `fetch_only_meta`.
- `taxquery` aceita `taxonomy`, `metadatum` (no lugar de `field`), `terms`, `operator`, `relation`. **`include_children` é descartado**: o filtro sempre inclui filhos (TNC-T07).
- `metaquery` aceita `key`, `value`, `compare`, `relation`, `metadatumtype` (não `type`), e o núcleo ainda sobrescreve o tipo pelo do filtro cadastrado.
- `perpage=-1`: na 1.0.x passa sem limite (derruba o servidor em acervo grande); na 1.4.x vira o teto. Pagine sempre.
- Total: cabeçalho `X-WP-Total`. Tempo da consulta: `X-Tainacan-Query-Time`.
- Valor de `metaquery`/`taxquery` passa por `wp_kses` sem a tag `a`.
- Curto-circuito para motor externo: filtro `tainacan-api-get-items-alternate`. Campos extras: `tainacan-api-response-<entidade>-meta` (confira permissão antes de expor) e `tainacan-api-items-prepare-for-response`.

## Verificação

Teste com usuário anônimo, com assinante e com quem tem a capability: cada um deve ver só o que pode. Rode `tnc-validacao`.
