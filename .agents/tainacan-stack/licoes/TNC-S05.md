# TNC-S05 · Saída HTML sem escape no ponto de saída

**Severidade:** aviso (sempre) · **Categoria:** segurança · **Arquivos:** .php (só em projeto WordPress)

## Sintoma
O Plugin Check do WordPress.org devolve dezenas de `WordPress.Security.EscapeOutput.OutputNotEscaped` num plugin que "já escapa tudo". Um título de item com `<script>` gravado por um catalogador aparece executando no admin de outro usuário. Um valor de metadado com `"` quebra o atributo HTML e o layout da página do item.

## Causa
O WordPress não escapa nada sozinho: quem imprime é responsável por escapar no contexto certo (texto, atributo, URL, JS). O Plugin Check, o PHPCS e qualquer revisor olham **o ponto do echo**: uma variável escapada 40 linhas antes, ou em outro arquivo, ou dentro de um template incluído com `ob_start()`, não prova nada para quem lê a linha do `echo`. Tradução também é saída: `__()` pode trazer HTML de um arquivo `.po`.

`sprintf`/`printf` substituem o `%s` mas não escapam nem o formato nem os argumentos.

## O que a regra detecta
Em cada `echo`, `print`, `<?= ?>` e em cada argumento de `printf()`/`vprintf()` (e de `sprintf()` quando impresso), a expressão precisa ser, em todos os seus pedaços de concatenação, ternário ou `??`:
- literal de string sem variável dentro, número, constante, cast `(int)`/`(float)`/`(bool)`;
- chamada a função de escape (`esc_html`, `esc_attr`, `esc_url`, `esc_js`, `esc_textarea`, `esc_html__`, `esc_attr__`, `esc_html_x`, `esc_attr_x`, `wp_kses`, `wp_kses_post`, `wp_kses_data`, `tag_escape`, `sanitize_html_class`, `sanitize_key`, `wp_json_encode`) ou a função que devolve número/identificador (`absint`, `intval`, `count`, `number_format_i18n`, `md5`, `wp_create_nonce`, `get_the_ID`...) ou HTML já escapado pelo WordPress (`wp_nonce_field`, `checked`, `selected`, `disabled`, `get_avatar`, `get_the_post_thumbnail`, `wp_get_attachment_image`, `get_search_query`...);
- `implode()` de `array_map( 'esc_html', ... )` (ou outro escape).

Acusa: variável solta, propriedade, chamada de método, `__()`/`_x()`/`_n()` sem escape, `_e()`/`_ex()` (hint: `esc_html_e()`), string com aspas duplas com variável dentro, heredoc com variável, `add_query_arg()`/`admin_url()`/`plugins_url()` em `href` sem `esc_url()`, `sanitize_text_field()` como se fosse escape (sanitização é entrada; escape é saída), argumento de `printf` sem escape, formato de `printf` com `__()` em vez de `esc_html__()`.

Funções do próprio projeto cujo retorno já sai escapado (um `render_badge()` que termina em `wp_kses_post`, as *template tags* `tainacan_get_the_*()` do núcleo) podem ser declaradas no `.tnc-check.json`:

```json
{ "escaped_functions": ["tainacan_get_the_*", "meu_plugin_render_*"] }
```

A regra não roda fora de projeto WordPress (sem cabeçalho de plugin, tema ou `text_domain` configurado).

## Como corrigir
Escape na linha do `echo`, com a função do contexto:

| Contexto | Função |
|---|---|
| Texto em HTML | `esc_html()` |
| Atributo (`title`, `value`, `data-*`) | `esc_attr()` |
| URL (`href`, `src`) | `esc_url()` |
| String dentro de `<script>` ou `onclick` | `esc_js()` (ou `wp_json_encode()` para dados) |
| HTML intencional (descrição com links) | `wp_kses_post()` ou `wp_kses( $html, $allowed )` |
| Tradução | `esc_html__()`, `esc_attr__()`, `esc_html_e()` |
| Exceção | `esc_html( $e->getMessage() )` |

```php
// errado: confiou em escape feito antes
$label = esc_js( __( 'Salvar', 'meu-plugin' ) );
include 'template.php';          // <button><?php echo $label; ?></button>

// certo: escape no ponto de saída
$label = __( 'Salvar', 'meu-plugin' );
include 'template.php';          // <button><?php echo esc_html( $label ); ?></button>

// printf: formato E argumentos
printf( esc_html__( 'Olá, %s!', 'meu-plugin' ), esc_html( $name ) );
printf( esc_html__( '%d dia(s)', 'meu-plugin' ), (int) $days );
```

Template incluído com `ob_start()`/`include`/`ob_get_clean()`: trate-o como entrada não confiável e escape tudo dentro dele, mesmo que quem incluiu tenha pré-escapado.

Quando a saída é um documento inteiro (CSV, PDF, XML, JSON de uma rota própria), o escape HTML não se aplica: a saída deve ir por `WP_REST_Response`, `wp_send_json()` ou por um `header( 'Content-Type' )` certo com `exit`, e a linha leva a supressão explicando o formato.

## Quando suprimir
`// tnc-check-ignore TNC-S05: saída de CSV com Content-Type text/csv; cada célula passa por fputcsv()`. Ou: `// tnc-check-ignore TNC-S05: HTML montado em render_cards(), que termina em wp_kses_post()` (melhor ainda: declarar a função em `escaped_functions`). "Já está escapado antes" não vale: mova o escape para aqui.

## Origem
Um plugin de biblioteca chegou a 1.707 achados no Plugin Check, a maioria de escape, depois de meses de desenvolvimento com "escape upstream". A limpeza levou duas versões. A regra reproduz o critério do Plugin Check no momento da edição, quando corrigir custa segundos.
