# TNC-T02 · <script> no HTML de gancho de admin do Tainacan (não executa)

**Severidade:** bloqueio (sempre) · **Categoria:** tainacan · **Arquivos:** .php

## Sintoma
O painel injetado no formulário do Tainacan aparece, mas fica parado: botões não respondem, o conteúdo não carrega, um elemento que o script deveria mostrar continua `hidden`. Não há erro no console nem no log, porque o script nunca rodou.

## Causa
O HTML devolvido pelo callback de `tainacan_register_admin_hook()` chega ao JavaScript em `tainacan_plugin['form_hooks']` (`views/class-tainacan-pages.php:384`) e é inserido no formulário com `v-html`: o mixin `formHooks` junta os HTMLs por posição (`views/admin/js/mixins.js:85-96`) e o formulário os renderiza em `<form ... class="form-hook-region" v-html="...">` (por exemplo `views/admin/components/edition/item-edition-form.vue:648-652`). `v-html` usa `innerHTML`, e o navegador não executa `<script>` inserido assim.

## O que a regra detecta
- `<script` (sem diferenciar maiúsculas) em string ou HTML solto dentro do callback de `tainacan_register_admin_hook()` declarado no mesmo arquivo.
- `<script` dentro de um template incluído pelo callback com caminho resolvível: `__DIR__ . '/x.php'`, `dirname( __FILE__ ) . '/x.php'` ou `plugin_dir_path( __FILE__ ) . 'x.php'`. O achado aponta a linha do `include` e cita a linha do template.
- Só procura `<script`. Não dispara para `<style>` nem para atributos `on*` (`onclick`, `onload`), que também falham quando chamam funções definidas num script que não rodou.
- Não dispara quando o template é incluído por caminho montado com variável ou constante própria, nem quando o callback está em outro arquivo.

## Como corrigir
1. Deixe no template só a marcação.
2. Mova o JavaScript para um arquivo enfileirado em `admin_enqueue_scripts`, passando URL da REST e nonce por `wp_localize_script()` ou `wp_add_inline_script()`.
3. Como o formulário é uma aplicação Vue que redesenha a região, escute o evento `tainacan-{entidade}-hook-reload` disparado no `document` a cada atualização (`views/admin/js/mixins.js:98-106`), ou observe a região com `MutationObserver`.

```php
// errado
function meu_campo() {
    ob_start(); ?>
    <div class="meu-painel" hidden></div>
    <script>document.querySelector('.meu-painel').hidden = false;</script>
    <?php return ob_get_clean();
}

// certo
function meu_campo() {
    return '<div class="meu-painel" hidden></div>';
}
add_action( 'admin_enqueue_scripts', function () {
    wp_enqueue_script( 'meu-painel', plugins_url( 'painel.js', __FILE__ ), [], '1.0.0', true );
} );
// painel.js (o evento segue o padrão tainacan-<entidade>-hook-reload)
document.addEventListener( 'tainacan-item-hook-reload', () => {
    const p = document.querySelector( '.meu-painel' );
    if ( p ) { p.hidden = false; }
} );
```

## Quando suprimir
Não deve ser suprimida: o `<script>` não executa nesse contexto. Se o template é usado também fora do gancho de admin, onde o script roda, separe a marcação usada no gancho da usada em outro lugar.

## Origem
Num plugin de dados interligados, o painel "Vínculos externos" ficou invisível no formulário do item, sem erro, porque o script inline que o mostrava nunca rodou. A correção foi levar o comportamento e o estilo para arquivos enfileirados.
