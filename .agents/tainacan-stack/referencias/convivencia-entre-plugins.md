# Convivência entre plugins

Colisões que só aparecem quando um plugin Tainacan roda ao lado de outros plugins, de outro tema ou de outra versão do núcleo, cada uma como sintoma, causa e regra de convivência.

Sumário:
- [1. Dois plugins que leem o mesmo índice](#1-dois-plugins-que-leem-o-mesmo-índice)
- [2. Plugin × plugin: configuração, dados e contratos](#2-plugin--plugin-configuração-dados-e-contratos)
- [3. Plugin × plugin: ganchos, filtros e DOM do vizinho](#3-plugin--plugin-ganchos-filtros-e-dom-do-vizinho)
- [4. Plugin × plugin: e-mail, admin e WP-CLI](#4-plugin--plugin-e-mail-admin-e-wp-cli)
- [5. Plugin × tema](#5-plugin--tema)
- [6. Plugin × núcleo do Tainacan](#6-plugin--núcleo-do-tainacan)
- [7. Plugin antigo × renomeado](#7-plugin-antigo--renomeado)
- [8. Casos de convivência cobertos em outros arquivos](#8-casos-de-convivência-cobertos-em-outros-arquivos)
- [Checklist para testar plugins juntos](#checklist-para-testar-plugins-juntos)
- [Ferramentas](#ferramentas)

Nesta referência, o campo **Correção:** é a regra de convivência: o que cada lado deve fazer para não quebrar o outro.

## Comece por aqui

- L-CON-01: ler a configuração de outro plugin pela API pública dele, nunca pela opção crua.
- L-CON-02: quem lê o índice de outro plugin confere a versão de mapeamento.
- L-CON-05: Elasticsearch compartilhado: nome de índice por site, saúde pelo próprio índice.
- L-CON-19: suspender filtros de decoração de terceiros só durante a montagem, sem mexer em `$wp_filter`.
- L-CON-23: nunca `remove_all_actions()`/`remove_all_filters()` num gancho compartilhado (e o Tainacan apaga os avisos de admin nas telas dele).
- L-CON-27: `phpmailer_init` e `wp_mail_from` valem para o site todo.
- L-CON-32 e L-CON-33: quem desenha a página do item muda com o tema; o Tainacan Interface não usa `the_content`.
- L-CON-39: `flush_rewrite_rules()` na ativação derruba as páginas de item do Tainacan.

---

## 1. Dois plugins que leem o mesmo índice

### L-CON-01 · Ler a conexão de outro plugin pela API pública dele, não pela opção crua
**Sintoma:** o plugin consumidor (dados ligados, contagens, painel) lia a configuração do Elasticsearch direto da opção do indexador. Quando as credenciais passaram para constantes no `wp-config.php`, a leitura crua deixou de achar a senha; "não configurado" era tratado como falha do servidor de busca e gerava tempestade de retentativas; getters tipados do indexador lançavam `Error` ("must not be accessed before initialization") quando o boot dele falhava.

**Causa:** acoplamento ao formato interno da opção de outro plugin e a propriedades tipadas que só são preenchidas no `boot()`.

**Correção:** o plugin dono do índice publica uma API estável e documentada: `is_booted()`, `is_routing()` (já considera motor configurado, outro indexador ativo e disjuntor aberto), getters anuláveis e um `connection()` que devolve URL, credenciais resolvidas (constante ou opção), nome do índice e versão de mapeamento. O consumidor chama essa API com `class_exists()`/`method_exists()`, trata "não configurado" como estado normal (o resultado do caminho SQL pode ir para cache) e degrada para SQL quando o outro plugin some. Nunca ler `get_option('<opção do outro plugin>')` nem campos como `route_item_lists` diretamente.

**Detecção:** manual: grep por `get_option(` com o nome de opção de outro plugin fora do próprio plugin; getter público `get_x(): Tipo { return $this->x; }` sobre propriedade tipada sem valor padrão.

**Vista em:** plugin de dados ligados e enriquecimento, indexador de busca, plugin de busca segmentada, painel público de indicadores.

### L-CON-02 · Contrato de versão de mapeamento entre quem escreve e quem lê o índice
**Sintoma:** (prevenção) painel ou recorte vazio, ou números errados, no dia em que o dono do índice muda o schema.

**Causa:** o plugin consumidor reproduz nomes de campo do índice de outro plugin sem saber se ainda existem.

**Correção:** o dono expõe uma constante (`MAPPING_VERSION`) e o consumidor declara a que conhece (`SUPPORTED_MAPPING`). Índice mais novo que o suportado: o consumidor vai para SQL. Só suba o suportado depois de conferir campo a campo o que você usa. Leia a versão pela API pública (L-CON-01), não pelo mapping cru.

**Detecção:** manual: plugin que consulta campos do índice de outro plugin sem comparar constante de versão.

**Vista em:** plugin de busca segmentada, painel público de indicadores, plugin de dados ligados (consumidores do mesmo indexador de busca).

### L-CON-03 · Contagem e lista precisam vir da mesma fonte
**Sintoma:** o número de um recorte ou de um gráfico não bate com o total da lista depois do clique.

**Causa:** um plugin conta por SQL enquanto a lista vem do índice (ou o contrário); ou dois plugins traduzem `metaquery`/`taxquery` por caminhos diferentes.

**Correção:** motor `auto` no consumidor segue o roteamento atual do indexador (`is_routing()`). Quando o consumidor reproduz a tradução de consultas do indexador, o contrato fica documentado nos dois repositórios e qualquer mudança na tradução é espelhada, com a versão de mapeamento conferida (L-CON-02). Teste comparando o número exibido com o `X-WP-Total` da lista filtrada.

**Detecção:** manual: comparar número do recorte com o total da lista após aplicar o filtro, nos dois motores.

**Vista em:** plugin de busca segmentada, painel público de indicadores, indexador de busca. Ver também: `busca-e-indice.md` (número do recorte e motor do recorte).

### L-CON-04 · Disjuntor e prazo do visitante compartilhados entre clientes do mesmo Elasticsearch
**Sintoma:** com o Elasticsearch lento, cada plugin consumidor esperava o próprio prazo longo (5 a 20 s) por pedido; dois clientes do mesmo servidor com descansos de disjuntor diferentes (300 s × 120 s) davam comportamento incoerente (um já tinha voltado, o outro não).

**Causa:** cada plugin com seu cliente HTTP, seu prazo e seu disjuntor.

**Correção:** o indexador expõe chamadas de visitante com prazo curto e disjuntor (`search_live()`/`count_live()`); os consumidores checam o disjuntor do indexador antes de chamar o Elasticsearch, usam o prazo dele no corpo da consulta e alinham o tempo de descanso ao dele.

**Detecção:** manual: procurar `wp_remote_post`/`wp_remote_get` para o Elasticsearch fora do indexador e comparar `timeout` e descanso do disjuntor.

**Vista em:** plugin de dados ligados e enriquecimento, painel público de indicadores, indexador de busca. Ver também: `busca-e-indice.md` (prazo curto e disjuntor no caminho do visitante).

### L-CON-05 · Elasticsearch compartilhado entre sites e sistemas
**Sintoma:** dois sites com o mesmo nome de índice padrão (um apagava o do outro); saúde lida do servidor inteiro (réplicas não alocadas de índices de outro sistema rebaixavam o site para "amarelo"); superusuário comum permitia apagar índice alheio.

**Causa:** um nó único de Elasticsearch atende indexadores de vários sites, outros plugins de busca e outros sistemas (por exemplo AtoM e catálogos de biblioteca).

**Correção:** nome padrão do índice derivado do `home_url()`; saúde perguntada pelo próprio índice (`GET <índice>/_stats`, `_cluster/health/<índice>`), nunca pelo `_cluster/health` global; réplicas 0 em nó único; usuário do Elasticsearch por site, restrito ao prefixo do índice.

**Detecção:** manual: grep por `_cluster/health` sem nome de índice e por nome de índice fixo no código.

**Vista em:** indexador de busca, vários projetos que dividem o mesmo Elasticsearch. Ver também: `busca-e-indice.md` (saúde pelo próprio índice; nó único e YELLOW).

### L-CON-06 · Dois mecanismos reescrevendo a mesma `WP_Query`
**Sintoma:** com outro plugin de busca ativo, a mesma listagem devolve resultados diferentes conforme a ordem de carga.

**Causa:** os dois respondem a consulta em `posts_pre_query`/`pre_get_posts`.

**Correção:** no início do seu callback de `posts_pre_query`, `if ( null !== $posts ) return $posts;` (consulta já respondida por outro, respeite). Se detectar outro indexador ativo (detecção resolvida uma vez por requisição), desligue o roteamento de listas e facetas, mantenha a indexação e avise nas telas do plugin.

**Detecção:** manual: callback de `posts_pre_query` que não começa testando `null !== $posts`; `tnc-check --conflicts` lista `pre_get_posts` na mesma prioridade como "Atenção".

**Vista em:** indexador de busca.

### L-CON-07 · Consultas de "todos os ids" ficam no SQL: `posts_per_page = -1`, nunca `suppress_filters`
**Sintoma:** risco de consulta de lista completa de ids ir ao Elasticsearch, ou de contornar o roteador com `suppress_filters`.

**Causa:** o roteador responde por `posts_pre_query`, mas devolve ao SQL quando `posts_per_page = -1`.

**Correção:** consultas internas de lista completa usam `-1` e seguem no SQL; não use `'suppress_filters' => true` (o Plugin Check reprova) nem pagine essas consultas para "ajudar". O `-1` vai ser apontado por TNC-P01: deixe a supressão com o motivo.

**Detecção:** manual: grep por `'suppress_filters' => true` em `WP_Query`/`get_posts`; TNC-P01 aponta o `-1` (justificar).

**Vista em:** página pública do item por tipo de mídia.

### L-CON-08 · Não escrever no índice de outro plugin
**Sintoma:** acrescentar um campo `dense_vector` ao índice do indexador exigiria mudar mapping alheio e reindexar tudo.

**Causa:** tentação de reaproveitar o índice que já existe.

**Correção:** índice próprio para o dado novo, com junção por `item_id` no PHP. O índice do outro plugin é só leitura e só pelo contrato de L-CON-01 e L-CON-02.

**Detecção:** manual: `PUT`/`POST _mapping`, `_doc` ou `_bulk` contra um índice cujo nome vem de outro plugin.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-CON-09 · Configuração que delega a outro plugin inativo desliga tudo em silêncio
**Sintoma:** Tainacan continuava lento com o indexador instalado; índice populado e nunca consultado.

**Causa:** o campo `engine` tinha um valor que delegava a busca a um plugin de terceiros que não estava ativo; o código via esse valor e se desligava ("o outro plugin cuida") e ninguém cuidava.

**Correção:** opção que descreve quem responde a consulta, com dois valores (`elasticsearch`/`sql`); valores legados migrados na leitura (`normalize_engine()`), não só ao salvar. Delegar a outro plugin só com `is_plugin_active()`/`class_exists()` confirmando que ele está lá.

**Detecção:** manual: `return` cedo quando uma opção aponta para outro plugin, sem checar `is_plugin_active()`/`class_exists()`.

**Vista em:** indexador de busca.

---

## 2. Plugin × plugin: configuração, dados e contratos

### L-CON-10 · Contrato REST entre plugin satélite e plugin central
**Sintoma:** cartões da central em branco.

**Causa:** a central esperava campos já processados (navegador, sistema, dispositivo) e o coletor mandava só o user agent cru.

**Correção:** enriquecer no coletor e manter fallback no servidor para coletores antigos; contrato do endpoint estável em toda a série major; campos novos são opcionais.

**Detecção:** manual: teste de contrato com o payload de um coletor da versão anterior.

**Vista em:** painel central de métricas e coletor nos sites.

### L-CON-11 · Plugins irmãos com a mesma política de IP atrás de proxy
**Sintoma:** cada plugin lia o IP de um jeito (um preferia `X-Real-IP`, outro `X-Forwarded-For`): fuga de cota em um, balde único para todos os visitantes em outro.

**Causa:** política de IP decidida plugin a plugin.

**Correção:** uma política só, igual em todos os plugins do conjunto: `REMOTE_ADDR`; último salto do `X-Forwarded-For` só quando o `REMOTE_ADDR` é proxy confiável; a mesma constante (`TAINACAN_TRUSTED_PROXIES`) lida por todos; IPv6 agrupado por /64; sem IP confiável, não limitar; contar só em cache miss; nunca contar dentro do `permission_callback` (roda mais de uma vez por GET).

**Detecção:** TNC-W10 (efeito colateral em `permission_callback`); manual para a leitura de cabeçalhos.

**Vista em:** plugin de dados ligados, indexador de busca, plugin de busca segmentada, plugin de impressão de itens, painel público de indicadores. Ver também: `seguranca-wordpress.md` (cabeçalhos forjáveis e limite por IP).

### L-CON-12 · Ler dados de outro plugin de forma tolerante e sem dependência rígida
**Sintoma:** leitura quebrava quando o outro plugin não estava ativo, ou quando o dado vinha em outro formato (meta de sincronização de um agregador gravada às vezes como data e hora, às vezes como timestamp).

**Causa:** dependência implícita do formato e da presença do outro plugin.

**Correção:** ler a meta de outro plugin (por exemplo o texto extraído por um plugin de OCR) sem dependência declarada e degradando sem ela; aceitar todos os formatos já gravados; arquivo gerado pelo seu plugin não deve ser filho do item (vira anexo e outros plugins passam a lê-lo como documento).

**Detecção:** manual.

**Vista em:** plugin de narrativas, OCR e busca em PDFs, plugin de dados ligados, agregador de acervos.

### L-CON-13 · Gravação em massa feita por outro plugin dispara os seus ganchos
**Sintoma:** agregador ou importador salvando milhares de itens derrubava o cache de todos os plugins a cada item (inclusive pelos logs do Tainacan); um callback tipado em `tainacan-insert` derrubou a colheita inteira de outro plugin (TypeError dentro da gravação).

**Causa:** `tainacan-insert` (`classes/repositories/class-tainacan-repository.php:238`) e as ações por tipo disparam para toda entidade salva, por qualquer código, com objetos de classes diferentes.

**Correção:** callbacks de ganchos do Tainacan sem tipo no parâmetro (ou `accepted_args` mínimo) e checando a classe da entidade dentro; invalidar só a coleção afetada; troca de geração de cache com carência; debounce de 1 min com esvaziamento final agendado.

**Detecção:** TNC-T09 (callback tipado em `tainacan-insert*`); manual para a invalidação.

**Vista em:** painel público de indicadores, plugin de busca segmentada, plugin de impressão de itens, plugin de dados ligados, agregador de acervos. Ver também: `ganchos-e-extensao.md` e `desempenho-e-escala.md` (cache invalidado a cada item).

### L-CON-14 · Exclusão pelo Tainacan deixa órfãos em outro plugin
**Sintoma:** apagar o item deixa registros órfãos no tipo de post de outro plugin, e a exclusão leva o `postmeta` de que o registro dependia.

**Causa:** o Tainacan não conhece os vínculos de terceiros.

**Correção:** o plugin dono do registro guarda um snapshot do que precisa no próprio registro e escuta `tainacan-pre-delete`/`tainacan-deleted` (`classes/repositories/class-tainacan-repository.php:679-696`) para tratar a exclusão do seu lado.

**Detecção:** manual: apagar um item vinculado e conferir os registros do plugin.

**Vista em:** plugin de fluxo (eventos e certificados).

### L-CON-15 · Itens criados em lote por outro plugin saem do fluxo normal
**Sintoma:** itens sem as taxonomias que o fluxo do seu plugin garantiria; a falha parecia do código recém-corrigido.

**Causa:** um plugin de migração de metadados criou itens direto, sem passar pelo fluxo.

**Correção:** backfill e registrar a origem diferente; o seu plugin não presume que todo item passou pelo fluxo dele.

**Detecção:** manual: consulta de itens sem os termos obrigatórios, agrupada por data de criação.

**Vista em:** plugin de fluxo (eventos e certificados).

### L-CON-16 · Instalação paralela apontando para o mesmo banco com versão antiga do plugin
**Sintoma:** risco de segurança e de escrita concorrente: uma cópia antiga do site, sem os patches, gravava no mesmo banco.

**Causa:** duas instalações WordPress com o mesmo `wp-config` de banco.

**Correção:** identificar as instalações que usam o banco, desativar ou atualizar a cópia antiga e confirmar a origem de cada escrita.

**Detecção:** manual: comparar `siteurl`/versão do plugin gravada no banco com a de cada instalação.

**Vista em:** plugin de fluxo (eventos e certificados).

### L-CON-17 · Painel de formulário precisa de filtro pela coleção gerida
**Sintoma:** painéis de um plugin apareciam no formulário de itens de qualquer coleção, inclusive coleções de outros plugins.

**Causa:** `tainacan_register_admin_hook('item', ...)` registra para todo item.

**Correção:** decidir pela coleção: parâmetro `conditional` do gancho (comparado com `this.form`, `views/admin/js/mixins.js:165-190`) ou checagem da coleção no JS do painel; sem coleção gerida, não desenhar nada.

**Detecção:** manual: gancho `item` sem `conditional` e sem checagem de coleção.

**Vista em:** plugin de biblioteca.

### L-CON-18 · Mesma rota REST registrada duas vezes
**Sintoma:** a rota respondia ora com validação, ora sem; uma das duas registrações (procedural e controller) sombreava a outra.

**Causa:** o mesmo esquema de rota registrado em dois lugares; o último `register_rest_route()` mistura ou substitui o anterior.

**Correção:** uma registração por rota, num lugar só; namespace próprio por plugin. Nunca `@` na regex da rota (derruba o REST de todos os plugins).

**Detecção:** `tnc-check --conflicts` (tipo `rest`); TNC-W10 para o `@`.

**Vista em:** plugin de biblioteca. Ver também: `ganchos-e-extensao.md` (`@` em regex de rota).

---

## 3. Plugin × plugin: ganchos, filtros e DOM do vizinho

### L-CON-19 · Suspender filtros de decoração de outros plugins durante a montagem
**Sintoma:** selos de outro plugin ("Wikidata", "Getty AAT") apareciam na folha impressa e na planilha.

**Causa:** filtros `tainacan-item-metadata-get-value-as-html` e `--type-*` são globais: valem para todo uso de `get_value_as_html()` (B.3 da referência do núcleo).

**Correção:** o plugin que monta uma saída própria suspende, só durante a montagem, os callbacks definidos fora de `wp-includes`, do Tainacan (pasta detectada pela classe) e dele mesmo (arquivo do callback via Reflection); restaura em `finally`, por merge, para não perder filtro registrado no meio; oferece um filtro para manter exceções. Use `remove_filter()`/`add_filter()`: desde o WordPress 6.4 o `WP_Hook` itera por uma lista própria de prioridades e mexer em `->callbacks` direto a dessincroniza ("Undefined array key", callbacks mortos). Do lado do decorador: checar contexto antes de decorar.

**Detecção:** TNC-W10 (escrita direta em `$wp_filter`); TNC-C01 (remoção de ganchos de terceiros).

**Vista em:** plugin de impressão de itens, plugin de dados ligados (decorador de metadados).

### L-CON-20 · Dois plugins no mesmo gancho de seção da página do item
**Sintoma:** botão de impressão e faixa de procedência disputavam o mesmo lugar; um plugin movia por JS o botão do outro (frágil, quebra a cada leiaute).

**Causa:** mesmo gancho de seção (`tainacan-get-metadata-section-as-html-before--index-0`, família em `classes/entities/class-tainacan-item.php:1296-1420`) sem prioridade combinada, e manipulação do DOM alheio.

**Correção:** prioridades explícitas e documentadas entre os plugins (exemplo real: impressão em 5, faixa em 10, blocos de dados ligados em 8 e 40); gancho "depois" pelo id da última seção, porque o índice reinicia e conta só seções com valor; para posicionar algo junto do elemento de outro plugin, use o gancho público dele (por exemplo um `..._after_title`), nunca mover o nó dele.

**Detecção:** `tnc-check --conflicts` (mesmo gancho sensível, mesma prioridade); manual para JS que move nó de outro plugin.

**Vista em:** plugin de impressão de itens, plugin de dados ligados e enriquecimento.

### L-CON-21 · Selo de um plugin cobrindo o de outro na lista de resultados
**Sintoma:** o selo de relacionados cobria o quadro "Encontrado em" de outro plugin no rodapé do cartão, ou a descrição.

**Causa:** dois plugins posicionando elementos absolutos no mesmo cartão da busca facetada.

**Correção:** ancorar o selo no canto superior direito da imagem do resultado (único lugar que não cobre título, texto nem o quadro do vizinho, em tabela, cartões, masonry e fichas); selo como irmão do link, nunca dentro dele.

**Detecção:** manual: busca facetada com os dois plugins ativos, em todos os modos de visualização.

**Vista em:** plugin de dados ligados, plugin de busca segmentada. Ver também: `front-end-e-vue.md` (gancho que injeta dentro do link; `:key="index"`).

### L-CON-22 · CSS de um plugin que esconde blocos de outro por prefixo de classe
**Sintoma:** depois do renome de um plugin (cerca de 1.900 classes com prefixo novo), o Ctrl+P de outro plugin deixaria de esconder os blocos dele, e temas que sobrescreviam o prefixo antigo perderiam o efeito.

**Causa:** dependência do prefixo de classe de outro plugin.

**Correção:** quem esconde ou estiliza blocos alheios acompanha o prefixo atual e documenta a dependência nos dois repositórios; o plugin que muda prefixo avisa no changelog e lista os temas e plugins afetados. Melhor ainda: o plugin dono oferece uma classe ou atributo estável (`data-*`) para isso.

**Detecção:** manual: grep do prefixo de classe de outro plugin no seu CSS.

**Vista em:** plugin de impressão de itens, plugin de dados ligados, tema.

### L-CON-23 · Nunca remover todos os callbacks de um gancho compartilhado
**Sintoma:** avisos de admin do seu plugin não aparecem nas páginas que ele registra dentro do menu do Tainacan; em outro caso, o `tnc-check --conflicts` acusou um plugin que roda `remove_all_actions()` em `tainacan-pre-delete` e `tainacan-deleted`, ganchos que outros plugins da mesma pasta usam para tratar exclusões.

**Causa:** `remove_all_actions()`/`remove_all_filters()` apagam callbacks de todos. O próprio Tainacan faz isso de propósito: `Pages::init()` liga `admin_head` a `remove_admin_notices()`, que roda `remove_all_actions('admin_notices')` e `remove_all_actions('all_admin_notices')` em toda tela cujo id contém `tainacan` (`views/class-tainacan-pages.php:51`, `:791-796`; `:708-712` [1.0.3]).

**Correção:** remova só o seu callback (`remove_filter($gancho, $callback, $prioridade)`). Nas telas do Tainacan, não conte com `admin_notices`: mostre o aviso dentro da própria página do plugin.

**Detecção:** TNC-C01; `tnc-check --conflicts` (tipo `removals`).

**Vista em:** conferência no núcleo e no `tnc-check --conflicts` sobre uma pasta real de plugins.

### L-CON-24 · Dois plugins no mesmo gancho JS por item da busca facetada
**Sintoma:** os botões de um plugin sumiam quando o outro estava ativo, ou apareciam duas vezes.

**Causa:** os dois usam `tainacan_faceted_search_item_after` e o nome neutro `tainacan_faceted_search_collection_item_after`, que o núcleo exige em par por causa dos nomes trocados (B.2 da referência do núcleo, `views/gutenberg-blocks/blocks/faceted-search/theme-search/js/view-modes-mixin.js:56-80`). Callback que devolve só o seu HTML apaga o do vizinho.

**Correção:** cada callback concatena ao HTML recebido (`return html + meu`); registre o filtro neutro de forma idempotente (o mesmo namespace não registra duas vezes) e com a mesma função nos dois nomes.

**Detecção:** TNC-T06 (registro com um nome só); manual para a concatenação.

**Vista em:** plugin de busca segmentada, plugin de comparação de itens. Ver também: `ganchos-e-extensao.md` (nomes trocados).

### L-CON-25 · Ponto de extensão JS com ordem de carga variável
**Sintoma:** módulo externo que monta uma tela dentro do app de outro plugin às vezes não aparecia.

**Causa:** a ordem de carga entre o app hospedeiro e o módulo muda conforme dependências e cache.

**Correção:** o hospedeiro anuncia a montagem pelos dois caminhos (`wp.hooks.doAction` e um global `window.*` que o módulo consulta ao carregar); marca o ponto como montado num `dataset`; avisa no console se ninguém responder em cerca de 2,5 s.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-CON-26 · Opções de UI do Tainacan que desmontam os ganchos de formulário de todos
**Sintoma:** ao ligar `itemEdition*InsideTabs` (via `tainacan-admin-ui-options`), sumiram os painéis de todos os plugins no formulário do item.

**Causa:** a opção desmonta a coluna secundária e, com ela, as regiões de gancho de formulário de qualquer plugin.

**Correção:** não ligar essas opções em site com plugins que usam ganchos de formulário; se precisar recolher, faça por JS no próprio painel.

**Detecção:** manual: grep por `tainacan-admin-ui-options` e `InsideTabs`.

**Vista em:** plugin de biblioteca. Ver também: `ganchos-e-extensao.md`.

---

## 4. Plugin × plugin: e-mail, admin e WP-CLI

### L-CON-27 · `phpmailer_init` e `wp_mail_from` valem para o site inteiro
**Sintoma:** o SMTP e o remetente do plugin passavam a valer para todos os e-mails do WordPress; ou o contrário: só os e-mails do plugin saíam pelo SMTP próprio e o "Perdeu a senha?" do núcleo não saía por servidor nenhum.

**Causa:** `phpmailer_init`, `wp_mail_from` e `wp_mail_from_name` são globais; plugins de SMTP também os usam.

**Correção:** todo e-mail do plugin passa por um `Mailer::send()` que liga uma flag (`$sending`); o callback de `phpmailer_init` só age com a flag ligada. Cobrir os e-mails do site é opção explícita, desligada por padrão, com prioridades altas (98/99) para não brigar com plugins de SMTP.

**Detecção:** TNC-W10.

**Vista em:** plugin de fluxos de gestão de acervo, vários projetos com SMTP próprio.

### L-CON-28 · Plugin que redireciona todo o wp-admin
**Sintoma:** todo `/wp-admin/*.php` redirecionava para a página de um plugin de "modo desktop", inclusive por curl; impossível operar pelo admin no ambiente de testes hospedado.

**Causa:** plugin de terceiros (do painel de hospedagem) interceptando o admin inteiro.

**Correção:** para automação e testes, pegar o nonce em `admin-ajax.php?action=rest-nonce` e operar pela REST com cookie e nonce; o seu plugin nunca deve redirecionar telas que não são dele.

**Detecção:** manual.

**Vista em:** plugin de agendamento, ambiente de testes hospedado.

### L-CON-29 · Mover o menu para "Tainacan > Outros" muda o `hook_suffix`
**Sintoma:** CSS e JS da página do plugin pararam de carregar depois de mover o menu.

**Causa:** o enfileiramento comparava `$hook_suffix` com o sufixo antigo; dentro do menu "Outros" do Tainacan (slug `tainacan_other_links`, `views/class-tainacan-pages.php:26`, `views/class-tainacan-admin-commands.php:32`) o sufixo passa a ser `tainacan_other_links_page_<slug>`.

**Correção:** guardar o retorno de `add_submenu_page()` e comparar com ele (ou ligar o enfileiramento em `load-<hook>`), nunca uma string fixa.

**Detecção:** manual: `admin_enqueue_scripts` que compara `$hook` com literal.

**Vista em:** indexador de busca, plugin de busca segmentada, plugin de impressão de itens, painel público de indicadores, plugin de dados ligados.

### L-CON-30 · Assumir a página inicial do Tainacan exige saída de emergência
**Sintoma:** (prevenção) redirecionar a página `tainacan_dashboard` para o painel do plugin impediria o acesso ao painel do núcleo.

**Causa:** sequestro de tela do núcleo.

**Correção:** escape hatch na URL (por exemplo `&tainacan-home=1`), filtro para desligar o redirecionamento e guarda por capability.

**Detecção:** manual: `wp_safe_redirect` em `load-*`/`admin_init` para telas `tainacan_*`.

**Vista em:** plugin de biblioteca.

### L-CON-31 · Aviso de outro plugin polui a saída do WP-CLI
**Sintoma:** saída de `wp eval`/testes por CLI com notice de textdomain carregado cedo demais por outro plugin.

**Causa:** plugin de terceiros chamando `load_plugin_textdomain()`/`__()` antes de `init` (aviso `_load_textdomain_just_in_time`).

**Correção:** `--skip-plugins=<outro>` nos comandos de teste; no seu plugin, traduções só a partir de `init`.

**Detecção:** manual: rodar `wp eval 'echo 1;'` e conferir a saída.

**Vista em:** plugin de narrativas, vários projetos.

---

## 5. Plugin × tema

### L-CON-32 · Quem desenha a página do item depende do tema e da integração
**Sintoma:** seção do plugin ausente ou duplicada conforme o tema; classes como `tainacan-item-section__metadatum` e `tainacan-metadata-value` não existem no código do Tainacan; estilo errado em tema filho.

**Causa:** o núcleo monta a página do item por duas vias: `tainacan/single-items.php` do tema, se existir (`classes/theme-helper/class-tainacan-theme-helper.php:627-644`), ou um filtro em `the_content` que troca o conteúdo por `get_tainacan_item_single_content()` (`:167-182`). Temas de bloco e o Blocksy usam `the_content`; um plugin de integração com o tema pode renderizar os invólucros e só chamar `tainacan_the_metadata_sections()`; `get_stylesheet()` devolve o tema filho.

**Correção:** localizar o renderizador real antes de diagnosticar; detectar a família do tema por `get_template()` (filho conta como o pai) com opção manual que sobrepõe a detecção; usar `the_content` ou `template_include` conforme a família; preferir filtros do Tainacan, com fallback quando a integração não está presente; ler a paleta publicada pelo tema (`--theme-palette-color-*` ou `--tainacan-secondary`); testar com e sem a integração.

**Detecção:** manual: `get_stylesheet() ===` usado para decidir integração; testar em dois temas.

**Vista em:** página pública do item por tipo de mídia, plugin de dados ligados, plugin de busca segmentada.

### L-CON-33 · O Tainacan Interface desliga `the_content` na página do item
**Sintoma:** a seção do plugin não aparecia na página do item com o Tainacan Interface, e aparecia em dobro em outros cenários.

**Causa:** o tema define `TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER` e monta o item com `get_template_part()`. Com a constante definida e `true`, o núcleo não liga o filtro de `the_content`; sem a constante, vale a opção `tainacan_option_override_item_single_template` (`classes/theme-helper/class-tainacan-theme-helper.php:31-37`).

**Correção:** ação do tema como ponto primário (`tainacan-interface-single-item-after-metadata` ou `...-after-attachments`); `tainacan_single_item_content` (`class-tainacan-theme-helper.php:840`) e `the_content` como fallback; guarda por item (`$printed[$item_id]`) contra impressão dupla; shortcode para os demais temas.

**Detecção:** manual: plugin que injeta na página do item só por `add_filter('the_content'`.

**Vista em:** busca semântica, colaboração do público em metadados, plugin de narrativas.

### L-CON-34 · O CSS do tema vence o do plugin: escopo, grade e `!important` só por seção
**Sintoma:** seletor enorme do tema impunha `display:inline-block` a uma seção do Tainacan; texto branco sobre faixa escura voltava a escuro; títulos de outro plugin ficavam invisíveis; o Blocksy aplicava a fonte de títulos a todo `h3`, inclusive `h3.metadata-label` do Tainacan.

**Causa:** especificidade e regras de elemento do tema sobre conteúdo de página, blocos e fichas.

**Correção:** primeiro, leiaute que não depende do `display` herdado (item de grade é sempre bloco); depois, seletores escopados no contêiner do plugin invertendo explicitamente tipografia e cor; `!important` só por seção (fundo claro × escuro) e nunca em seletor global.

**Detecção:** TNC-C02 (seletor global no seu CSS); manual: medir `getComputedStyle` com o tema ativo.

**Vista em:** plugin de dados ligados, plugin de biblioteca, plugins de página pública.

### L-CON-35 · Ganchos do tema não são do Tainacan
**Sintoma:** blocos somem ao trocar de tema.

**Causa:** `blocksy:template:after` e `tainacan-blocksy-single-item-after-document` pertencem ao tema ou ao plugin de integração do tema.

**Correção:** uma camada (`Theme_Hooks`) escolhe o gancho do tema quando ele está ativo e, nos demais, usa os filtros de seção do Tainacan (com `ob_start` em `template_redirect` quando preciso); shortcodes como saída manual; testar num site sem o tema.

**Detecção:** manual: `add_action('blocksy:` sem caminho alternativo condicionado ao tema ativo.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-CON-36 · A ordem das seções do tema muda qual gancho é o "fim"
**Sintoma:** uma barra ancorada em `after-attachments` foi para o fim da página quando o site pôs os metadados primeiro.

**Causa:** o Tainacan Interface empilha documento, anexos e metadados na ordem escolhida em Personalizar.

**Correção:** traduzir a ordem configurada em gancho a cada carga, com rede de segurança por filtro de conteúdo.

**Detecção:** manual: mudar a ordem das seções no Personalizar e recarregar.

**Vista em:** kit de memorial virtual.

### L-CON-37 · Página do item alternativa precisa reaplicar os pontos de extensão
**Sintoma:** com um plugin que substitui a página do item, botões e seções de outros plugins (impressão, dados ligados) sumiam ou mudavam de lugar.

**Causa:** o "palco" próprio não chamava os filtros por seção nem as ações do tema.

**Correção:** o plugin que substitui a página reaplica os filtros por seção do Tainacan (`...-before/after--index-N`) e as ações do tema (`tainacan-blocksy-single-item-after-document`, `...-after-metadata`) nos lugares equivalentes.

**Detecção:** manual: página do item com o plugin ligado e desligado, comparando o que outros plugins injetam.

**Vista em:** página pública do item por tipo de mídia.

### L-CON-38 · Tema desloca a busca facetada e estica todo `input`
**Sintoma:** botão inserido antes da busca ficava coberto; o campo "Os primeiros [100] itens" quebrava em três linhas.

**Causa:** o Blocksy aplica `position:relative; top:-52px` ao contêiner da busca facetada; o tema dá `width:100%` a todo `input`.

**Correção:** inserir dentro da barra `.search-control`, desenhada pelo Vue do Tainacan e igual em qualquer tema; quando inserir fora, somar o deslocamento à margem; escopar estilos de input do diálogo (com `!important` se preciso) e usar especificidade acima do `.button` do Bulma e do tema (por exemplo 0,4,0).

**Detecção:** manual.

**Vista em:** plugin de impressão de itens.

---

## 6. Plugin × núcleo do Tainacan

### L-CON-39 · `flush_rewrite_rules()` na ativação derruba as páginas de item do Tainacan
**Sintoma:** todas as páginas de item em 404 logo depois de ativar um plugin.

**Causa:** o flush na ativação regrava as regras antes de o Tainacan registrar, no `init`, os tipos de post das coleções (`classes/repositories/class-tainacan-items.php:216-252`), e as regras dos itens somem.

**Correção:** nunca dar flush direto na ativação; gravar uma flag e dar o flush em `init` tardio (prioridade alta) na requisição seguinte. Se já quebrou: `wp rewrite flush --hard` ou salvar de novo a tela de links permanentes.

**Detecção:** manual: grep por `flush_rewrite_rules(` dentro do callback de `register_activation_hook`.

**Vista em:** plugin de agendamento, vários projetos. Ver também: `ganchos-e-extensao.md` (flush por assinatura de versão).

### L-CON-40 · Rolagem das páginas clássicas dentro do admin do Tainacan
**Sintoma:** o fim do conteúdo da página do plugin ficava inalcançável dentro do admin do Tainacan.

**Causa:** alturas e `overflow` dos contêineres do Tainacan pensados para o app Vue.

**Correção:** bloco CSS de rolagem só no `admin.css` do plugin (`html{overflow-y:auto}` e neutralização de alturas e `overflow` dos contêineres), escopado nas telas do plugin; documentar para ninguém remover.

**Detecção:** manual.

**Vista em:** colaboração do público em metadados, plugins com páginas de admin clássicas.

### L-CON-41 · CSS do admin do Tainacan vence o CSS de painéis injetados
**Sintoma:** `<strong>` e `<code>` repintados dentro dos painéis; `padding-right` computado 12px em vez de 44px, com a seta sobre o botão "Adicionar" dos anexos.

**Causa:** especificidade maior do CSS do admin do núcleo sobre o HTML que entra pelos ganchos de formulário.

**Correção:** estilos escopados no prefixo do painel, com `!important` onde o núcleo vence; conferir por medição no DOM renderizado (`getComputedStyle`, `getBoundingClientRect`), não pelo CSS escrito.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-CON-42 · `.wp-core-ui .button` vence o `.button` do Bulma
**Sintoma:** ícone e rótulo desciam para o rodapé dos botões no app Vue/Buefy do plugin.

**Causa:** especificidade 0,2,0 contra 0,1,0: o WordPress troca `inline-flex` por `inline-block` e aplica `line-height: 2.15`.

**Correção:** repor no escopo do app `display:inline-flex; align-items:center; justify-content:center; line-height:1.5`.

**Detecção:** manual.

**Vista em:** plugin de biblioteca (app Vue/Buefy dentro do wp-admin).

---

## 7. Plugin antigo × renomeado

### L-CON-43 · Nunca deixar o plugin antigo e o renomeado ativos juntos
**Sintoma:** ganchos, rotas e classes duplicados; risco de colisão de autoloader. **Causa:** ativar o novo sem desativar o antigo. **Correção:** desativar o antigo antes de ativar o novo; migração de opções e metas idempotente e com trava; identificadores de persistência congelados (detalhes em `release-e-deploy.md`, dono do tema renome). **Detecção:** `tnc-check --conflicts` sobre a pasta com os dois (funções, classes, rotas). **Vista em:** plugin de dados ligados, plugin unificado que absorveu outros, vários projetos.

---

## 8. Casos de convivência cobertos em outros arquivos

Regra de convivência em uma linha; o detalhe fica no arquivo dono.

- `!important` antigo na folha base vence o redesenho do próprio plugin: procure antes de redesenhar. Ver `front-end-e-vue.md`.
- Bulma devolve `display` a elementos `hidden` e o CSS scoped do app não alcança HTML injetado por módulos: `[hidden]{display:none!important}`. Ver `front-end-e-vue.md`.
- Ancestral do tema com `transform`/`filter`/`contain` quebra `position: fixed`: mover o elemento para filho de `<body>`. Ver `front-end-e-vue.md`.
- Blocos de módulos diferentes herdam tamanhos de `<h3>` do tema: tamanho de título único na casca. Ver `front-end-e-vue.md`.
- Cores do bloco Busca facetada vêm como variáveis inline no invólucro: só `!important` vence. Ver `front-end-e-vue.md`.
- Nome de classe CSS reaproveitado achata componentes (vale entre plugins sem prefixo): procure o nome antes de criar. Ver `front-end-e-vue.md`.
- Tokens do `wp-components` e paleta do tema reprovam contraste dentro do tema: redefinir tokens no contêiner. Ver `front-end-e-vue.md`.
- A galeria do Tainacan faz `stopPropagation()` no download e o Swiper suprime cliques de arrasto: interceptar na fase de captura em `document`, com guarda de distância. Ver `front-end-e-vue.md`.
- Modal abrindo por baixo do lightbox do Tainacan: ordem fixa lightbox < overlay < modal. Ver `front-end-e-vue.md`.
- Trava de reenvio do formulário público do Tainacan precisa rodar antes do handler Vue: captura, overlay como irmão. Ver `front-end-e-vue.md`.
- DataTables Responsive quebra com CSS na célula de controle e sem prioridades. Ver `front-end-e-vue.md`.
- `$` não é global no admin (jQuery em `noConflict`). Ver `front-end-e-vue.md`.
- Filtros de documento/oEmbed anteriores ao `wp_kses` do Tainacan perdem marcação: use `tainacan-get-the-document`. Ver `ganchos-e-extensao.md`.
- Exposer de terceiro passa pelo `wp_kses` do núcleo: servir e dar `exit`. Ver `ganchos-e-extensao.md`.
- `options.php` reescreve opções de outro plugin no mesmo grupo de settings. Ver `ganchos-e-extensao.md`.
- `$items_repo->update()` pode zerar termos aplicados antes por `wp_set_object_terms()`: aplicar termos depois do update. Ver `nucleo-tainacan.md`.
- Dois metadados na mesma taxonomia se contaminam. Ver `nucleo-tainacan.md`.
- `var tainacan_blocks` (~290 KB) localizado em toda página: desenfileirar só onde não há bloco `wp:tainacan/`. Ver `desempenho-e-escala.md`.
- `purge_queue()` da biblioteca de tarefas em segundo plano é global: execuções simultâneas se apagam. Ver `desempenho-e-escala.md`.
- Cache de página (Varnish) na frente do site serve HTML velho a anônimo: PURGE pelo plugin. Ver `release-e-deploy.md`.
- Comentários do WordPress usados como dado interno vazam por widget, feed e REST: status próprio. Ver `seguranca-wordpress.md`.

---

## Checklist para testar plugins juntos

1. **Inventário antes de ativar.** Rode `tnc-check --inventory=<pasta de plugins> --format=json` e compare, entre os plugins que vão conviver: ganchos sensíveis e prioridades, opções e transients gravados, handles de script e estilo (e o `src` de cada um), rotas REST, objetos JS globais, shortcodes, tipos de post, taxonomias, cron e páginas de admin.
2. **Colisões.** Rode `tnc-check --conflicts=<pasta de plugins>`. Todo item "Quebra" precisa de resposta antes do teste manual; "Revisar" e "Atenção" viram casos de teste.
3. **Ativação em pares e todos juntos.** Ative os plugins dois a dois e depois todos; repita invertendo a ordem de ativação (a ordem de carga muda prioridades empatadas e quem registra handle primeiro). Depois de cada ativação, abra uma página de item (L-CON-39) e confira o log de erros do PHP.
4. **Plugin antigo e renomeado.** Se houver renome, confirme que o antigo está desativado (L-CON-43).
5. **Temas.** Repita o roteiro com pelo menos dois temas: Tainacan Interface e um tema de bloco ou Blocksy, mais um tema filho (L-CON-32 a L-CON-38).
6. **Página do item.** Seções e botões de cada plugin no lugar, sem duplicação; mudar a ordem das seções no Personalizar; imprimir (Ctrl+P e folha própria) e conferir que nenhum selo de outro plugin vaza.
7. **Busca facetada.** Todos os modos de visualização (tabela, cartões, masonry, fichas, mosaico, mapa); paginar e trocar de modo, conferindo se o que cada plugin injeta acompanha o item certo; filtro aplicado: números de recortes e gráficos iguais ao total da lista (L-CON-03).
8. **Formulário do item no admin.** Criar item novo e editar existente, navegar entre itens sem recarregar; cada painel aparece só nas coleções que gere (L-CON-17); salvar e conferir que os campos de todos os plugins foram enviados.
9. **Gravação em massa.** Uma importação ou colheita de algumas centenas de itens com todos ativos: sem TypeError, sem cache derrubado a cada item (L-CON-13).
10. **Exclusão.** Apagar um item vinculado a registros de outro plugin (L-CON-14).
11. **Índice compartilhado.** Com o indexador fora do ar ou lento: consumidores degradam para SQL rápido, sem esperar prazos longos (L-CON-04); com outro plugin de busca ativo (L-CON-06).
12. **E-mail.** Disparar um e-mail do plugin e o "Perdeu a senha?" do núcleo, com e sem plugin de SMTP (L-CON-27).
13. **WP-CLI e cron.** `wp eval 'echo 1;'` sem avisos de outros plugins (L-CON-31); `wp cron event list` sem eventos duplicados; rodar os eventos de cada plugin com todos ativos.
14. **Anônimo × logado.** Repetir página do item e busca facetada em janela anônima (cache de página, limites por IP, conteúdo privado) e como editor.
15. **Ambiente de convivência permanente.** Mantenha um site de testes com o conjunto completo de plugins que costumam andar juntos (indexador, recortes, painel, impressão, dados ligados, página de item alternativa) e uma seção "Convivência" de referência no repositório de um deles, atualizada a cada caso novo.

---

## Ferramentas

As duas ferramentas abaixo fazem análise estática: leem o código das subpastas de `<pasta>` que têm cabeçalho `Plugin Name` (ignoram `tests/`, `developer/` e `bin/`) sem carregar o WordPress.

### `tnc-check --inventory=<pasta>`

Lista o que cada plugin registra no WordPress compartilhado.

- `--format=md` (ou `text`): uma tabela com contagens por plugin (ganchos, opções, handles, rotas REST, funções globais, classes, registros no Tainacan) e, por plugin, as rotas REST, shortcodes, páginas de admin, eventos de cron, objetos JS globais e registros no Tainacan (por exemplo `admin_hook:item`, exportadores).
- `--format=json`: o inventário completo, por plugin, com `hooks` (nome, prioridade, trecho do callback e `arquivo:linha`), `removals`, `options`, `transients`, `handles`, `js_globals`, `shortcodes`, `rest`, `post_types`, `taxonomies`, `cron`, `admin_pages`, `functions`, `classes`, `constants` e `tainacan`. É o formato para comparar dois plugins (`jq` sobre os dois slugs) ou guardar como referência entre versões.
- `--output=<arquivo>` grava o relatório. Também aceita a pasta de um plugin só.

### `tnc-check --conflicts=<pasta>`

Cruza o inventário e classifica as colisões. O cabeçalho resume "N colisão(ões) que quebram, N a revisar, N ponto(s) de atenção"; cada item traz o tipo, o nome, o motivo e os plugins com `arquivo:linha`.

- **Quebra:** função global ou classe declarada em dois plugins sem `function_exists`/`class_exists` (erro fatal ou versão errada no autoload); mesmo shortcode, tipo de post, taxonomia, rota REST, slug de página de admin ou objeto JS global em dois plugins; mesmo handle de script ou estilo apontando para arquivos diferentes (o WordPress carrega só o primeiro); `remove_all_actions`/`remove_all_filters` num gancho que outro plugin usa.
- **Revisar:** constante definida em dois plugins; opção ou transient gravado por mais de um plugin; mesmo evento de cron.
- **Atenção:** mais de um plugin no mesmo gancho sensível (`tainacan-*`, `the_content`, `template_include`, `pre_get_posts`, `posts_*`, `rest_*`, `wp_head`, `body_class`, `script_loader_tag`, `query_vars`) e na mesma prioridade: a ordem depende de quem carrega primeiro.
- Código de saída: 2 se houver alguma "Quebra"; 1 se houver "Revisar" (a menos que `--fail-on=block`); 0 caso contrário. Útil em CI de um repositório que agrupa vários plugins.

Como ler o resultado (observado numa pasta real com cerca de 30 plugins, núcleo do Tainacan incluído):
- **Handles iguais com `src` diferente** são o achado mais comum e real: várias cópias de uma biblioteca de gráficos com o mesmo handle (`chartjs`, `chart-js`) em versões e caminhos diferentes. Regra: handle com prefixo do plugin, ou registre a biblioteca com o mesmo handle e versão que os outros e só enfileire.
- **Rotas REST** são comparadas pelo texto da fonte, não pela rota resolvida: `self::NS/collections` ou `$this->namespace/$this->rest_base` em plugins diferentes aparecem como colisão mesmo com namespaces distintos. Confirme o namespace antes de agir.
- **Opções do núcleo** (`active_plugins`, `permalink_structure`, `show_on_front`, `page_on_front`) e constantes como `ABSPATH` aparecem quando um plugin de testes ou ferramenta de laboratório as grava: confirme se o código roda em produção.
- **`removals` do próprio Tainacan** (`admin_notices` nas telas dele) é comportamento intencional do núcleo: trate como L-CON-23, não como bug a corrigir no núcleo.
- Rodar sobre dezenas de plugins pode levar alguns minutos; restrinja a pasta aos plugins que vão conviver.
