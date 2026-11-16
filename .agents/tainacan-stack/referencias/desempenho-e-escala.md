# Desempenho e escala em plugins Tainacan

Lições para manter rápidos os acervos grandes (dezenas de milhares de itens): consultas e `wp_postmeta`, contagens, cache, travas e filas, cron e processos em segundo plano, lotes longos, MariaDB/MySQL, assets, rede fora do render e capacidade do Elasticsearch/OpenSearch.

Roteamento de busca por índice externo, disjuntor, contagens que não batem entre origem, banco e índice, analisador e sinônimos estão em `busca-e-indice.md`.

## Sumário

- [O que o núcleo já faz (e o que custa)](#o-que-o-núcleo-já-faz-e-o-que-custa)
- [Consultas, wp_postmeta e contagens](#consultas-wp_postmeta-e-contagens)
- [MariaDB/MySQL: modos SQL, collation e NULL](#mariadbmysql-modos-sql-collation-e-null)
- [Concorrência: travas, filas e transações](#concorrência-travas-filas-e-transações)
- [Cron, WP-Cron e processos em segundo plano](#cron-wp-cron-e-processos-em-segundo-plano)
- [Lotes longos: memória, tempo e importação em massa](#lotes-longos-memória-tempo-e-importação-em-massa)
- [Cache: transients, autoload, gerações e invalidação](#cache-transients-autoload-gerações-e-invalidação)
- [Rede e IA fora do caminho crítico](#rede-e-ia-fora-do-caminho-crítico)
- [Assets e página pública](#assets-e-página-pública)
- [Capacidade do Elasticsearch/OpenSearch](#capacidade-do-elasticsearchopensearch)

## Comece por aqui

- L-DES-14 · Trava "verifica e depois grava" não é atômica: use `GET_LOCK`
- L-DES-15 · Ler-modificar-gravar de opção ou meta sem trava apaga escrita alheia
- L-DES-20 · Agendar cron de forma idempotente e conferir que agendou
- L-DES-45 · Nenhuma chamada de rede ou geração cara na requisição pública
- L-DES-36 · Autoload: ligado no que é lido sempre, desligado no que é grande ou regravado sempre
- L-DES-38 · Não esvaziar o cache inteiro a cada item salvo
- L-DES-35 · Falha de fonte externa não é "zero resultados" e não vai para o cache
- L-DES-01 · Toda consulta por `meta_value` filtra `meta_key`
- L-DES-21 · Lotes conduzidos pelo navegador: teto de tempo e laço até `remaining = 0`
- L-DES-50 · `tainacan_blocks` localizado em toda página pública

---

## O que o núcleo já faz (e o que custa)

Resumo da pesquisa do núcleo (citações relativas a `src/`, linha da 1.4.1 salvo indicação). Antes de otimizar um plugin, saiba de onde vem o custo de base.

- **CPTs registrados em todo `init`.** `Items::register_post_type()` busca todas as coleções com `nopaging` e todas as taxonomias e registra um CPT `tnc_col_{id}_item` e uma taxonomia `tnc_tax_{id}` por entidade a cada requisição (`classes/repositories/class-tainacan-items.php:216-252`; o próprio código diz "TODO: This can be a problem in large repositories." em `:221`). O CPT de itens é `hierarchical => true` (`classes/entities/class-tainacan-collection.php:133`).
- **Valores em `wp_postmeta` com `meta_key` igual ao id do metadado.** Toda faceta, filtro e busca avançada vira `meta_query` com JOIN em `wp_postmeta` (`classes/repositories/class-tainacan-item-metadata.php:62-109`). O `type` da cláusula é forçado pelo tipo do filtro cadastrado (`classes/api/endpoints/class-tainacan-rest-items-controller.php:697-707`); `NUMERIC`/`DATE` geram `CAST` (efeito no índice é comportamento do MySQL, não confirmado na pesquisa).
- **Busca textual `s`.** O `Search_Engine` troca o `posts_search` por título/conteúdo `OR` termos `OR EXISTS(... wp_postmeta ... meta_value LIKE '%termo%')`, com `SELECT DISTINCT` e `LEFT JOIN` de relacionamentos; para anônimo acrescenta um JOIN que compara `meta_key` (texto) com `ID` (`classes/class-tainacan-search-engine.php:305-333`, `:335-359`, `:362-370`, `:373-390`). `LIKE '%x%'` varre `wp_postmeta`. Desliga com `TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE` ou `tainacan_option_enable_default_search_engine` (`:104-110`).
- **Contagem em toda listagem.** `Items::fetch` não define `no_found_rows` (`classes/repositories/class-tainacan-items.php:278-418`) e a REST usa `found_posts` para `X-WP-Total` (`classes/api/endpoints/class-tainacan-rest-items-controller.php:798`).
- **Facetas.** `fetch_all_metadatum_values` monta a consulta de itens sem executá-la (`posts_pre_query` com `__return_empty_array`) e a usa como subconsulta em cada faceta (`classes/repositories/class-tainacan-metadata.php:1267-1273`, `:1325-1360`, `:1485-1495`). Com `count_items` ligado há **uma `WP_Query` por valor** (`:1448-1462`, `:1549-1561`) e, por inferência, cada uma traz todos os IDs, porque `items_filter` recebe `nopaging = 1` (`:1243`; `:1229` [1.0.3]). Mitigações: `TAINACAN_FACETS_DISABLE_COUNT_ITEMS` / `tainacan_option_facets_enable_count_items`, `TAINACAN_FACETS_DISABLE_FILTER_ITEMS` / `tainacan_option_facets_enable_filter_items` (`classes/api/endpoints/class-tainacan-rest-facets-controller.php:100-108`, `:153-167`) ou responder no filtro de curto-circuito `tainacan-fetch-all-metadatum-values` (`classes/repositories/class-tainacan-metadata.php:1261-1264`).
- **Listagem REST sem `fetch_only`.** Cada item monta `value`, `value_as_html` e `value_as_string` de todos os metadados (`classes/api/endpoints/class-tainacan-rest-items-controller.php:229-257`, `:311`). Não há cache de objeto nos repositórios (grep por `wp_cache_get`, `wp_cache_set` e `set_transient` em `classes/repositories/` vazio). Em listagem de plugin, sempre `fetch_only` e `fetch_only_meta`.
- **Δ `perpage`.** Teto `$TAINACAN_API_MAX_ITEMS_PER_PAGE` (constante, mínimo 12, ou opção `tainacan_option_search_results_per_page`, padrão 96; `tainacan.php:27`) aplicado em `classes/api/endpoints/class-tainacan-rest-items-controller.php:710-715`. Na 1.4.1, `perpage < 1` (inclusive `-1`) vira o teto (`classes/api/class-tainacan-rest-controller.php:173-177`, `:196-202`). Na 1.0.3, `perpage=-1` passa sem limite.
- **Usuário logado e várias coleções.** `Items::fetch` injeta `_filter_where`, uma cláusula por post type para checar `read_private` (`classes/repositories/class-tainacan-items.php:397-407`).
- **Compound.** `Compound::get_options()` busca os filhos e renderiza o `form()` de cada um sempre que as opções são lidas, inclusive na API (`views/admin/components/metadata-types/compound/class-tainacan-compound.php:145-182`).
- **Busca avançada com `tainacan_core_title` sem coleção** expande para um `OR` com o metadado de título de cada coleção (`classes/repositories/class-tainacan-items.php:975-1030`). **Itens relacionados** com metadado de repositório percorrem todas as coleções (`:846-880`).
- **Δ Logs.** Todo `insert()` de repositório gera log e diff. Na 1.0.3 o log é um post do CPT `tainacan-log` (com postmeta) (`classes/repositories/class-tainacan-logs.php:144-180` [1.0.3]); na 1.4.1 é linha da tabela `{$wpdb->prefix}tainacan_logs` (`classes/repositories/class-tainacan-logs.php:451-454`; `migrations.php:553-560`), mas o `insert()` do log continua disparando `tainacan-pre-insert*` e `tainacan-insert*` (`classes/repositories/class-tainacan-logs.php:468-523`). Desligar em massa: `$repo->disable_logs()` / `enable_logs()` (`classes/repositories/class-tainacan-repository.php:67-89`).
- **Processos em segundo plano.** Tabela `{$wpdb->prefix}tnc_bg_process` (`classes/background-process/class-tainacan-background-process.php:62`), cron de saúde `{identifier}_cron` a cada 5 min e trava em transient `{identifier}_process_lock` (`classes/background-process/class-tainacan-background-process-base.php:106-123`, `:285-311`): é uma trava do tipo "verifica e grava", não atômica (ver L-DES-14). Processo genérico próprio: `tainacan-register-generic_process` em `init` e `Generic_Process_Handler::get_instance()->register_generic_process(['name','slug','class_name'])`, enfileirado com `add_to_queue()` (`classes/background-process/generic-process/class-tainacan-generic-handler.php:22`, `:34`, `:37-46`, `:48-60`).
- **Scripts de blocos no tema.** Fora do admin, `add_common_theme_scripts` roda em `init` (prioridade 90) e enfileira `tainacan-blocks-common-scripts` com os globais `tainacan_blocks`, `tainacan_user` e `tainacan_plugin` (`views/gutenberg-blocks/class-tainacan-gutenberg-blocks.php:71-73`, `:328-347`; na 1.0.3 em `views/gutenberg-blocks/class-tainacan-gutenberg-block.php:271-284`). Conferido no código das duas versões; não consta da pesquisa do núcleo. Ver L-DES-50.

---

## Consultas, wp_postmeta e contagens

### L-DES-01 · Toda consulta por `meta_value` filtra `meta_key`
**Sintoma:** varredura de milhões de linhas; custo alto por item mesmo em acervos sem o recurso (ex.: consulta reversa de relacionamento rodando em todo item).

**Causa:** `wp_postmeta` só tem índice composto `(meta_key, meta_value(100))`; não há índice em `meta_value` sozinho. Consulta por valor sem `meta_key` varre a tabela.

**Correção:** sempre restringir às chaves relevantes (no Tainacan, os ids dos metadados envolvidos; ex.: só os metadados do tipo Relationship, lista guardada em transient de 12 h e limpa ao salvar metadado). Se não há nenhuma chave aplicável, pular a consulta. Juntar por id numérico (ex.: id da instituição), não por nome.

**Detecção:** manual: SQL em `postmeta` com `WHERE meta_value =`/`LIKE` sem `meta_key` (para `meta_query` com LIKE/REGEXP, TNC-P03).

**Vista em:** plugin de dados ligados e enriquecimento; busca semântica por embeddings

### L-DES-02 · Subconsulta correlata ou `IN (SELECT ... GROUP BY ... HAVING)` degenera: materializar
**Sintoma:** 504 com 32 mil autoridades; consulta de cobertura sobre `term_relationships` × `postmeta` que nunca termina.

**Causa:** `NOT EXISTS` comparando `meta_value = p.ID` de alias externo não tem índice utilizável e roda por linha; o otimizador do MariaDB degenera `IN (SELECT ... GROUP BY ... HAVING)` em tabelas grandes.

**Correção:** materializar os ids uma vez (array em PHP e `IN (...)`, ou `CREATE TEMPORARY TABLE` + `ADD INDEX`) e comparar contra isso; limitar a consulta com `SET SESSION max_statement_time`.

**Detecção:** manual: `NOT EXISTS` com `meta_value` comparado a `ID` de alias externo; `IN (SELECT ... GROUP BY ... HAVING` sobre tabelas do WordPress.

**Vista em:** plugin de biblioteca; plugin de dados ligados e enriquecimento

### L-DES-03 · N+1 ao devolver conjunto completo
**Sintoma:** centenas de consultas individuais de meta e termos numa resposta.

**Causa:** leitura item a item sem pré-carga de cache; contagem com uma `WP_Query` por usuário.

**Correção:** antes do laço, `_prime_post_caches()`, `update_meta_cache('post', $ids)`, `update_object_term_cache($ids, $post_type)`; contagens em uma ou duas consultas agregadas. Em listagens REST do Tainacan, `fetch_only`/`fetch_only_meta` (ver "O que o núcleo já faz").

**Detecção:** TNC-P02

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan

### L-DES-04 · Carrossel de relacionados e navegação do tema carregam milhares de itens
**Sintoma:** página de autoridade com 2,65 s e 303 consultas.

**Causa:** carrossel de itens relacionados e anterior/próximo do tema em páginas do plugin; listagem de 300 descrições com `get_permalink()` uma a uma.

**Correção:** desligar esses recursos do tema nas páginas do plugin (filtros `theme_mod_*`); agregar com uma consulta `GROUP BY` (caiu para 0,28 s e 5 consultas). O núcleo também percorre todas as coleções em itens relacionados com metadado de repositório (`classes/repositories/class-tainacan-items.php:846-880`).

**Detecção:** TNC-P01 (consulta sem limite); manual: `get_permalink(` dentro de laço sobre resultado com `posts_per_page => -1`.

**Vista em:** agregador arquivístico (AtoM/EAD)

### L-DES-05 · `post__not_in` é desencorajado
**Sintoma:** aviso do Plugin Check `WPQueryParams.PostNotIn_post__not_in`.

**Causa:** exclusão em SQL (`NOT IN`) atrapalha o cache de consulta e o plano.

**Correção:** buscar um registro a mais e filtrar em PHP. Na REST do Tainacan, `exclude` vira `post__not_in` (`classes/api/class-tainacan-rest-controller.php:94-187`), com o mesmo custo.

**Detecção:** manual: `'post__not_in'` em `WP_Query`/`get_posts` (Plugin Check aponta).

**Vista em:** kit de memorial virtual

### L-DES-06 · `count` do termo do WordPress não serve para contagem por coleção
**Sintoma:** contagens de assunto erradas por coleção.

**Causa:** a mesma taxonomia `tnc_tax_*` pode estar ligada a várias coleções; o `count` do termo é um número só, somado entre todos os post types e mantido pelo mecanismo de contagem do WordPress (o comportamento exato com os CPTs dinâmicos não está confirmado na pesquisa do núcleo).

**Correção:** contar via `term_relationships` × `posts` publicados do post type da coleção (`tnc_col_{id}_item`), ou pela faceta do Tainacan.

**Detecção:** manual: uso de `$term->count` em taxonomia `tnc_tax_*`.

**Vista em:** plugin de biblioteca

### L-DES-07 · Contagem de pendentes precisa considerar meta ausente
**Sintoma:** avaliador aparecia livre (ou ocupado) indevidamente.

**Causa:** linha de meta inexistente não casa com comparação por valor (`!=` não inclui quem não tem a meta).

**Correção:** `meta_query` com `relation => OR` e cláusula irmã `compare => 'NOT EXISTS'`, ou `IN (estados pendentes)` explícito.

**Detecção:** manual: `meta_query` com `compare => '!='` sem cláusula `NOT EXISTS` irmã.

**Vista em:** gestor de periódicos

### L-DES-08 · Contador desnormalizado fica defasado
**Sintoma:** leitor com multa em aberto passava no balcão.

**Causa:** o total guardado (ex.: soma de multas no perfil) só era atualizado em alguns caminhos; importação e semeadura não passavam por eles.

**Correção:** decisões somam a tabela de origem; o contador fica só para exibição; rotina só-leitura de conferência de consistência.

**Detecção:** manual: listar todos os caminhos que gravam a origem e conferir se cada um atualiza o derivado.

**Vista em:** plugin de biblioteca

### L-DES-09 · Custo fixo de bootstrap por requisição REST
**Sintoma:** cerca de 0,9 s por requisição REST antes de qualquer trabalho.

**Causa:** inicialização do WordPress, de todos os plugins e, no Tainacan, registro de todos os CPTs e taxonomias em todo `init` (`classes/repositories/class-tainacan-items.php:216-252`).

**Correção:** cache de objetos persistente, OPcache, menos plugins carregados no REST; agrupar chamadas do cliente (uma rota que devolve o lote, não N rotas). É o maior ganho fácil.

**Detecção:** manual: medir uma rota vazia (`/wp-json/`) e comparar com a rota do plugin.

**Vista em:** busca semântica por embeddings

---

## MariaDB/MySQL: modos SQL, collation e NULL

### L-DES-10 · `GREATEST(NULL, x)` devolve NULL
**Sintoma:** contadores presos em zero na interface enquanto a coleta rodava.

**Causa:** coluna aceita NULL e `GREATEST(NULL, 400)` é NULL no MySQL/MariaDB; uma vez nula, a coluna nunca sai do lugar.

**Correção:** `GREATEST(COALESCE(col, 0), %d)`; preferir colunas `NOT NULL DEFAULT 0`.

**Detecção:** manual: `GREATEST(`/`LEAST(` sobre coluna sem `COALESCE`/`IFNULL`.

**Vista em:** agregador de acervos de vários sites

### L-DES-11 · `ONLY_FULL_GROUP_BY` e `ANY_VALUE()` no MariaDB
**Sintoma:** relatório "mais visitadas" vazio; detalhamentos em branco.

**Causa:** expressão complexa (`COALESCE(NULLIF(...))`) no `GROUP BY` com `ONLY_FULL_GROUP_BY`; `ANY_VALUE()` não existe no MariaDB até a 10.4.

**Correção:** agrupar por colunas reais (ou por um hash gravado), `MAX()` nas não agregadas, deduplicar em PHP.

**Detecção:** manual: `ANY_VALUE(` em SQL; `GROUP BY` contendo chamada de função.

**Vista em:** painel central de métricas e coletor nos sites

### L-DES-12 · Collations diferentes em JOIN com `meta_value`
**Sintoma:** erro 1267 (mistura ilegal de collations).

**Causa:** `utf8mb4_unicode_ci` × `utf8mb4_general_ci` entre `meta_value` e `CAST(... AS CHAR)`.

**Correção:** `COLLATE` explícito na comparação. A ordenação também difere entre motores: ver "Ordenação alfabética do índice × collation" em `busca-e-indice.md`.

**Detecção:** manual: `meta_value = CAST(` ou JOIN entre `meta_value` e `CAST(... AS CHAR)` sem `COLLATE`.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan

### L-DES-13 · `vector` é palavra reservada no MariaDB 11.7+
**Sintoma:** criação de tabela falha em MariaDB recente.

**Causa:** `vector` virou palavra reservada (tipo nativo de vetor).

**Correção:** nomear a coluna `embedding` (ou sempre usar crases nos nomes).

**Detecção:** manual: `CREATE TABLE` com coluna `vector` sem crase.

**Vista em:** catalogação assistida por IA local

---

## Concorrência: travas, filas e transações

### L-DES-14 · Trava "verifica e depois grava" não é atômica: use `GET_LOCK`
**Sintoma:** páginas processadas 3 a 5 vezes, contador andando para trás, 10 mil itens criados para 1,5 mil; 25 metadados criados em dobro pela preparação da estrutura; dois lotes de indexação simultâneos.

**Causa:** flag `is_processing` ou transient lido e depois gravado (WP-CLI e WP-Cron rodaram o mesmo setup ao mesmo tempo); trava por `INSERT ... ON DUPLICATE KEY UPDATE` que é sempre "obtida"; trava em transient ou `wp_cache_add` com TTL de 120 s protegendo um `_bulk` de até 240 s (a trava vence no meio). A trava de processo em segundo plano do próprio núcleo também é um transient (`classes/background-process/class-tainacan-background-process-base.php:285-311`).

**Correção:** `SELECT GET_LOCK('<prefixo>_<operação>', 0)` (atômico, sem TTL fixo, liberado ao fechar a conexão) e `RELEASE_LOCK` em `finally`; reler o estado sob a trava. Para unicidade, tabela de mapeamento com `UNIQUE KEY` + `INSERT IGNORE`, desfazendo o item recém-criado se a linha já existia. Se precisar de trava com TTL, o TTL tem de ser maior que o pior caso da operação protegida.

**Detecção:** manual: `get_transient(`/`get_option(` seguido de `set_transient(`/`update_option(` da mesma chave usada como trava; `ON DUPLICATE KEY UPDATE` em função de "acquire lock"; TTL da trava menor que o timeout HTTP da operação.

**Vista em:** agregador de acervos de vários sites; plugin de agendamento; agregador arquivístico (AtoM/EAD); indexador de busca (Elasticsearch/OpenSearch)

### L-DES-15 · Ler-modificar-gravar de opção ou meta sem trava apaga escrita alheia
**Sintoma:** roteamento de busca desligando e religando sozinho ("lentidão intermitente"); 46 itens nunca indexados, sem falha registrada e com fila vazia; corrida no estado de processo e dois usuários reservando a mesma peça.

**Causa:** um objeto de vida longa (cron gravando um timestamp a cada tick) reescrevia a opção-array inteira a partir do cache em memória, revertendo `engine`/`index_name` mudados por outro processo; o lote lia a fila (opção serializada) no início e regravava a cópia no fim, apagando o que a colheita enfileirou no meio (o mesmo defeito existia nos métodos irmãos de enfileirar); meta lida e gravada sem exclusão mútua, com o cache de meta devolvendo valor velho.

**Correção:** reler a opção crua (`get_option`, não a versão com constantes aplicadas) imediatamente antes de mesclar o parcial; `GET_LOCK` por site ou por entidade envolvendo todo o ler-modificar-gravar, limpando o cache (`wp_cache_delete`, `clean_post_cache`) antes de reler; corrigir todos os caminhos irmãos. Fila que recebe IDs em `save_post` vai para tabela própria (`INSERT IGNORE` por gravação, `DELETE` por linha), com teto (ex.: 500 mil IDs). Ordem fixa de aquisição de travas (entidade, depois recurso); nada de e-mail dentro da trava; travas de integração externa com timeout curto.

**Detecção:** manual: `update_option( X, array_merge( $this->cache, $partial ) )` sem `get_option(X)` logo antes; `get_option(Q)` ... laço longo ... `update_option(Q, ...)` sem trava; `get_post_meta` seguido de `update_post_meta` da mesma chave sem `GET_LOCK`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch); plugin de processos de gestão de acervo

### L-DES-16 · Fila própria: reivindicação atômica e executor único pelo cron do sistema
**Sintoma:** jobs processados em dobro por execuções concorrentes do cron; coletas grandes que não cabem numa requisição; WP-Cron imprevisível (depende de visita).

**Causa:** seleção (`SELECT ... WHERE status='queued'`) e marcação (`UPDATE ... WHERE id=`) separadas; execução longa dependente de visitas.

**Correção:** reivindicar por `UPDATE ... SET token=%s, status='running' WHERE status='queued' LIMIT n` e depois ler as linhas com aquele token; trava global por `add_option` (padrão do `WP_Upgrader`) ou `GET_LOCK` de executor; recuo 1/3/9 min; `DISABLE_WP_CRON` e WP-CLI no cron do sistema para filas grandes; publicar em lotes; órfãos despublicados, nunca apagados; desativação limpa cron e trava. Leitura de XML grande em fluxo com `XMLReader` (89 MB e 30 mil nós em 7,6 s com 27 MB de pico), não `simplexml_load_file`/`DOMDocument::load`. Alternativa no núcleo: processo genérico do Tainacan (ver "O que o núcleo já faz").

**Detecção:** manual: `SELECT ... WHERE status='queued'` seguido de `UPDATE ... WHERE id=` sem token nem condição de status; `simplexml_load_file`/`DOMDocument::load` em arquivo de importação.

**Vista em:** plugin de narrativas/exposições virtuais; agregador arquivístico (AtoM/EAD)

### L-DES-17 · Trava de linha do InnoDB antes da primeira leitura comum
**Sintoma:** renovação dupla dobrava o prazo; possibilidade de dois empréstimos da mesma obra.

**Causa:** mutação sem trava; no InnoDB (REPEATABLE READ) o snapshot da transação nasce na primeira leitura comum, então o que se lê depois pode estar velho.

**Correção:** `START TRANSACTION` + `SELECT ... FOR UPDATE` nas linhas envolvidas (exemplar e leitor) antes de qualquer SELECT comum; checagem de unicidade dentro da transação, num ponto único do código.

**Detecção:** manual: `START TRANSACTION` seguido de SELECT sem `FOR UPDATE` antes do primeiro `FOR UPDATE`.

**Vista em:** plugin de biblioteca

### L-DES-18 · Selar como pendente e confirmar só no fim
**Sintoma:** falha depois de selar poderia deixar uma assinatura válida de etapa não concluída.

**Causa:** registro definitivo gravado antes do fim da transação lógica.

**Correção:** o registro nasce `pending` e vira `valid` só no fim do envio; listas mostram só `valid`; credencial errada não grava nada (selagem antes de persistir valores, dentro da trava).

**Detecção:** manual: procurar gravação de registro definitivo antes de etapas que ainda podem falhar.

**Vista em:** plugin de processos de gestão de acervo

### L-DES-19 · Estado em memória exige uma instância só
**Sintoma:** tarefas de fundo e limitadores de login inconsistentes com vários workers.

**Causa:** estado guardado no processo (não em banco ou cache compartilhado).

**Correção:** um worker com threads, ou mover o estado para armazenamento compartilhado antes de escalar.

**Detecção:** manual: variáveis globais ou de módulo usadas como fila, contador ou limitador num serviço com vários workers.

**Vista em:** gerador de documentação com IA

---

## Cron, WP-Cron e processos em segundo plano

### L-DES-20 · Agendar cron de forma idempotente e conferir que agendou
**Sintoma:** passos agendados repetidos e empilhados; lembretes e expirações que jamais rodaram; fila própria que nunca rodou enquanto o evento `daily` do mesmo plugin funcionava (a assimetria denuncia).

**Causa:** `wp_schedule_single_event`/`wp_schedule_event` sem checar se já existe; função de agendamento sem nenhum chamador; recorrência personalizada registrada em `cron_schedules` só em `plugins_loaded`, enquanto o gancho de ativação roda antes, e `wp_schedule_event()` recusa a recorrência desconhecida devolvendo `false` sem aviso.

**Correção:** `wp_next_scheduled()` antes de agendar; registrar a recorrência dentro da própria função de agendamento; autoagendamento idempotente no `init` (autocura); recuo para `hourly` se a recorrência não existir; checar o retorno de `wp_schedule_event()`; na migração, desagendar eventos antigos, inclusive os agendados com argumentos (`wp_clear_scheduled_hook($hook, $args)`); desativação remove tudo. Não chamar `wp_next_scheduled()` em toda requisição pública (ver L-DES-51).

**Detecção:** TNC-P08; manual: `add_action('<hook_cron>', ...)` sem `wp_schedule_event(..., '<hook_cron>')` alcançável; `add_filter('cron_schedules'` dentro de `plugins_loaded` com agendamento em `register_activation_hook`.

**Vista em:** plugin de dados ligados e enriquecimento; plugin de biblioteca; plugin de eventos e certificados em PDF

### L-DES-21 · Lotes conduzidos pelo navegador: teto de tempo e laço até `remaining = 0`
**Sintoma:** excluir milhares de registros numa requisição estoura o tempo; "0 removidos" no navegador sem nada no log.

**Causa:** operação em massa síncrona; proxies cortam `admin-ajax` e REST em cerca de 60 s independentemente de `set_time_limit`; fatal ou timeout antes do `wp_send_json`.

**Correção:** rota de lote (até 500 ids explícitos ou um filtro) que processa lotes com teto de contagem **e** de tempo (ex.: 50 itens ou 20 s, o que vier antes) e devolve `remaining`; o JS repete até `remaining = 0` e aborta se um lote não progride; `register_shutdown_function` que devolve JSON legível. Leitura de erro: 504 é o proxy, 500 é fatal, 400 é nonce vencido.

**Detecção:** manual: handler `wp_ajax_*` ou rota REST com laço sem checar o tempo decorrido (`microtime`).

**Vista em:** plugin de narrativas/exposições virtuais; importador de pacotes de preservação (DIP)

### L-DES-22 · Fila longa disparada pelo navegador para quando a aba fecha
**Sintoma:** "sem indicação de que está rodando"; processamento interrompido.

**Causa:** lotes síncronos chamados pelo JS, sem cron nem processo de fundo.

**Correção:** WP-CLI ou agendamento para filas grandes (o navegador só para lotes curtos, ver L-DES-21); barra de progresso determinada só onde o total é conhecido; nada de `sleep()` no caminho síncrono.

**Detecção:** manual: `sleep(` em código alcançável por rota REST.

**Vista em:** plugin de dados ligados e enriquecimento

### L-DES-23 · Agendamento semanal: fuso do site e natureza do WP-Cron
**Sintoma:** lembrete que escorrega com servidor em UTC; disparo com minutos de atraso.

**Causa:** horário calculado sem `wp_timezone()`; WP-Cron dispara na primeira requisição depois do horário.

**Correção:** calcular o próximo disparo com `wp_timezone()`/`DateTimeZone`; manter o evento agendado mesmo com a função desligada (a opção só suprime o envio); desativação remove o agendamento; cron do sistema quando a pontualidade importa.

**Detecção:** manual: `wp_schedule_event(strtotime('next monday 8:00')` sem `wp_timezone()`.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan

### L-DES-24 · WP Background Processing: `return $item` sem atraso e `dispatch()` sem `save()`
**Sintoma:** laço infinito (68 mil linhas de log em 5 min); lotes não processados.

**Causa:** `task()` que devolve o item o reenfileira imediatamente; `dispatch()` sem `save()` não persiste a fila; o loopback HTTP do `dispatch()` falha em alguns ambientes locais.

**Correção:** controlar a espera (`delay_until`) e limitar o log por transient; sempre `$this->save()->dispatch()`; processar o primeiro lote de forma síncrona quando o loopback falha.

**Detecção:** manual: `->dispatch()` sem `->save()` encadeado; `return $item;` em `task()` sem condição de atraso.

**Vista em:** agregador de acervos de vários sites

### L-DES-25 · `purge_queue()` é global: processos simultâneos se apagam
**Sintoma:** duas reaplicações simultâneas se cancelariam ao terminar.

**Causa:** a rotina de finalização de uma unidade (uma instituição) chamava `purge_queue()`, que esvazia a fila de lotes do processo inteiro.

**Correção:** fila sequencial entre unidades, encadeada no fim da finalização, depois do purge; vigia em cron de 5 min; execução "morta" (sem lotes, sem tarefa, 15 min) volta ao fim da fila uma única vez.

**Detecção:** manual: chamada de `purge`/`delete_all` de fila dentro da finalização de uma unidade de trabalho.

**Vista em:** agregador de acervos de vários sites

### L-DES-26 · E-mail síncrono na requisição AJAX
**Sintoma:** respostas AJAX de 2 a 10 s esperando o SMTP.

**Causa:** `wp_mail()` dentro do handler.

**Correção:** diferir para `wp_schedule_single_event()` (resposta caiu para cerca de 200 ms). Blindar os templates contra arrays: datas do Tainacan podem vir como array.

**Detecção:** manual: `wp_mail(` dentro de callback `wp_ajax_*` ou de rota REST.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan

### L-DES-27 · Ação longa que grava "running" antes de validar fica presa para sempre
**Sintoma:** estado "processando" eterno após pedir reindexação.

**Causa:** a rota aceitava a ação e gravava o estado sem o backend estar configurado.

**Correção:** validar pré-condições antes de gravar estado e responder 409 (ou 503) quando não dá para começar; estado com carimbo de tempo para detectar execução morta.

**Detecção:** manual: rota REST que grava estado "running" antes de validar pré-condições.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

---

## Lotes longos: memória, tempo e importação em massa

### L-DES-28 · Lotes longos: defesas de memória e trava que sobrevive a OOM
**Sintoma:** host com 128 MB morria no 3º lote; cada pacote grande somava cerca de 120 MB; o OOM deixava a trava presa por 2 h, sem recuo e sem log.

**Causa:** cache de objetos em memória e contagem de termos crescendo ao longo do lote; OOM é fatal não capturável e o `finally` não roda.

**Correção:** subir `memory_limit` (nunca reduzir); `wp_suspend_cache_addition(true)` + `wp_defer_term_counting(true)` durante o lote; guarda de 70% da memória antes de cada unidade; `wp_cache_flush_runtime()` (só se suportado) + `gc_collect_cycles()` entre unidades; `register_shutdown_function` com reserva de memória que libera a trava, marca falha e loga.

**Detecção:** manual: laço de importação ou exclusão em massa sem `wp_suspend_cache_addition`/`wp_defer_term_counting`; trava por transient sem `register_shutdown_function` de liberação.

**Vista em:** importador de pacotes de preservação (DIP)

### L-DES-29 · `max_execution_time` não é capturável
**Sintoma:** fatal "Maximum execution time of 120 seconds exceeded" após cerca de 22 itens; trava nunca liberada.

**Causa:** página de 100 itens com cerca de 3 s cada; tempo verificado só entre tarefas; cURL com timeout maior que o do PHP.

**Correção:** verificar o tempo a cada item e salvar progresso parcial (índice de retomada); timeout do cURL menor que o limite do PHP (falha recuperável); `register_shutdown_function()` para liberar travas e marcar a queda; prazo de órfão compatível com o tempo real de uma página.

**Detecção:** manual: laço sobre itens de API remota sem checar `microtime`; `timeout` de `wp_remote_*` maior ou igual a `max_execution_time`.

**Vista em:** agregador de acervos de vários sites

### L-DES-30 · `per_page` adaptativo quebra a paginação da coleta
**Sintoma:** 733 de 1.040 itens coletados.

**Causa:** a descoberta calculou páginas com `per_page=20`; o processamento passou a 50 e os offsets ficaram errados. Pré-enfileirar páginas gerava lotes velhos que criavam mais lotes (laço).

**Correção:** `per_page` fixo durante toda a coleta, guardado no estado; pré-enfileirar uma página; descartar lote de página já processada.

**Detecção:** manual: conferir se o tamanho de página da coleta é constante e persistido.

**Vista em:** agregador de acervos de vários sites

### L-DES-31 · Download grande: limite de velocidade mínima em vez de timeout fixo
**Sintoma:** downloads grandes abortados por timeout.

**Causa:** timeout total não serve para arquivos de tamanho variável.

**Correção:** cURL com `CURLOPT_LOW_SPEED_LIMIT`/`CURLOPT_LOW_SPEED_TIME`, nova tentativa com recuo exponencial e retomada por `Range`.

**Detecção:** manual: download de arquivo grande com `timeout` total fixo.

**Vista em:** importador de pacotes de preservação (DIP)

### L-DES-32 · `wp_insert_term` em taxonomia hierárquica é quadrático em lote
**Sintoma:** importação de milhares de assuntos cada vez mais lenta.

**Causa:** `wp_insert_term` reconstrói o cache de hierarquia da taxonomia a cada termo.

**Correção:** pré-carregar os termos existentes, inserir em lote e chamar `clean_taxonomy_cache()` uma vez por lote; `wp_defer_term_counting(true)` durante o lote.

**Detecção:** manual: `wp_insert_term(` dentro de laço sobre taxonomia hierárquica.

**Vista em:** agregador arquivístico (AtoM/EAD)

### L-DES-33 · Importação em massa multiplica os logs do Tainacan
**Sintoma:** importação em massa deixou 918.360 posts de log, o maior peso do banco.

**Causa:** todo `insert()` de repositório gera log e diff (ver "O que o núcleo já faz"). **Δ** Na 1.0.3 cada log é um post `tainacan-log` com postmeta; na 1.4.1 é uma linha em `{$wpdb->prefix}tainacan_logs` (menos peso, mas o volume continua).

**Correção:** em importações próprias, `$repo->disable_logs()` antes e `enable_logs()` depois (`classes/repositories/class-tainacan-repository.php:67-89`); prever limpeza do diário (manter os N mais recentes). Na 1.0.3, apagar logs é apagar posts: exclusão em lote do Tainacan manda para a lixeira, então lixeira e depois excluir, nessa ordem. Em UPDATE direto em `wp_posts`, filtrar `post_type` (ver `migracao-de-acervos.md`).

**Detecção:** manual: laço de `insert()` de repositório sem `disable_logs()`; contar `tainacan-log` (1.0.3) ou linhas de `tainacan_logs` (1.4.1).

**Vista em:** plugin de biblioteca

### L-DES-34 · Detecção de duplicatas par a par não escala
**Sintoma:** comparar 30 mil autoridades par a par não termina dentro de uma requisição.

**Causa:** O(n²) com Levenshtein.

**Correção:** agrupar por chave (ex.: sobrenome normalizado) antes de comparar; dizer o motivo da semelhança; fuzzy é alerta, nunca fusão automática; união sempre decisão humana (homônimos existem).

**Detecção:** manual: laço duplo sobre o conjunto inteiro com distância de edição.

**Vista em:** plugin de biblioteca

---

## Cache: transients, autoload, gerações e invalidação

### L-DES-35 · Falha de fonte externa não é "zero resultados" e não vai para o cache
**Sintoma:** "a busca não encontra nada"; selos de relacionados somem; "sem correspondência" na Wikidata por 1 h; candidatos certos marcados como inadequados por 12 h.

**Causa:** 429 (cota), 403 (anti-robô), 400 (chave inválida), 404 e erro de rede viravam lista vazia, e o chamador gravava esse vazio em transient como resposta válida; "tem chave" só testava se a opção estava preenchida.

**Correção:** distinguir erro de vazio (`WP_Error` ou `available:false`); bloco `sources` com `{ok, status, count}` por fonte; REST devolve 502/503 ou "repetir" com `Cache-Control: no-store` e o cliente tenta de novo com recuo; nunca cachear resultado de cálculo com falha; aviso explícito na interface. Sugestão de IA só conta como resultado se tiver título não vazio.

**Detecção:** TNC-W10 (resposta remota guardada em cache sem conferir erro); manual: ramo `is_wp_error($r)` ou código diferente de 200 que devolve `[]`.

**Vista em:** plugin de dados ligados e enriquecimento; plugin de biblioteca

### L-DES-36 · Autoload: ligado no que é lido sempre, desligado no que é grande ou regravado sempre
**Sintoma:** centenas de KB carregados por requisição; cache de itens (cerca de 2 MB) falhando ao salvar; seis opções sem autoload viravam seis SELECTs por página; corrida de reescrita do `alloptions`.

**Causa:** opção grande (tabela de equivalências, lote de itens remotos) com autoload; opções lidas em toda requisição (configurações) sem autoload; opção regravada em caminho quente (estado do disjuntor) com autoload, o que reescreve o `alloptions` a cada mudança; lista pequena em transient (sem cache de objetos, é uma consulta a mais).

**Correção:** autoload desligado em opção grande ou de escrita frequente (criada na instalação com `false`); autoload ligado em configuração lida sempre (migração única para corrigir opções herdadas); lista pequena em opção com autoload, não em transient; não usar `wp_options` para blobs (rebuscar com índice de retomada ou usar tabela própria); sonda cacheada só quando há cache de objetos persistente; `_prime_post_caches` só quando posts são pedidos.

**Detecção:** manual: `update_option( $k, $array_grande )` sem `false` no 3º parâmetro; `add_option(..., 'no')` para chave lida em `init`/`wp`; autoload ligado em chave regravada a cada requisição.

**Vista em:** plugin de dados ligados e enriquecimento; indexador de busca (Elasticsearch/OpenSearch); agregador de acervos de vários sites; kit de memorial virtual

### L-DES-37 · Sem cache de objetos, transients e limitadores enchem `wp_options`
**Sintoma:** uma escrita em `wp_options` por requisição já cacheada; tabela crescendo a cada termo digitado pelo visitante.

**Causa:** limite de taxa contado também no acerto de cache; transients com chave derivada do texto digitado, nunca limpos (sem cache de objetos, transient vencido só sai quando alguém o lê).

**Correção:** limite só no cache miss (o portão é chamado só quando vai calcular); chave normalizada (espaços, caixa); faxina oportunista de transients vencidos (ex.: 1 a cada 50 gravações); não cachear por frase em corretor ortográfico; Redis/Memcached recomendado em produção grande. Limite por IP em IPv6 agrupado por /64.

**Detecção:** manual: `set_transient( 'prefixo_' . md5( $request['q'] ) ...)` sem rotina de limpeza; incremento de limite antes da consulta ao cache.

**Vista em:** plugin de busca segmentada; painel público de indicadores; indexador de busca (Elasticsearch/OpenSearch); plugin de impressão de itens

### L-DES-38 · Não esvaziar o cache inteiro a cada item salvo
**Sintoma:** colheita ou importação esvaziava o cache a cada item ou lote; cliques de curadoria apagavam o cache inteiro; lista de metadados e mapa de coleções recalculados a cada item.

**Causa:** `wp_cache_flush()` ou apagar todos os transients em `save_post` e em toda ação de admin; uma geração de cache única invalidada por qualquer gravação.

**Correção:** gerações separadas (itens × esquema); `tainacan-insert`/`tainacan-deleted` recebem a entidade e só mexem no esquema para `Collection`, `Metadatum`, `Filter`, `Metadata_Section` e `Taxonomy`; carência no aumento de geração (60 s a 5 min) ou debounce (1 por minuto) com esvaziamento final agendado; esvaziar só em ações que mudam a página; limpar só os grupos tocados.

**Detecção:** manual: `wp_cache_flush()` em `save_post`; `add_action('tainacan-insert', ...)` que invalida tudo sem inspecionar o tipo da entidade.

**Vista em:** plugin de dados ligados e enriquecimento; plugin de busca segmentada; painel público de indicadores

### L-DES-39 · Invalidar também nas escritas de meta e termos, não só no save do item
**Sintoma:** facetas e painéis com números velhos.

**Causa:** transient sem invalidação, ou invalidado só no save do item; o Tainacan grava valores de metadado em `wp_postmeta` e termos sem passar pelo save do post.

**Correção:** subir a versão do cache em `added_post_meta`/`updated_post_meta`/`deleted_post_meta` de item, em `set_object_terms`, no save de metadado e em ações próprias; contar filhos agrupados e fazer COUNT exato só dos que entram no corte.

**Detecção:** manual: `set_transient` de estatística sem `delete_transient` ou aumento de versão correspondente nesses ganchos.

**Vista em:** plugin de biblioteca; gestor de periódicos

### L-DES-40 · Derivado em cache precisa da versão da lógica na chave
**Sintoma:** mudanças em prompts, extratores ou normalização não refaziam roteiros nem áudios; correção de conteúdo não aparecia no certificado PDF já gerado.

**Causa:** hash de origem sem a versão da lógica; handler que só gera se o arquivo não existe.

**Correção:** constantes de versão (`PROMPT_VERSION`, `EXTRACTOR_VERSION` etc.) no hash; hashes separados por etapa (mudar só a voz refaz só o áudio); arquivo em disco com versão ou hash do conteúdo no nome; para reemitir, backup e apagar; validar lendo o texto do PDF, não só pela tela. O mesmo vale para mapa de configuração guardado em opção: versione-o.

**Detecção:** manual: `if ( ! file_exists( $path ) ) { generate(...) }` sem invalidação por versão; chave de cache sem versão da lógica.

**Vista em:** plugin de narrativas/exposições virtuais; plataforma de mapeamento comunitário sobre Tainacan

### L-DES-41 · Orçamento de tempo por pedido em agregações SQL caras
**Sintoma:** 6 a 73 s por recorte via SQL em cerca de 74 mil itens (0,06 a 0,7 s pelo índice); robô variando filtro de texto livre nunca acerta o cache e cada erro disparava até 8 consultas de 20 s; orçamento fixo derrubava o panorama que antes passava, e a falha era cacheada.

**Causa:** agregações sobre `wp_postmeta` sem índice adequado; sem orçamento por pedido, ou orçamento único sem considerar o número de dimensões; janela degradada global; pedido refeito em SQL na mesma hora da queda do índice.

**Correção:** preferir o índice quando disponível; orçamento proporcional às dimensões (com teto, ex.: 120 s) e `max_statement_time`; erro lembrado por 2 min; após falha do índice, só SQL por 5 min por coleção, com 503 `busy` e `Retry-After` (o JS repete até cerca de 2 min); teto global de cálculos simultâneos (ex.: 3); trava anti-estouro de manada com TTL maior que o cálculo mais longo; checar o disjuntor do indexador antes de chamar o índice. Ver "Rota pública cara sem cache na queda" em `seguranca-wordpress.md`.

**Detecção:** manual: trava anti-estouro com TTL menor que o timeout da consulta.

**Vista em:** painel público de indicadores; plugin de busca segmentada

### L-DES-42 · Nada de escrita no banco por requisição em caminho quente
**Sintoma:** INSERT de log mais `set_transient` em toda `WP_Query` de item, dezenas por tela de admin, 24 horas por dia, numa instalação recém-instalada e sem índice configurado.

**Causa:** o roteador marcava "fallback: não configurado" a cada consulta; com o disjuntor aberto, cada listagem gravava uma linha.

**Correção:** "não configurado" não loga; registrar só transições de estado; mesmo motivo no máximo 1 vez por minuto; recusa do disjuntor nunca vira log; alerta inalterado regravado no máximo 1 vez por minuto; tick ocioso não gera linha; erros 4xx limitados a uma linha por (código, tipo) por minuto.

**Detecção:** manual: `set_transient`/`$wpdb->insert`/`update_option` dentro de callback de `posts_pre_query`, `pre_get_posts` ou `init` sem limitação.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-DES-43 · Invalidação que reage a logs e a taxonomias alheias
**Sintoma:** cache de folhas apagado a cada gravação de item.

**Causa:** invalidação em toda gravação de post, incluindo o CPT `tainacan-log`, e em toda taxonomia do site. **Δ** Na 1.0.3 o log é post, então `save_post` dispara para ele; na 1.4.1 o log é linha de tabela (não dispara `save_post`), mas o `insert()` do log ainda dispara `tainacan-insert` (`classes/repositories/class-tainacan-logs.php:468-523`), então quem invalida em `tainacan-insert` continua recebendo entidades `Log`.

**Correção:** invalidar só para `Collection`, `Metadatum`, `Metadata_Section`, `Taxonomy` e `Filter` (inspecionar a classe da entidade, não só o post type) e só em taxonomias `tnc_tax_*`; caches secundários (ex.: QR) com TTL próprio (1 h).

**Detecção:** manual: invalidação em `save_post`/`tainacan-insert` sem filtrar tipo (log incluso) ou em `edited_term` sem filtrar `tnc_tax_`.

**Vista em:** plugin de impressão de itens

### L-DES-44 · Cache HTTP público para rotas REST anônimas
**Sintoma:** (otimização) respostas públicas idênticas recalculadas a cada visita.

**Causa:** rota pública sem cabeçalho de cache; amostras de itens montadas com `get_post()` um a um.

**Correção:** `Cache-Control: public, max-age` de até 120 s ao anônimo, com `Vary: X-WP-Nonce` (logado não recebe a cópia anônima); `_prime_post_caches()` antes de montar amostras (cerca de 120 consultas viraram 3); `MutationObserver` só ligado quando necessário. Cuidado com cache de página servindo HTML velho: ver `release-e-deploy.md`.

**Detecção:** manual: laço de `get_post()` sobre lista de ids sem `_prime_post_caches()` antes.

**Vista em:** plugin de busca segmentada

---

## Rede e IA fora do caminho crítico

### L-DES-45 · Nenhuma chamada de rede ou geração cara na requisição pública
**Sintoma:** lentidão de serviço externo vira lentidão do acervo; fontes externas quebram com frequência; timeout e perda silenciosa de lotes num endpoint de ingestão; risco de geração cara disparada por visitante.

**Causa:** buscar contexto da Wikidata, GeoIP ou chamar provedor de IA no render ou dentro da rota quente.

**Correção:** buscar no momento do vínculo ou da curadoria (admin) e gravar (`term_meta`, postmeta); pendentes preenchidos por cron com trava; instantâneo com data e disjuntor por fonte; limpar `term_meta` **e** transient ao mudar contexto. No caminho quente, só meios instantâneos (cabeçalho do CDN, cache, heurística), o resto por cron em lotes, marcando sentinela em falha. Público só lê o estado (`ready`/`stale`); geração só na fila; regeneração de item com edição humana volta para revisão, nunca sobrescreve.

**Detecção:** manual: `wp_remote_get`/`wp_remote_post` ou chamada a provedor de IA alcançável de `the_content`, `template_redirect`, `tainacan-item-metadata-get-value-as-html*` ou rota REST pública/de ingestão.

**Vista em:** plugin de dados ligados e enriquecimento; plugin de narrativas/exposições virtuais; painel central de métricas e coletor nos sites

### L-DES-46 · Modelos de raciocínio estouram timeout fixo
**Sintoma:** "erro de IA" por timeout (22,5 s contra limite de 30 s); chamadas a modelo local lentas, com centenas de tokens "pensando" mesmo com `think: false`.

**Causa:** modelo de raciocínio com esforço padrão; a etiqueta padrão do modelo local é a variante de raciocínio.

**Correção:** `reasoning_effort=low` (se a API responder 400 citando "reasoning", reenviar sem o parâmetro); timeout próprio maior para modelos de raciocínio; mensagem de timeout sugerindo modelo mais rápido; no modelo local, usar a variante `-instruct`. IA sempre opcional e fora do caminho rápido; processamento local pela fila (minutos por item em CPU).

**Detecção:** manual: timeout único para todos os modelos; etiqueta de modelo local sem variante explícita.

**Vista em:** plugin de biblioteca; catalogação assistida por IA local

### L-DES-47 · Chamadas HTTP externas em paralelo
**Sintoma:** catalogação por ISBN levava 8 a 17 s (caiu para 2,7 a 3,5 s); quatro fontes sequenciais somariam cerca de 3,5 s.

**Causa:** `wp_remote_get` em série, um por autor ou fonte.

**Correção:** `\WpOrg\Requests\Requests::request_multiple()` com recuo sequencial se a classe não existir; cache transient por consulta (sem cachear falha, ver L-DES-35); User-Agent descritivo (política da Wikimedia).

**Detecção:** TNC-P02

**Vista em:** plugin de biblioteca

### L-DES-48 · Modelo de embeddings não cabe no plugin
**Sintoma:** tentação de embutir o modelo no PHP.

**Causa:** cerca de 470 MB e exigiria FFI no PHP-FPM.

**Correção:** serviço HTTP externo, desligado por padrão; índice de vetores próprio, sem tocar o índice do acervo (ver `convivencia-entre-plugins.md`).

**Detecção:** manual: dependência de modelo ou FFI no `composer.json` do plugin.

**Vista em:** plugin de dados ligados e enriquecimento

---

## Assets e página pública

### L-DES-49 · Assets do plugin só onde vão ser usados
**Sintoma:** script, CSS e configuração de todas as coleções em toda página; portão por shortcode nunca abria.

**Causa:** enfileiramento sem condição; o portão testava o shortcode `tainacan-items-list`, que não existe (o da busca é `tainacan-search`, `classes/theme-helper/class-tainacan-theme-helper.php:98`).

**Correção:** função de portão com filtro: arquivo `tnc_col_*`, taxonomia `tnc_tax_*`, singular com bloco `tainacan/faceted-search` ou `tainacan/search-bar` ou shortcode `tainacan-search`, página da lista do repositório; assets de recurso por item só quando o item tem o recurso (ex.: relacionados); pontes de tema só para coleções abrangidas.

**Detecção:** manual: `wp_enqueue_script` em `wp_enqueue_scripts` sem condição; `has_shortcode(..., 'tainacan-items-list')`.

**Vista em:** plugin de impressão de itens; plugin de dados ligados e enriquecimento

### L-DES-50 · `tainacan_blocks` localizado em toda página pública
**Sintoma:** páginas sem nenhum bloco do Tainacan com HTML de mais de 350 KB; páginas públicas 5 a 9 vezes mais pesadas que o necessário (cerca de 290 KB só do global `tainacan_blocks`).

**Causa:** fora do admin, o núcleo enfileira `tainacan-blocks-common-scripts` em `init` e localiza nele `tainacan_blocks`, `tainacan_user` e `tainacan_plugin` em toda página (`views/gutenberg-blocks/class-tainacan-gutenberg-blocks.php:71-73`, `:328-347`; 1.0.3 em `views/gutenberg-blocks/class-tainacan-gutenberg-block.php:271-284`). O tamanho cresce com o número de coleções e metadados.

**Correção:** nas páginas do plugin (ou em todas sem `<!-- wp:tainacan/`), desenfileirar `tainacan-blocks-common-scripts` e `tainacan-blocks-query-variations` em `wp_enqueue_scripts` com prioridade alta; scripts próprios com `defer`. Faça isso só onde não há bloco nem shortcode do Tainacan e meça antes e depois.

**Detecção:** TNC-C01 (remoção de asset de terceiro, para revisar o portão); manual: HTML de página sem `wp:tainacan/` contendo `tainacan_blocks`.

**Vista em:** agregador arquivístico (AtoM/EAD)

### L-DES-51 · Página do item: o que não pode voltar
**Sintoma:** 13 consultas, 67 arquivos PHP, 11 assets e 1,73 MB de JS de terceiros por página de item.

**Causa:** subsistemas construídos com avidez em toda requisição; `wp_next_scheduled()` por requisição pública; varredura de anexos repetida porque só a resposta positiva era memorizada; ficha de impressão renderizada em toda visita.

**Correção:** construção preguiçosa; controlador REST instanciado dentro de `rest_api_init`; ouvintes de escrita só fora de GET público; memorizar também a resposta vazia; memos por requisição limitados ao item corrente (desligados em cron e escrita); ficha sob demanda por REST; pacote único só quando todos os módulos contidos estão ligados; `SCRIPT_DEBUG` devolve as fontes. Medir por A/B desligando o plugin pelo filtro `option_active_plugins` num mu-plugin temporário.

**Detecção:** manual: `wp_next_scheduled(` fora de admin/cron; `new` de controlador REST fora de `rest_api_init`; `get_posts(['numberposts' => -1` em gancho de página pública (TNC-P01).

**Vista em:** kit de memorial virtual

### L-DES-52 · PDF.js por `import()` dinâmico, fora do caminho crítico
**Sintoma:** 1,7 MB disputando banda com o conteúdo da página.

**Causa:** biblioteca carregada junto com a página.

**Correção:** script clássico que faz `import(cfg.lib)` depois do `load` e em tempo ocioso; embarcar só o build legacy e sem `cmaps/` (só necessário para CJK); não converter o arquivo para módulo ES (evita `wp_enqueue_script_module` e etapa de build).

**Detecção:** manual: `wp_enqueue_script(` de `pdf.min.js` ou do worker em toda página de item.

**Vista em:** kit de memorial virtual

---

## Capacidade do Elasticsearch/OpenSearch

### L-DES-53 · Capacidade de ES compartilhado: o limite é RAM, não número de índices
**Sintoma:** dúvida sobre quantos índices ou sistemas novos cabem no mesmo nó.

**Causa:** a busca degrada quando os índices deixam de caber no cache de arquivos do sistema operacional (RAM do contêiner menos heap menos sobrecarga); shards e disco costumam sobrar.

**Correção:** medir o peso por item (cerca de 2 KB por item Tainacan no esquema medido) e dimensionar por RAM; heap até 50% da RAM; acompanhar índices vizinhos grandes (vetores, índices de outros plugins de busca); reindexar fora do pico. Diagnóstico de lentidão e saúde por índice: ver `busca-e-indice.md`.

**Detecção:** manual: `_cat/indices?v&h=index,store.size` somado contra a RAM livre para cache de arquivos.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)
