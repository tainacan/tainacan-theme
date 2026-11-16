# TNC-R06 · Cron agendado sem remoção

**Severidade:** aviso (sempre) · **Categoria:** release · **Arquivos:** todos os .php do plugin (só com `tnc-check --release`)

## Sintoma
Depois de desativar ou remover o plugin, o WP-Cron continua tentando disparar os eventos dele; ferramentas como `wp cron event list` mostram ganchos órfãos, e o site gasta tempo a cada execução do cron. Ao renomear ou migrar o plugin, os eventos antigos se somam aos novos.

## Causa
Eventos agendados com `wp_schedule_event()`/`wp_schedule_single_event()` ficam guardados na opção `cron` até serem removidos. O WordPress não apaga os eventos de um plugin quando ele é desativado: isso cabe ao próprio plugin, normalmente em `register_deactivation_hook()` com `wp_clear_scheduled_hook()` (WordPress 6.9.1, `wp-includes/cron.php:549`) ou `wp_unschedule_hook()` (`:654`).

## O que a regra detecta
- Plugin cujos arquivos PHP têm o texto `wp_schedule_event(` ou `wp_schedule_single_event(` e em nenhum lugar têm `wp_clear_scheduled_hook`, `wp_unschedule_event` ou `wp_unschedule_hook`.
- A verificação é por presença no texto do plugin inteiro (os mesmos arquivos que o `tnc-check` analisa, sem as pastas excluídas): basta existir alguma chamada de limpeza de cron, de qualquer gancho e em qualquer lugar, para a conferência passar. Ela não confere se a limpeza está no gancho de desativação nem se cobre todos os eventos agendados.
- Como a busca é textual, comentários também contam: um `wp_schedule_event(` citado num comentário pode gerar o achado, e um `wp_clear_scheduled_hook` citado num comentário faz a conferência passar.

## Como corrigir
Remova os eventos do plugin na desativação.

```php
register_deactivation_hook( __FILE__, function () {
    wp_clear_scheduled_hook( 'meu_plugin_sincronizar' );
    wp_clear_scheduled_hook( 'meu_plugin_limpar_cache' );
} );
```

Ao renomear ou migrar, desagende também os eventos antigos, inclusive os agendados com argumentos.

## Quando suprimir
As conferências de release não aceitam supressão em linha. Se os eventos são removidos por outro mecanismo (por exemplo, Action Scheduler com limpeza própria), registre isso no `AGENTS.md` do projeto.

## Origem
Boa prática geral do WordPress. Num plugin de dados interligados, a migração para um novo nome precisou apagar crons antigos, inclusive os agendados com argumentos, para que não continuassem rodando ao lado dos novos.
