# TNC-C01 · Remoção de ganchos ou assets de terceiros

**Severidade:** aviso (sempre) · **Categoria:** convivencia · **Arquivos:** .php

## Sintoma
Ao ativar o plugin, outro plugin ou o tema para de funcionar sem erro aparente: um filtro de conteúdo some, o jQuery não carrega, o admin do Tainacan perde fontes ou estilos, um construtor de páginas quebra. Desativar o plugin resolve, o que dificulta achar a causa.

## Causa
`remove_all_filters()` e `remove_all_actions()` tiram todos os callbacks de um gancho, inclusive os de outros plugins, do tema e do núcleo. `wp_deregister_script()`, `wp_deregister_style()`, `wp_dequeue_script()` e `wp_dequeue_style()` em handles compartilhados (jQuery, `wp-*`, React, Vue, Tainacan) tiram um recurso de que outros componentes dependem.

## O que a regra detecta
- Toda chamada a `remove_all_filters()` ou `remove_all_actions()`, para qualquer gancho.
- `wp_deregister_script`, `wp_deregister_style`, `wp_dequeue_script` ou `wp_dequeue_style` com handle literal que case com `jquery`, `jquery-*`, `wp-*`, `react`, `react-dom`, `lodash`, `moment`, `backbone`, `underscore`, `tainacan*`, `vue`, `vuejs`, `elementor*` ou `blocksy*`.
- Não dispara para handle que começa com o Text Domain do próprio plugin (o asset é dele).
- Não dispara para handle dinâmico (variável) nem para handles fora da lista.
- Não analisa `remove_action()`/`remove_filter()` pontuais, que são a forma correta.

## Como corrigir
1. Remova só o seu callback, com o mesmo gancho, callback e prioridade usados no registro.
2. Em vez de tirar um asset compartilhado, declare dependência dele; se a troca for inevitável, faça só na tela do próprio plugin.

```php
// errado
remove_all_filters( 'the_content' );
wp_deregister_script( 'jquery' );

// certo
remove_filter( 'the_content', [ $this, 'decorar' ], 20 );
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'toplevel_page_meu-plugin' !== $hook ) {
        return;
    }
    wp_enqueue_script( 'meu-plugin-admin', plugins_url( 'admin.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
} );
```

## Quando suprimir
Página própria do plugin, isolada do resto do site (folha de impressão montada fora do tema, tela de exportação), em que tirar um asset ou limpar um gancho é a intenção e está restrito a essa tela: `// tnc-check-ignore TNC-C01: só na folha de impressão do plugin, montada fora do tema`.

## Origem
Boa prática geral de convivência entre plugins no WordPress. Não há um caso real específico registrado para esta regra; ela complementa os casos de convivência vistos na revisão conjunta de plugins da equipe, como o de um plugin de impressão que precisava suspender filtros de outros plugins só durante a montagem da folha (ver TNC-W10).
