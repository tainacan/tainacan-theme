# TNC-S06 · Construção perigosa (eval, unserialize, extract, include dinâmico)

**Severidade:** bloqueio; `unserialize()` e `extract()` sobre valor que não vem da requisição são aviso · **Categoria:** seguranca · **Arquivos:** .php

## Sintoma
Um parâmetro da URL ou um cookie passa a controlar qual código roda: inclusão de arquivo arbitrário, execução de PHP, injeção de objeto na desserialização, script refletido na página. Em casos mais brandos, variáveis do escopo são sobrescritas em silêncio e a tela mostra "arquivo indisponível" para um arquivo que existe.

## Causa
`eval()`, `create_function()` (removida no PHP 8), `assert()` com string e `preg_replace()` com modificador `/e` executam texto como código. `unserialize()` sem `allowed_classes` instancia qualquer classe e dispara seus métodos mágicos. `extract()` cria variáveis a partir das chaves de um array e pode sobrescrever as existentes. `include`/`require` com caminho vindo da requisição permite incluir qualquer arquivo. `echo` de entrada HTTP sem escape é XSS refletido.

## O que a regra detecta
- `eval` (bloqueio), exceto em arquivos sob `test/`, `tests/` ou `spec/`.
- `include`, `include_once`, `require`, `require_once` cuja expressão contém `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_FILES` ou `$_SERVER` (bloqueio).
- `echo`, `print` ou `<?=` de uma dessas superglobais sem `esc_*`, `wp_kses*`, `absint`, `intval`, `sanitize_*`, `number_format`, `json_encode`/`wp_json_encode`, `isset`/`empty` ou conversão `(int)`/`(float)` em volta (bloqueio).
- `create_function()` (bloqueio), `assert()` com string literal (bloqueio), `preg_replace()` com padrão literal que tem modificador `e` (bloqueio).
- `unserialize()` sem `allowed_classes` no segundo argumento: bloqueio se o argumento contém superglobal, aviso nos demais casos, inclusive com valor literal ou vindo do banco.
- `extract()`: bloqueio sobre superglobal, aviso nos demais casos.
- Não dispara em `maybe_unserialize()`, em `unserialize( $x, [ 'allowed_classes' => false ] )` nem em `include` com caminho fixo (`__DIR__ . '/views/tela.php'`).

## Como corrigir
1. Reescreva sem `eval`/`create_function`; use closures.
2. Para incluir telas escolhidas pela URL, mapeie a entrada para uma lista fixa.
3. Para dados estruturados, use `json_decode()`; para dado do próprio banco, `maybe_unserialize()`; em último caso, `unserialize( $x, [ 'allowed_classes' => false ] )`.
4. Em templates, passe as variáveis explicitamente em vez de `extract()`.
5. Escape toda saída conforme o contexto.

```php
// errado
include $_GET['pagina'] . '.php';
extract( $dados );
echo $_GET['busca'];

// certo
$telas  = [ 'lista' => 'lista.php', 'ficha' => 'ficha.php' ];
$pagina = isset( $_GET['pagina'] ) ? sanitize_key( $_GET['pagina'] ) : 'lista';
include __DIR__ . '/views/' . ( isset( $telas[ $pagina ] ) ? $telas[ $pagina ] : 'lista.php' );
$titulo = $dados['titulo'];
echo esc_html( wp_unslash( $_GET['busca'] ) );
```

## Quando suprimir
`unserialize()` sobre dado que só o próprio plugin grava e que precisa reconstruir objetos de classes conhecidas: prefira passar `allowed_classes` com a lista dessas classes, que dispensa supressão. `extract()` em código de terceiros copiado para o plugin, até a refatoração: `// tnc-check-ignore TNC-S06: biblioteca empacotada, extract sobre array interno`. Não suprima `eval`, `include` dinâmico ou `echo` de entrada HTTP.

## Origem
Um plugin de certificados teve um `extract( $data, EXTR_SKIP )` em template que descartava a chave `file` porque o método já tinha um parâmetro `$file`, e a tela dizia que o arquivo de importação não existia. As demais construções são boa prática geral de segurança em PHP e no WordPress.
