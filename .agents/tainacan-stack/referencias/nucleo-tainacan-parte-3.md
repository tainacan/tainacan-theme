# Núcleo do Tainacan, parte 3: lições do modelo de dados e da API

Pegadinhas reais do modelo de dados e da API interna do Tainacan, vistas em plugins que o estendem, conferidas contra a referência do núcleo ([parte 1](nucleo-tainacan-parte-1.md) e [parte 2](nucleo-tainacan-parte-2.md)). Citações `arquivo:linha` relativas a `src/`; **Δ** marca diferença 1.0.3 × 1.4.1. Índice geral em [nucleo-tainacan.md](nucleo-tainacan.md).

Partes: [1: fontes e arquitetura](nucleo-tainacan-parte-1.md) · [2: ganchos, REST, ferramental, desempenho, modelos](nucleo-tainacan-parte-2.md) · [3: lições do modelo de dados e da API](nucleo-tainacan-parte-3.md)

## Sumário

- [Comece por aqui](#comece)
- [Gravar valores e entidades](#gravar) (L-NUC-01 a L-NUC-10)
- [Onde cada dado mora (leitura e SQL)](#onde) (L-NUC-11 a L-NUC-24)
- [Identificar e configurar metadados](#identificar) (L-NUC-25 a L-NUC-31)
- [Documento, anexos e miniatura](#anexos) (L-NUC-32 e L-NUC-33)
- [Ciclo de vida, permissões e exclusão](#ciclo) (L-NUC-34 a L-NUC-37)
- [Código do plugin e bugs de versão](#codigo) (L-NUC-38 a L-NUC-40)

<a id="comece"></a>
## Comece por aqui

Lições vistas em dois ou mais projetos:

- L-NUC-01 · Metadado `required` quebra a criação de item publicado
- L-NUC-02 · Valor tem de seguir a cardinalidade e o primitivo do metadado real
- L-NUC-03 · Toda gravação pelas entidades, num ponto único
- L-NUC-11 · Valor de metadado Taxonomy não está em `wp_postmeta`
- L-NUC-12 · Coleção do metadado está no postmeta `collection_id`, não em `post_parent`
- L-NUC-13 · `post_type => 'any'` não acha itens
- L-NUC-14 · Formato do valor muda conforme o tipo de metadado
- L-NUC-25 · Identificar metadados e coleções por id persistido, nunca por nome
- L-NUC-26 · Mapa de ids em opção precisa de versão e de validação
- L-NUC-32 · `get_attachments()` lista filhos do item e exclui o documento
- L-NUC-34 · Exclusão não se propaga: itens e registros órfãos

---

<a id="gravar"></a>
## Gravar valores e entidades

### L-NUC-01 · Metadado `required` quebra a criação de item publicado
**Sintoma:** com um metadado marcado como obrigatório, testes de sincronização e criação de itens pela REST falham; item novo criado como `publish` não passa na validação.

**Causa:** a interface e a REST do Tainacan publicam o item antes de gravar os valores dos metadados, e a validação do obrigatório recusa o item ainda vazio. A validação só é exigida para status `publish`, `future`, `private` e `pending` (filtro `tainacan-status-require-validation`, `classes/repositories/class-tainacan-repository.php:164-166`); rascunho e `auto-draft` gravam sem validar.

**Correção:** na criação programática, criar como rascunho (`draft`/`auto-draft`), gravar os valores e só publicar no fim. Em plugin que também cria itens, evitar `required => 'yes'` no Tainacan e sinalizar a obrigatoriedade na própria interface (selo no rótulo).

**Detecção:** manual: procurar `set_status('publish')` antes da gravação dos `Item_Metadata_Entity`, e metadados criados com `required => 'yes'` por plugin que também cria itens.

**Vista em:** agregador arquivístico (AtoM/EAD), busca semântica por embeddings.

### L-NUC-02 · Valor tem de seguir a cardinalidade e o primitivo do metadado real
**Sintoma:** campos múltiplos (autoria, classificação, material/técnica) vazios em ~73 mil itens com a coleta dizendo "sucesso"; ISSN vazio por receber lista em campo único; instalações antigas e novas gravando ou lendo errado (texto × taxonomia × relationship; compound × grupo).

**Causa:** `Item_Metadata_Entity::validate()` recusa valor escalar em metadado múltiplo ("%s is invalid") e lista em metadado simples, e `insert()` só aceita entidade validada (`classes/repositories/class-tainacan-item-metadata.php:30-34`). O código decidia o formato por uma constante de esquema, que só descreve instalações novas, e não pelo metadado existente; dois formatos convivem para sempre.

**Correção:** decidir pelo metadado real: `$metadatum->is_multiple()` para a cardinalidade (array para múltiplo, escalar para simples) e `get_primitive_type()` do tipo (A.4) para o formato. Contar falhas de validação por metadado num contador persistente exibido na interface, em vez de só registrar aviso.

**Detecção:** manual: `set_value(` com string sem consultar `$metadatum->is_multiple()`; formato escolhido por constante de esquema sem `get_primitive_type()`.

**Vista em:** agregador de acervos de vários sites, plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-03 · Toda gravação pelas entidades, num ponto único
**Sintoma:** valores gravados violando cardinalidade ou tipo; alterações feitas por um plugin nos metadados do acervo sem histórico, difíceis de auditar.

**Causa:** gravação direta com `update_post_meta($item_id, $metadatum_id, ...)` pula a validação, os ganchos `tainacan-pre-insert-Item_Metadata_Entity`/`tainacan-insert-Item_Metadata_Entity` (`classes/repositories/class-tainacan-item-metadata.php:37-38`, `:116-117`) e o log do Tainacan (gerado a partir de `tainacan-insert`, A.8). Escritas espalhadas pelo código não deixam trilha.

**Correção:** um serviço único de gravação: `Item_Metadata_Entity` + `validate()` + `tainacan_item_metadata()->insert()`, com separador interno canônico para multivalor. O serviço lê o valor antes e depois, grava uma linha append-only com contexto (fluxo, etapa, motivo) e aparece no log de atividade do Tainacan. Metadados sensíveis (título, autor, número de registro) ficam protegidos por padrão, com liberação explícita só por administrador.

**Detecção:** TNC-T05 (gravação sem `validate()`); manual: `update_post_meta($item_id, $metadatum_id, ...)` ou `new \Tainacan\Entities\Item_Metadata_Entity` fora do serviço central.

**Vista em:** colaboração do público em metadados, plugin de processos de gestão de acervo.

### L-NUC-04 · `validate()` antes de `insert()`/`update()`, e falha contada
**Sintoma:** `set_document()` falhava em 100% dos itens coletados e a coleta reportava sucesso.

**Causa:** entidade com status `publish`/`pending`/`private`/`future` sem `validate()` faz `Repository::insert()` lançar `Exception('Entities must be validated before you can save them')` (`classes/repositories/class-tainacan-repository.php:164-166`, 1.0.3 e 1.4.1); `Item_Metadata::insert()` exige validação sempre (`classes/repositories/class-tainacan-item-metadata.php:30-34`). Um `catch` genérico engolia a exceção.

**Correção:** chamar `$item->validate()` e `$item_metadata->validate()` antes de cada `insert()`/`update()`, tratar `false` como falha contabilizada e registrar `get_errors()` (`classes/entities/class-tainacan-entity.php:335`). Lembrar que `insert()` devolve uma nova instância (`classes/repositories/class-tainacan-repository.php:244`).

**Detecção:** TNC-T05.

**Vista em:** agregador de acervos de vários sites.

### L-NUC-05 · Protocolo de gravação de metadado composto (compound)
**Sintoma:** fatal em `Compound::get_value_as_string()`; valores (ISBN, imprenta) "gravados" que nunca entravam, porque `validate()` falhava e devolvia `false` em silêncio.

**Causa:** passar as entidades filhas como "valor" do metadado pai, ou tratar compound/múltiplo como texto simples. No núcleo, o registro pai tem `meta_value = ''` e os filhos se ligam por `parent_meta_id` (`classes/repositories/class-tainacan-item-metadata.php:53-67`, `:250`).

**Correção:** inserir o pai com valor vazio (o `insert()` devolve o `parent_meta_id`), depois cada filho com `set_parent_meta_id()`; múltiplo recebe array; termos recebem lista; registrar o motivo quando `validate()` falha.

**Detecção:** manual: `new Item_Metadata_Entity($item, $compound)` seguido de `set_value()` com array de entidades; retorno de `validate()` ignorado.

**Vista em:** plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-06 · `update()` do item pode zerar termos aplicados antes
**Sintoma:** itens públicos criados por um fluxo de certificação ficavam sem estado/cidade de forma inconsistente.

**Causa:** os termos eram aplicados com `wp_set_object_terms()` antes de `$items_repo->update($saved)`; a entidade regravada não conhecia esses termos e podia apagá-los.

**Correção:** aplicar os termos depois do último `update()` da entidade, ou gravá-los como valor do metadado Taxonomy pelo `Item_Metadata` (que usa `wp_set_object_terms`, `classes/repositories/class-tainacan-item-metadata.php:162-204`).

**Detecção:** manual: `wp_set_object_terms(` seguido, na mesma função, de `->update(` sobre a entidade do mesmo item.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-NUC-07 · Atribuição de termo inexistente falha sem erro
**Sintoma:** risco de o primeiro registro de uma cidade inédita ficar sem cidade para sempre.

**Causa:** o helper do plugin não fazia nada quando o termo não existia na taxonomia: sem erro, sem log. Caminhos diferentes do mesmo plugin se comportavam de modo diferente (um criava, outro não). No núcleo, a criação automática de termos depende da opção `allow_new_terms` do metadado (`classes/repositories/class-tainacan-item-metadata.php:162-204`; opção em A.5).

**Correção:** criar o termo (`wp_insert_term` ou `Terms::insert`) e registrar falha no log, com o mesmo comportamento em todos os caminhos; checar `is_wp_error()`.

**Detecção:** manual: `get_term_by(...)` falso seguido de `return` silencioso, sem criação nem log.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-NUC-08 · Derivar item: copiar ids e entidades, não nomes
**Sintoma:** risco de criar um segundo registro de autoridade ou termo ao duplicar uma obra.

**Causa:** reescrever os valores pelo nome passa por "achar ou criar", que pode criar duplicata.

**Correção:** percorrer os metadados da coleção (não uma lista fixa), copiar ids de Relationship, passar entidades `Term`, copiar compound grupo a grupo, deixar campos voláteis vazios e só gravar o campo de vínculo se ele existir no mapeamento. A REST tem rota própria de duplicação (`/collection/{cid}/items/{id}/duplicate`, `class-tainacan-rest-items-controller.php:56-210`).

**Detecção:** manual: revisar a rotina de duplicação.

**Vista em:** plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-09 · Tipo de metadado não se troca no mesmo registro
**Sintoma:** impossível mudar um metadado de texto para data ou taxonomia mantendo o registro.

**Causa:** o tipo do metadado é estrutural: os valores ficam em lugares diferentes conforme o tipo (postmeta para texto e data, `wp_term_relationships` para taxonomia, A.6).

**Correção:** mandar o antigo para a lixeira (renomeado, por exemplo `<tag>:antigo`), criar o novo, reler os valores da origem e gravar a ordem dos metadados na coleção.

**Detecção:** manual: migração de esquema que altera `metadata_type` de metadado existente.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-NUC-10 · O Tainacan não move itens entre coleções
**Sintoma:** item deduplicado ficou numa coleção diferente da roteada.

**Causa:** o post type do item é o da coleção (`tnc_col_{id}_item`, `classes/entities/class-tainacan-collection.php:119-169`; definido no `insert()`, `classes/repositories/class-tainacan-repository.php:198-210`).

**Correção:** registrar aviso; roteamento sem reserva (sem rota, o item é bloqueado com erro explícito, nunca vai para uma coleção padrão). Mudar `post_type` à mão não leva metadados nem permissões.

**Detecção:** manual: `wp_update_post` alterando `post_type` de item `tnc_col_*_item`.

**Vista em:** importador de pacotes de preservação (DIP).

---

<a id="onde"></a>
## Onde cada dado mora (leitura e SQL)

### L-NUC-11 · Valor de metadado Taxonomy não está em `wp_postmeta`
**Sintoma:** colunas de estado, cidade e povos saíam vazias embora o dado existisse em ~98% dos itens; um critério de "mais usos em `wp_postmeta`" escolheria o metadado errado para autoria/classificação/material.

**Causa:** o valor de Taxonomy vai para `wp_term_relationships` via `wp_set_object_terms` (`classes/repositories/class-tainacan-item-metadata.php:162-204`); os demais tipos ficam em `wp_postmeta` com `meta_key` = id do metadado (`:62-109`). Ler a taxonomia por `get_post_meta($id, 'taxonomy_id')` devolve vazio sempre (A.5).

**Correção:** resolver Taxonomy pela taxonomia `tnc_tax_{id}`, com o id em `_option_taxonomy_id` ou em `get_metadata_type_options()['taxonomy_id']`. Não confiar em `_option_taxonomy`: é derivado e pode estar vazio ou desatualizado (A.5). Para medir uso, `term_taxonomy.count` em Taxonomy e `COUNT` em postmeta nos demais. Ver também: L-DES em desempenho-e-escala.md (`count` de termo não serve para contagem por coleção).

**Detecção:** TNC-T04 (chave sem `_option_`); manual: `get_post_meta($item_id, $metadatum_id)` ou JOIN/`COUNT` em postmeta para metadado do tipo Taxonomy.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan, plugin de agendamento, plugin de dados ligados e enriquecimento.

### L-NUC-12 · Coleção do metadado está no postmeta `collection_id`, não em `post_parent`
**Sintoma:** descoberta de metadados por coleção deixava de fora filhos de compostos; invalidação de cache aplicada à coleção errada.

**Causa:** a propriedade `parent` do metadado mapeia para `post_parent` e é o metadado composto pai (padrão 0) (`classes/repositories/class-tainacan-metadata.php:72-77` [1.0.3]); a coleção é `collection_id`, `'map' => 'meta'` (`:168-173` [1.0.3]), com `'default'` para metadados do repositório (`:22` [1.0.3]).

**Correção:** JOIN em postmeta `collection_id` (incluindo `'default'`) em vez de filtrar `post_parent IN (0, coleções)`; ou usar `Metadata::fetch_by_collection()`, que sobe a hierarquia de coleções (`classes/repositories/class-tainacan-metadata.php:426-447`).

**Detecção:** manual: SQL ou `WP_Query` sobre `tainacan-metadatum` filtrando a coleção por `post_parent`.

**Vista em:** plugin de dados ligados e enriquecimento, painel público de indicadores.

### L-NUC-13 · `post_type => 'any'` não acha itens
**Sintoma:** contagem de obras ligadas a uma autoridade dava zero e uma união de duplicatas quase apagou o vínculo sem reapontar obra alguma; busca de item por meta não encontrava itens existentes.

**Causa:** os CPTs `tnc_col_{id}_item` são `exclude_from_search => true` (`classes/entities/class-tainacan-collection.php:131-162`), e o `'any'` do WordPress ignora post types excluídos da busca.

**Correção:** listar explicitamente os post types das coleções (`tnc_col_*_item`), ou usar `tainacan_items()->fetch()` sem coleção, que consulta todos e respeita permissão (`classes/repositories/class-tainacan-items.php:311-361`). Em SQL direto, ignorar `trash` e itens órfãos (L-NUC-34).

**Detecção:** TNC-T09.

**Vista em:** plugin de biblioteca (exemplares, circulação, MARC21), importador de pacotes de preservação (DIP).

### L-NUC-14 · Formato do valor muda conforme o tipo de metadado
**Sintoma:** leitura de autor devolvia números em vez de nomes; conversões erradas ao ler e gravar.

**Causa:** cada tipo tem formato próprio: Relationship (`primitive_type = 'item'`) guarda o id do post como string e preserva a ordem de gravação, removendo duplicatas (`classes/repositories/class-tainacan-item-metadata.php:94-110`); Taxonomy volta como `\Tainacan\Entities\Term`, em ordem alfabética; GeoCoordinate é `"lat,long"`; Date só aceita `Y-m-d`; campo checkbox de gancho de formulário chega como array (`['1']`).

**Correção:** resolver ids em títulos num helper único; na escrita, converter nome em id (achar ou criar); tratar cada formato explicitamente; leitores aceitam as duas formas de checkbox.

**Detecção:** manual: `get_post_meta` de Relationship usado como texto; comparação `=== '1'` sobre checkbox de gancho; data em formato diferente de `Y-m-d`.

**Vista em:** busca semântica por embeddings, plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-15 · Título e descrição também são postmeta com chave = id do metadado
**Sintoma:** (achado útil) `metaquery` com `LIKE` pelo id do metadado funciona para título e descrição.

**Causa:** os metadados núcleo são gravados também como meta comum, para `meta_query` funcionar (`classes/repositories/class-tainacan-item-metadata.php:44-49`), além de `post_title`/`post_content`.

**Correção:** nenhuma; aproveitar. Para busca avançada sem coleção, as chaves especiais `tainacan_core_title`/`tainacan_core_description` expandem para cada coleção, com custo (E.11 na parte 2).

**Detecção:** não se aplica.

**Vista em:** plugin de busca segmentada ("onde o termo foi achado").

### L-NUC-16 · Slug da taxonomia do Tainacan fica em `post_name`, não em meta
**Sintoma:** a instalação duplicava todo o conjunto de taxonomias a cada execução.

**Causa:** a checagem de reuso lia um postmeta `_taxonomy_slug` que nada grava. A taxonomia do Tainacan é um post `tainacan-taxonomy` (`classes/entities/class-tainacan-taxonomy.php:30`); o slug da entidade é o `post_name`, e a taxonomia do WordPress registrada é `tnc_tax_{id}` (`:62-100`).

**Correção:** reuso em três níveis: opção com o id gravado → `post_name` → nome de exibição, aceitando variantes `-2` legadas.

**Detecção:** manual: `get_post_meta($id, '_taxonomy_slug'...)` em post `tainacan-taxonomy`.

**Vista em:** plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-17 · Slug de metadado recebe sufixo numérico
**Sintoma:** mapeamento por slug só funcionava em uma coleção.

**Causa:** metadado é post (`tainacan-metadatum`); slug repetido ganha sufixo (`titulo-49` ... `titulo-62`, `produtor-2`), como em qualquer post do WordPress.

**Correção:** mapear por id (L-NUC-25); se precisar comparar slug, normalizar (remover acento e sufixo `-N`).

**Detecção:** manual: comparação de `get_slug()` de metadatum com literal exato (`=== 'titulo'`).

**Vista em:** kit de memorial virtual.

### L-NUC-18 · Conferir todos os status presentes, não só `publish`
**Sintoma:** exportação saía com 234 de 236 registros, sem erro; só a soma por status revelou a falta.

**Causa:** a lista de status "reais" só tinha `publish`; havia itens com status `public`. O núcleo não registra status `public` (nenhum `register_post_status` na 1.0.3); ele usa `get_post_stati(['public' => true])` para os status públicos (`classes/repositories/class-tainacan-items.php:445` [1.0.3]). Um status fora do padrão veio dos dados.

**Correção:** listar os status existentes no banco (`SELECT post_status, COUNT(*) ... GROUP BY`), usar `get_post_stati(['public' => true])` quando a intenção é "público", e conferir a identidade `soma por status = total`.

**Detecção:** manual: consulta a `tnc_col_*_item` com `post_status = 'publish'` fixo, sem conferência da soma.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-NUC-19 · `value_as_string` de metadado múltiplo usa ` | ` como separador
**Sintoma:** um campo de artista com 22 nomes virou um único termo de 383 caracteres, e `wp_insert_term()` falhou calado.

**Causa:** a API do Tainacan junta multivalores em `value_as_string` com o separador padrão ` | ` (no HTML, `<span class="multivalue-separator"> | </span>`, filtrável por `tainacan-item-metadata-get-multivalue-separator`, `classes/entities/class-tainacan-item-metadata-entity.php:125`, `:136` [1.0.3]; `:139` [1.4.1]). O importador só reconhecia `;`. `wp_terms.name` tem limite de 200 caracteres.

**Correção:** preferir o campo estruturado `value` a `value_as_string`; se tiver de dividir, aceitar `;` e `|` (respeitando o separador configurado); checar `is_wp_error()` de `wp_insert_term()`.

**Detecção:** manual: `explode(` sobre `value_as_string` sem considerar `|`; `wp_insert_term(` sem `is_wp_error`.

**Vista em:** agregador de acervos de vários sites.

### L-NUC-20 · Documento do item pode vir como `{type, value}`
**Sintoma:** o documento importado era a miniatura (290x195 em vez de 5038x3403).

**Causa:** o extrator só entendia documento como string; algumas fontes entregam objeto `{type, value}`. O documento tem tipo (`document_type`: `attachment`, `url`, `text`) além do valor (`tainacan_get_the_document_type`, `classes/theme-helper/template-tags.php:154`).

**Correção:** normalizar as duas formas e só cair para a miniatura quando não houver documento.

**Detecção:** manual: `['document']` tratado como string sem ramo para array ou objeto.

**Vista em:** agregador de acervos de vários sites.

### L-NUC-21 · Mudar termo não altera `post_modified`
**Sintoma:** reconstrução incremental perdia itens cujos termos mudaram.

**Causa:** `wp_set_object_terms` não toca o post; sincronização baseada só em `post_modified` não vê mudança de termo.

**Correção:** fila própria de pendentes alimentada por `set_object_terms` (ou pelos ganchos `tainacan-insert-Item_Metadata_Entity`, B.5), além do `post_modified`; reconstrução em tabelas-sombra trocadas por um único `RENAME TABLE`; contadores incrementais atômicos (`df + 1`, `GREATEST(0, df - 1)`) no `shutdown`.

**Detecção:** manual: sincronização incremental filtrando só `post_modified >` sem ouvir `set_object_terms`.

**Vista em:** busca semântica por embeddings.

### L-NUC-22 · Achatamento de valor com guarda de existência
**Sintoma:** fatal 500 ao listar registros por causa de uma única linha órfã.

**Causa:** `(string) $value` e `implode()` aplicados direto ao retorno de `get_value()`, que pode ser entidade (`Term`, item relacionado) ou apontar para post inexistente.

**Correção:** helper único de achatamento que trata escalar, lista, `Term` e id de item, com guarda de existência.

**Detecção:** manual: `(string)` ou `implode()` sobre retorno de `get_value()` de `Item_Metadata_Entity`.

**Vista em:** plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-23 · `REGEXP_SUBSTR` do MariaDB devolve `''`, não `NULL`
**Sintoma:** datas não agrupadas; "NULLs" que não eram NULL.

**Causa:** numa tabela de índice própria, a coluna `value_date` nunca era preenchida pelo indexador, e o `REGEXP_SUBSTR` do MariaDB devolve string vazia quando não casa.

**Correção:** agrupar por ano em PHP a partir do valor textual; no SQL, `NULLIF(REGEXP_SUBSTR(...), '')`. Lembrar que Date do Tainacan guarda `Y-m-d` (L-NUC-14).

**Detecção:** manual: `REGEXP_SUBSTR(` sem `NULLIF` em agrupamento ou contagem.

**Vista em:** painel público de indicadores.

### L-NUC-24 · `get_term(0)` e id 0 como "não mapeado"
**Sintoma:** "Termo não encontrado" só numa linha (instituição).

**Causa:** o campo vinha de metadado de texto, não de taxonomia, e viajava com `term_id = 0`. Id de metadado `0` também significava "não mapeado", e o valor podia vir como lista.

**Correção:** chave própria estável (id numérico do item) derivada no servidor; tratar `0` como ausente; normalizar valor-lista.

**Detecção:** manual: `get_term( $id )` sem guarda `$id > 0`.

**Vista em:** plugin de dados ligados e enriquecimento.

---

<a id="identificar"></a>
## Identificar e configurar metadados

### L-NUC-25 · Identificar metadados e coleções por id persistido, nunca por nome
**Sintoma:** agregação por instituição vinha vazia e um mecanismo de rebaixamento virou no-op silencioso por meses; um painel mostrava e-mail para 27 de 143 itens embora o dado existisse em quase todos; um plugin lia "Nome da Instituição" vazio; autodescoberta de coleções quebraria em outro idioma.

**Causa:** nomes de metadado e de coleção não são únicos nem estáveis: havia dois metadados homônimos (um com 74 mil usos, outro Taxonomy com zero), ou dois campos de mesmo rótulo em coleções diferentes; o mapa `título → id` sobrescrevia em colisão e a consulta não tinha `ORDER BY`. Nomes de coleção são traduzíveis e editáveis.

**Correção:** mapear por id (e coleção) em tela de configuração, guardado em opção; descoberta por título só como reserva, só metadados `publish`, com desempate pelo uso real medido onde o valor mora (L-NUC-11), e empate pelo menor id. Fallback explícito entre campos homônimos, documentado. Coleções por id ou slug persistido em opção, nunca por nome traduzido.

**Detecção:** manual: array indexado por `post_title` montado em laço sem checar colisão; SELECT de metadados sem `ORDER BY`; busca de coleção por `__('...')`.

**Vista em:** plugin de dados ligados e enriquecimento, plataforma de mapeamento comunitário sobre Tainacan, plugin de impressão de itens, plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-26 · Mapa de ids em opção precisa de versão e de validação
**Sintoma:** mudanças de nome, ajuda, seção ou tipo dos campos não chegavam a instalações existentes; perfis públicos desiguais, com 138 valores gravados que nunca apareciam.

**Causa:** estrutura criada só na primeira execução, com o mapa de ids guardado em opção sem versão; seis opções tinham id de metadado errado por uma unidade, e os valores iam para chaves órfãs enquanto os ids reais ficavam vazios.

**Correção:** esquema declarativo com `VERSION`; ao mudar, uma rotina de atualização põe em dia nome, orientação, seção e ordem de todos os campos e invalida o mapa. Na leitura e na gravação, validar que o id configurado existe, é `tainacan-metadatum` e pertence à coleção esperada; migrar valores das chaves erradas.

**Detecção:** manual: opção com mapa de ids sem chave de versão; `get_option('*_meta_*_id')` usada em gravação sem checar `get_post_type($id) === 'tainacan-metadatum'`.

**Vista em:** agregador arquivístico (AtoM/EAD), plataforma de mapeamento comunitário sobre Tainacan.

### L-NUC-27 · Metadado privado não vem em `fetch_by_collection()` sem a capability
**Sintoma:** um plugin gravava vínculos num metadado que "não existia" ao listar os metadados da coleção; dados invisíveis e difíceis de auditar.

**Causa:** a migração do plugin criou um metadado **privado**. Sem `post_status` nos argumentos, `Metadata::fetch_by_collection()` só inclui os privados se o usuário atual tem `tnc_col_{id}_read_private_metadata` (`classes/repositories/class-tainacan-metadata.php:445-456` [1.0.3]); em cron, CLI ou visitante, eles somem.

**Correção:** passar `post_status` explícito (incluindo `private`) ao procurar metadados criados pelo próprio plugin; documentar quais metadados o plugin cria; ao limpar dados de demonstração, remover só os vínculos criados por ela.

**Detecção:** manual: `fetch_by_collection(` sem `post_status` em código que procura metadado próprio.

**Vista em:** plugin de processos de gestão de acervo.

### L-NUC-28 · Dois metadados da mesma coleção não podem usar a mesma taxonomia
**Sintoma:** dois campos na mesma taxonomia se contaminam, e o `wp_set_object_terms` de um apaga os termos do outro.

**Causa:** `wp_term_relationships` liga item e termo sem a dimensão do metadado (issue #327 do Tainacan); o Tainacan proíbe esse arranjo.

**Correção:** para autoria compartilhada entre vários campos, usar metadado Relationship apontando para uma coleção de autoridades (recomendação dos mantenedores): um registro por identidade, referenciado por N campos.

**Detecção:** manual: dois metadados da mesma coleção com `_option_taxonomy_id` iguais.

**Vista em:** plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-29 · Restrições de taxonomia dentro de compound
**Sintoma:** não era possível pôr editora/local como taxonomia dentro de um compound de imprenta.

**Causa:** taxonomia só entra em compound **não** múltiplo, e o não múltiplo exige entrada radio (inviável para centenas de valores); compound múltiplo não aceita taxonomia.

**Correção:** trocar o compound por um "grupo": filhos independentes sem pai (mapeamento só com `children`), com filhos-taxonomia em tag-input.

**Detecção:** manual: revisar o esquema de compounds com filhos Taxonomy.

**Vista em:** plugin de biblioteca (exemplares, circulação, MARC21).

### L-NUC-30 · Código único pela coleção (`collection_key`)
**Sintoma:** códigos de referência repetidos.

**Causa:** metadado sem restrição de unicidade.

**Correção:** `collection_key = yes` no metadado (o Tainacan recusa valor repetido na tela); gerar código para descrições locais; listar repetições vindas da origem para curadoria.

**Detecção:** manual: metadado de código sem `collection_key`.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-NUC-31 · Slug de coleção não ASCII quebra a página pública do item
**Sintoma:** página do item inacessível.

**Causa:** caractere como "·" no slug da coleção, usado em `rewrite.slug` do CPT de itens (`classes/entities/class-tainacan-collection.php:131-162`).

**Correção:** forçar slug ASCII (`sanitize_title` com transliteração) ao criar coleções.

**Detecção:** manual: `set_slug(` de coleção sem `sanitize_title`.

**Vista em:** busca semântica por embeddings.

---

<a id="anexos"></a>
## Documento, anexos e miniatura

### L-NUC-32 · `get_attachments()` lista filhos do item e exclui o documento
**Sintoma:** uma narrativa em áudio "se alimentaria" do próprio áudio gerado; o slide do documento caía no lightbox nativo, com botão de baixar, furando a política do plugin.

**Causa:** `Item::get_attachments()` lista todo anexo com `post_parent` = item e, sem `$exclude`, exclui a imagem destacada e o documento quando `document_type == 'attachment'` (`classes/entities/class-tainacan-item.php:90-113` [1.0.3]). Arquivo gerado como filho do item vira anexo; o documento fica fora da lista e seu slide não carrega id.

**Correção:** arquivo derivado vai para a biblioteca de mídia com `post_parent = 0` e uma marca própria em meta; só apagar anexos com a marca. Para galeria completa, acrescentar o documento quando `document_type = attachment` e resolver o slide por índice; documento `url`/`text` fica de fora. O filtro `tainacan-item-get-attachments` permite ajustar a lista.

**Detecção:** manual: `wp_insert_attachment(..., $item_id)` ou `media_handle_sideload(..., $item_id)` para arquivo derivado.

**Vista em:** plugin de narrativas/exposições virtuais, kit de memorial virtual.

### L-NUC-33 · Miniatura: derivar do documento e não confundir com "sem anexo"
**Sintoma:** itens sem miniatura nas visualizações próprias; duplicatas diagnosticadas como "sem arquivo" porque a miniatura estava cinza.

**Causa:** miniatura lida só da imagem destacada; em servidor sem ImageMagick/Ghostscript, PDFs não geram miniatura, mas o anexo existe.

**Correção:** derivar a miniatura do documento (o núcleo tem o filtro `tainacan-get-thumbnail-id-from-document`, `classes/repositories/class-tainacan-items.php:686`); conferir os anexos reais antes de concluir que falta arquivo.

**Detecção:** manual: `get_post_thumbnail_id($item_id)` sem reserva.

**Vista em:** página pública do item por tipo de mídia, importador de pacotes de preservação (DIP).

---

<a id="ciclo"></a>
## Ciclo de vida, permissões e exclusão

### L-NUC-34 · Exclusão não se propaga: itens e registros órfãos
**Sintoma:** itens órfãos (`tnc_col_{id}_item` de coleção inexistente) somando no banco e nas buscas; dezenas de avaliações de outro plugin mostrando "N/A" no nome da instituição.

**Causa:** apagar a coleção não apaga de imediato os posts dos itens; apagar o item remove o postmeta dele, mas não os registros de outro plugin que apontam para ele pelo id.

**Correção:** rotina de manutenção que localiza e exclui órfãos em lotes (anexos e arquivos inclusive); consultas ignoram órfãos (JOIN com coleção existente). No plugin que guarda id de item, gravar um retrato dos dados de exibição (por exemplo o nome) ao criar o vínculo, ouvir `tainacan-pre-delete`/`tainacan-deleted` (`classes/repositories/class-tainacan-repository.php:679-680`, `:695-696`) ou `before_delete_post`, e oferecer exclusão pelo próprio plugin; não ocultar órfãos que guardam dados válidos.

**Detecção:** manual: consulta por `tnc_col_%_item` sem JOIN com coleção; plugin que guarda id de item sem gancho de exclusão.

**Vista em:** importador de pacotes de preservação (DIP), plataforma de mapeamento comunitário sobre Tainacan.

### L-NUC-35 · Item restaurado da lixeira volta como rascunho
**Sintoma:** item "some" do público depois de restaurado.

**Causa:** desde o WordPress 5.6, restaurar da lixeira devolve `draft`.

**Correção:** na reingestão, republicar só `draft → publish`, preservando `private`/`pending`; lembrar que publicar exige `validate()` (L-NUC-04).

**Detecção:** manual: rotina de restauração sem tratar o status.

**Vista em:** importador de pacotes de preservação (DIP).

### L-NUC-36 · Item só entra num fluxo do plugin se o usuário puder lê-lo
**Sintoma:** risco de vincular ou expor itens de coleção privada ou rascunhos.

**Causa:** rotas aceitavam ids de item sem checar a permissão do Tainacan.

**Correção:** todo caminho (id, campo, vínculo, checagem) passa por `$item->can_read()` (`classes/entities/class-tainacan-entity.php:422-452`); valores de metadado só se o metadado for legível. `Items::fetch()` com id numérico já devolve `[]` para coleção não legível (`classes/repositories/class-tainacan-items.php:282-299`).

**Detecção:** TNC-S04 (permissão da rota); manual: rota que recebe `item_id`/`ids` e chama `Items::fetch` sem `can_read()`.

**Vista em:** plugin de processos de gestão de acervo.

### L-NUC-37 · Fluxo privado em CPT próprio, publicação no Tainacan
**Sintoma:** necessidade de confidencialidade e de versões que a permissão por coleção não cobre.

**Causa:** o Tainacan é camada pública (busca, facetas, OAI); o modelo de capabilities é por coleção (A.9).

**Correção:** CPTs privados para o fluxo editorial e item Tainacan só na publicação; coleção provisionada de forma idempotente, com os ids de metadados em opções versionadas (L-NUC-26).

**Detecção:** manual: revisão de arquitetura.

**Vista em:** gestor de periódicos.

---

<a id="codigo"></a>
## Código do plugin e bugs de versão

### L-NUC-38 · Bugs do Tainacan 1.1.0: lista em branco e fatal em coleção grande
**Sintoma:** (a) lista de itens do admin vazia, sem erro nem requisição, nas visualizações cards/masonry; (b) `Call to a member function get_filters_order() on array` em `class-tainacan-rest-items-controller.php` numa coleção grande.

**Causa:** (a) `loadItems()` só busca se `fetch_only` não for nulo, e `prepareMetadata()` limpa esse campo; nos modos cards/masonry o bloco que o repovoa é pulado. (b) não diagnosticada além do ponto do fatal. Observado na 1.1.0; não conferido na pesquisa do núcleo (1.0.3 e 1.4.1).

**Correção:** contorno para (a): trocar a visualização uma vez (fica salva em `tainacan_prefs`); para (b), manipulador de `shutdown` no plugin chamador. Reportar ao núcleo.

**Detecção:** manual: reproduzir na versão em uso.

**Vista em:** agregador de acervos de vários sites.

### L-NUC-39 · Herança na leitura com risco de recursão
**Sintoma:** quase laço infinito.

**Causa:** um caminho legado de "valores próprios do termo" chamava "contexto do termo", que herdaria via "termo dono" e voltava a "valores próprios".

**Correção:** a checagem "tem vínculo próprio" lê a chave escalar direto; escrita só pelo caminho "próprio".

**Detecção:** manual: revisar funções de herança mutuamente recursivas.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-NUC-40 · Chaves de status diferentes entre classes zeram contadores
**Sintoma:** soma de filas sempre zero.

**Causa:** uma classe usava `aprovado`/`recusado`, outra `aprovada`/`recusada`.

**Correção:** constantes compartilhadas de status.

**Detecção:** manual: literais de status com variação de gênero ou plural no mesmo plugin.

**Vista em:** plugin de dados ligados e enriquecimento.
