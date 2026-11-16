# Migração de acervos para o Tainacan (parte 1 de 2)

Lições para levar um acervo de qualquer origem (planilha, banco legado, DSpace, OJS, AtoM, pacotes de preservação, outro Tainacan) até o Tainacan sem alterar um único valor que o catálogo de origem registrou, e para provar isso com contagens.

**Princípio central:** o Tainacan recebe o que a origem documentou, exatamente. Caixa, espaços, pontuação, duplicados, siglas, não-valores ("s/d", "Não identificado") e até erros evidentes são documentação do acervo; normalizar, expandir ou "corrigir" é decisão de curadoria, tomada por escrito e aplicada como passo separado e reversível. Toda etapa (extração, arquivo gerado, banco, índice) precisa de uma contagem que feche com a anterior.

Este tema está dividido em dois arquivos; a numeração das lições continua de um para o outro. Parte 1: L-MIG-01 a 33. [Parte 2](migracao-de-acervos-parte-2.md): L-MIG-34 a 54 e o checklist de importação.

## Sumário

1. [Fidelidade à origem](#1-fidelidade-à-origem) (L-MIG-01 a 05)
2. [Reconciliação e verificação](#2-reconciliação-e-verificação) (L-MIG-06 a 10)
3. [Como o Tainacan grava o que você importa](#3-como-o-tainacan-grava-o-que-você-importa) (referência do núcleo + L-MIG-11 a 16)
4. [Escrita em massa no banco](#4-escrita-em-massa-no-banco) (L-MIG-17 a 28)
5. [Identidade e deduplicação](#5-identidade-e-deduplicação) (L-MIG-29 a 33)

Na [parte 2](migracao-de-acervos-parte-2.md): 6. Coleta e importação retomável (34 a 38); 7. Datas e fusos (39 a 40); 8. Enriquecimento com dados ligados (41 a 44); 9. IA na catalogação (45 a 46); 10. Sistemas de origem (47 a 53); 11. Processo (54); 12. Checklist de importação.

## Comece por aqui

- **L-MIG-01**: o gerador do CSV é o lugar onde a integridade mais se perde (caixa, espaços, `array_unique`, títulos inventados).
- **L-MIG-06**: reconcilie origem × arquivo gerado × banco × índice, separadamente.
- **L-MIG-11** e **L-MIG-12**: o próprio núcleo grava `>` como `&gt;`, `&` como `&amp;`, apara espaços e **corta o texto a partir de um `<` solto**.
- **L-MIG-13**: com a opção padrão `encode = automatic`, o importador CSV esvazia valores de um arquivo Latin-1.
- **L-MIG-14**: multivalor `||`, hierarquia `>>`, composto `Nome|compound(...)` e o separador de exibição `' | '`.
- **L-MIG-17**: nunca `UPDATE ... REPLACE` de texto em `wp_postmeta` (PHP serializado corrompido em silêncio).
- **L-MIG-29**: identidade por marca no próprio item e chave natural; duplicata visível é melhor que sobrescrita silenciosa.
- **L-MIG-02** e **L-MIG-42** (parte 2): não inventar fato (siglas só na exibição; reconciliação nunca autoaceita).
- **L-MIG-39** (parte 2): datas no fuso do site; `Y-m-d` lido como meia-noite UTC vira o dia anterior.

---


## 1. Fidelidade à origem

### L-MIG-01 · Gerador de CSV de migração alterando valores
**Sintoma:** a extração bate com o banco de origem (md5 linha a linha), mas o CSV final difere: caixa trocada, ponto final removido, espaços e NBSP limpos, valores repetidos sumidos, vazios e "valores de teste" descartados, itens sem título recebendo "[sem título]".

**Causa:** o script que monta o arquivo de importação "ajuda": `ucfirst`/`mb_convert_case`, `trim`, `preg_replace` de espaços, `array_unique`, filtros de "lixo" e defaults inventados. Cada um parece inofensivo; juntos, reescrevem o catálogo.

**Correção:** exportar fiel. Nenhuma transformação sem estar numa lista escrita e aprovada pela curadoria, cada uma como passo separado, nomeado e reversível (entrada e saída guardadas). Rodar um script de auditoria que compara origem × saída célula a célula (por chave natural, não por número de linha) e lista toda diferença. Repetidos dentro de um multivalor são dado (dois exemplares, duas autorias iguais): manter. Item sem título fica sem título, ou recebe um título derivado declarado como tal num metadado próprio. Atenção: o próprio núcleo apara e altera texto ao gravar (L-MIG-11, L-MIG-12); a auditoria final precisa conhecer essas regras para não acusar falso positivo nem esconder perda real.

**Detecção:** manual: procurar `array_unique(`, `trim(`, `ucfirst(`, `mb_convert_case(`, `strtolower(`, `preg_replace('/\s+/'` e literais como `'[sem título]'` em scripts de exportação; exigir o relatório de auditoria origem × saída com zero diferenças não aprovadas.

**Vista em:** migração de repositório DSpace; migração de sistema legado de acervo museológico.

### L-MIG-02 · Expandir siglas e códigos só na exibição, sem inventar
**Sintoma:** tentação (e protótipos) de gravar a expansão de siglas de estado de conservação, acrescentar unidade de medida que o museu não registrou, expandir "BC" com o significado errado; narração automática lendo "Fulano L." como "Fulano cinquenta", inicial de nome como "rua", "cap." como "capítulo" fora de contexto.

**Causa:** inferência tratada como documentação. Tabelas de abreviações e de algarismos romanos aplicadas sem contexto.

**Correção:** o valor gravado é o da origem. Expansão só na exibição, só para sigla exata de uma tabela fechada, com o original entre parênteses, desligável por filtro. Não acrescentar unidade quando a maioria dos registros não a traz; texto livre fica intocado. Para geração de texto: só abreviações inequívocas; tokens de 1 letra nunca; abreviações numéricas só antes de número; siglas de estado só depois de "(", "-" ou "/"; romanos depois de nome só com 2+ letras e valor ≤ 40. Pergunta-teste antes de incluir uma regra: "isto pode ser inicial, sigla ou palavra?" Se pode, não entra.

**Detecção:** manual: procurar `update_post_meta`/`set_value` cujo valor sai de uma tabela de siglas; a expansão deve estar só em filtro de exibição (`tainacan-item-metadata-get-value-as-html--type-*` ou equivalente).

**Vista em:** plugin de dados ligados e enriquecimento; plugin de narrativas/exposições virtuais; agregador nacional de acervos (~74 mil itens).

### L-MIG-03 · Não-valores: lista explícita, ignorados na leitura, nunca apagados
**Sintoma:** itens "relacionados" ou "semelhantes" só por compartilharem "Em pesquisa", "Não identificado", "s/d", "[sem informação]", "Ignorado", "Desconhecido"; polos duplicados num agregador (museu = coleção = cidade); tentação de apagar os não-valores do catálogo.

**Causa:** filtro heurístico (`LIKE '%identificad%'`) deixa escapar variantes; placeholders catalográficos e campos redundantes tratados como características.

**Correção:** lista explícita, ampla e filtrável de não-valores, comparada depois de normalização (fingerprint no estilo OpenRefine: minúsculas, sem acento, sem pontuação, tokens ordenados). Períodos incertos ("195-", "18--", "[196-?]", "séc. XIX") reconhecidos como tais. Para semelhança: descartar campos de texto com mais de 90% de valores únicos e características com frequência acima de 5%; colapsar atributos com a mesma cobertura. Os não-valores continuam gravados: são ignorados na renderização e nos cálculos, não removidos.

**Detecção:** manual: procurar filtros de não-valor por `LIKE '%identificad%'` isolado ou listas de 2 ou 3 itens; conferir se existe um filtro (`apply_filters`) para estender a lista.

**Vista em:** plugin de dados ligados e enriquecimento; busca semântica por embeddings.

### L-MIG-04 · Data do documento é gravada uma vez; regerar não muda a data
**Sintoma:** regerar um certificado ou comprovante reescrevia a data para "hoje".

**Causa:** `date_i18n()`/`wp_date()` chamados no momento de escrever o arquivo.

**Correção:** gravar a data de emissão num meta uma única vez; regeração preserva data e código. Backfill idempotente, com a fonte validada contra os arquivos já emitidos. Para marcar "desatualizado", guardar um hash dos dados usados na geração; sem hash, não chutar. Vale para migração: data de catalogação, de aquisição ou de publicação vem da origem, nunca da data em que o importador rodou (`post_date` do item importado é data de importação, não do acervo).

**Detecção:** manual: `date_i18n(`, `wp_date(` ou `date(` dentro do gerador de documento sem leitura de meta de data persistida; em importadores, metadado de data preenchido com `current_time`.

**Vista em:** plugin de eventos e certificados em PDF; plataforma de mapeamento comunitário sobre Tainacan.

### L-MIG-05 · Entradas controladas: casar com a forma já cadastrada, não inventar forma nova
**Sintoma:** o mesmo autor entra de várias maneiras quando os dados vêm de fontes externas.

**Causa:** valores gravados como vieram, sem comparação com o vocabulário existente.

**Correção:** casar em níveis: exato → normalizado → tokens ordenados → fuzzy (este só gera alerta, nunca decide). A forma já cadastrada vence. Entradas novas passam como digitadas, sem Title Case inventado. A fila de revisão precisa de tela: um alerta guardado numa option que nenhuma tela lia ficou invisível.

**Detecção:** manual: conferir que o casamento fuzzy não grava sozinho e que toda fila de revisão tem tela de consumo.

**Vista em:** plugin de biblioteca.

---

## 2. Reconciliação e verificação

### L-MIG-06 · Reconciliar contagens: origem × arquivo gerado × banco × índice
**Sintoma:** "a contagem não bate" sem saber onde; números de distintos diferentes entre o banco e o índice; cards de painel que não somam o total.

**Causa:** cada etapa tem sua própria regra de contagem e cada fonte de leitura pode ser outra (a listagem pode vir do índice e o filtro do SQL). `GROUP BY` no MariaDB/MySQL usa a collation da coluna (normalmente ignora caixa e acento); `keyword` do Elasticsearch/OpenSearch compara bytes. Diferença origem × banco é do importador ou do agregador, não do índice.

**Correção:** medir cada par separadamente, com a mesma definição de universo (status, coleção, lixeira): (1) registros na origem; (2) linhas e valores no arquivo gerado; (3) itens e valores no banco (`wp_posts` do CPT `tnc_col_{id}_item`, `wp_postmeta` com `meta_key` = id do metadado, `wp_term_relationships`); (4) documentos no índice. Particionar: cada registro em exatamente um balde (importado, duplicata, pulado com motivo, falhou com motivo), e a soma dos baldes = total; órfãos declarados à parte. Essa identidade não envelhece e serve de verificação em qualquer data. Para distintos de texto, declarar se a contagem é sensível a caixa e acento.

**Detecção:** manual: relatório de importação com "esperado × real" e motivos; consulta de soma dos baldes = total.

**Vista em:** painel público de indicadores; plataforma de mapeamento comunitário sobre Tainacan; indexador de busca (Elasticsearch/OpenSearch).

### L-MIG-07 · Perfilar qualidade e declarar cobertura antes de contar ou exibir
**Sintoma:** termo de autoria cujo nome é um código numérico da origem (4.458 itens); datas "[196-?]"; dois metadados com o mesmo nome (um vazio); "Data de produção" do tipo texto; gráficos enganosos num acervo com cobertura de data/local de ~1%.

**Causa:** dados da origem gravados sem perfil; configuração por nome ambígua; não-valores contados como valores.

**Correção:** perfil por metadado antes de mapear (preenchimento, distintos, amostras, padrões). Alertar "valores que parecem códigos". Década incerta conta como década, com ressalva. Identificador = menos de 2,5 itens por valor (não "90% distintos"). Entre homônimos, escolher o metadado preenchido. Todo gráfico declara sobre quantos itens fala; não-valores em cinza; datas em metadado de texto reconhecidas pelo nome do metadado.

**Detecção:** manual: relatório de perfil por metadado anexado ao plano de migração.

**Vista em:** painel público de indicadores; plugin de impressão de itens.

### L-MIG-08 · Num acervo agregado, o id carrega a proveniência: amostrar por estrato
**Sintoma:** amostra de 2.000 itens com 68% de um só museu; métrica de cruzamento sem sentido.

**Causa:** paginação por `item_id` crescente; a colheita importa instituição por instituição, então ids vizinhos têm a mesma origem.

**Correção:** amostra estratificada por instituição ou coleção; comparar com o acaso; amostra dirigida quando o caso testado é raro; provar com ids conhecidos.

**Detecção:** manual: conferir a distribuição de origem da amostra antes de tirar conclusão.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-MIG-09 · Teste de ida e volta para todo formato de exportação
**Sintoma:** exportar e reimportar perde em silêncio campos acrescentados depois ("Outro (especifique)", limites de escala).

**Causa:** a allowlist do formato portátil não acompanhou o modelo.

**Correção:** teste automatizado export → import → comparar, para cada tipo de campo, rodado a cada mudança de modelo.

**Detecção:** manual: exigir o teste de ida e volta no CI.

**Vista em:** plugin de pesquisas/formulários.

### L-MIG-10 · Marcador de sucesso só com sucesso; filtro novo sobre dado antigo
**Sintoma:** `last_hit_at` atualizado mesmo quando todos os registros falhavam validação; campos enviados pelo coletor descartados no insert; KPIs lidos de uma tabela que nada alimentava; drill-down em branco porque o filtro `device_type <> 'unknown'` eliminava todo o histórico gravado antes do parser existir.

**Causa:** marcador de saúde gravado fora do ramo de sucesso; insert sem mapear o contrato inteiro; leitura da tabela errada; filtro que ignora dados legados.

**Correção:** atualizar o marcador só se ≥ 1 registro foi gravado. Mapear todos os campos do contrato e falhar alto quando chega campo desconhecido. Calcular a partir da tabela realmente populada. Ao introduzir classificação nova, fazer backfill ou prever fallback para o histórico.

**Detecção:** manual: `update(... last_*_at ...)` fora do ramo de sucesso; tabela lida em relatório sem nenhum `INSERT` no plugin.

**Vista em:** painel central de métricas e coletor nos sites.

---

## 3. Como o Tainacan grava o que você importa

Referência do núcleo (linhas da 1.4.1, relativas a `src/`; "1.0.3 e 1.4.1" quando o trecho coincide).

| O quê | Como fica no banco | Onde |
|---|---|---|
| Valor de metadado comum | `wp_postmeta` do item com `meta_key` = id numérico do metadado | `classes/repositories/class-tainacan-item-metadata.php:62-109` |
| Título e descrição (núcleo) | `post_title`/`post_content` **e** meta comum, para `meta_query` | `classes/repositories/class-tainacan-item-metadata.php:44-49` |
| Multivalorado | `delete_post_meta` e um `add_post_meta` por valor; vazios pulados; Relationship passa por `array_unique` | `:94-110` |
| Taxonomia | `wp_term_relationships` via `wp_set_object_terms`; termo ausente criado com `Terms::insert` quando permitido | `:162-204` |
| Composto | linha pai com `meta_value = ''`; filhos ligados por `parent_meta_id` | `:53-67`, `:250` |
| Sanitização de todo texto | `sanitize_value()`: `trim(wp_kses($v, tags de 'post' sem <a>))`; numéricos e vazios passam direto | `classes/repositories/class-tainacan-repository.php:1011-1023` (`:987-999` [1.0.3]); usada em `class-tainacan-item-metadata.php:68`, `:108`, `:146`, `:189` e no título em `class-tainacan-repository.php:194-217` |
| Validação | `Item_Metadata::insert()` exige `validate()` sempre; `Repository::insert()` exige para `publish`/`future`/`private`/`pending` | `class-tainacan-item-metadata.php:30-34`; `class-tainacan-repository.php:164-166` |
| Ganchos de entrada | `tainacan-pre-insert-Item_Metadata_Entity` (antes de sanitizar e gravar), `tainacan-insert-Item_Metadata_Entity`, `tainacan-pre-insert-term` | `class-tainacan-item-metadata.php:37-38`, `:116-117`; `classes/repositories/class-tainacan-terms.php:125` |
| Logs | todo `insert()` gera log e diff; `disable_logs()`/`enable_logs()`. **Δ** 1.0.3: CPT `tainacan-log`; 1.4.1: tabela `{prefix}tainacan_logs` | `class-tainacan-repository.php:67-89`; `classes/repositories/class-tainacan-logs.php:451-454` |
| Arquivos do item | `uploads/tainacan-items/{coleção}/{item}`; item privado em `_x_{item}` | `classes/class-tainacan-private-files.php:121`, `:134`, `:142-145` |

Importador CSV nativo (`classes/background-process/importer/class-tainacan-csv.php`):

- Padrões: `delimiter` `,`, `multivalued_delimiter` `||`, `encode` `automatic`, `enclosure` vazio, `escape_empty_value` `[empty value]` (`:16-22`; mesmos valores na 1.0.3).
- Colunas especiais: `special_document` (valor com prefixo `url:`, `text:` ou `file:`; `file:` aceita URL ou caminho relativo a `server_path`), `special_thumbnail`, `special_attachments` (separados pelo `multivalued_delimiter`), sufixos `|APPEND`/`|REPLACE`, `special_item_status`, `special_item_id`, `special_comment_status` (`:76-98`, `:624-670`, `:766`). Com `special_item_id`, a opção `repeated_item` decide entre `update` e `ignore` (`:557-559`, `:948`).
- Durante `insert()`: logs desligados nos três repositórios, revisões desligadas, valores gravados dentro de `SET autocommit = 0` ... `COMMIT` (`:893`, `:918-920`, `:1048`, `:1089`).
- Exportadores CSV e XLSX usam o mesmo `||` (`classes/background-process/exporter/class-tainacan-csv.php:27-28`; `class-tainacan-xlsx-exporter.php:41-42`).

### L-MIG-11 · Entidades HTML: o banco guarda `&gt;` e `&amp;`; decodificar só fora do HTML
**Sintoma:** `&#039;`, `&quot;` ou `&gt;` aparecendo literais em PDF, e-mail, lista montada por JS, JSON e índice; valores hierárquicos ("Pintura > Óleo") com `&gt;`; depois de corrigir o banco, o índice ainda mostrava `&gt;`.

**Causa:** duas fontes. (1) O núcleo grava todo texto passando por `wp_kses()` (`classes/repositories/class-tainacan-repository.php:1011-1023`), e o `wp_kses` do WordPress transforma `>` solto em `&gt;`, `&` solto em `&amp;` e normaliza `&#39;` para `&#039;`; `get_the_title()` também devolve entidades (`&#8211;`). (2) Agregadores e importadores que gravam valores já escapados da origem produzem escape duplo (`&amp;gt;`), que aparece literal na tela. No HTML o navegador decodifica uma camada; em JSON + `textContent`, PDF, e-mail ou índice, não, e quem escapa de novo na saída gera `&amp;#039;`.

**Correção:** tratar a forma gravada como "HTML com uma camada de entidades". Em toda saída que não é HTML (JSON para JS, PDF, CSV, e-mail em texto, índice de busca), aplicar `html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8')` e escapar só na saída final; tornar o escape do gerador de PDF idempotente (decodifica, depois escapa). Escape duplo vindo da origem se corrige na entrada (L-MIG-15) ou no banco com as regras de L-MIG-17, e depois se reindexa; não "corrigir" o índice, porque o indexador re-escapa `>` legítimo e o falso positivo volta. Ao mudar o formato de uma resposta cacheada, incluir a versão do plugin na chave do cache.

**Detecção:** manual: `esc_html(`/`htmlspecialchars(` sobre título ou valor de item sem `html_entity_decode` prévio; `get_the_title(` ou `get_value()` dentro de array passado a `wp_json_encode`/resposta REST sem decodificar.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan; busca semântica por embeddings; plugin de dados ligados e enriquecimento; agregador nacional de acervos (~74 mil itens).

### L-MIG-12 · O núcleo apara espaços e corta o texto a partir de um `<` solto
**Sintoma:** depois da importação, "Altura <10 cm, largura 5 cm" vira "Altura"; "1 < 2" vira "1"; "<<antigo>>" vira `&gt;`; "R$ 5 <> R$ 6" perde o `<>`; "a < b > c" vira "a <b> c" (um negrito inventado); links `<a>` somem e fica só o texto; espaços no início e no fim desaparecem.

**Causa:** `sanitize_value()` faz `trim(wp_kses($content, $allowed_html))` com as tags de `'post'` menos `<a>` (`classes/repositories/class-tainacan-repository.php:1011-1023`, igual na 1.0.3). O `wp_kses` interpreta `<` seguido de texto como início de tag e remove tudo até o próximo `>` ou até o fim. Resultado verificado executando o código de `sanitize_value()` contra o `wp-includes/kses.php` do WordPress 6.9.1, fora do Tainacan. Valores numéricos e vazios passam sem sanitização (`:1015-1017`).

**Correção:** antes de importar, varrer a origem por `<` em campos de texto e listar os registros afetados. Nesses valores, gravar `&lt;` no lugar de `<` (o `wp_kses` preserva entidades válidas, e a exibição em HTML mostra `<`), registrando a transformação no plano como exigência técnica do destino. Espaços de borda não sobrevivem; se forem significativos, documentar. Na auditoria final, comparar a origem com a forma esperada depois de `sanitize_value` (aplicar a mesma função ao valor de origem) para separar alteração do núcleo de perda real.

**Detecção:** manual: `SELECT` na origem por valores com `<`; depois da importação, comparar o comprimento do valor de origem com o gravado (valores encurtados indicam corte).

**Vista em:** referência do núcleo do Tainacan (comportamento confirmado por execução).

### L-MIG-13 · CSV: `encode = automatic` esvazia valores Latin-1; BOM na 1.0.3
**Sintoma:** um CSV em ISO-8859-1/Windows-1252 é importado sem erro, mas todo valor com acento chega vazio (e é pulado); valores só com ASCII chegam normais. Na 1.0.3, CSV salvo pelo Excel com BOM não casa a primeira coluna (por exemplo `special_item_id`).

**Causa:** em `handle_encoding()`, no caso `automatic` (o padrão), quando o valor não é UTF-8 o código chama `mb_convert_encoding($value, ...)` com a variável `$value`, que não existe (o parâmetro é `$string`) (`classes/background-process/importer/class-tainacan-csv.php:602-615`, `$value` em `:607`; `:547-560` [1.0.3]). No PHP 8 isso devolve `''` (conferido no PHP 8.2), e `is_empty_value()` (`:1131-1137`) descarta o valor. Como `ISO-8859-1` aceita qualquer sequência de bytes, todo valor não UTF-8 cai nesse ramo. **Δ** a 1.4.1 pula o BOM UTF-8 em `open_tmp_file()` (`:26-53`); a 1.0.3 abre o arquivo com `fopen` direto e o BOM vira parte do nome da primeira coluna.

**Correção:** converter o arquivo para UTF-8 antes (com `iconv`/`mb_convert_encoding` fora do importador, conferindo uma amostra acentuada) e escolher `encode = utf8` na tela do importador. Na 1.0.3, gravar o CSV sem BOM ou não deixar coluna especial na primeira posição. Depois da importação, contar valores não vazios por metadado e comparar com a origem (L-MIG-06). Ver também: BOM gravado por editores e pelo PowerShell em `ambiente-de-desenvolvimento.md`.

**Detecção:** TNC-B03 (no arquivo CSV antes de importar: UTF-8 inválido ou BOM); manual: contagem de valores por metadado origem × banco.

**Vista em:** referência do núcleo do Tainacan (inferência pelo código e conferência no PHP 8.2; não executado dentro de uma importação real).

### L-MIG-14 · Multivalor, hierarquia, composto e o separador `' | '` de exibição
**Sintoma:** valores grudados ("Pintura||Desenho" num metadado simples), termos criados com `>>` no nome, item inteiro pulado por causa de um composto, duplicatas exatas depois de backfill, valores multivalorados quebrados ao reler pela REST.

**Causa:** regras do núcleo que o arquivo gerado precisa seguir:
- Multivalor: a célula é dividida por `multivalued_delimiter` (`||`) **só** se o metadado está configurado como múltiplo (`class-tainacan-csv.php:299-300`); em metadado simples, `||` fica literal no valor. Um valor que contém `||` vira dois.
- Taxonomia: `Pai>>Filho>>Neto` cria ou reaproveita cada nível, com `trim` em cada parte (`:1155-1200`); nível vazio (`A>>>>B`) descarta o termo com erro no log. Sem `>>`, o nome vai direto para `save_terms_metadatum_value()`, que procura por nome com `term_exists($nome, $taxonomia, null, true)` sem restringir o pai (`classes/repositories/class-tainacan-item-metadata.php:178-182`): um nome que existe em dois ramos casa com o primeiro achado (inferência). A busca por nome passa pela collation do banco, que costuma ignorar caixa e acento: "Sao Paulo" pode casar com o termo "São Paulo" já existente (inferência; depende da collation).
- Composto: cabeçalho `Nome|compound(Filho1,Filho2)`; a célula traz os valores dos filhos separados pelo próprio `delimiter` do CSV (lida com `str_getcsv`), e cada ocorrência do composto múltiplo separada por `||` (`:106-109`, `:265-296`). Número de valores diferente do número de filhos faz `process_item` devolver `false` e o item inteiro não é importado (`:287-289`).
- Vazio × limpar: célula vazia ou só com espaços é ignorada (não apaga valor existente numa atualização); para apagar, usar o literal de `escape_empty_value` (`[empty value]`) (`:1131-1148`).
- No banco, multivalor é uma linha de `wp_postmeta` por valor (A.6). `INSERT ... SELECT` em backfill duplica valores.
- Na leitura, `value_as_html` junta valores com `<span class="multivalue-separator"> | </span>` (`classes/entities/class-tainacan-item-metadata-entity.php:126-139`, filtro `tainacan-item-metadata-get-multivalue-separator`) e `value_as_string` é esse HTML sem tags (`:214-234`). Dividir `value_as_string` por `|` quebra valores que contêm `|`.

**Correção:** escolher um `multivalued_delimiter` que não ocorre em nenhum valor da origem (verificar com busca) e conferir que os metadados de destino estão marcados como múltiplos antes de importar. Gerar hierarquias sempre com o caminho completo `>>`. Validar a contagem de filhos de cada composto no próprio gerador. Para reler valores (auditoria, migração Tainacan → Tainacan), usar o campo `value` da REST (array), nunca `value_as_string`. Em backfill SQL, deduplicar por `(post_id, meta_key, meta_value)` e, ao recalcular relações de termo, recalcular `wp_term_taxonomy.count`.

**Detecção:** manual: grep do separador escolhido nos valores de origem; `INSERT INTO ...postmeta ... SELECT` sem `DISTINCT`/`NOT EXISTS`; `explode('|'` sobre `value_as_string`.

**Vista em:** referência do núcleo do Tainacan; plataforma de mapeamento comunitário sobre Tainacan (backfill com 5 duplicatas exatas).

### L-MIG-15 · Normalizar escape duplo na entrada pelo gancho de pré-inserção
**Sintoma:** valores entrando com escape duplo por um agregador que não expõe filtros e cujo diretório leva a versão no nome (corrigir o plugin de origem seria desfeito na próxima atualização).

**Causa:** o gravador da origem escapa antes de entregar ao Tainacan.

**Correção:** interceptar `tainacan-pre-insert-Item_Metadata_Entity` (`classes/repositories/class-tainacan-item-metadata.php:37-38`) e `tainacan-pre-insert-term` (`classes/repositories/class-tainacan-terms.php:125`). Como o agregador grava por `Item_Metadata::insert()`, o gancho cobre também importador CSV, edição manual e REST. Decodificar só nos tipos primitivos `string` e `long_string`; nos demais (compound, control, date, float, geo, item, term, user) não mexer. Limite: o gancho dispara **antes** de `sanitize_value()` (`:68`, `:108`), que volta a codificar `>` e `&` soltos (L-MIG-11). O gancho remove a camada extra (`&amp;gt;` → `&gt;`); a forma gravada final continua com uma camada.

**Detecção:** manual: `html_entity_decode` aplicado a valor de metadado sem checar o tipo primitivo.

**Vista em:** agregador nacional de acervos (~74 mil itens).

### L-MIG-16 · Tipo de metadado não se troca: criar um novo e migrar
**Sintoma:** necessidade de converter campo de texto em taxonomia ou relacionamento em instalações existentes.

**Causa:** o Tainacan não permite trocar o tipo de um metadado existente.

**Correção:** criar o metadado novo (mesmo nome e seção), converter os valores (split, reuso de termos ou registros existentes), assumir a posição no `metadata_order`, atualizar mapeamentos e mandar o antigo para a lixeira (o postmeta bruto fica preservado, então é reversível). Idempotente, com diário de migração e rota de reversão.

**Detecção:** manual: conferir diário de migração e contagem de valores antigo × novo.

**Vista em:** plugin de biblioteca.

---

## 4. Escrita em massa no banco

### L-MIG-17 · `UPDATE ... REPLACE` de texto em `wp_postmeta` corrompe PHP serializado
**Sintoma:** 4.256 linhas serializadas (`_wp_attachment_metadata`, trilha de auditoria do log do Tainacan e outras) deixaram de desserializar, sem erro nem log; num caso irmão, um `UPDATE` em `wp_posts` alterou 1.943 títulos, só 48 de acervo (o resto `tainacan-log` e `revision`).

**Causa:** string serializada declara o tamanho em bytes (`s:46:"..."`); trocar `&amp;` por `&` muda o tamanho e `unserialize()` devolve `false`. O `WHERE` da aplicação era diferente do usado na medição (4× mais linhas). Sem filtro de `meta_key`/`post_type`, o comando alcança tudo. **Δ** na 1.0.3 o log é CPT `tainacan-log` em `wp_posts`/`wp_postmeta`; na 1.4.1 está em tabela própria, mas revisões e anexos continuam lá.

**Correção:** edição em lote pela API: `Item_Metadata_Entity` + `validate()` + repositório, em lotes de 100, com prévia antes/depois e desfazer (valor anterior guardado). Se SQL for inevitável: `meta_key` explícito, excluir serializado (`AND meta_value NOT REGEXP '^(a|O|s|b|i|d):[0-9]*[:;{]'`), `post_type` explícito em `wp_posts`, `WHERE` da aplicação idêntico ao da medição, dump direcionado antes. Reversão determinística de entidade não funciona: só backup. Para restaurar, renomear a tabela no dump (ele traz `DROP TABLE IF EXISTS`), carregar ao lado e copiar de volta com conferência `BINARY`. Limpeza nunca recebe consulta aberta: recebe ids, reconfere cada um antes de apagar, em lotes de até 500.

**Detecção:** TNC-P04.

**Vista em:** plugin de dados ligados e enriquecimento; plugin de biblioteca; agregador nacional de acervos (~74 mil itens).

### L-MIG-18 · SQL direto fura o repositório: versão, validação e ganchos não rodam
**Sintoma:** modelo alterado sem incremento de versão (certificados não refletiam a defasagem); correções por SQL deixaram os dados certos, mas nenhum e-mail saiu, e o teste de notificação "passaria sem testar nada".

**Causa:** quem detecta mudança e incrementa versão é o `save()` do repositório; `updated_post_meta`, `save_post`, `tainacan-insert` e os logs do Tainacan só disparam pela API.

**Correção:** gravar sempre pelo repositório ou serviço que versiona e dispara ganchos. SQL só para montar cenário de teste; a transição observada (a que deve disparar efeitos) é feita pela interface ou pela API.

**Detecção:** TNC-T05 (repositório do Tainacan usado sem `validate()`); manual: `$wpdb->update(` sobre tabela que tem classe de repositório com `save()` no plugin.

**Vista em:** plugin de eventos e certificados em PDF; plataforma de mapeamento comunitário sobre Tainacan.

### L-MIG-19 · Estrutura em JSON e uma chave por identificador, não PHP serializado
**Sintoma:** valor encurtado por engano deixa de desserializar, calado.

**Causa:** `update_post_meta`/`update_term_meta` com array grava PHP serializado, que falha em silêncio.

**Correção:** listas em JSON (a quebra fica visível); identificadores escalares em chaves próprias de `term_meta`/`post_meta`; manter espelho escalar da primeira entrada para leitores SQL existentes; migrar na leitura quando a chave nova não existe.

**Detecção:** manual: `update_term_meta`/`update_post_meta` com array como valor.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-MIG-20 · `update_*_meta` com chave única substitui em silêncio
**Sintoma:** vincular de novo apagava o vínculo anterior, sem aviso.

**Causa:** chave única gravada com `update_*_meta`.

**Correção:** lista explícita como fonte da verdade; acréscimo e remoção por item da lista.

**Detecção:** manual: `update_*_meta` em fluxo descrito como "adicionar vínculo".

**Vista em:** plugin de dados ligados e enriquecimento.

### L-MIG-21 · `REPLACE INTO` zera as colunas não informadas
**Sintoma:** contagens de uma árvore zeradas até o fim da publicação.

**Causa:** `REPLACE` apaga e reinsere a linha; colunas não enviadas voltam ao default.

**Correção:** upsert com `INSERT ... ON DUPLICATE KEY UPDATE`, listando só as colunas que mudam.

**Detecção:** manual: `REPLACE INTO`/`$wpdb->replace(` em tabela com colunas calculadas.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-MIG-22 · Gravação parcial corrompe estrutura derivada
**Sintoma:** recoleta criou cópia de um dossiê inteiro.

**Causa:** o `UPDATE` do nó falhava (coluna fora da lista), mas a movimentação da subárvore rodava mesmo assim.

**Correção:** falha ao gravar o nó aborta a sincronização. Ter um comando de reparo da estrutura derivada (no caso, reconstrução da árvore) que deve dar 0 alterações numa árvore saudável, e usá-lo como verificação.

**Detecção:** manual: retorno de `$wpdb->update(`/`insert(` não comparado com `=== false` antes de operações dependentes.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-MIG-23 · Modo estrito do MySQL/MariaDB: valor maior que a coluna, colunas sem default
**Sintoma:** linha presa em `running` para sempre (execução concluída confundida com morta); `INSERT` em `wp_posts` falha com `ERROR 1364`; cadastros falhando depois que um campo deixou de ser preenchido.

**Causa:** sob `STRICT_TRANS_TABLES` o comando inteiro falha: status `completed_with_errors` (22 caracteres) numa `varchar(20)`; `to_ping`, `pinged`, `post_content_filtered` e `post_mime_type` de `wp_posts` não têm default; coluna descontinuada mantida `NOT NULL` e `UNIQUE`.

**Correção:** dimensionar colunas pelo maior literal e ampliar por migração; rotina para corrigir linhas travadas que tenham evidência de sucesso; checar `$wpdb->last_error` depois de cada escrita. Inserir posts com `wp_insert_post` (ou listar todas as colunas). Coluna obsoleta: migração para `DEFAULT ''` e remoção do índice único.

**Detecção:** manual: literais de status maiores que o `VARCHAR(n)` do `dbDelta`; `INSERT INTO {$wpdb->posts}` sem as quatro colunas; coluna `NOT NULL` sem `DEFAULT` que nenhum `insert()` do plugin preenche.

**Vista em:** agregador de acervos de vários sites; plataforma de mapeamento comunitário sobre Tainacan.

### L-MIG-24 · Editar registro global pelo contexto local o converte em local
**Sintoma:** risco de orfanizar cerca de 96 participantes ao editar uma categoria global.

**Causa:** `save_category()` gravava o `event_id` corrente incondicionalmente, transformando a categoria global (`event_id = 0`) em local.

**Correção:** preservar o escopo original na edição; recusar editar registro de outro escopo específico. Em migração: ao reaplicar mapeamento ou corrigir em lote, nunca reescrever campos de escopo, coleção ou dono que não fazem parte da correção.

**Detecção:** manual: handler de edição que grava o contexto corrente em campo de escopo.

**Vista em:** plugin de eventos e certificados em PDF.

### L-MIG-25 · `wp_delete_attachment($id, false)` apaga o arquivo em definitivo
**Sintoma:** risco de perda irreversível de anexos ao "excluir" um registro duplicado.

**Causa:** o anexo só vai para a lixeira se `MEDIA_TRASH` estiver ativa; o padrão do WordPress é `false`, e o arquivo some do disco.

**Correção:** para remoção reversível, `wp_trash_post($attachment_id)`; recusar se `EMPTY_TRASH_DAYS = 0`; só tocar anexos com `post_parent` igual ao item; registrar autor e data. Restaurar o item da lixeira não restaura os anexos.

**Detecção:** manual: `wp_delete_attachment(` com segundo argumento `false` ou omitido em fluxo descrito como "lixeira" ou "reversível".

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-MIG-26 · Arquivos do item sobrevivem à exclusão: recuperação forense
**Sintoma:** nome de instituição perdido depois da exclusão do item.

**Causa:** os uploads ficam em `uploads/tainacan-items/{coleção}/{item}` (ou `_x_{item}` quando o item é privado, `classes/class-tainacan-private-files.php:142-145`) e não são removidos com o item.

**Correção:** identificar pelo nome dos arquivos e pelo texto interno dos PDFs (descomprimir streams com zlib); cruzar com um registro gêmeo; copiar o valor via SQL ou API, nunca redigitar. Proximidade de id não é evidência.

**Detecção:** manual: listar pastas de `tainacan-items` sem item correspondente.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-MIG-27 · Não religar órfãos quando isso viola um invariante
**Sintoma:** tentação de religar uma avaliação órfã à inscrição sobrevivente depois de uma deduplicação.

**Causa:** a sobrevivente já tinha avaliação própria; religar criaria duas para o mesmo item.

**Correção:** preservar o invariante ("um registro vinculado por item"); manter a órfã visível, com o nome preservado, e declará-la na contagem (L-MIG-06).

**Detecção:** manual: consulta de invariantes (contagem de vínculos por item) antes e depois da mescla.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-MIG-28 · Página pública de verificação sobrevive à origem apagada
**Sintoma:** QR code de um documento legítimo levava a erro.

**Causa:** a página parava quando o item de origem não existia mais.

**Correção:** guardar um snapshot dos dados mínimos na emissão e reconstruir a página a partir dele, mantendo a checagem de autenticidade. Em migração: redirect 301 das URLs antigas e permalinks preservados quando o item muda de coleção ou de site.

**Detecção:** manual: apagar um item de teste e abrir sua página de verificação.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

---

## 5. Identidade e deduplicação

### L-MIG-29 · Identidade pela marca no próprio item; duplicata visível é melhor que sobrescrita
**Sintoma:** acervo duplicado ou triplicado a cada reingest; itens de coleções diferentes fundidos; pacotes de grupos diferentes com o mesmo identificador sobrescrevendo-se (o segundo grupo nunca era criado, itens híbridos com anexos dos dois); centenas de duplicatas em itens antigos sem marca.

**Causa:** UUID do pacote muda a cada reingest; o identificador do item (IT0001...) se repete entre coleções e grupos; tabela de rastreio própria não confiável (opcache, truncamentos); fallback legado aceitava candidato único sem comparar a chave natural e sem excluir itens que já tinham marca; itens anteriores à marca não tinham sinal de deduplicação.

**Correção:** gravar no post meta do item uma marca `_uid = "{config}:{chave natural}"`, na criação e na atualização; chave natural = coleção + grupo + item (identificador sozinho nunca é identidade). Todo candidato a "mesmo item" é comparado pela chave natural; sem chave verificável, só se aceita um caso degradado específico; item sem marca nenhuma não é aceito (cria-se outro: a duplicata aparece e pode ser mesclada; a sobrescrita não aparece). Ordem obrigatória antes de reingest em massa: implantar e recarregar, verificar duplicados, mesclar (mantém o mais antigo; itens cuja chave é só o identificador ficam fora dos grupos), só então reingerir; o scan reporta "não avaliados" em vez de silenciar. Assinatura do problema no log: self-heal em N/N itens + "metadados alterados" em N/N + `success_rows:0`. O importador CSV nativo só reconhece repetição por `special_item_id` (id do Tainacan) com `repeated_item = update|ignore` (`class-tainacan-csv.php:557-559`, `:948`); para chave da origem, gravar a chave num metadado próprio e conferir antes.

**Detecção:** manual: deduplicação de importação que consulta tabela própria em vez de post meta do item; contagem de itens com a mesma chave natural (deve ser 0).

**Vista em:** importador de pacotes de preservação (DIP).

### L-MIG-30 · Chave natural a partir de nomes de arquivo; extensões compostas
**Sintoma:** chave instável entre transferências e dependente da ordem no METS; arquivos `.warc.gz` ou `.tar.gz` casados ou deduplicados errado.

**Causa:** o Archivematica põe UUID em qualquer posição do nome (prefixo no original, sufixo na cópia normalizada), e só o prefixo era removido; a chave vinha da base do primeiro arquivo (itens contaminados podiam ter anexos alheios); `pathinfo` considera só a última extensão.

**Correção:** voto da maioria entre os arquivos do item. Limpeza nesta ordem: UUID em qualquer posição → sufixo `-N` de `wp_unique_filename` → extensão composta (`warc.gz`, `tar.gz`, `tar.bz2`, `tar.xz` contam como uma) → paginação `_mNNNNdeNNNN`. Formatos diferentes são arquivos diferentes. Normalizar marcas antigas no próximo reingest (self-heal).

**Detecção:** manual: regex de UUID ancorada só no início (`^[0-9a-f]{8}-`); `pathinfo($f, PATHINFO_EXTENSION)` em código de casamento de arquivos de preservação.

**Vista em:** importador de pacotes de preservação (DIP).

### L-MIG-31 · Lote sequencial × paginação em nomes de pacote e arquivo
**Sintoma:** confusão entre versões de pacote e partes de um arquivo.

**Causa:** `_m0000N` no nome do pacote é lote sequencial (faixas disjuntas de itens); `_mNNNNdeNNNN` no nome do arquivo é paginação.

**Correção:** tratar separadamente; manter no armazenamento só o pacote vigente quando há versões que compartilham chaves.

**Detecção:** manual.

**Vista em:** importador de pacotes de preservação (DIP).

### L-MIG-32 · Identidade estável de nós sem id na origem (EAD/AtoM)
**Sintoma:** nós `<c>` exportados pelo AtoM sem id; a lista de autoridades crescia a cada nó.

**Causa:** exportação sem identificador estável; `atom_NNN_actor` identifica o evento, não a autoridade.

**Correção:** identidade = `sha1(fundo|unitid)` quando o unitid é único no fundo, senão `sha1(fundo|pai|posição|título)`. Autoridades reconciliadas por nome normalizado, guardando uma forma de origem por fonte.

**Detecção:** manual: recoletar a mesma fonte duas vezes e conferir que nenhum id muda.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-MIG-33 · Restrição `UNIQUE` contornada por sub-endereçamento de e-mail
**Sintoma:** pessoa com dois papéis no mesmo evento não podia ter dois cadastros.

**Causa:** índice `UNIQUE (event_id, email_hash)`.

**Correção:** cadastro com `fulano+papel@dominio`; na entrega, remover o `+tag` só na parte local (antes do último `@`) e nunca na posição 0. Nem todo servidor de e-mail aceita `+`. Em migração: e-mail não é chave natural de pessoa; registrar a regra se a origem tem pessoas repetidas.

**Detecção:** manual.

**Vista em:** plugin de eventos e certificados em PDF.

---

