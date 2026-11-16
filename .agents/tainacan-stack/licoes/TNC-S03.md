# TNC-S03 · Handler AJAX/admin-post sem nonce ou sem capability

**Severidade:** bloqueio quando falta nonce e o handler altera dados; aviso quando falta nonce num handler que só lê; aviso quando falta verificação de capability (mesmo que o handler altere dados) · **Categoria:** seguranca · **Arquivos:** .php

## Sintoma
Uma página de outro site, aberta por um administrador logado, consegue disparar a ação do plugin sem que ele perceba (CSRF): opção alterada, item apagado, e-mail enviado. Ou um usuário com papel baixo, como assinante, chama `admin-ajax.php?action=...` e executa uma operação de gestão.

## Causa
`admin-ajax.php` e `admin-post.php` disparam `admin_init` e depois `wp_ajax_{acao}` (ou `wp_ajax_nopriv_{acao}` para visitante) para qualquer pedido com o parâmetro `action` (WordPress 6.9.1, `wp-admin/admin-ajax.php:45`, `:192`, `:207`). O WordPress não verifica nonce nem capability por conta própria: `wp_ajax_*` só garante que há alguém logado, com qualquer papel. Cada handler precisa chamar `check_ajax_referer()`/`check_admin_referer()` e `current_user_can()`.

## O que a regra detecta
- `add_action()` com gancho literal `wp_ajax_*`, `wp_ajax_nopriv_*`, `admin_post_*` ou `admin_post_nopriv_*`, ou montado como `'wp_ajax_' . $acao`, cujo callback está declarado no mesmo arquivo (função, closure ou `[ $this, 'metodo' ]`).
- Sem nonce: nenhuma chamada a `check_ajax_referer`, `check_admin_referer`, `wp_verify_nonce` ou `hash_equals` no handler. Bloqueio se o handler grava (por exemplo `update_option`, `wp_insert_post`, `update_post_meta`, `wp_delete_post`, `wp_set_object_terms`, `wp_mail`, `move_uploaded_file`, `unlink`, `file_put_contents`, ou método `->insert`, `->update`, `->delete`, `->replace`, `->query`, `->save`, `->trash`), inclusive dentro dos métodos auxiliares descritos abaixo; aviso se não grava. `set_transient` e `wp_cache_set` não contam como gravação.
- Sem capability: nenhuma chamada a `current_user_can`, `user_can` ou `is_super_admin`, só em ganchos sem `nopriv`. Sempre aviso.
- A busca inclui métodos da mesma classe chamados por `$this->`, `self::` ou `static::`, até três níveis de profundidade.
- Não dispara quando o callback está em outro arquivo ou não pode ser resolvido (nome dinâmico, objeto que não é `$this`).
- `hash_equals` conta como nonce para cobrir token próprio e `state` de OAuth/OIDC.

## Como corrigir
No início do handler: verificar nonce, verificar capability, depois ler e sanitizar a entrada.

```php
// errado
add_action( 'wp_ajax_meu_salvar', function () {
    update_option( 'meu_plugin_opcao', sanitize_text_field( wp_unslash( $_POST['v'] ) ) );
    wp_send_json_success();
} );

// certo
add_action( 'wp_ajax_meu_salvar', function () {
    check_ajax_referer( 'meu_salvar' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( null, 403 );
    }
    $v = isset( $_POST['v'] ) ? sanitize_text_field( wp_unslash( $_POST['v'] ) ) : '';
    update_option( 'meu_plugin_opcao', $v );
    wp_send_json_success();
} );
```

## Quando suprimir
Handler público de leitura que não depende de sessão, como um contador anônimo protegido por limite de taxa: `// tnc-check-ignore TNC-S03: leitura pública sem efeito colateral, sem sessão`. Handler cuja verificação acontece numa classe ou arquivo que a regra não alcança, citando onde ela está. Handler `nopriv` que grava dado precisa de outra defesa (nonce, honeypot, limite de taxa); suprima só explicando qual.

## Origem
Boa prática geral do WordPress. Um plugin de pontos de memória teve dados de gestão acessíveis a anônimos por um handler `wp_ajax_nopriv_` atrás de uma página privada, o que reforça que a autorização precisa morar no handler.
