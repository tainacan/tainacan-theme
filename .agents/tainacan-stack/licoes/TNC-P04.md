# TNC-P04 · SQL destrutivo em tabela do WordPress

**Severidade:** bloqueio para `UPDATE`/`DELETE` sem `WHERE` em tabela do WordPress, `UPDATE` de `meta_value` sem `meta_key` e `DROP`/`TRUNCATE` de tabela do WordPress ou do núcleo do Tainacan; aviso para `REPLACE()` direto em valor, `UPDATE`/`DELETE` sem `WHERE` em tabela própria e `DELETE` em tabela de meta sem chave · **Categoria:** desempenho · **Arquivos:** .php, .inc, .sql

## Sintoma
Depois de uma migração ou "limpeza", valores somem sem erro nem log: anexos sem tamanhos de imagem, opções de metadados vazias, trilha de auditoria do Tainacan ilegível. Ou uma tabela inteira foi alterada ou apagada quando a intenção era mexer em poucas linhas. A reversão só é possível por backup.

## Causa
O WordPress e o Tainacan guardam arrays como PHP serializado, que registra o tamanho de cada string em bytes (`s:46:"..."`). Um `REPLACE()` que muda o tamanho do texto invalida a linha e o `unserialize()` devolve `false`. No Tainacan, as opções de cada metadado ficam serializadas no meta `metadata_type_options` (`classes/repositories/class-tainacan-metadata.php:160-167`). Sem filtro por `meta_key`, o `UPDATE` atinge valores de todos os plugins e do núcleo. `DROP`/`TRUNCATE` em tabelas do núcleo, como `{prefix}tainacan_logs` (`classes/repositories/class-tainacan-logs.php:451-454`) e `{prefix}tnc_bg_process` (`classes/background-process/class-tainacan-background-process.php:62`), apaga histórico e processos em andamento.

## O que a regra detecta
A análise roda sobre o texto de `$wpdb->query()` e `$wpdb->prepare()` (inclusive quando o SQL foi montado antes numa variável na mesma função), sobre literais de string em qualquer lugar do arquivo (juntando as partes concatenadas até o `;`) e sobre cada comando de arquivos `.sql`. Cada comando só é analisado se tiver a forma real de SQL destrutivo: `UPDATE <tabela> SET` (aceitando apelido e `JOIN` entre a tabela e o `SET`), `DELETE FROM` (ou `DELETE <apelido> FROM`), `DROP TABLE` ou `TRUNCATE`. Texto comum que só contém essas palavras ("update the settings", mensagens, `SELECT`) não é analisado. A tabela é a que vem logo depois de `UPDATE`, `DELETE FROM`, `DROP TABLE [IF EXISTS]` ou `TRUNCATE [TABLE]`; ela é do WordPress quando o nome termina em `posts`, `postmeta`, `options`, `users`, `usermeta`, `terms`, `term_taxonomy`, `term_relationships`, `termmeta`, `comments`, `commentmeta`, `links`, `sitemeta` ou `blogs` (com prefixo `_` ou como `$wpdb->posts`):
- `UPDATE` sem `WHERE`: bloqueio se a tabela é do WordPress, aviso em tabela própria.
- `UPDATE` em tabela de meta que mexe em `meta_value` sem citar `meta_key`: bloqueio.
- `SET meta_value|option_value|post_content = REPLACE(...)` com `WHERE`: aviso (o risco de serializado continua).
- `DELETE` sem `WHERE`: bloqueio em tabela do WordPress, aviso em tabela própria. `DELETE` em tabela de meta sem `meta_key`, `meta_id` nem `post_id`: aviso.
- `DROP TABLE`/`TRUNCATE` de tabela do WordPress ou de `tainacan_logs`/`tnc_bg_process`: bloqueio.
- Não dispara em `UPDATE` com `WHERE` que filtra `meta_key`, nem em `DROP`/`TRUNCATE` de tabela própria do plugin.
- Não confere `post_type` em `UPDATE` de `wp_posts`: um `UPDATE` com `WHERE` que não filtra o tipo passa, mesmo alcançando logs e revisões.
- Entre as chamadas, só olha as que têm como alvo a variável `$wpdb`; um SQL guardado em variável e executado por outro objeto só é visto se o literal estiver no arquivo. Linhas começadas por `--` ou `#` em `.sql` são ignoradas.
- O mesmo analisador (`SqlRisk`) é usado pelo modo hook para interceptar comandos de terminal como `wp db query` e `mysql -e`.

## Como corrigir
1. Filtre sempre por `meta_key` (e por `post_type` em `wp_posts`).
2. Exclua valores serializados do alvo, por exemplo `AND meta_value NOT REGEXP '^(a|O|s|b|i|d):[0-9]*[:;{]'`.
3. Use na aplicação exatamente o mesmo `WHERE` da consulta de medição.
4. Faça backup da tabela antes e, para troca de texto, prefira `wp search-replace`, que entende PHP serializado, ou a API do Tainacan entidade por entidade.

```sql
-- errado
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '&amp;', '&') WHERE meta_value LIKE '%&amp;%';

-- certo
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '&amp;', '&')
WHERE meta_key = '123' AND meta_value LIKE '%&amp;%'
  AND meta_value NOT REGEXP '^(a|O|s|b|i|d):[0-9]*[:;{]';
```

## Quando suprimir
Migração do próprio plugin sobre tabela própria, ou rotina de desinstalação que apaga dados do plugin por desenho: `// tnc-check-ignore TNC-P04: uninstall apaga a tabela própria do plugin` (em `.sql`, `-- tnc-check-ignore TNC-P04: <motivo do caso>`). Não suprima `UPDATE` de `meta_value` sem `meta_key`.

## Origem
Uma troca de entidade HTML (`&amp;` por `&`) feita por `REPLACE` em `wp_postmeta`, sem filtro de `meta_key`, corrompeu 4.256 linhas serializadas (metadados de anexos, logs do Tainacan e outras), e só um backup direcionado permitiu restaurar. Em outro caso, um `UPDATE` em `wp_posts` sem `post_type` alterou 1.943 títulos, dos quais só 48 eram do acervo.
