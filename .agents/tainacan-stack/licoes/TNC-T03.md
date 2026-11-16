# TNC-T03 · Gancho de admin registrado fora de tainacan-register-admin-hooks

**Severidade:** aviso (sempre) · **Categoria:** tainacan · **Arquivos:** .php

## Sintoma
O campo do plugin no formulário do Tainacan não aparece, ou dá erro fatal "Call to undefined function tainacan_register_admin_hook()" quando a chamada está no nível de cima do arquivo e o plugin carrega antes do Tainacan. Em requisições REST, cron e WP-CLI, o callback do gancho roda à toa, gerando HTML que ninguém usa e, se imprimir, quebrando a resposta.

## Causa
O ponto oficial de registro é a ação `tainacan-register-admin-hooks`, que o `Admin_Hooks` dispara dentro de `admin_init` (`views/admin/classes/hooks/class-tainacan-admin-hooks.php:12-18`). Chamar `tainacan_register_admin_hook()` em `init` ou no carregamento do plugin executa o callback (ele roda na hora do registro, `:48`) em toda requisição, e depende da ordem de carga dos plugins para a função existir.

## O que a regra detecta
- Arquivo que chama `tainacan_register_admin_hook()` e não tem a string literal `'tainacan-register-admin-hooks'` no código: um achado por chamada.
- A verificação é por arquivo: basta a string literal aparecer em qualquer lugar do código (em geral no `add_action`) para a regra aceitar todas as chamadas do arquivo, mesmo as que estão fora do callback dessa ação. Menção em comentário não conta.
- Acusa também quando o `add_action( 'tainacan-register-admin-hooks', ... )` está em outro arquivo e chama um método deste (falso positivo conhecido). Também acusa quando o nome da ação vem de constante ou variável.

## Como corrigir
Registre todas as chamadas dentro da ação oficial.

```php
// errado
add_action( 'init', function () {
    tainacan_register_admin_hook( 'item', 'meu_plugin_campo' );
} );

// certo
add_action( 'tainacan-register-admin-hooks', function () {
    tainacan_register_admin_hook( 'item', 'meu_plugin_campo', 'end-left' );
} );
```

## Quando suprimir
Quando o registro está dentro de um método chamado a partir de `tainacan-register-admin-hooks` em outro arquivo: `// tnc-check-ignore TNC-T03: chamado pelo add_action de tainacan-register-admin-hooks em class-plugin.php`.

## Origem
Num plugin de dados interligados, um painel registrado em `init` não aparecia e a saída impressa vazava fora de hora; a correção foi registrar no gancho oficial e devolver a string (ver TNC-T01).
