# Migração de acervos para o Tainacan (parte 2 de 2)

Lições para levar um acervo de qualquer origem (planilha, banco legado, DSpace, OJS, AtoM, pacotes de preservação, outro Tainacan) até o Tainacan sem alterar um único valor que o catálogo de origem registrou, e para provar isso com contagens.

**Princípio central:** o Tainacan recebe o que a origem documentou, exatamente. Caixa, espaços, pontuação, duplicados, siglas, não-valores ("s/d", "Não identificado") e até erros evidentes são documentação do acervo; normalizar, expandir ou "corrigir" é decisão de curadoria, tomada por escrito e aplicada como passo separado e reversível. Toda etapa (extração, arquivo gerado, banco, índice) precisa de uma contagem que feche com a anterior.

Continuação da [parte 1](migracao-de-acervos-parte-1.md) (L-MIG-01 a 33: fidelidade, reconciliação, como o Tainacan grava, escrita em massa, identidade). As referências a lições da parte 1 (por exemplo L-MIG-06, L-MIG-13, L-MIG-14) estão naquele arquivo.

## Sumário

6. [Coleta e importação retomável](#6-coleta-e-importação-retomável) (L-MIG-34 a 38)
7. [Datas e fusos](#7-datas-e-fusos) (L-MIG-39 a 40)
8. [Enriquecimento com dados ligados](#8-enriquecimento-com-dados-ligados) (L-MIG-41 a 44)
9. [IA na catalogação](#9-ia-na-catalogação) (L-MIG-45 a 46)
10. [Sistemas de origem](#10-sistemas-de-origem) (L-MIG-47 a 53)
11. [Processo](#11-processo) (L-MIG-54)
12. [Checklist de importação](#12-checklist-de-importação)

## Comece por aqui

- **L-MIG-34**: importação em massa retomável, desfazível, com faixas disjuntas e limpeza no fim.
- **L-MIG-37**: anexos conferidos pelo conteúdo (URL sem extensão, DSpace devolvendo login com 200).
- **L-MIG-39**: datas no fuso do site; `Y-m-d` lido como meia-noite UTC vira o dia anterior.
- **L-MIG-41** e **L-MIG-42**: Wikidata como hub, Getty só por ID, reconciliação nunca autoaceita.
- **L-MIG-45**: texto de IA só depois de verificação determinística de fidelidade.
- **Checklist de importação** (seção 12): antes, durante e depois.

---

## 6. Coleta e importação retomável

### L-MIG-34 · Importação em massa: colunas por nome, retomável, desfazível, faixas disjuntas
**Sintoma:** arquivos com variações entre versões e campos que se perderiam calados; itens duplicados ao relançar; logs duplicados; `pkill` matando o próprio shell; WP-CLI falhando em silêncio como root; disco cheio com amostras de laboratório.

**Causa:** destino com menos metadados que a origem; duas instâncias no mesmo offset; STDOUT de vários workers no mesmo log; `pkill -f` casa o próprio comando; falta `--allow-root`; cada imagem grande gera cerca de 6 MB de tamanhos intermediários.

**Correção:** reconhecer colunas por nome, com apelidos, e mostrar quais campos de origem não têm metadado no destino antes de começar. Tratar BOM e Latin-1 na leitura (L-MIG-13). Passos curtos (cerca de 8 a 10 s) com estado retomável; área de preparação; desfazer em ordem inversa preservando o que teve movimentação depois. Workers com faixas disjuntas; antes de relançar, conferir `pgrep` e apagar os itens da faixa; matar por PID excluindo `$$`/`$PPID`. Durante a carga: `disable_logs()` nos repositórios (`classes/repositories/class-tainacan-repository.php:67-89`), itens criados como `auto-draft` (podem ser gravados sem `validate()`, `:164-166`) e publicados no fim, termos cacheados em memória. Para amostras de laboratório, importar de outro Tainacan pela REST (campo `value`, L-MIG-14), de olho no disco. No fim: contagem exata, remover CSV e scripts de `uploads` (acessíveis pela web), redirect 301 das URLs antigas.

**Detecção:** manual: scripts de importação (`*.php`, `*.csv`) gravados em `wp-content/uploads/`; relatório "esperado × real".

**Vista em:** plugin de biblioteca; migração de sistema de protocolo de um órgão público (264 mil registros); laboratórios de página pública do item.

### L-MIG-35 · Coleta resiliente por item e por página
**Sintoma:** coleta parava em 44 de 895 itens depois de um timeout; um item com erro parava tudo.

**Causa:** erro de página finalizava a coleção inteira; laço sem `try/catch` de `Throwable`.

**Correção:** registrar páginas e itens que falharam, continuar e repetir no fim (no máximo 2 vezes), com backoff; relatório de falhas permanentes; diagnóstico "esperado × real" com motivos (duplicata, falha, pulado).

**Detecção:** manual: `catch (Exception $e)` sem `Throwable` em laço de importação; `break`/`return` dentro do `catch` de página.

**Vista em:** agregador de acervos de vários sites.

### L-MIG-36 · Coleta incremental e reaplicação de mapeamento
**Sintoma:** lentidão reprocessando itens; risco de pular mudanças da origem.

**Causa:** itens sem data `modified` eram sempre reprocessados; reaplicar mapeamento atualizando `last_sync` faria a próxima coleta incremental ignorar mudanças reais.

**Correção:** pular itens sem `modified` que já existem. Modo "remap" regrava só os metadados escolhidos e não mexe em `last_sync`, título, status, documento nem miniatura. Chave de origem única incluindo a coleção de destino.

**Detecção:** manual.

**Vista em:** agregador de acervos de vários sites.

### L-MIG-37 · Anexos: conferir o conteúdo baixado, não a URL nem o status HTTP
**Sintoma:** imagens de uma fonte nunca baixavam; link de arquivo "funciona" mas baixa a página de login.

**Causa:** `media_sideload_image()` valida a extensão da URL por regex antes de qualquer requisição e recusa URLs sem extensão. O importador do Tainacan baixa com `download_url()`, dá ao arquivo o nome tirado da URL e define o MIME por `wp_check_filetype()` sobre esse nome (`classes/media-helper/class-tainacan-media.php:365-383`, `:411-418`, `:451`), então URL sem extensão gera anexo sem tipo ou recusado (inferência). DSpace responde 200 com HTML de login para arquivo sem leitura anônima, e esse HTML vira anexo.

**Correção:** baixar por conta própria, conferir `Content-Type` e assinatura do conteúdo (`getimagesize`, `finfo`) e tamanho, e só então criar o anexo com nome e extensão corretos (ou entregar ao importador um caminho local via `file:` + `server_path`). Rejeitar HTML onde se esperava binário. No DSpace, quando houver dois handles, usar o de `dc.identifier.uri`. Contar anexos por item origem × destino.

**Detecção:** manual: `media_sideload_image(` aplicado a URL de API externa; anexos com `post_mime_type` vazio ou `text/html` depois da importação.

**Vista em:** agregador de acervos de vários sites; migração de repositório DSpace.

### L-MIG-38 · Pointer file só existe para AIP
**Sintoma:** ERROR "does not have a pointer file" (404) em todo download de DIP.

**Causa:** consulta de integridade própria de AIP aplicada a DIP.

**Correção:** não consultar pointer file para DIP; códigos de resposta esperados vão para o log como debug, não como erro.

**Detecção:** manual.

**Vista em:** importador de pacotes de preservação (DIP).

---

## 7. Datas e fusos

### L-MIG-39 · Datas no fuso do site; `Y-m-d` não é meia-noite UTC
**Sintoma:** site em UTC-3 ganhava +1 dia à noite; "vence hoje" virava atrasado 3 h antes; prazos em PDF e rótulos de data mostravam o dia anterior; horários exibidos 3 h antes; painel e listagem discordando em outro fuso.

**Causa:** mistura de `date()`/`time()`/`NOW()` do MySQL com `current_time`; data de calendário sem hora interpretada como meia-noite UTC e convertida para o fuso local; `strtotime()` sobre datetime local gravado sem fuso; cálculo em JS no fuso do navegador.

**Correção:** "hoje" = `gmdate('Y-m-d', current_time('timestamp'))` (ou `wp_date('Y-m-d')`); datetime local lido com `new DateTimeImmutable($v, wp_timezone())`; datas de calendário formatadas em UTC ou lidas ao meio-dia UTC antes de exibir. Cálculos de prazo (`is_overdue`, `days_overdue`) no servidor. Em migração: datas da origem entram como texto de calendário (`Y-m-d`, ou a forma incerta original num metadado de texto), nunca convertidas por fuso.

**Detecção:** manual: `date(`, `time()`, `DATE_SUB(NOW()` em lógica de prazo; `strtotime($x)`/`new DateTime($x)` sobre `Y-m-d` seguido de `wp_date(`/`date_i18n(` sem fuso explícito; `new Date()` comparando vencimentos no front.

**Vista em:** plugin de biblioteca; plugin de processos de gestão de acervo; catalogação assistida por IA local; plugin de eventos e certificados em PDF.

### L-MIG-40 · Datas da Wikidata têm precisão
**Sintoma:** exibir data cheia para um dado que só tem ano inventa precisão.

**Causa:** ignorar o campo `precision` do valor `time`.

**Correção:** formatar pela precisão (9 = ano, 10 = mês, 11 = dia; 8 = década, 7 = século). Ao gravar no acervo, gravar na precisão da fonte.

**Detecção:** manual: formatação de `datavalue.value.time` sem leitura de `precision`.

**Vista em:** plugin de biblioteca.

---

## 8. Enriquecimento com dados ligados

### L-MIG-41 · Wikidata é o hub; Getty só por ID; buscar e depois verificar
**Sintoma:** id.loc.gov e VIAF sem resposta do servidor; Google Books sempre 429; uma biblioteca nacional respondendo 403; links errados reconciliando vocabulário em português direto no AAT; consulta SPARQL por ISBN estourando o timeout.

**Causa:** id.loc.gov atrás de desafio anti-robô (403 para não navegador); endpoints antigos do VIAF em 404 e a API nova em 403 (só o AutoSuggest com `Accept: application/json` responde); Google Books sem chave estoura a cota por IP compartilhado e devolve 503 `backendFailed` em cerca de 1 a cada 8 chamadas; reconciliação do Getty não casa português e o SPARQL dele está degradado; `FILTER(REPLACE(...))` no WDQS varre todos os P212 sem índice, e a Wikidata grava ISBN hifenizado.

**Correção:** reconciliar na Wikidata e obter as demais autoridades pelas propriedades do QID (P244 dá a URI canônica da LoC, P214 o VIAF, P1014 o AAT); Getty só por id, em JSON-LD. Para ISBN: busca fulltext por título+autor → `wbgetentities` nos candidatos → comparar P212/P957 normalizados (dígitos e X). Classificar entidades por P31 (para descartar artigos e obras quando se procura pessoa); ignorar afirmações `deprecated`. Outras fontes úteis: SRU da LoC (exige ASCII, transliterar), K10plus (forte em edições brasileiras), Open Library com `q=` livre (title+author é estrito demais; buscar o *work* quando a *edition* não tem autor). Tudo atrás de um gateway com cache, limite de taxa, ETag, pausa depois de 403 e allowlist contra SSRF.

**Detecção:** manual: SPARQL com `FILTER(REPLACE(`; chamadas diretas a id.loc.gov ou à reconciliação do Getty com texto em português.

**Vista em:** plugin de biblioteca; plugin de dados ligados e enriquecimento.

### L-MIG-42 · Reconciliação nunca autoaceita; dúvida do catalogador não vira certeza
**Sintoma:** homônimos com `score 100` e `match:false`; "Sobrenome, Nome" de um pintor conhecido devolvendo outra entidade no topo; "Madeira" (material) casando com a região de Portugal; "Fulano (?)" herdando vínculo sozinho; QIDs de artigos pendurados num autor; QID no autor errado em obras com coautores; vínculos antigos sem dados.

**Causa:** `wbsearchentities` casa pelo começo do rótulo e trava de tipo não resolve homônimos; a normalização remove "?" e "(?)" junto com a pontuação; meta legado de vínculo único por obra; enriquecimento só no clique.

**Correção:** identidade é escolha humana: aceitar só `match:true` confirmado ou curadoria. Buscar variantes (forma limpa e natural primeiro; inversão "Sobrenome, Nome" só em autoria e com até duas palavras). Conferir P31 pelo id (Q5 para pessoa), não pelo rótulo. Marcadores de incerteza ("?", "(?)", "atribuído a", "[...]") detectados explicitamente e mandados para fila de aprovação. Vínculo por autor, não por obra. Dados de identidade confirmada copiados só para campos vazios (sem sobrescrever, sem repetir). Antes de gravar, garantir que o metadado de destino existe (um campo inexistente era pulado em silêncio).

**Detecção:** manual: aceitação automática por `score >= N` em resposta de reconciliação, sem checar `match`; normalização que remove `?` antes de reconciliar.

**Vista em:** plugin de biblioteca; plugin de dados ligados e enriquecimento.

### L-MIG-43 · Publicar amplifica: tipo neutro quando não se sabe
**Sintoma:** JSON-LD publicou uma instituição como `{"@type":"Person", ...}`.

**Causa:** `@type` fixo por campo; a trava de tipo (P31) protegia o vínculo, não a publicação.

**Correção:** classificar pessoa ou organização no vínculo, gravar o tipo e validar contra lista fechada antes de gerar o JSON-LD; `Thing` como padrão.

**Detecção:** manual: `'@type' => 'Person'` literal em gerador de JSON-LD.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-MIG-44 · LLM narra, grafo comprova
**Sintoma:** entidades inventadas por LLM (26% a 35% sem QID válido numa avaliação multilíngue).

**Causa:** LLM usado como fonte de fato em domínio de poucos recursos.

**Correção:** o LLM só ranqueia candidatos para um humano decidir e redige a partir de triplas já verificadas. Ver L-MIG-45 para a verificação do texto.

**Detecção:** manual.

**Vista em:** plugin de dados ligados e enriquecimento.

---

## 9. IA na catalogação

### L-MIG-45 · Texto de IA só é publicado depois de verificação determinística de fidelidade
**Sintoma:** narração citava fatos que não estavam no item.

**Causa:** prompts que pediam "cena de abertura" ou estilo documentário; confiança só no prompt; fraseador de metadados com frases interpretativas ("está situado em").

**Correção:** checagem frase a frase contra o corpus original do item: números de 2+ dígitos, nomes próprios por radical, sobreposição lexical mínima. Uma chamada corretiva; frases reprovadas removidas; abaixo de 25 palavras, rejeitar e cair para template. O template só usa frases que reafirmam o rótulo do metadado. Nenhum caminho grava texto de IA fora da função central do gerador verificado.

**Detecção:** manual: gravação do campo de texto gerado fora da função central do gerador.

**Vista em:** plugin de narrativas/exposições virtuais.

### L-MIG-46 · Regras determinísticas depois do modelo (modo híbrido)
**Sintoma:** modelo local errava identificadores com máscara, datas e formas de nome.

**Causa:** modelos pequenos não respeitam formatos.

**Correção:** depois da resposta e antes da verificação, regras confirmam ou corrigem: máscara achada no texto (tolerando `−` U+2212 vindo de PDFs), data por extenso presente no documento, forma autorizada já usada na coleção, inversão "Sobrenome, Prenomes". Cada intervenção vira checagem visível na revisão. Atenção à métrica: F1 de palavras ignora ordem, então não mede inversão de nome.

**Detecção:** manual.

**Vista em:** catalogação assistida por IA local (Ollama).

---

## 10. Sistemas de origem

### L-MIG-47 · Perfilar a origem: o grão certo e o campo que organiza de verdade
**Sintoma:** significado de colunas deduzido pelo nome; descrição e datas migradas no nível errado; estrutura de coleções da origem que não reflete o setor real do acervo.

**Causa:** num sistema legado de acervo museológico, descrição, local, data e conservação ficam na tabela de partes do objeto, não na da obra, e o banco traz o próprio manual de catalogação em tabelas; num repositório DSpace, o campo de propriedade ou setor é o que organiza o acervo, não a coleção.

**Correção:** perfilar todos os campos antes de desenhar coleções e metadados no destino; ler o manual embutido quando existir; decisões sem resposta na origem (por exemplo, não existe uma "Classificação" única, só colunas candidatas) vão para a curadoria por escrito.

**Detecção:** manual: plano de migração com o perfil de cada tabela/campo e as decisões de curadoria registradas.

**Vista em:** migração de sistema legado de acervo museológico; migração de repositório DSpace.

### L-MIG-48 · Armadilhas de OAI-PMH/EAD em AtoMs reais
**Sintoma:** fundos sem título; `cannotDisseminateFormat`; resposta OAI vindo como página HTML.

**Causa:** anunciar `oai_ead` não garante a entrega (sem cache de XML gerado); AtoM 2.9+ com desafio anti-robô (`/challenge`); fundos fora do `ListSets`; `level="otherlevel"` com nome vazio; um AtoM com várias entidades custodiadoras.

**Correção:** mensagem clara para cada caso; títulos faltantes buscados no `oai_dc`; `otherlevel` vazio com filhos vira subdivisão; custodiadora lida do `<repository>` de cada fundo; equivalência de níveis aplicada só ao rótulo exibido (o nível de origem fica gravado).

**Detecção:** manual: conferir `Content-Type` da resposta OAI antes de parsear; contar fundos do `ListSets` × fundos da interface.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-MIG-49 · DSpace: rótulos espalhados em quatro fontes
**Sintoma:** mapeamento de metadados errado; 21 campos gravam dados que a interface nunca exibe; XSL do tema procurando um campo com grafia diferente da gravada (`caver` × `carver`).

**Causa:** o rótulo de um campo depende de input-forms, XSL do tema, arquivo de mensagens (vale a última definição de chave duplicada) e registro de campos.

**Correção:** cruzar as quatro fontes e validar contra a página real de um item. Campos que a interface não exibe também são migrados.

**Detecção:** manual.

**Vista em:** migração de repositório DSpace.

### L-MIG-50 · Banco legado só no tablespace: subir o servidor original em cópia
**Sintoma:** `.frm` sem dados; MariaDB atual não lê.

**Causa:** dados no `ibdata1` de um MySQL 5.0.

**Correção:** subir o `mysqld` original numa cópia dos arquivos, em outra porta, com `--skip-grant-tables` (só desliga a autenticação) e `innodb_log_file_size` igual ao dos logs; converter latin1 para UTF-8 na leitura; nunca abrir o original. Algumas opções (`skip-networking=0`, `enable-named-pipe`) quebram o 5.0.

**Detecção:** manual.

**Vista em:** migração de sistema legado de acervo museológico.

### L-MIG-51 · PostgreSQL antigo com encoding misto
**Sintoma:** consultas com `string_agg` + `CASE` falham.

**Causa:** alguns registros com bytes Latin-1 numa base declarada UTF-8.

**Correção:** extrair com `SET client_encoding TO 'SQL_ASCII'` (bytes crus) e filtrar com LIKE; converter registro a registro detectando a codificação; revisar nomes corrompidos depois de importar.

**Detecção:** TNC-B03 (no arquivo extraído: UTF-8 inválido ou mojibake).

**Vista em:** migração de sistema de arquivo histórico de um museu (PostgreSQL 9.2).

### L-MIG-52 · OJS 3: caminho do arquivo do galley
**Sintoma:** PDFs não localizados; dump sem `files_dir`.

**Causa:** o caminho é calculado pelo OJS: `{files_dir}/journals/{jid}/articles/{sid}/{stageDir}/{sid}-{genre}-{file}-{rev}-{stage}-{YYYYMMDD}.{ext}`; o `stageDir` de galley publicado é `submission/proof`.

**Correção:** obter o `files_dir` real da instalação; mapear pelo equivalente de `_fileStageToPath()`. Contar galleys no banco × PDFs achados.

**Detecção:** manual.

**Vista em:** migração de revista científica em OJS.

### L-MIG-53 · Exportar planilha para Excel sem destruir dados
**Sintoma:** acentos quebrados ao abrir CSV no Excel; CPF, CEP e telefone com zero à esquerda destruídos; XML inválido; nomes de arquivo problemáticos no ZIP.

**Causa:** Excel não detecta UTF-8 sem BOM; strings numéricas viram número; caracteres de controle vindos de textarea.

**Correção:** CSV para Excel com BOM `\xEF\xBB\xBF` e `;`. Em XLSX, só `int`/`float` do PHP viram célula numérica (nunca `is_numeric()` sobre string); remover `\x00-\x1F` (exceto tab e quebra quando o formato permite); congelar a primeira linha e ligar autofiltro; suportar colunas além de Z; transliterar para ASCII o nome do arquivo (o conteúdo mantém acentos). O CSV destinado ao importador do Tainacan é outro caso: UTF-8, sem conversão de tipos (L-MIG-13). Ver também: neutralização de fórmulas (`=`, `+`, `-`, `@`) em `seguranca-wordpress.md`.

**Detecção:** manual: `fputcsv` sem escrita prévia do BOM em exportação para público com Excel; escritor de XLSX que usa `is_numeric()` para decidir o tipo da célula.

**Vista em:** gestor de periódicos; plataforma de mapeamento comunitário sobre Tainacan; plugin de eventos e certificados em PDF.

---

## 11. Processo

### L-MIG-54 · Exceção à regra exige motivo registrado
**Sintoma:** liberação de leitor com pendência sem rastro.

**Causa:** exceção aplicada sem justificativa.

**Correção:** a API responde 422 exigindo motivo; o motivo vai para o log de auditoria; o front reage ao 422 pedindo a justificativa. Em migração, vale o mesmo para toda transformação fora do plano (L-MIG-01): quem aprovou, quando e por quê.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

---

## 12. Checklist de importação

Cada item tem uma evidência verificável (arquivo, consulta ou número). Guarde as evidências junto do plano.

### Antes

- [ ] **Origem congelada e copiada.** Dump ou cópia dos arquivos com hash; nunca trabalhar no original (L-MIG-50). Evidência: hash do dump.
- [ ] **Perfil da origem.** Por campo: preenchimento, distintos, amostras, valores que parecem códigos, não-valores, campos com `<`, `&`, `>`, `||`, `>>`, quebras de linha e espaços de borda (L-MIG-07, L-MIG-12, L-MIG-14, L-MIG-47). Evidência: relatório de perfil.
- [ ] **Codificação conferida.** Arquivo extraído é UTF-8 válido, sem mojibake (TNC-B03); amostra com acentos conferida visualmente (L-MIG-13, L-MIG-51).
- [ ] **Contagens da origem registradas.** Registros por coleção/tipo, valores por campo, anexos por registro (L-MIG-06).
- [ ] **Mapeamento escrito.** Campo de origem → metadado de destino (tipo, múltiplo sim/não, taxonomia com hierarquia, composto e filhos); lista dos campos de origem sem destino, com decisão (L-MIG-34).
- [ ] **Lista de transformações aprovada.** Cada transformação nomeada, justificada e aprovada; o resto é cópia fiel (L-MIG-01, L-MIG-02, L-MIG-54). Inclui as exigidas pelo destino (`<` → `&lt;`).
- [ ] **Chave natural definida.** Coleção + grupo + identificador de origem, gravada num metadado do item; consulta de unicidade na origem dá 0 repetidos (L-MIG-29).
- [ ] **Separadores escolhidos e testados.** O `multivalued_delimiter` não aparece em nenhum valor; hierarquias com caminho completo `>>`; compostos com número certo de filhos (L-MIG-14).
- [ ] **Metadados de destino criados e conferidos.** Múltiplo marcado onde precisa; `allow_new_terms` decidido por taxonomia; nenhum metadado homônimo vazio (L-MIG-07, L-MIG-16).
- [ ] **Datas.** Formato de calendário definido; datas incertas mantidas na forma original num metadado de texto quando não cabem no tipo data (L-MIG-39).
- [ ] **Backup do destino** e plano de reversão (desfazer por lote, por chave natural).
- [ ] **Importação-piloto** com 20 a 50 registros escolhidos a dedo (casos com acento, `<`, multivalor, composto, hierarquia, anexo, sem título) e auditoria completa do piloto antes do lote inteiro.

### Durante

- [ ] **Gerador fiel.** Script de auditoria origem × arquivo gerado com zero diferenças não aprovadas (L-MIG-01).
- [ ] **Importador configurado.** `encode = utf8` com arquivo já em UTF-8; `delimiter`, `enclosure`, `multivalued_delimiter` conferidos na tela; na 1.0.3, CSV sem BOM (L-MIG-13).
- [ ] **Escrita pela API.** Repositórios + `validate()`; nada de `UPDATE`/`REPLACE` em `wp_postmeta` ou `wp_posts` (L-MIG-17, L-MIG-18; TNC-P04, TNC-T05).
- [ ] **Carga controlada.** Logs desligados durante a carga e religados no fim; lotes curtos e retomáveis; workers em faixas disjuntas; falhas registradas sem parar o lote (L-MIG-34, L-MIG-35).
- [ ] **Anexos conferidos pelo conteúdo.** Tipo MIME e assinatura; nenhum `text/html` onde se esperava imagem ou PDF (L-MIG-37).
- [ ] **Relatório por lote.** Esperado × importado × duplicata × pulado × falhou, com motivo; a soma fecha (L-MIG-06).
- [ ] **Enriquecimento separado da migração.** Nenhuma reconciliação automática grava vínculo; fila de aprovação humana (L-MIG-42).

### Depois

- [ ] **Contagens fecham.** Origem = arquivo = banco (itens, valores por metadado, termos, anexos); diferenças explicadas uma a uma (L-MIG-06).
- [ ] **Valores conferidos.** Auditoria origem × banco por chave natural, comparando com a forma esperada depois de `sanitize_value` (L-MIG-11, L-MIG-12); lista de valores encurtados vazia.
- [ ] **Multivalor e hierarquia.** Valores lidos pelo campo `value` da REST, não por `value_as_string`; nenhum termo com `>>` ou `||` no nome; nenhum termo casado com o ramo errado (L-MIG-14).
- [ ] **Sem duplicatas.** Contagem de itens por chave natural = 1 (L-MIG-29).
- [ ] **Entidades.** Nenhum valor com escape duplo (`&amp;gt;`, `&amp;#039;`); saídas não HTML decodificam (L-MIG-11).
- [ ] **Datas.** Amostra de datas exibidas no front e em PDF bate com a origem, sem deslocamento de dia (L-MIG-39).
- [ ] **Índice.** Reindexar depois de qualquer correção no banco; contagem banco × índice com o mesmo universo (L-MIG-06).
- [ ] **Limpeza.** CSV e scripts removidos de `uploads`; logs religados; redirects 301 das URLs antigas (L-MIG-34, L-MIG-28).
- [ ] **Teste de ida e volta.** Exportar uma coleção e comparar com o arquivo importado (L-MIG-09).
- [ ] **Relatório final arquivado.** Contagens, transformações aplicadas, decisões de curadoria, pendências e órfãos declarados.
