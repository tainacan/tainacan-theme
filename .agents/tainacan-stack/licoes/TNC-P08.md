# TNC-P08 · Evento de cron agendado sem verificar se já existe

**Severidade:** aviso (sempre) · **Categoria:** desempenho · **Arquivos:** .php

## Sintoma
A mesma tarefa roda várias vezes seguidas, a fila do WP-Cron (`cron` em `wp_options`) cresce sem parar e o site fica lento a cada carregamento que dispara o cron. Ferramentas como `wp cron event list` mostram o mesmo gancho repetido dezenas ou centenas de vezes.

## Causa
`wp_schedule_event()` não impede duplicatas de evento recorrente: chamado em `init` ou no carregamento do plugin, cria uma cópia nova a cada requisição com outro horário. A própria documentação do WordPress manda usar `wp_next_scheduled()` para evitar duplicatas (WordPress 6.9.1, `wp-includes/cron.php:19`, repetido no docblock de `wp_schedule_event()`). `wp_schedule_single_event()` só ignora repetição dentro de uma janela de 10 minutos com os mesmos argumentos (`wp-includes/cron.php:109-135`).

## O que a regra detecta
- Chamada a `wp_schedule_event()` ou `wp_schedule_single_event()` cuja função (ou o arquivo, se estiver no nível de cima) não chama `wp_next_scheduled()`, `as_next_scheduled_action()` ou `wp_get_scheduled_event()`.
- Não dispara na função de ativação, porque ela roda uma vez só. Conta como função de ativação a que tem no nome, sem diferenciar maiúsculas, `activat` (`activate`, `on_activation`), `ativar`, `ativacao`, `ativação` ou `instal` (`install`, `instalar`), e a que é o callback de `register_activation_hook()` no mesmo arquivo, inclusive closure ou `[ $this, 'metodo' ]`.
- A isenção pelo nome não vale para nomes de desativação ou desinstalação (`deactivate`, `desativar`, `uninstall`). Callback de `register_activation_hook()` registrado em outro arquivo só fica isento se o nome casar.
- A verificação é por presença na mesma função: não confere se o `wp_next_scheduled()` consulta o mesmo gancho.

## Como corrigir
1. Antes de agendar, verifique se o evento já existe.
2. Na desativação, remova o evento com `wp_clear_scheduled_hook()` (ver também TNC-R06).

```php
// errado
add_action( 'init', function () {
    wp_schedule_event( time(), 'hourly', 'meu_plugin_sincronizar' );
} );

// certo
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'meu_plugin_sincronizar' ) ) {
        wp_schedule_event( time(), 'hourly', 'meu_plugin_sincronizar' );
    }
} );
register_deactivation_hook( __FILE__, function () {
    wp_clear_scheduled_hook( 'meu_plugin_sincronizar' );
} );
```

## Quando suprimir
Agendamento que só acontece num caminho que roda uma vez e que a regra não reconhece (rotina de migração com trava de versão, callback de ativação registrado em outro arquivo com nome que não casa), ou evento único com argumentos diferentes a cada chamada por desenho: `// tnc-check-ignore TNC-P08: migração protegida por trava de versão, roda uma vez`. Para a função de ativação não é preciso suprimir: basta ela ser o callback de `register_activation_hook()` no mesmo arquivo ou ter nome como `meu_plugin_activate` ou `ativar_plugin`.

## Origem
Num plugin de dados interligados, passos de geração de embeddings agendados com `wp_schedule_single_event` sem checagem se empilharam. Em outro plugin, de biblioteca, o problema era o inverso: o cron nunca foi agendado, e a correção foi agendar de forma idempotente no `init` com `wp_next_scheduled()`.
