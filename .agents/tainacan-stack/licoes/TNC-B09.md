# TNC-B09 — Callback para método que a classe não tem

**Sintoma.** Uma rota REST responde 500 em todo pedido. O log do PHP-FPM do
site não mostra nada (o erro é do PHP, não do FPM), e o teste que exercita o
callback por linha de comando passa:

```php
$req = new WP_REST_Request( 'GET', '/tema/v1/filtros/descrever' );
$req->set_param( 'url', $url );
$res = tnc_acervo_rest_describe_filters( $req );   // verde, e mentindo
```

**Causa.** O `register_rest_route` declara um callback que não existe:

```php
'args' => array(
    'url' => array(
        'validate_callback' => array( __CLASS__, 'validate_internal_url' ),
    ),
),
```

…e a classe nunca declarou `validate_internal_url()`. O PHP só falha quando o
callback é chamado, e o WordPress chama os `validate_callback` dentro de
`WP_REST_Request::has_valid_params()`, **antes** do callback da rota:

```
Uncaught TypeError: call_user_func(): Argument #1 ($callback) must be a valid
callback, class X does not have a method "validate_internal_url"
 in wp-includes/rest-api/class-wp-rest-request.php:948
```

O teste acima passa porque chamar a função do callback na mão não atravessa a
validação de `args` — pula justamente o pedaço quebrado.

**Correção.** Declarar o método, ou corrigir o nome. E, no teste, usar o
despachante em vez da função:

```php
$res = rest_do_request( $req );          // passa por permission e args
echo $res->get_status();                 // 200, 400, 403 — a verdade
```

**Regra de bolso.** Rota REST se testa por `rest_do_request()`. Chamar a função
do callback prova que a função funciona; não prova que a rota responde.

**Como a regra decide.** Lê os métodos declarados no arquivo e acusa referência
por `array( __CLASS__, 'x' )`, `[ self::class, 'x' ]` ou `static::class` a
método que não está ali. Arquivo cuja classe estende outra fica de fora, porque
o método pode vir da mãe. Bloqueia, porque é fatal garantido quando o caminho
é exercitado — e, em `validate_callback`, isso é todo pedido da rota.

**Origem.** Tema Tainacan Acervo 3.5.2. A rota ficou no ar quebrada por três
publicações: `php -l`, PHPCS e o verificador estavam verdes, e o teste de
linha de comando também.
