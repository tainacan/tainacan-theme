# TNC-W12 · wp_localize_script() com booleano ou número (chegam como string)

**Severidade:** aviso (sempre) · **Categoria:** wordpress · **Arquivos:** .php

## Sintoma
Um recurso que o PHP desligou (`'ativo' => false`) aparece ligado no navegador; um limite numérico comparado com `===` nunca bate; `if ( dados.ativo )` é sempre verdadeiro. O console não mostra erro nenhum.

## Causa
`wp_localize_script()` foi feita para traduzir textos e converte cada valor escalar do array em string antes de gerar o `var X = {...}` (`wp-includes/class-wp-scripts.php`, método `localize`): `true` vira `"1"`, `false` vira `""`, `24` vira `"24"`. No JavaScript, `"1"` e `"24"` são verdadeiros, `"" !== false` e `"24" !== 24`.

## O que a regra detecta
- Chamadas `wp_localize_script( $handle, 'Objeto', [ ... ] )` cujo terceiro argumento é um array literal.
- Em cada entrada, valor literal `true`, `false`, `null`, inteiro ou decimal, ou expressão iniciada por `(bool)`, `(int)` ou `(float)`: um achado por entrada, na linha do valor.
- Valores em variável, chamadas de função e arrays aninhados não são avaliados.
- Array montado antes em variável fica fora da regra.

## Como corrigir
Para dados com tipos, use `wp_add_inline_script()` com `wp_json_encode()`, que preserva booleanos e números; ou envie strings deliberadas (`'1'`/`'0'`) e trate no JS.

```php
// errado: chega como { perPage: "24", ativo: "" }
wp_localize_script( 'meu-plugin', 'MeuPluginDados', [ 'perPage' => 24, 'ativo' => false ] );

// certo: chega como { perPage: 24, ativo: false }
wp_add_inline_script(
    'meu-plugin',
    'var MeuPluginDados = ' . wp_json_encode( [ 'perPage' => 24, 'ativo' => false ] ) . ';',
    'before'
);
```

## Quando suprimir
Quando o JavaScript já trata o valor como string de propósito (`dados.ativo === '1'`): `// tnc-check-ignore TNC-W12: o JS compara com '1' e '' de propósito`.

## Origem
Um plugin de visualização de acervo enviou `enabled => false` e o JavaScript recebeu `""`; a comparação `!== false` ligou um recurso que o administrador tinha desligado. É comportamento documentado do WordPress, mas pouco conhecido.
