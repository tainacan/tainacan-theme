# TNC-T01 · Gancho de admin do Tainacan imprime em vez de devolver string

**Severidade:** bloqueio (sempre) · **Categoria:** tainacan · **Arquivos:** .php

## Sintoma
O campo ou painel que o plugin acrescenta ao formulário do Tainacan (item, coleção, metadado, taxonomia...) não aparece. Ao mesmo tempo, o HTML dele vaza no topo de páginas do admin, respostas REST e AJAX quebram com "headers already sent", e `wp_safe_redirect()` falha em silêncio (tela branca, ação executada, resultado visível só ao recarregar). Em `wp eval`, o HTML do painel sai no terminal.

## Causa
`tainacan_register_admin_hook( $context, $callback, $position = 'end-left', $conditional = null )` delega para `Admin_Hooks::register()` (`views/admin/classes/hooks/admin-hooks-functions.php:8-11`). O registro roda em `admin_init`, por meio da ação `tainacan-register-admin-hooks` (`views/admin/classes/hooks/class-tainacan-admin-hooks.php:12-18`), ou seja, cedo e antes dos cabeçalhos, inclusive em `admin-ajax.php` e `admin-post.php`. O núcleo executa o callback na hora (`call_user_func( $callback )`, `:48`) e só guarda o resultado se for string (`:50-59`). O que o callback imprimir vaza para a resposta e o gancho é descartado. Contexto ou posição fora das listas aceitas fazem a função devolver `false` (`:20-26`, `:44-46`).

## O que a regra detecta
- Contexto literal fora de `collection`, `metadatum`, `item`, `taxonomy`, `term`, `filter`, `role`, `metadataSection` (a comparação diferencia maiúsculas: `metadataSection`).
- Posição literal (terceiro argumento) fora de `begin-left`, `begin-right`, `end-left`, `end-right`.
- Callback declarado no mesmo arquivo que imprime: `echo`, `print`, `<?=`, HTML fora do PHP (`?>...<?php`), `include`/`require`, ou funções que imprimem (`printf`, `vprintf`, `readfile`, `print_r` sem `true`, `var_dump`, `wp_nonce_field`, `submit_button`, `settings_fields`, `do_settings_sections`, `wp_editor`, `the_title`, `the_content`, `esc_html_e`, `esc_attr_e`, `_e`, `_ex`, `get_template_part`, `load_template`), sem `ob_start()` no corpo.
- Callback sem nenhum `return` com valor.
- Não dispara para arrow function (`fn() => '...'`), que sempre devolve a expressão.
- Não dispara quando o callback tem `ob_start()` em qualquer ponto do corpo, nem quando o callback está em outro arquivo ou não pode ser resolvido.
- Não confere o tipo do valor devolvido: `return $html;` é aceito sem verificar se é string.
- Contextos e posições acrescentados pelos filtros `tainacan-admin-hooks-contexts` e `tainacan-admin-hooks-positions` não são conhecidos pela regra.

## Como corrigir
Capture a saída e devolva a string.

```php
// errado
public function campo() {
    include __DIR__ . '/views/campo.php';
}

// certo
public function campo() {
    ob_start();
    include __DIR__ . '/views/campo.php';
    return (string) ob_get_clean();
}
```

Registre dentro de `add_action( 'tainacan-register-admin-hooks', ... )` (ver TNC-T03).

## Quando suprimir
Contexto ou posição que o próprio plugin acrescentou pelos filtros `tainacan-admin-hooks-contexts`/`tainacan-admin-hooks-positions`: `// tnc-check-ignore TNC-T01: contexto acrescentado pelo filtro tainacan-admin-hooks-contexts`. Um callback que imprime não deve ser suprimido.

## Origem
Num plugin de dados interligados, um painel registrado com `include` seco produziu ao mesmo tempo redirecionamentos que falhavam em silêncio e respostas REST quebradas por "headers already sent", e o HTML do painel vazava na saída do `wp eval`. A correção foi `ob_start()` com `return (string) ob_get_clean()`.
