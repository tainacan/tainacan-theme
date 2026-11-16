# TNC-S04 · Rota REST sem permissão adequada

**Severidade:** bloqueio; leitura com `__return_true` é aviso · **Categoria:** seguranca · **Arquivos:** .php

## Sintoma
Uma rota de escrita do plugin aceita pedidos de qualquer visitante, sem login. Ou a rota passa a responder 500 ou "rest_forbidden" para todo mundo porque o `permission_callback` não pode ser chamado. Sem `permission_callback`, o WordPress mostra o aviso "The REST API route definition for ... is missing the required permission_callback argument".

## Causa
Desde o WordPress 5.5, `register_rest_route()` exige `permission_callback` e emite `_doing_it_wrong` quando ele falta (WordPress 6.9.1, `wp-includes/rest-api.php:122-135`). O WordPress chama o callback de fora da classe com `call_user_func()` (por exemplo em `wp-includes/rest-api.php:879`), então um método `protected` ou `private` não pode ser usado. `__return_true` é aceitável para leitura pública, mas abre rota de escrita a qualquer pessoa.

## O que a regra detecta
- `register_rest_route()` cujo terceiro argumento é um array literal, com um endpoint ou com uma lista de endpoints.
- Endpoint sem a chave `permission_callback`, de leitura ou de escrita.
- Endpoint de escrita (`methods` com algo além de `GET`, `HEAD`, `OPTIONS` ou `WP_REST_Server::READABLE`) com `permission_callback` igual a `'__return_true'`, `function () { return true; }` ou `fn() => true`.
- `permission_callback` que aponta para método `protected` ou `private` declarado no mesmo arquivo.
- Não dispara quando o array de argumentos está numa variável (fora do alcance da análise estática).
- Leitura com `__return_true` (ou closure que só devolve `true`) é **aviso**, não bloqueio: mesmo em leitura, o callback deve conferir que o recurso existe e é público (`'publish' === $post->post_status`, `$item->can_read()`, coleção pública); senão rascunho ou item privado vaza pela REST. Endpoint sem `methods` é tratado como leitura (o padrão do WordPress é `GET`).
- Não avalia a lógica de callbacks mais complexos: uma closure que sempre devolve `true` por outro caminho não é acusada.

## Como corrigir
1. Declare `permission_callback` em toda rota.
2. Em rota de escrita, verifique capability; para uso no navegador, envie o nonce `wp_rest`.
3. Em leitura de conteúdo do Tainacan, decida pela entidade (por exemplo `$item->can_read()`) em vez de `__return_true`, para não vazar rascunho ou item privado.
4. Deixe o método do `permission_callback` `public`.

```php
// errado
register_rest_route( 'meu-plugin/v1', '/salvar', [
    'methods'             => 'POST',
    'callback'            => [ $this, 'salvar' ],
    'permission_callback' => '__return_true',
] );

// certo
register_rest_route( 'meu-plugin/v1', '/salvar', [
    'methods'             => WP_REST_Server::CREATABLE,
    'callback'            => [ $this, 'salvar' ],
    'permission_callback' => [ $this, 'pode_salvar' ],
] );

public function pode_salvar() {
    return current_user_can( 'edit_posts' );
}
```

## Quando suprimir
Rota de escrita pública por desenho, com outra defesa no callback (captcha, honeypot, limite de taxa, token próprio): `// tnc-check-ignore TNC-S04: inscrição pública protegida por honeypot e limite por IP no callback`. Nunca suprima um `permission_callback` privado ou protegido: torne-o público.

## Origem
Um plugin de OCR registrou rotas que falhavam porque o `permission_callback` era `protected`, e um plugin de biblioteca precisou trocar `__return_true` por `can_read()` do item para não expor rascunhos. A exigência de `permission_callback` é boa prática geral do WordPress.
