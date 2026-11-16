# TNC-P01 · Consulta sem limite de resultados

**Severidade:** aviso (sempre) · **Categoria:** desempenho · **Arquivos:** .php

## Sintoma
A página ou a rota fica lenta conforme o acervo cresce e, em coleções grandes (dezenas de milhares de itens), termina em "Allowed memory size exhausted" ou em tempo esgotado. O problema não aparece no ambiente de desenvolvimento, que tem poucos itens.

## Causa
`posts_per_page => -1`, `numberposts => -1` e `nopaging => true` carregam todos os registros de uma vez, com objetos completos e caches de meta e termos. No Tainacan, cada item é um post do CPT `tnc_col_{id}_item`, com valores de metadados em `wp_postmeta` (`classes/repositories/class-tainacan-item-metadata.php:62-109`). A REST do Tainacan 1.4.1 já limita `perpage < 1` ao teto seguro (`classes/api/class-tainacan-rest-controller.php:173-177`, `:196-202`), mas a 1.0.x deixava `perpage=-1` passar sem limite; consultas diretas por `WP_Query`, `get_posts()` ou pelos repositórios não têm essa proteção.

## O que a regra detecta
- Par de array ou atribuição com chave literal `posts_per_page`, `numberposts`, `perpage`, `per_page`, `posts_per_archive_page` ou `number` e valor `-1` ou `'-1'` (`[ 'posts_per_page' => -1 ]` e `$args['posts_per_page'] = -1`).
- `'nopaging' => true` (também em atribuição).
- `number => -1` só conta perto de `get_terms`, `get_users`, `get_comments`, `wp_get_object_terms`, `WP_Term_Query` ou `WP_User_Query`.
- Não dispara quando o `-1` vem de variável ou constante, nem em JavaScript (por exemplo `perpage=-1` numa URL montada no navegador).
- Não leva em conta `'fields' => 'ids'`: uma lista de IDs sem limite também é acusada.

## Como corrigir
1. Pagine em lotes (100 a 500) e processe lote a lote.
2. Se só precisa dos IDs, use `'fields' => 'ids'` e `'no_found_rows' => true`; se só precisa do total, use `found_posts` ou `COUNT`.
3. Em listagens pela REST do Tainacan, use `perpage` explícito e `fetch_only`.

```php
// errado
$itens = get_posts( [ 'post_type' => $tipo_da_colecao, 'posts_per_page' => -1 ] );

// certo
$pagina = 1;
do {
    $ids = get_posts( [
        'post_type'      => $tipo_da_colecao,
        'posts_per_page' => 200,
        'paged'          => $pagina++,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ] );
    foreach ( $ids as $id ) {
        // processa
    }
} while ( count( $ids ) === 200 );
```

## Quando suprimir
Consulta que comprovadamente devolve poucos registros (por exemplo, todas as coleções de um repositório pequeno, ou as opções de configuração do próprio plugin). Também consultas de lista completa de IDs que precisam ir ao SQL: o Tainacan Index Manager devolve ao SQL as consultas com `posts_per_page = -1`, e um plugin que conta com isso pode usar o `-1` de propósito. Formato: `// tnc-check-ignore TNC-P01: lista completa de IDs, mantida no SQL pelo Index Manager`.

## Origem
Num plugin de arquivo, o carrossel de relacionados e a navegação da coleção carregavam milhares de itens por página; ao agregar com uma consulta só, o tempo caiu de 2,65 s para 0,28 s. O núcleo do Tainacan também registra essa armadilha: `Items::register_post_type()` busca todas as coleções com `nopaging` a cada `init`, com um comentário "TODO: This can be a problem in large repositories." (`classes/repositories/class-tainacan-items.php:216-252`, `:221`).
