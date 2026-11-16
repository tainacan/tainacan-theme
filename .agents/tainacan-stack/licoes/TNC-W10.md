# TNC-W10 · Armadilha de WordPress que afeta o site inteiro

**Severidade:** aviso; `@` na rota REST e escrita direta em `$wp_filter[...]->callbacks` são bloqueio · **Categoria:** wordpress · **Arquivos:** .php

## Sintoma
Um detalhe do plugin quebra algo fora dele: toda requisição REST do site registra `preg_match(): Unknown modifier` e a rota nunca responde; filtros de outros plugins deixam de rodar ou geram "Undefined array key"; todos os e-mails do WordPress passam a sair pelo SMTP ou remetente do plugin; um limite por IP conta pela metade; um serviço externo fora do ar faz a tela mostrar "sem resultado" por horas.

## Causa
- Rota REST: o WordPress compara cada rota com `preg_match( '@^' . $route . '$@i', $path )` (WordPress 6.9.1, `wp-includes/rest-api/class-wp-rest-server.php:1171`). Um `@` no padrão fecha o delimitador e a regex fica inválida.
- `$wp_filter`: desde o WordPress 6.4, `WP_Hook` itera por uma lista própria de prioridades (`wp-includes/class-wp-hook.php:28-35`, `:316-323`); escrever direto em `->callbacks` dessincroniza essa lista, e trocar ou apagar a entrada inteira remove os callbacks de todos os plugins naquele gancho.
- `permission_callback`: o WordPress pode chamá-lo mais de uma vez por requisição, por exemplo de novo para montar o cabeçalho `Allow` (`wp-includes/rest-api.php:868-879`), e antes de validar os argumentos.
- `phpmailer_init`, `wp_mail_from`, `wp_mail_from_name`, `wp_mail_content_type` e `wp_mail_charset` são globais: valem para todo `wp_mail()` do site.
- Transient com resposta de `wp_remote_*` sem conferir erro: a falha é guardada como se fosse resposta válida pelo tempo do cache.

## O que a regra detecta
- `register_rest_route()` cuja rota (segundo argumento) contém `@`: bloqueio.
- Atribuição (`=`, `.=`) ou `unset()` em `$wp_filter[...]->callbacks...`: bloqueio. Atribuição ou `unset()` na entrada inteira (`$wp_filter[...] = ...`, `unset( $wp_filter[...] )`): aviso. Leitura (`$copia = $wp_filter['init']`) e atribuição à variável `$wp_filter` inteira não são acusadas.
- `permission_callback` (função, método ou closure do mesmo arquivo, num array literal de `register_rest_route()`) que chama `update_option`, `add_option`, `delete_option`, `set_transient`, `delete_transient`, `wp_cache_set`, `wp_cache_incr`, `wp_cache_add`, `update_post_meta`, `update_user_meta`, `wp_insert_post` ou `wp_update_post`: aviso. `error_log()` não conta como efeito colateral.
- `add_action`/`add_filter` em um dos ganchos de e-mail acima fora de uma função que também chama `wp_mail`, `remove_action` ou `remove_filter`: aviso. Registro no nível de cima do arquivo é sempre acusado.
- Função que chama `wp_remote_get`, `wp_remote_post`, `wp_remote_request`, `wp_safe_remote_get` ou `wp_safe_remote_post` e também `set_transient`, `set_site_transient` ou `wp_cache_set`, sem chamar `is_wp_error` nem `wp_remote_retrieve_response_code`: aviso no primeiro `set_*` da função.
- Não confere se o callback de e-mail verifica o contexto por dentro (uma flag do próprio plugin, por exemplo); nesse caso é preciso suprimir.

## Como corrigir
1. Rota REST: tire o `@` do padrão; para e-mail, use `[^/]+` ou codifique o valor.
2. `$wp_filter`: use `remove_filter()`/`add_filter()`; para suspender um callback de terceiro, guarde e restaure pela mesma API, em `try/finally`.
3. `permission_callback`: só decida; mova contadores e caches para o callback da rota.
4. E-mail: ligue o filtro em volta do seu `wp_mail()` e remova logo depois, ou confira no callback se o e-mail é do plugin.
5. Cache de resposta remota: grave só depois de confirmar sucesso; na falha, não grave ou grave por pouco tempo.

```php
// errado
register_rest_route( 'meu-plugin/v1', '/autor/(?P<email>[\w.@-]+)', $args );
add_action( 'phpmailer_init', 'meu_plugin_smtp' );

// certo
register_rest_route( 'meu-plugin/v1', '/autor/(?P<email>[^/]+)', $args );
function meu_plugin_enviar( $para, $assunto, $texto ) {
    add_action( 'phpmailer_init', 'meu_plugin_smtp' );
    try {
        return wp_mail( $para, $assunto, $texto );
    } finally {
        remove_action( 'phpmailer_init', 'meu_plugin_smtp' );
    }
}
```

## Quando suprimir
Callback de e-mail global que verifica o contexto por dentro (só age com uma flag ligada pelo próprio plugin), ou opção explícita de "usar este SMTP para todo o site", desligada por padrão: `// tnc-check-ignore TNC-W10: callback só age com a flag de envio do plugin ligada`. Cache de resposta remota em que a validação acontece em outra função: cite-a no motivo. Não suprima `@` em rota REST nem escrita em `->callbacks`.

## Origem
Casos reais em plugins da equipe: um plugin de biblioteca derrubou o REST com `@` numa rota; um plugin de impressão dessincronizou `WP_Hook` ao mexer em `->callbacks` para suspender filtros; uma busca segmentada contava o limite por IP duas vezes no `permission_callback`; um plugin de processos ligava `phpmailer_init` para o site inteiro; um plugin de dados interligados guardou falha de rede em transient e mostrou "sem correspondência" por horas.
