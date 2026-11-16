# TNC-B04 · Sobra de depuração

**Severidade:** aviso; `debugger` em JavaScript é bloqueio · **Categoria:** base · **Arquivos:** .php, .inc, .js, .jsx, .ts, .tsx, .vue

## Sintoma
Aparece texto de depuração no meio da página, do JSON de uma rota REST ou da resposta AJAX, que passa a ser rejeitada pelo JavaScript ("Unexpected token"). No navegador, o console enche de mensagens, ou a página para no depurador para quem está com as ferramentas de desenvolvedor abertas.

## Causa
`var_dump()`, `print_r()` sem o segundo argumento e `var_export()` sem o segundo argumento imprimem direto na saída. Em PHP, isso corrompe respostas JSON e pode causar "headers already sent". Em JavaScript, `debugger` interrompe a execução quando as ferramentas estão abertas, e `console.log` expõe dados no console de qualquer visitante.

## O que a regra detecta
- PHP: chamadas a `var_dump`, `debug_print_backtrace`, `debug_zval_dump`, `dd` e `dump` (aviso).
- PHP: `print_r` e `var_export` com um só argumento (aviso), inclusive dentro de `error_log( ... )`: sem o segundo argumento eles imprimem na resposta, e o log recebe só `true` ou `null`.
- PHP, em projeto WordPress: `error_log()` sem referência a `WP_DEBUG` (ou a constante/variável de depuração do plugin, como `MEU_PLUGIN_DEBUG`, `$this->debug`) entre o início da função e a chamada (aviso). Em produção o log cresce sem controle e pode guardar dado pessoal; o Plugin Check acusa (`WordPress.PHP.DevelopmentFunctions.error_log_error_log`). Envolva em `if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { ... }` e, para o PHPCS, acrescente `// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Guarded by WP_DEBUG; surfaces only in dev.`
- JavaScript/Vue: `debugger` (bloqueio) e `console.log`, `console.debug`, `console.dir`, `console.table` (aviso).
- Não dispara em `print_r( $x, true )` ou `var_export( $x, true )`, que devolvem string.
- Não dispara em `console.error` e `console.warn`, nem em código comentado com `//` ou `/* */` na mesma linha. Os comentários são retirados por linha, então `//` dentro de uma string JavaScript pode esconder um achado.

## Como corrigir
1. Remova a chamada, ou troque por `error_log()` condicionado a `WP_DEBUG`.
2. Dentro de `error_log()`, use sempre o segundo argumento `true`. `error_log( print_r( $x ) )` imprime `$x` na resposta e grava `1` no log; a regra acusa esse caso.
3. Em JavaScript, mantenha só `console.error`/`console.warn` para falhas reais.

```php
// errado
var_dump( $item );
error_log( print_r( $item ) );

// certo
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( print_r( $item, true ) );
}
```

## Quando suprimir
Ferramenta de linha de comando ou de diagnóstico que deve imprimir de propósito: `// tnc-check-ignore TNC-B04: saída do comando WP-CLI de diagnóstico`. Pastas inteiras de ferramentas que não vão no pacote podem ser excluídas com `"exclude"` no `.tnc-check.json`.

## Origem
Boa prática geral do WordPress (o Plugin Check do WordPress.org também cobra `error_log` só sob `WP_DEBUG`). Não há um caso real específico registrado para esta regra.
