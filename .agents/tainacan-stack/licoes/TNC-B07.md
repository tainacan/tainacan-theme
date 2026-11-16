# TNC-B07 · Supressão do PHPCS sem justificativa técnica

**Severidade:** aviso (sempre) · **Categoria:** base · **Arquivos:** .php

## Sintoma
O PHPCS passa limpo, mas o revisor do PR pergunta por que há `$wpdb` direto, SQL interpolado ou `$_POST` sem nonce, e ninguém sabe responder. Um `phpcs:disable` no topo de um arquivo de 2.000 linhas esconde tudo o que foi escrito depois. Um revisor externo resume: "só mandar o phpcs:ignore não resolve o risco; é dizer 'assumo que tem que ser assim' sem ter procurado jeito melhor".

## Causa
A supressão desliga o aviso, não o risco. A hierarquia da equipe é **refatorar > usar a API canônica > suprimir**: antes do `phpcs:ignore`, tentar a função do WordPress ou o repositório do Tainacan que resolve o caso; só então suprimir, com a frase que responde "por que a alternativa não serve aqui". Sem a frase, quem revisa (pessoa ou agente) não consegue distinguir supressão pensada de supressão por pressa.

## O que a regra detecta
- `phpcs:ignore` ou `phpcs:disable` sem ` -- motivo` depois dos códigos.
- Justificativa vazia: "is safe", "false positive", "trust me", "verified manually", "needed", "performance", "para passar", "seguro", "intencional" e variantes, ou menos de 12 caracteres.
- `phpcs:ignore`/`phpcs:disable` sem código de sniff (`// phpcs:ignore` sozinho suprime todas as regras).
- `phpcs:disable` sem `phpcs:enable` no mesmo arquivo.
- Sintaxe antiga `@codingStandardsIgnoreLine/Start/End/File`, removida no PHPCS 4.

Só conta anotação no início do comentário, como o PHPCS lê; menção em prosa de docblock não acusa.

## Como corrigir
1. **Refatore**: `WP_Query`/`get_posts` no lugar de `$wpdb` em tabela do WordPress; repositório do Tainacan no lugar de SQL em `wp_postmeta`; `wp_remote_get` no lugar de `file_get_contents(URL)`; `wp_json_encode`, `wp_delete_file`, `wp_rand`, `gmdate` (TNC-W13).
2. **Se a API não serve**, suprima com o código do sniff e a razão técnica:

```php
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Plugin's own table; not available via WP_Query; $table from $wpdb->prefix; $id via %d placeholder.
$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
```

Razões que sobrevivem à revisão: "tabela própria do plugin, fora do alcance de WP_Query"; "consulta agregada COUNT/GROUP BY, não expressa em WP_Query"; "cache invalida por evento X; ler do cache devolveria estado velho"; "ORDER BY validado contra o allowlist $allowed acima"; "IN-clause montada com array_fill de %d no tamanho de $ids"; "nonce verificado na entrada do handler via check_ajax_referer()"; "filtro de listagem admin só leitura, sem mutação de estado".

3. SQL de várias linhas: `phpcs:disable <códigos> -- motivo` antes e `phpcs:enable <códigos>` logo depois. `phpcs:ignore` numa linha só silencia a primeira linha do `prepare()`.

## Quando suprimir
Não há caso: a correção é escrever a justificativa ou refatorar. Se o projeto não usa PHPCS, não há anotação para acusar.

## Origem
Revisão de um plugin bibliotecário por parceiro externo, que criticou as supressões sem explicação; depois, limpeza de 1.707 achados do Plugin Check em que cada supressão passou a carregar a razão. A regra impede a volta do hábito.
