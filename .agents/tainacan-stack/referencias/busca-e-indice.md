# Busca e índice externo em plugins Tainacan

Lições para rotear listagem, facetas e busca do Tainacan por um índice Elasticsearch/OpenSearch próprio sem perder fidelidade: `posts_pre_query`, recuo para o SQL, disjuntor, contagens que não batem, recortes, analisador e sinônimos, mapeamento `nested`, limites do Elasticsearch 8.6, saúde do índice em cluster compartilhado, reindexação e texto de PDF/OCR.

Capacidade do Elasticsearch (RAM), travas de fila de indexação, autoload, cache e escrita por requisição em caminho quente estão em `desempenho-e-escala.md`.

## Sumário

- [Pontos de entrada do núcleo para busca](#pontos-de-entrada-do-núcleo-para-busca)
- [Checklist quando a contagem não bate](#checklist-quando-a-contagem-não-bate)
- [Roteamento por índice externo](#roteamento-por-índice-externo)
- [Recuo para o SQL, disjuntor e respostas de falha](#recuo-para-o-sql-disjuntor-e-respostas-de-falha)
- [Contagens, recortes e facetas](#contagens-recortes-e-facetas)
- [Analisador, sinônimos e texto](#analisador-sinônimos-e-texto)
- [Mapeamento](#mapeamento)
- [Limites do Elasticsearch 8.6](#limites-do-elasticsearch-86)
- [Saúde e operação do índice](#saúde-e-operação-do-índice)
- [Busca nativa do Tainacan, PDF e OCR](#busca-nativa-do-tainacan-pdf-e-ocr)

## Comece por aqui

- L-BUS-16 · Órfãos no índice: lixeira, despublicação e exclusão seguida de recriação
- L-BUS-11 · Disjuntor: prazo curto no caminho do visitante e só 5xx/rede contam como falha
- L-BUS-24 · Stemming funde termos e acha o que o PHP literal não acha
- L-BUS-39 · Texto de PDF no núcleo: só documento principal, só com a opção ligada e só após reindexar
- L-BUS-12 · Prazos separados por operação: DDL, índice fechado e `_bulk`
- L-BUS-01 · Rotear todas as `WP_Query` de itens por `posts_pre_query`, não só `is_search()`
- L-BUS-03 · O Tainacan usa `orderby` em array: aceitar só string desliga o roteamento sem erro
- L-BUS-17 · O número do recorte tem de ser o número da lista depois do clique
- L-BUS-19 · `_stats` e `_cat/indices` contam sub-documentos `nested`
- L-BUS-35 · ES compartilhado de nó único: YELLOW permanente; perguntar pelo próprio índice

---

## Pontos de entrada do núcleo para busca

Fatos do núcleo (citações relativas a `src/`, linha da 1.4.1 salvo indicação). O custo de base das consultas do núcleo está em "O que o núcleo já faz" de `desempenho-e-escala.md`.

- **Curto-circuito da REST de itens:** filtro `tainacan-api-get-items-alternate` com `(false, $request)`; se devolver `WP_REST_Response`, ela é a resposta (`classes/api/endpoints/class-tainacan-rest-items-controller.php:680-683`; `:656` [1.0.3]). Só cobre a REST: tema, blocos que rodam em PHP e `WP_Query` diretas não passam por ele. Por isso os roteadores que querem cobrir tudo usam `posts_pre_query` (L-BUS-01).
- **Curto-circuito das facetas:** filtro `tainacan-fetch-all-metadatum-values` com `(null, $metadatum, $args)` (`classes/repositories/class-tainacan-metadata.php:1261-1264`; `:1247` [1.0.3]). Rota: `GET /tainacan/v2[/collection/{cid}]/facets/{metadatum_id}` (`classes/api/endpoints/class-tainacan-rest-facets-controller.php:95-175`).
- **Facetas montam a consulta de itens sem executá-la:** `posts_pre_query` com `__return_empty_array` e leitura de `WP_Query::request` (`classes/repositories/class-tainacan-metadata.php:1267-1273`). Inferência: um roteador em `posts_pre_query` deve devolver o `$posts` recebido quando ele já não é `null`, senão consulta o índice à toa nessa montagem.
- **Argumentos:** `tainacan-fetch-args` com `($args, 'items')` (`classes/repositories/class-tainacan-items.php:395`) e `tainacan-api-prepare-items-args` com `($args, $request)` (`classes/api/class-tainacan-rest-controller.php:186`). A REST acrescenta `perm = 'readable'` (`:184`).
- **Ordenação:** sem `orderby`, o padrão é `post_date`/`DESC`; qualquer `orderby` string vira array `[$orderby => $order, 'ID' => 'DESC']` (`classes/repositories/class-tainacan-items.php:373-386`).
- **Parâmetros descartados pela REST:** `taxquery[..][include_children]` (o filtro por termo sempre inclui os filhos), `taxquery[..][field]` (o nome aceito é `metadatum`) e `metaquery[..][type]` (o nome aceito é `metadatumtype`) (`classes/api/class-tainacan-rest-controller.php:131-161`, `:280-330`). Regra TNC-T07.
- **Busca textual padrão:** `Search_Engine` com `LIKE '%termo%'` em título, conteúdo, termos e `wp_postmeta` (`classes/class-tainacan-search-engine.php:305-359`); desligável com `TAINACAN_DISABLE_DEFAULT_SEARCH_ENGINE` ou `tainacan_option_enable_default_search_engine` (`:104-110`). É contra esse `LIKE` que o índice (com stemming) é comparado (L-BUS-10).
- **CPT de itens com `exclude_from_search => true`** (`classes/entities/class-tainacan-collection.php:131-162`): `post_type => 'any'` deixa os itens de fora (TNC-T09).
- **Texto de PDF:** ver L-BUS-39 (opção, CLI e, **Δ** só na 1.4.1, rota REST de extração).

---

## Checklist quando a contagem não bate

Antes de mexer em código, separe as fontes. Quase sempre a pergunta "o índice está errado?" tem resposta "não, a diferença é de outra camada".

1. **Defina as três contagens, cada uma com o mesmo recorte (coleção, status, filtro):**
   - **origem:** o sistema ou site de onde os itens vieram (planilha, API do site de origem, banco legado);
   - **banco:** posts do CPT `tnc_col_{id}_item` com o status esperado (contar por SQL ou por `X-WP-Total` da REST com o roteamento desligado);
   - **índice:** `_count` do índice certo com o mesmo filtro (nunca `_stats`/`_cat/indices`, L-BUS-19; confira o `index_name`, L-BUS-22).
2. **Compare par a par, separadamente.** Diferença **origem × banco** é da importação ou do agregador (colheita incompleta, itens excluídos e recriados, falha de lote, mapeamento). Não é o índice; não reindexe para "consertar". Diferença **banco × índice** é do índice: órfãos (L-BUS-16) ou itens que não entraram na fila.
3. **Descubra quem respondeu cada número.** Filtro e listagem podem vir de fontes diferentes: a lista pelo índice e a faceta pelo SQL (`fetch_all_metadatum_values`), ou o contrário quando o roteador recusa a consulta e cai no SQL em silêncio (L-BUS-03, L-BUS-07, L-BUS-08). Medir tempo não prova nada: leia `_stats/search.query_total` do índice antes e depois da requisição.
4. **Confira se a condição é a mesma.** Recorte e clique usam a mesma condição (L-BUS-17, L-BUS-20)? Stemming do índice × `LIKE` do SQL (L-BUS-10, L-BUS-24)? Termo com filhos (a REST sempre inclui os filhos)? Status (o admin pede `pending` e privados; o visitante só `publish`)? Agregação dentro de `nested` sem `reverse_nested` (L-BUS-21)? Contagem de distintos difere entre MariaDB e ES por collation e normalização (ver `migracao-de-acervos.md`)?
5. **Lixeira e status.** Item na lixeira ou despublicado some do banco publicado mas pode continuar no índice (L-BUS-16). Termo renomeado ou mesclado no banco continua com o nome antigo no índice até reindexar (L-BUS-18).
6. **Caches.** Versão do cache de facetas, transient de contagem, cache de página para anônimo: confira se o número velho não é cache antes de concluir que a fonte está errada (ver `desempenho-e-escala.md` e `release-e-deploy.md`).
7. **Prove com os casos conhecidos**, não por amostra: liste os ids que sobram de um lado e confira um a um (os órfãos eram 0,37% do índice; amostra aleatória não os acha).

---

## Roteamento por índice externo

### L-BUS-01 · Rotear todas as `WP_Query` de itens por `posts_pre_query`, não só `is_search()`
**Sintoma:** mesmo com o motor certo, listagem de coleção e facetas continuavam lentas.

**Causa:** o roteamento só interceptava `is_search()` com `s` não vazio; abrir uma coleção e aplicar facetas nunca passam por `is_search()`, e o gargalo real são os JOINs de `wp_postmeta` e termos da listagem e das facetas.

**Correção:** interceptar as `WP_Query` de itens do Tainacan (post types `tnc_col_*_item`) em `posts_pre_query`; facetas pelo filtro `tainacan-fetch-all-metadatum-values`. Respeitar `$posts` não nulo recebido (ver "Pontos de entrada do núcleo").

**Detecção:** manual: gancho de roteamento condicionado a `is_search()` em plugin que pretende acelerar listagens do Tainacan.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-02 · `pre_get_posts` com `post__in` quebra a paginação
**Sintoma:** paginação errada; resultados limitados a 200.

**Causa:** a versão antiga pegava até 200 hits do índice e injetava `post__in`; `found_posts`/`max_num_pages` refletiam só esse recorte.

**Correção:** `posts_pre_query` curto-circuita a `WP_Query` antes de qualquer SQL: devolver os IDs da página e publicar `found_posts` e `max_num_pages` corretos (o total vem do índice).

**Detecção:** manual: `pre_get_posts` que faz `$query->set('post__in', ...)` com IDs vindos de motor externo.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-03 · O Tainacan usa `orderby` em array: aceitar só string desliga o roteamento sem erro
**Sintoma:** nenhuma listagem jamais foi roteada; nenhum erro; comparativos "índice × SQL" todos inválidos.

**Causa:** o núcleo transforma todo `orderby` string em array associativo `[$orderby => $order, 'ID' => 'DESC']` (padrão `post_date`) (`classes/repositories/class-tainacan-items.php:373-386`); o tradutor só aceitava string e o recuo para o SQL escondia a recusa.

**Correção:** aceitar a forma associativa do WordPress (e as chaves `date`/`post_date`, `title`, `ID`, `meta_value*`). Medir tempo não prova que o índice respondeu: conferir `_stats/search.query_total` do índice antes e depois de cada requisição, e registrar o motivo de cada recusa.

**Detecção:** manual: tratamento de `$query->get('orderby')` com `is_string()` sem ramo para `is_array()`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-04 · `posts_pre_query` não pode sequestrar consulta de post único
**Sintoma:** todo permalink de item abria o mesmo item (HTTP 200); slugs inexistentes paravam de dar 404.

**Causa:** o roteador não distinguia listagem de busca por post específico; o tradutor ignorava `name`/`post_name` e devolvia a listagem inteira, e o WordPress pegava o primeiro.

**Correção:** não rotear quando `is_singular()` ou quando há `name`, `pagename`, `p`, `page_id` ou `post_name__in`.

**Detecção:** manual: callback de `posts_pre_query` sem checar `is_singular()`/`name`/`p`/`page_id`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-05 · Recursão infinita: `posts_pre_query` que chama repositório do Tainacan
**Sintoma:** HTTP 500 por memória esgotada em produção por cerca de 1 minuto.

**Causa:** o callback chamava `Collections::fetch()` (uma `WP_Query`), que disparava `posts_pre_query` de novo na mesma classe.

**Correção:** trava de reentrância estática (um "sem roteamento" em volta das consultas internas); ler dados auxiliares de cache ou antes do gancho.

**Detecção:** manual: callback de `posts_pre_query`/`pre_get_posts` que chama `\Tainacan\Repositories\*::fetch()` ou `new WP_Query` sem flag estática de reentrância.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-06 · `spl_object_id()` é reciclado entre objetos
**Sintoma:** no admin, "às vezes a lista aparece, às vezes fica vazia".

**Causa:** o total do índice era guardado por `spl_object_id($query)`; o PHP reaproveita ids de objetos destruídos e a entrada nunca era limpa (o WordPress não chama `set_found_posts()` depois do curto-circuito), então uma `WP_Query` nova herdava o total de outra.

**Correção:** guardar a referência ao objeto junto e conferir identidade (`===`); limpar o total pendente de cada consulta depois de usado (também evita vazamento de memória em processo longo).

**Detecção:** manual: array estático indexado por `spl_object_id(` sem guardar o objeto.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-07 · Fidelidade acima de cobertura no tradutor de consultas
**Sintoma:** (prevenção) resultado rápido e errado.

**Causa:** tradução parcial de `WP_Query` para a DSL produz resultados diferentes do SQL.

**Correção:** o tradutor devolve `null` (deixa o SQL rodar) para operador desconhecido, ordenação que o índice não sustenta, paginação além de `max_result_window` etc. Toda mudança é validada comparando o JSON completo da resposta SQL × índice para o mesmo filtro.

**Detecção:** manual: teste de paridade SQL × índice no CI ou numa sonda.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-08 · Listagem do admin pede `pending` e derruba o roteamento
**Sintoma:** lista do admin com 74 mil itens levava 24 s para 12 itens e às vezes aparecia vazia.

**Causa:** o admin pede `pending` (e outros status) junto com `publish`; o tradutor desistia e caía no SQL.

**Correção (planejada):** sonda "nenhum post usa estes status" com cache, para rotear quando os status extras estão vazios; ou indexar o status e filtrar por ele.

**Detecção:** manual: registrar o motivo de recusa do tradutor e procurar `post_status` múltiplo.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-09 · Medir com `EXPLAIN` antes de trocar de motor
**Sintoma:** tentação de rotear "relacionados" (ou tudo) pelo índice.

**Causa:** a consulta SQL de relacionados media 8 a 11 ms com `EXPLAIN` sem varredura; no índice, a instituição só existia dentro do `metadata` aninhado, tornando a consulta mais complexa.

**Correção:** decidir pelo `EXPLAIN` e pelos contadores do servidor, não por "parece mais rápido"; levar ao índice só o que o SQL não resolve bem (facetas, texto, agregações).

**Detecção:** manual: plano de execução registrado na decisão.

**Vista em:** plugin de dados ligados e enriquecimento

### L-BUS-10 · Trocar de motor muda o recall
**Sintoma:** contagens diferentes entre SQL e índice para a mesma busca.

**Causa:** o SQL do núcleo usa `LIKE '%termo%'` (ver "Pontos de entrada do núcleo para busca"); o índice usa análise com stemming.

**Correção:** é esperado; validar com a equipe de acervo e documentar. A busca do Tainacan ordena por data, não por relevância: decidir explicitamente qual ordem usar no índice.

**Detecção:** manual: comparar contagens das buscas mais frequentes nos dois motores.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

---

## Recuo para o SQL, disjuntor e respostas de falha

### L-BUS-11 · Disjuntor: prazo curto no caminho do visitante e só 5xx/rede contam como falha
**Sintoma:** com o índice travado, cada listagem esperava 5 s antes de cair para o SQL e o banco compartilhado chegou a 1000/1000 conexões; em outro plugin, impossível criar o índice de vetores ("não funcionou", sem motivo na tela).

**Causa:** o mesmo timeout para indexação e para consultas do visitante; o cliente contava qualquer não-2xx como falha, e checar índice inexistente dá 404: três checagens abriam o disjuntor por 300 s e bloqueavam justamente a criação.

**Correção:** consultas do visitante com prazo curto (ex.: 1,5 s) e disjuntor (3 falhas de transporte, 5xx ou 429 abrem por 120 s e vão direto ao SQL); 4xx é o cluster respondendo e não conta, exceto estados que impedem a busca (`index_closed`, `cluster_block`, `index_not_found`) que vão direto ao SQL; indexação e saúde seguem com prazo longo; ticks de indexação pulam com disjuntor aberto ou por 5 min após `_bulk` falho. Guardar `error.reason` da última falha e as últimas falhas numa opção para diagnóstico (sem autoload; escrita só na transição, ver `desempenho-e-escala.md`). Alinhar o tempo de descanso entre plugins que usam o mesmo índice.

**Detecção:** manual: chamada HTTP ao índice no render público sem timeout curto explícito; disjuntor que incrementa falha em `code < 200 || code >= 300`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch); plugin de dados ligados e enriquecimento

### L-BUS-12 · Prazos separados por operação: DDL, índice fechado e `_bulk`
**Sintoma:** criação de índice falha por timeout; ensaio com 1.629 regras de sinônimo estourou os 5 s ao criar o índice temporário.

**Causa:** criar, fechar, abrir e configurar índice usavam o prazo curto pensado para a busca desistir rápido.

**Correção:** prazos próprios: criação de índice e análise (30 a 120 s), `_bulk` (60 s ou mais), busca do visitante (curto). Operação com índice fechado não pode morrer no meio: `ignore_user_abort(true)` + `set_time_limit(0)` e reabrir o índice sempre, mesmo se o `PUT _settings` falhar.

**Detecção:** manual: mesma função e timeout para `PUT /<índice>`, `_close`/`_open`/`_settings`/`_bulk` e `_search`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch); plugin de dados ligados e enriquecimento

### L-BUS-13 · O ES rejeita `"aggs": []`
**Sintoma:** erro do ES na consulta.

**Causa:** `array()` vazio do PHP vira `[]` no `json_encode`, e o ES espera objeto.

**Correção:** só incluir sub-agregação quando não vazia, ou usar `new stdClass`; o mesmo para `reverse_nested`, que precisa de `{}`.

**Detecção:** manual: `'aggs' => $var` ou `'reverse_nested' => array()` passado a `wp_json_encode` sem checar vazio.

**Vista em:** painel público de indicadores; plugin de busca segmentada

### L-BUS-14 · Resposta parcial do ES conta como falha
**Sintoma:** contagens menores que as reais.

**Causa:** `timed_out: true` ou shard com falha devolvem resultado parcial com HTTP 200.

**Correção:** tratar como exceção e refazer no SQL; resposta vinda de queda não vai para o cache (ou só com TTL curto). Ver "Rota pública cara sem cache na queda" em `seguranca-wordpress.md`.

**Detecção:** manual: leitura de `hits.total` ou agregações sem checar `timed_out` e `_shards.failed`.

**Vista em:** plugin de busca segmentada

### L-BUS-15 · `_bulk` devolve 200 mesmo recusando tudo
**Sintoma:** "falhos: 50" sem causa; índice de pé, vazio e inútil.

**Causa:** erro por item não é erro de requisição; a razão útil está em `items[].index.error.caused_by.reason`.

**Correção:** checar `errors` na resposta e propagar a primeira razão por item.

**Detecção:** manual: chamada a `_bulk` que trata sucesso só pelo status HTTP.

**Vista em:** plugin de dados ligados e enriquecimento

---

## Contagens, recortes e facetas

### L-BUS-16 · Órfãos no índice: lixeira, despublicação e exclusão seguida de recriação
**Sintoma:** índice com 278 a 317 documentos a mais que itens publicados; instituição aparecendo "dobrada" nas facetas sem alerta; cartão de relacionado sem título e com `href` vazio, clicável, na página pública.

**Causa:** a sincronização só enganchava `before_delete_post`, que dispara na exclusão permanente, não ao mandar para a lixeira nem ao despublicar; colheita que exclui e recria itens com IDs novos não apagou o documento antigo; o painel calculava divergência com `max(0, 100 - cobertura)`, então sobra nunca alertava; o front usava `get_the_title()`/`get_permalink()` em ids vindos do índice sem checar existência.

**Correção:** remover do índice também em `wp_trash_post` e em `transition_post_status` saindo de `publish`; rotina de purga de órfãos (manual ou agendada, sob decisão de quem mantém), com trava de segurança no script de remoção; alerta quando há mais documentos no índice que itens no banco; no front, guarda por `get_post_status() === 'publish'` (cobre apagado e despublicado). Provar com os ids órfãos conhecidos, não por amostra. Ver também a restauração da lixeira em `nucleo-tainacan.md`.

**Detecção:** manual: sincronização que só engancha `before_delete_post`/`delete_post`; fórmula de divergência com `max(0, ...)`; laço sobre ids do índice chamando `get_permalink` sem `get_post_status`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch); plugin de dados ligados e enriquecimento; painel público de indicadores

### L-BUS-17 · O número do recorte tem de ser o número da lista depois do clique
**Sintoma:** número de faceta ou recorte diferente do total da lista filtrada; "Títulos" contava título e subtítulo mas filtrava só o título; total cortado pelo LIMIT; barra de busca associada à coleção errada.

**Causa:** contagem e filtro com condições diferentes, ou vindos de fontes diferentes (índice × SQL); `count($items)` usado como total; formulário associado à coleção pela posição no DOM.

**Correção:** cada contagem usa exatamente a condição do filtro aplicado no clique; totais por `COUNT(DISTINCT)` ou `doc_count`; coleção tirada do `action` do formulário; sonda de fidelidade comparando recorte × lista. Para diagnóstico, seguir o checklist do topo.

**Detecção:** manual: `count( $items )` como total após consulta com LIMIT; `querySelectorAll(...)[i]` para associar formulário a coleção.

**Vista em:** plugin de busca segmentada

### L-BUS-18 · Renomear ou mesclar termos altera o banco, não o índice
**Sintoma:** facetas com o texto antigo depois de corrigir termos; dúvida do gestor sobre o alcance da mescla; perda de vínculos ao desfazer.

**Causa:** listagem e facetas vêm do índice, que fica defasado; a mescla faz `wp_set_object_terms()` + `wp_delete_term()`, e desfazer recria por nome com `term_id` novo sem restaurar o `term_meta` do termo apagado.

**Correção:** reindexar todos os itens afetados após correção em massa e conferir no índice (`taxonomies.terms.raw`, `title.raw`); confirmação que diz as três coisas (dados mudam, índice precisa reindexar, desfazer é parcial) e quantos itens vão para qual termo; registro de desfazer que guarda o `term_meta`; validar e mesclar antes de vincular a fontes externas.

**Detecção:** manual: `wp_delete_term` sem salvar `get_term_meta` antes; correção em massa sem reindexação em seguida.

**Vista em:** plugin de dados ligados e enriquecimento

### L-BUS-19 · `_stats` e `_cat/indices` contam sub-documentos `nested`
**Sintoma:** cobertura do índice lida como 5000%; 1,6 milhão de documentos para cerca de 74 mil itens.

**Causa:** a contagem do Lucene inclui um documento por sub-objeto `nested`.

**Correção:** contar itens com `_count` (só documentos-raiz).

**Detecção:** manual: cobertura calculada a partir de `docs.count` de `_stats`/`_cat/indices`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-20 · Escolher o motor do recorte pelo motor da lista
**Sintoma:** números divergentes entre recortes e lista.

**Causa:** índice (stemming) × SQL (`LIKE`).

**Correção:** modo `auto`: o recorte usa o índice só quando o indexador está de fato roteando a lista; os recortes reproduzem a tradução do roteador (`LIKE` vira `match_phrase` em `value_text`, `IN` vira `terms`, `taxquery` vira `taxonomies.term_ids`); duas passadas no índice para texto (candidatos, depois contagem exata). Ler a conexão pela API pública do indexador (ver `convivencia-entre-plugins.md`).

**Detecção:** manual: plugin de recorte que escolhe o motor sem perguntar ao indexador se ele está roteando.

**Vista em:** plugin de busca segmentada

### L-BUS-21 · Agregações `nested` precisam de `reverse_nested`
**Sintoma:** contagens por metadado erradas.

**Causa:** agregação dentro do `nested` conta sub-documentos, não itens.

**Correção:** `reverse_nested` (`{}`, ver L-BUS-13) para voltar ao item; conferir valor a valor com o banco.

**Detecção:** manual: agregação `terms` dentro de `nested` sem `reverse_nested`.

**Vista em:** painel público de indicadores

### L-BUS-22 · Conferir `index_name` antes de medir
**Sintoma:** contagens erradas por medir no índice de outro site no mesmo cluster.

**Causa:** vários índices com nomes parecidos convivem no ES compartilhado.

**Correção:** ler o nome do índice na configuração do indexador antes de qualquer medida; nunca nome fixo em script. Nome padrão do índice deve ser único por site (ver `seguranca-wordpress.md`).

**Detecção:** manual: nome de índice escrito no código de scripts ou plugins.

**Vista em:** plugin de dados ligados e enriquecimento

### L-BUS-23 · Duas listas com cálculos separados divergem
**Sintoma:** os 4 relacionados da página não eram os 4 primeiros da constelação.

**Causa:** dois cálculos independentes e resumo calculado antes da fusão.

**Correção:** um cálculo só (tamanho base) e listas menores como recortes dele; resumo só dos itens mostrados; `_prime_post_caches` ao montar os cartões.

**Detecção:** manual: duas funções que consultam o índice para listas que deveriam ser uma contida na outra.

**Vista em:** plugin de dados ligados e enriquecimento

---

## Analisador, sinônimos e texto

### L-BUS-24 · Stemming funde termos e acha o que o PHP literal não acha
**Sintoma:** metade dos itens sem "Encontrado em"; "Tecido" e "teca" fundidos; reconciliação por grafia rende pouco.

**Causa:** o índice acha "retratado" por "retrato", mas o PHP exigia o termo literal para marcar o campo; o stemmer reduz palavras diferentes à mesma raiz (`tec`); os termos raros (média de 40 caracteres) são texto livre em taxonomia.

**Correção:** quando o acerto vem do `inner_hits`, o campo conta e a marcação usa a raiz curta; stemming só para a "cabeça" do vocabulário; cauda por `more_like_this` ou vetores; revisar falsos positivos.

**Detecção:** manual: marcação de acerto feita com `stripos` do termo literal sobre resultado vindo do índice.

**Vista em:** plugin de busca segmentada; plugin de dados ligados e enriquecimento

### L-BUS-25 · Sinônimos no ES 8.6 sem API: ordem dos filtros e fechar/reabrir o índice
**Sintoma:** regras de sinônimo sem efeito ou descartadas em silêncio; perda de documentos.

**Causa (medida em índice de laboratório):** sinônimo antes do stemmer não pega plural; `asciifolding` com `preserve_original` antes do sinônimo invalida regras; palavra vazia dentro de um termo ("rio de janeiro") faz o ES descartar a regra em silêncio com `lenient: true`; sem `auto_generate_synonyms_phrase_query: false` a expansão vira frase e perde documentos. A 8.6 não tem a API `synonyms_set` (L-BUS-34).

**Correção:** sinônimos inline no analisador de busca (não no de indexação) e aplicar com fechar, `PUT _settings`, reabrir (sem reindexar; prazos em L-BUS-12); ensaiar a definição estrita (`lenient: false`) num índice temporário antes; reabrir o índice real sempre, mesmo se o PUT falhar.

**Detecção:** manual: filtro `synonym` com `lenient: true` sem ensaio estrito; `multi_match`/`query_string` sem `auto_generate_synonyms_phrase_query: false` com sinônimos de várias palavras.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-26 · Completar palavras e busca aproximada inflam resultados
**Sintoma:** "prefixo sempre" fazia "arte" chegar a 6.337 resultados e "rio" a 9.414; `fuzziness: AUTO` fazia "casa" casar com caso, cada, cara e cama.

**Causa:** prefixo aplicado a palavras completas; `AUTO` permite edição em palavras curtas.

**Correção:** prefixo só quando a última palavra não existe no índice ou a busca não acha nada (sonda sem fuzziness); `fuzziness: "AUTO:5,8"`. Fuzziness não passa por sinônimos: erro frequente vai para a lista de correções.

**Detecção:** manual: `fuzziness: "AUTO"` sem limites em campos de texto curto.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-27 · Corrigir a palavra antes, em vez de busca aproximada nos recortes
**Sintoma:** palavra errada achava itens na lista (fuzziness) mas zero recortes.

**Causa:** o clique aplica `LIKE "fotrografia"` (zero); ligar fuzziness nos recortes quebraria a fidelidade com a lista.

**Correção:** corretor antes de tudo (candidata tirada do `highlight` de uma busca aproximada, preservando acento; só palavras de 5 letras ou mais que não existem na coleção); lista, recortes e clique usam a palavra corrigida. Orçamento de 3 palavras por pedido; `continue` (não `break`) quando o orçamento acaba; frase só cacheada se todas as palavras foram avaliadas.

**Detecção:** manual: recorte com fuzziness ligado enquanto o clique filtra por `LIKE`.

**Vista em:** plugin de busca segmentada

### L-BUS-28 · Ordenação alfabética do índice diverge da collation do banco
**Sintoma:** o topo alfabético de 20 valores de uma faceta era um conjunto diferente entre índice e SQL.

**Causa:** `_key: asc` do ES é ordem de bytes UTF-8; o MySQL/MariaDB usa collation insensível a caixa e acento (ex.: `utf8mb4_unicode_520_ci`).

**Correção:** pedir todos os valores distintos e ordenar em PHP com `Collator` (ICU); só quando `sum_other_doc_count == 0`; senão recusar e cair para o SQL.

**Detecção:** manual: agregação `terms` com `order: {_key: asc}` exibida como lista alfabética.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-29 · Metadados do tipo URL casam o termo pelo domínio
**Sintoma:** "Museu" casava 2.204 itens pelo domínio do link.

**Causa:** valores de URL incluídos na busca textual.

**Correção:** metadados URL fora da busca textual por padrão (opção para incluir).

**Detecção:** manual: conferir se o mapeamento de texto inclui metadados do tipo URL.

**Vista em:** plugin de busca segmentada

### L-BUS-30 · Embeddings agrupam por estilo de catalogação
**Sintoma:** 10% de vizinhos de outra instituição contra cerca de 68% esperado ao acaso; `more_like_this` cru devolve 305 de 308 do mesmo museu.

**Causa:** o vetor captura o estilo de catalogação de cada instituição; séries com textos idênticos.

**Correção:** `boosting` negativo por instituição; teto por instituição na fusão; colapsar gêmeos por título e instituição.

**Detecção:** manual: medir a fração de vizinhos de outras instituições numa amostra.

**Vista em:** plugin de dados ligados e enriquecimento

---

## Mapeamento

### L-BUS-31 · `multi_match` no nível de cima nunca casa campos `nested`
**Sintoma:** a busca por texto nunca olhou os metadados ("arte": 551 resultados, 7.449 depois da correção).

**Causa:** `metadata.value_text` e `taxonomies.terms` são `nested`, mas estavam na lista `fields` do `multi_match` da raiz.

**Correção:** cláusulas `nested` explícitas para esses caminhos, combinadas por `bool.should` com o `multi_match` dos campos de topo.

**Detecção:** manual: `fields` de `multi_match` com caminhos mapeados como `type: nested`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-32 · Mudar o idioma do analisador não pode reescrever o índice vivo
**Sintoma:** "aplicar vocabulário" reescreveria o índice com outro stemmer.

**Causa:** a opção de idioma alterava os filtros usados pelo analisador de indexação.

**Correção:** ler o idioma do `_settings` do próprio índice; mudar o idioma exige recriar o índice e reindexar.

**Detecção:** manual: opção de idioma lida da configuração do plugin ao aplicar `_settings` num índice existente.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-33 · Metadados de repositório × de coleção ao rotear ou mapear
**Sintoma:** mapeamento de campos quebrando na coleção destino.

**Causa:** ids de metadados de coleção diferem entre coleções (os de repositório são iguais); coleção recriada perde os metadados próprios.

**Correção:** remapear por nome (ou por mapeador) para a coleção destino; repor os metadados por coleção antes de reimportar numa coleção recriada; no índice, não supor que o mesmo id de metadado significa o mesmo campo em coleções diferentes. Contrato de mapeamento entre plugins: ver `convivencia-entre-plugins.md`.

**Detecção:** manual: id de metadado fixo usado para várias coleções.

**Vista em:** importador de pacotes de preservação (DIP)

---

## Limites do Elasticsearch 8.6

### L-BUS-34 · Limites do ES 8.6 que moldam o código
**Sintoma:** recursos esperados indisponíveis; índice criado com 1.536 dimensões e depois todo documento recusado, após pagar os embeddings.

**Causa:** a 8.6 tem `dense_vector` com kNN, mas `element_type: byte`/`int8_hnsw` (8.12), RRF nativo (8.9) e a API `synonyms_set` (8.10) não existem; `dense_vector` indexado aceita no máximo 1024 dimensões, e o limite só é aplicado na gravação, não no `PUT` do mapeamento; `significant_terms` sobre campo `nested` devolve `bg_count: 0` (não é bug).

**Correção:** fusão RRF e expansão de sinônimos em PHP (ou sinônimos fechando e reabrindo o índice, L-BUS-25); `significant_text` sobre `content`; após criar um índice de vetores, gravar e apagar um vetor sintético e, se recusar, remover o índice e mostrar a razão; encurtar o vetor na origem (parâmetro `dimensions` dos modelos que o aceitam). Testar a operação que falha, não a vizinha. Revisitar ao atualizar o cluster.

**Detecção:** manual: uso de `rrf`, `synonyms_set` ou `int8_hnsw` sem checar a versão; `dense_vector` com `dims` acima de 1024 para cluster anterior à 8.11.

**Vista em:** plugin de dados ligados e enriquecimento

---

## Saúde e operação do índice

### L-BUS-35 · ES compartilhado de nó único: YELLOW permanente; perguntar pelo próprio índice
**Sintoma:** painel "YELLOW" e status geral rebaixado com a busca perfeita; instalação de nó único nunca ficava verde; duas classes do plugin discordavam do status.

**Causa:** leitura de `_cluster/health` global (índice de vizinho com réplica não alocada) e alerta sem olhar `number_of_nodes`. Num nó só, índice com `number_of_replicas >= 1` deixa réplicas UNASSIGNED para sempre. YELLOW significa todos os primários alocados; RED é perda real.

**Correção:** `_cluster/health/<índice>` e status de shards por índice; descontar o amarelo de nó único; cluster pior que o nosso índice vira achado informativo, não alerta. Criar índices com `number_of_replicas: 0` em nó único (zerar réplicas não apaga dados; a correção permanente para todos é um index template).

**Detecção:** manual: `_cluster/health` sem nome de índice decidindo o status do plugin; criação de índice sem `number_of_replicas` explícito.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-36 · Timeouts do indexador causados por E/S do ES e banco esgotado
**Sintoma:** indexador estoura o timeout de 5 s; buscas de 5 a 6 s dentro do próprio ES.

**Causa:** ES com carga altíssima e CPU baixa (E/S travada), somado ao banco esgotado em conexões.

**Correção:** antes de teste de carga, conferir conexões do banco e saúde de E/S do ES; não testar em horário de uso; disjuntor no plugin (L-BUS-11). Dimensionamento por RAM: ver `desempenho-e-escala.md`.

**Detecção:** manual: `_nodes/stats` (fs, os.cpu, load) e `SHOW STATUS LIKE 'Threads_connected'` antes de medir.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch)

### L-BUS-37 · Indexador sem WP-CLI: reindexar por `wp eval-file` com a API interna
**Sintoma:** não há comando para reindexar após mudança em massa.

**Causa:** o indexador não registra comando WP-CLI; o retorno do lote tem formato próprio (ex.: `{ok, processed, failed, skipped, dropped, remaining, state, message}`), não um `indexed`.

**Correção:** script fora do webroot rodado com `wp eval-file`, chamando a API do indexador (enfileirar tudo e repetir o processamento de lote até `remaining = 0`); se for preciso pôr o `.php` no webroot, apagá-lo logo depois. Ritmo medido: cerca de 1.000 itens por lote, 10 a 12 s por lote. Melhor ainda: o indexador expor WP-CLI.

**Detecção:** manual: `*.php` utilitário na raiz web, fora de `wp-*`, plugins e temas.

**Vista em:** plugin de dados ligados e enriquecimento

### L-BUS-38 · Indexador que escapa `>` gera falso positivo de auditoria
**Sintoma:** documentos continuam casando `*&gt;*` no índice mesmo com o banco limpo e reindexado.

**Causa:** o indexador escapa `>` ao montar o documento, e o conteúdo tem `>` legítimo (`B1>`, `-> ver:`).

**Correção:** nada a corrigir no dado; registrar para que auditorias não leiam como regressão; auditar entidades HTML no banco, não no índice. Entidades gravadas escapadas no banco: ver `migracao-de-acervos.md`.

**Detecção:** manual: auditoria de entidades HTML feita contra o índice.

**Vista em:** plugin de dados ligados e enriquecimento

---

## Busca nativa do Tainacan, PDF e OCR

### L-BUS-39 · Texto de PDF no núcleo: só documento principal, só com a opção ligada e só após reindexar
**Sintoma:** anexos e PDFs sem texto disponível para busca; termo do documento não encontrado depois do OCR.

**Causa:** o núcleo só extrai o texto do **documento principal** PDF do item para `document_content_index` e só com `TAINACAN_INDEX_PDF_CONTENT` ou a opção `tainacan_option_index_pdf_content` (`classes/media-helper/class-tainacan-media.php:65-70`, `:759-765`); não indexa anexos. A busca textual depende desse conteúdo, então texto novo (OCR) só aparece depois de reindexar. **Δ** A 1.4.1 tem a rota `GET /tainacan/v2/items/{item_id}/document-content-index/extract` (permissão de edição do item) (`classes/api/endpoints/class-tainacan-rest-items-controller.php:125-139`), que não existe na 1.0.3; a lição original dizia "não há endpoint REST", o que vale só para a 1.0.3. O comando `wp tainacan index-content --collection=<id|all>` existe nas duas (`classes/cli/class-tainacan-cli.php:44`). Esses três pontos foram conferidos no código e não constam da pesquisa do núcleo.

**Correção:** cadeia de extração própria quando precisar de anexos: texto de OCR do anexo (meta própria), depois `document_content_index` do item (com teste de texto legível), extratores próprios e OCR plugável; detectar PDF digitalizado por letras por página e razão de letras. Depois do OCR, regravar o item-pai e rodar `wp tainacan index-content --collection=all` (e reindexar o índice externo, se houver); marca de idempotência por anexo para não reprocessar.

**Detecção:** manual: leitura de `document_content_index` para anexos (só existe no item).

**Vista em:** plugin de narrativas/exposições virtuais; OCR e busca em texto de PDFs/imagens

### L-BUS-40 · Busca nativa (`s`) não encontra autor em metadado Relationship
**Sintoma:** buscar um autor conhecido devolvia zero numa biblioteca que tinha três obras dele.

**Causa:** com a autoria como vínculo (Relationship), o nome não está no título, no conteúdo nem no `meta_value` do item (lá está o id do item de autoridade).

**Correção:** busca em dois saltos: autoridades cujo nome ou variante casa, depois itens cujo postmeta de autoria aponta para elas (restringindo `meta_key` ao metadado de autoria). O núcleo tem `TAINACAN_ENABLE_RELATIONSHIP_METAQUERY` para metaquery de relacionamento (`classes/repositories/class-tainacan-items.php:387-393`); confira se cobre o seu caso antes de reimplementar.

**Detecção:** manual: testar a busca por um valor que só existe no item relacionado.

**Vista em:** plugin de biblioteca

### L-BUS-41 · Link para a lista já filtrada: `metaquery`/`taxquery` na URL
**Sintoma:** necessidade de link que abra a lista do Tainacan já filtrada.

**Causa:** o roteador da busca facetada só entende os parâmetros no formato serializado da REST.

**Correção:** `metaquery[0][key]=<id>&metaquery[0][value][0]=<valor>&metaquery[0][compare]=IN`; `LIKE` exige valor escalar (`metaquery[0][value]=<texto>`); o título aceita metaquery `LIKE` pela chave especial do título. Tipo numérico vai em `metadatumtype`, nunca `type`; em `taxquery`, `metadatum` no lugar de `field`, e `include_children` é ignorado (`classes/api/class-tainacan-rest-controller.php:131-161`).

**Detecção:** TNC-T07; manual: `compare=LIKE` com `value[0]` (array) em URL montada para o Tainacan.

**Vista em:** plugin de biblioteca
