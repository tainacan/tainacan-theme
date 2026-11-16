# TNC-S02 · Entrada HTTP usada sem sanitização

**Severidade:** aviso (sempre) · **Categoria:** seguranca · **Arquivos:** .php

## Sintoma
Dados enviados pelo visitante chegam crus ao banco, a opções, a e-mails ou à tela: barras invertidas extras (`O\'Brien`), HTML ou scripts gravados, valores fora do tipo esperado. Em geral não há erro visível; o problema aparece como XSS, dado sujo ou achado de auditoria.

## Causa
`$_GET`, `$_POST`, `$_REQUEST` e `$_COOKIE` trazem o que o cliente mandou, e o WordPress acrescenta barras (`wp_magic_quotes`). Cada leitura precisa de `wp_unslash()` e de uma função de sanitização adequada ao tipo. `$_REQUEST` mistura cookies com parâmetros e torna a origem do valor ambígua.

## O que a regra detecta
- Leitura de `$_GET`, `$_POST`, `$_REQUEST` ou `$_COOKIE` (com ou sem índice) que não está dentro de uma chamada de sanitização na mesma expressão.
- Para `$_REQUEST`, a mensagem recomenda trocar por `$_GET` ou `$_POST`.
- Não dispara em: escrita (`$_POST['x'] = ...`, `??=`); comparação direta (`'salvar' === $_POST['acao']`, `<`, `>`, `==` etc.); conversão `(int)`, `(float)`, `(bool)`.
- Não dispara dentro de funções de sanitização ou verificação: as `sanitize_*`, `esc_*`, `absint`, `intval`, `floatval`, `boolval`, `wp_kses*`, `filter_var`, `filter_input`, `map_deep`, `array_map`, `isset`, `empty`, `array_key_exists`, `in_array`, `is_numeric`, `ctype_digit`, `preg_match`, `wp_verify_nonce`, `check_admin_referer`, `check_ajax_referer`, `wp_safe_redirect`, `is_array`, `count`, `json_encode`, `wp_json_encode`, `hash_equals`, além de qualquer função cujo nome contenha `sanitize` ou `validate`.
- Aceita o ternário `isset( $_GET['k'] ) ? sanitize_key( $_GET['k'] ) : ''` quando a sanitização está no mesmo enunciado.
- Em projeto WordPress, sanitizador de texto (`sanitize_text_field`, `sanitize_textarea_field`, `sanitize_email`, `esc_url_raw`, `wp_kses*`, `esc_*`) aplicado direto ao superglobal, sem `wp_unslash()` (ou `stripslashes_deep`/`map_deep`) na mesma expressão, gera aviso próprio: as barras do `wp_magic_quotes` ficam no valor (`O\'Brien`). O Plugin Check exige (`ValidatedSanitizedInput.MissingUnslash`). `absint`, `intval`, `sanitize_key`, `is_numeric`, `wp_parse_id_list`, `filter_var` e as verificações de nonce dispensam o unslash; `in_array( $_GET['aba'], [...] )` também.
- `array_map` conta como sanitização com qualquer callback; `array_map( 'trim', $_POST['x'] )` passa sem achado.

## Como corrigir
Leia cada valor com `wp_unslash()` e a sanitização do tipo esperado, uma vez, no início do handler.

```php
// errado
update_option( 'meu_plugin_titulo', $_POST['titulo'] );
$tipo = $_REQUEST['tipo'];

// certo
$titulo = isset( $_POST['titulo'] ) ? sanitize_text_field( wp_unslash( $_POST['titulo'] ) ) : '';
update_option( 'meu_plugin_titulo', $titulo );
$tipo = isset( $_GET['tipo'] ) ? sanitize_key( $_GET['tipo'] ) : '';
```

## Quando suprimir
Quando o valor não pode passar por sanitização de texto sem ser alterado, como uma senha que será cifrada: `// tnc-check-ignore TNC-S02: senha cifrada sem sanitize_text_field, que altera caracteres`. Também quando a validação acontece numa função própria que a regra não reconhece pelo nome; nesse caso, prefira renomeá-la para conter `sanitize` ou `validate`.

## Origem
Versão leve da regra `WordPress.Security.ValidatedSanitizedInput` do WPCS, para quem não roda o PHPCS. Boa prática geral do WordPress. Um plugin de processos registrou o caso inverso, que motiva a supressão acima: `sanitize_text_field` alterava a senha SMTP gravada.
