# TNC-P02 · Consulta ou requisição HTTP dentro de laço (N+1)

**Severidade:** aviso (sempre) · **Categoria:** desempenho · **Arquivos:** .php

## Sintoma
O número de consultas ao banco cresce com o número de itens da página (centenas por requisição) e o tempo de resposta acompanha. Com requisição HTTP no laço, a página fica presa esperando serviços externos e cai quando um deles está lento.

## Causa
Cada volta do laço faz uma ida ao banco ou à rede, em vez de buscar tudo de uma vez antes. Os repositórios do Tainacan não têm cache de objeto próprio (não há `wp_cache_get`/`set_transient` em `classes/repositories/`, segundo a pesquisa do núcleo 1.4.1), e cada `fetch` monta uma `WP_Query`. Uma listagem REST sem `fetch_only` já faz isso por item, gerando `value_as_html` de todos os metadados (`classes/api/endpoints/class-tainacan-rest-items-controller.php:229-257`).

## O que a regra detecta
- Dentro do corpo de `foreach`, `for`, `while` ou `do`: `get_posts`, `get_children`, `get_term_by`, `get_terms`, `wp_get_object_terms`, `get_page_by_path`, `get_page_by_title`, `url_to_postid`, `attachment_url_to_postid`, `count_user_posts`, `wp_remote_get`, `wp_remote_post`, `wp_remote_request`, `wp_remote_head`, `wp_safe_remote_get`, `wp_safe_remote_post` e `curl_exec`.
- `file_get_contents` no laço quando o argumento parece remoto (contém `http:`, `https:`, `url`, `uri` ou `endpoint`).
- `$wpdb->get_results`, `get_var`, `get_row`, `get_col` e `query` no laço (só quando o alvo é a variável `$wpdb`).
- Métodos `->fetch`, `->fetch_one`, `->fetch_by_collection`, `->fetch_ids`, `->fetch_items` e `->fetch_by_db_identifier` no laço, em qualquer objeto que não seja `$wpdb`.
- `new WP_Query`, `new WP_Term_Query` e `new WP_User_Query` no laço.
- Não dispara em laço sobre array literal (`foreach ( [ 'a', 'b' ] as $x )`).
- Não dispara em `get_post_meta`, `get_the_terms` e outras leituras que o WordPress cacheia depois de uma `WP_Query`.
- Não acompanha chamadas indiretas: uma função chamada no laço que faz a consulta não é acusada, nem consultas dentro de `array_map` com closure.
- Como `->fetch` é reconhecido pelo nome, um `fetch` de outra biblioteca (por exemplo PDO) dentro de laço também é acusado.

## Como corrigir
Busque tudo antes do laço com uma consulta só e use o resultado indexado dentro dele.

```php
// errado
foreach ( $ids as $id ) {
    $item = $items_repo->fetch( $id );
    $titulos[] = $item->get_title();
}

// certo
$itens = $items_repo->fetch( [ 'post__in' => $ids, 'posts_per_page' => count( $ids ) ], [], 'OBJECT' );
foreach ( $itens as $item ) {
    $titulos[] = $item->get_title();
}
// para metas e termos de muitos posts: update_meta_cache( 'post', $ids ); update_object_term_cache( $ids, $tipo );
```

## Quando suprimir
Laço curto e limitado por construção (poucas coleções configuradas, um lote fixo pequeno) ou rotina de fundo em que a consulta por volta é intencional: `// tnc-check-ignore TNC-P02: laço sobre no máximo cinco coleções configuradas`.

## Origem
Um plugin de pontos de memória fazia centenas de consultas individuais de meta e termos ao devolver um conjunto completo, e um plugin de arquivo pagava uma consulta por item ao montar a navegação da coleção. A regra também segue a boa prática geral de evitar o padrão N+1.
