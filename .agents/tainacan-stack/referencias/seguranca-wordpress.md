# Segurança em plugins WordPress e Tainacan

Lições de segurança, privacidade e LGPD que as regras `TNC-S*`/`TNC-W*` do `tnc-check` não detectam ou só detectam em parte: visibilidade do Tainacan, cache por usuário, rotas públicas, segredos, SSRF, arquivos, saída, contas e IA. Quando a lição é exatamente o que uma regra já cobre, ela aparece curta, apontando o ID.

Citações `arquivo:linha` são relativas a `src/` do núcleo do Tainacan (1.4.1, salvo indicação). **Δ** marca diferença entre 1.0.3 e 1.4.1.

## Sumário

1. [Visibilidade e permissões do Tainacan](#1-visibilidade-e-permissões-do-tainacan) (L-SEG-01 a 11)
2. [Confiança no cliente](#2-confiança-no-cliente) (L-SEG-12)
3. [Cache por usuário e diagnóstico](#3-cache-por-usuário-e-diagnóstico) (L-SEG-13 e 14)
4. [Rotas públicas, limite de taxa e IP atrás de proxy](#4-rotas-públicas-limite-de-taxa-e-ip-atrás-de-proxy) (L-SEG-15 a 19)
5. [Segredos, cifra e assinaturas](#5-segredos-cifra-e-assinaturas) (L-SEG-20 a 33)
6. [SSRF, PDFs e redirecionamentos](#6-ssrf-pdfs-e-redirecionamentos) (L-SEG-34 a 37)
7. [Uploads, arquivos e servidor web](#7-uploads-arquivos-e-servidor-web) (L-SEG-38 a 43)
8. [SQL além do `prepare()`](#8-sql-além-do-prepare) (L-SEG-44 e 45)
9. [Saída e escape](#9-saída-e-escape) (L-SEG-46 a 51)
10. [Contas, dados pessoais e LGPD](#10-contas-dados-pessoais-e-lgpd) (L-SEG-52 a 59)
11. [IA e modelos de linguagem](#11-ia-e-modelos-de-linguagem) (L-SEG-60 e 61)
12. [Processo de trabalho](#12-processo-de-trabalho) (L-SEG-62 e 63)

## Comece por aqui

- L-SEG-01: item público exige item publicado **e** coleção publicada; `can_read()` do anônimo só olha o item.
- L-SEG-02: ids vindos do navegador ou do índice passam por filtro de visibilidade.
- L-SEG-03: metadado privado (e o composto pai) fica fora de exportações, exposers e textos gerados.
- L-SEG-05: `?status=private` na REST do Tainacan: o núcleo só confere se o status existe.
- L-SEG-13: valor cacheado nunca leva filtro de permissão sem o usuário na chave.
- L-SEG-15: `REMOTE_ADDR` atrás de proxy, `X-Forwarded-For` forjável, lista de proxies confiáveis.
- L-SEG-16: rota pública cara precisa de limite, trava e cache, sobretudo quando o motor cai.
- L-SEG-20: segredo por constante no `wp-config.php`, sem regravar no banco e redigido em logs.
- L-SEG-34: SSRF em URL configurável e o comportamento de `wp_http_validate_url`.
- L-SEG-38: `.htaccess` não protege nada em Nginx.

## Referência rápida: visibilidade no núcleo do Tainacan

- Capabilities de item por coleção: `tnc_col_{id}_read_item`, `_edit_item`, `_delete_item` (meta caps singulares), `_read_private_items`, `_edit_items`, `_publish_items` etc. (`classes/entities/class-tainacan-collection.php:186-202`). Catálogo completo, incluindo `tnc_rep_read_private_metadata` e `tnc_col_%d_read_private_metadata`, em `Roles::populate_tainacan_capabilities()` (`classes/class-tainacan-roles.php:92-512`). Quem tem `manage_tainacan` ganha toda cap `tnc_*` (`:686-690`).
- Checagens: `$entity->can_read()`/`can_edit()` (`classes/entities/class-tainacan-entity.php:422-452`), implementadas no repositório (`classes/repositories/class-tainacan-repository.php:754-773` para edição, `:784-812` para leitura; `:760` [1.0.3]). `$collection->user_can('edit_items')` monta `tnc_col_{id}_edit_items` (`class-tainacan-collection.php:1267-1279`).
- `Repository::can_read()` para **anônimo** devolve só `get_post_status_object(status do próprio post)->public` (`class-tainacan-repository.php:788-794`; `:764-770` [1.0.3]). Para **logado**, chama `user_can(read_post)`, e `Items::map_meta_cap()` acrescenta `read_private_posts` da coleção quando ela não é pública (`classes/repositories/class-tainacan-items.php:782-810`; comentário "Even if the item is public, user must have read_private_posts if the collection is private" em `:784`, `:623` [1.0.3]).
- REST de itens: `GET /items/{id}` usa `$item->can_read()` (`classes/api/endpoints/class-tainacan-rest-items-controller.php:817-825`); `GET /collection/{cid}/items` confere `$collection->can_read()` e a legibilidade das taxonomias do `taxquery`, e `GET /items` sem coleção devolve `true` (`:847-877`). A listagem acrescenta `perm = 'readable'` (`classes/api/class-tainacan-rest-controller.php:184`) e, para logado em várias coleções, `Items::fetch` filtra por `read_private` de cada post type (`class-tainacan-items.php:397-407`).
- Parâmetro `status` → `post_status` (`class-tainacan-rest-controller.php:104`). **Δ** [1.4.1]: `sanitize_callback` `tainacan_sanitize_post_statuses` (`:408-415`, `:799-820`) só confere se cada status existe em `get_post_stati()` e expande `any`. [1.0.3]: só um `enum` com os status existentes (`:379-386` [1.0.3]). Nenhuma das duas confere a capacidade de ler aquele status.
- Campos extras na resposta por `tainacan-api-response-{entidade}-meta`: o núcleo faz `get_post_meta` de cada chave devolvida, e o próprio código avisa para checar permissão antes (`class-tainacan-rest-items-controller.php:280-292`).
- Exposers atuam em `rest_request_after_callbacks` e `tainacan-api-get-items-alternate` (`classes/exposers/class-tainacan-exposers-handler.php:33-35`): um exposer ou motor alternativo próprio herda a obrigação de filtrar visibilidade.
- Valores de `metaquery`/`taxquery` passam por `sanitize_value()` (`wp_kses` com a lista `post` sem a tag `a`, mais `trim`) (`class-tainacan-rest-controller.php:259-268`).

## 1. Visibilidade e permissões do Tainacan

### L-SEG-01 · Item público é item publicado de coleção publicada
**Sintoma:** item publicado de coleção privada imprimível, exportável ou visível em JSON-LD, prévia de busca e relacionados vindos do índice; rascunho servido por rota pública.

**Causa:** a checagem olhava só o status do item. No núcleo, `can_read()` sem usuário logado devolve apenas se o status do próprio post é público (`classes/repositories/class-tainacan-repository.php:788-794`); a exigência de `read_private_posts` da coleção privada só entra pelo `map_meta_cap` de itens, que roda para usuário logado (`classes/repositories/class-tainacan-items.php:782-810`). Resultados vindos de Elasticsearch/OpenSearch ou de índice vetorial não passam por nada disso.

**Correção:** um helper único `pode_ler_item($id)`: item com status público **e** coleção (`tnc_col_{id}_item` → coleção) com status público, ou, para logado, `current_user_can('read_post', $id)` (que já aplica a regra da coleção). Usar em toda rota pública, em todo resultado de índice e no `permission_callback` de leitura (em vez de `__return_true`); rascunho ou fora do escopo → 404. Mandar o nonce quando houver sessão, senão rascunho do próprio autor dá 401. `$item->can_read()` sozinho basta para logado, não para anônimo (inferência sobre o código 1.4.1 e 1.0.3, confirmada em execução por um dos projetos).

**Detecção:** TNC-S04 só para rota sem `permission_callback` ou de escrita aberta; manual: rota de leitura com `__return_true` que recebe `item_id`, ou `get_post_status($id) === 'publish'` sem olhar a coleção.

**Vista em:** plugin de impressão de itens, plugin de dados ligados e enriquecimento, plugin de biblioteca.

### L-SEG-02 · Ids vindos do navegador ou do índice passam por filtro de visibilidade
**Sintoma:** rota pública de trechos devolvia texto de itens privados a partir de ids enviados pelo cliente; títulos de relacionados de coleções privadas em painéis públicos; mapa slug→coleção no HTML incluindo coleções privadas e rascunhos; id do autor exposto em destaques; sugestões ortográficas tiradas de metadados privados.

**Causa:** confiar em lista de ids do cliente e em listas montadas sem filtro de status; tratar taxonomia não publicada como pública.

**Correção:** todo id recebido passa por um `visible_ids()` (L-SEG-01) antes de `get_post`/meta; relacionado de coleção privada só para quem pode editá-la, senão rótulo neutro ("item não público"); taxonomia não publicada = privada; mapas e listas no HTML só com coleções públicas; lista de páginas com `read_post`; criar página exige `publish_pages` e é idempotente; nunca devolver `post_author` em resposta pública; sugestões só de metadados visíveis a quem pergunta.

**Detecção:** manual: rota pública que lê `ids`/`item_ids` do request; `post_author` em resposta REST pública.

**Vista em:** plugin de busca segmentada, painel público de indicadores.

### L-SEG-03 · Metadado privado fora de exportações, exposers e conteúdo gerado
**Sintoma:** exportação MARC/JSON, facetas ou texto público gerado (narrativa, corpus para IA) poderiam carregar metadado privado.

**Causa:** corpus montado com todos os metadados; status do metadado ignorado, ou checado no filho e não no composto pai.

**Correção:** para conteúdo exposto, só metadado com status público, e o composto pai também público; descartar campos de uso interno para anônimo; metadado privado nunca vira faceta nem filtro em rota anônima; contar e registrar os pulados. Exposer, motor alternativo (`tainacan-api-get-items-alternate`) e campos extras por `tainacan-api-response-{entidade}-meta` herdam essa obrigação (ver referência rápida). Para quem lê privados, a cap é `tnc_col_{id}_read_private_metadata` / `tnc_rep_read_private_metadata` (`classes/class-tainacan-roles.php:95-228`, `:238-505`). Ver também: metadado privado criado por migração some de `fetch_by_collection` (nucleo-tainacan.md).

**Detecção:** manual: código que gera saída pública chamando `fetch_by_collection(` com `post_status` incluindo `private`, ou percorrendo metadados sem olhar `get_status()`.

**Vista em:** plugin de biblioteca, plugin de narrativas/exposições virtuais.

### L-SEG-04 · Capability granular por rota; menu `read` não protege a API
**Sintoma:** papel de catalogação lia multas, relatórios e usuários pela REST apesar do menu escondido; páginas do admin registradas com `read` (padrão das páginas nativas do Tainacan) com conteúdo acessível a qualquer logado.

**Causa:** dezenas de rotas compartilhando três checagens genéricas; esconder no menu tomado como controle de acesso.

**Correção:** menu pode usar `read`, mas o render e cada rota exigem a capacidade real (`manage_options` ou cap própria do plugin), repetida no handler; `args` com `validate_callback`/`sanitize_callback`; rotas do SPA levam a cap exigida em `meta.cap`; testar com `rest_do_request()` por papel (L-SEG-62).

**Detecção:** TNC-S04 (rota sem permissão ou de escrita aberta); manual: muitas rotas com o mesmo `permission_callback` genérico; `add_submenu_page(..., 'read', ...)` cujo render não chama `current_user_can`.

**Vista em:** plugin de biblioteca, colaboração do público em metadados.

### L-SEG-05 · `?status=private` na REST do Tainacan
**Sintoma:** requisição anônima com `?status=private` listava itens privados quando a listagem era roteada para o índice; depois se mediu que o próprio núcleo (consulta com `perm=readable`) também listava um item privado ao anônimo.

**Causa:** o sanitizador do parâmetro `status` só confere se o status existe (`classes/api/class-tainacan-rest-controller.php:799-820`; **Δ** em 1.0.3 é um `enum`, `:379-386` [1.0.3]); quem implementa `tainacan-api-get-items-alternate` recebe o `post_status` pedido e responde o que o índice tiver.

**Correção:** status não público só é honrado para quem tem `read_private_posts` daqueles post types (`current_user_can( get_post_type_object($pt)->cap->read_private_posts )`); anônimo recebe sempre o conjunto público, mesmo que peça outro; logado sem a capacidade cai na consulta SQL (regra de autoria). Não usar o SQL como "fallback seguro" para anônimo sem confirmar o filtro. Vale reportar ao núcleo.

**Detecção:** manual: `status`/`post_status` da requisição repassado à consulta ou ao índice sem checar `read_private_posts`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch).

### L-SEG-06 · Termo (ou recurso) precisa pertencer ao item que o usuário pode editar
**Sintoma:** qualquer usuário com `edit_posts` podia reescrever o vínculo externo de qualquer termo.

**Causa:** autorização por capacidade genérica, sem conferir a relação item↔termo.

**Correção:** exigir `edit_post` no item **e** `has_term()` do termo no item, na taxonomia esperada (ou `manage_options`); escopo que afeta muitos itens (instituição, coleção inteira) exige administrador; validar identificadores externos por regex (ex.: QID `^Q\d+$`).

**Detecção:** manual: `permission_callback` com `current_user_can('edit_posts')` em rota que recebe `term_id`/`item_id` do cliente.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-SEG-07 · "Pode editar" e "pode publicar" são regras diferentes
**Sintoma:** 403 no formulário do item em coleção privada; o painel do plugin sumia da catalogação.

**Causa:** a autorização de edição reaproveitava a regra de exposição pública (coleção publicada).

**Correção:** separar: editar = item do Tainacan + `edit_post`; expor ao público = L-SEG-01. Fora do escopo do plugin, devolver resposta vazia para o painel se esconder, não erro.

**Detecção:** manual.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-SEG-08 · IDOR: `get_param('id')` mistura query string e rota
**Sintoma:** acesso a registro alheio trocando `?id=` na URL.

**Causa:** `$request->get_param('id')` junta parâmetros de várias fontes; a query string pode prevalecer sobre o id capturado na rota, enquanto o `permission_callback` conferiu outro.

**Correção:** ids da rota por `$request->get_url_params()`; checar permissão sobre o recurso efetivamente carregado, no callback e no handler.

**Detecção:** manual: `get_param('id')` em rota com `(?P<id>...)`.

**Vista em:** plugin de processos de gestão de acervo.

### L-SEG-09 · Página com status `private` e papéis sem edição
**Sintoma:** gestores e avaliadores com papel de assinante recebiam 404/403 nas páginas de relatório.

**Causa:** página `private` exige `read_private_pages`.

**Correção:** conceder só `read_private_pages` (não `read_private_posts`) a esses papéis. A página privada não protege os endpoints que ela consome (L-SEG-13).

**Detecção:** manual.

**Vista em:** plataforma de mapeamento comunitário.

### L-SEG-10 · Dado sensível não vira item do Tainacan
**Sintoma:** risco de expor pacientes e consultas.

**Causa:** itens são feitos para publicação: CPT `public => true`, `show_in_rest => true` (`classes/entities/class-tainacan-collection.php:131-162`), aparecem na REST, nos exposers e no índice de busca.

**Correção:** dados pessoais ou clínicos em tabela própria, ligada ao item pelo id; campo clínico cifrado e decifrado só para o profissional responsável.

**Detecção:** manual.

**Vista em:** plugin de agendamento.

### L-SEG-11 · Janela de "condições de uso" não é controle de acesso
**Sintoma:** administrador acreditava que o arquivo estava protegido.

**Causa:** a janela intercepta o clique no front; a URL em `uploads/` continua pública.

**Correção:** declarar o limite na tela, no README e no readme.txt; restrição real é a condição de acesso do item no Tainacan ou entrega por PHP (L-SEG-38). Textos da janela escapados (`esc_html`, `textContent`).

**Detecção:** manual.

**Vista em:** kit de memorial virtual.

## 2. Confiança no cliente

### L-SEG-12 · O servidor calcula; o cliente só escolhe
**Sintoma:** POST montado à mão podia remover termo fora do grupo, gravar identificador externo arbitrário, vincular instituição errada, trocar nome/caminho/hash de arquivo enviado, marcar etapa como `signed` ou forjar quem aprovou.

**Causa:** o formulário enviava a lista a remover, o identificador, a chave derivada, o descritor do upload ou o `approved_by`, e o servidor gravava como recebido.

**Correção:** enviar só o grupo e a opção escolhida, e calcular a diferença no servidor; aceitar só identificador que esteja entre os candidatos que o próprio servidor ofereceu (rótulo, tipo e ids vêm de lá); derivar chaves (instituição, coleção) do item no servidor; registrar o descritor do arquivo no upload (nome, tamanho, hash, quem, quando) e, ao salvar, trocar o que veio do cliente pelo registrado; estado (`status`, assinatura) e autoria (aprovador = usuário da sessão) nunca vêm do corpo. Edição concorrente com revisão otimista (`rev`) e HTTP 409.

**Detecção:** manual: handler que lê `remover[]`, `qid`, `path`/`url`/`file`, `approved_by`/`user_id` ou `status` de `$request->get_param` e grava sem confrontar com dado do servidor.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de processos de gestão de acervo, gerador de documentação com IA.

## 3. Cache por usuário e diagnóstico

### L-SEG-13 · Valor cacheado com filtro de permissão sem o usuário na chave
**Sintoma:** anônimo herdava por 1 h a lista com coleções relacionadas privadas que um usuário logado tinha populado; dados de gestão (nota, contatos) acessíveis a anônimo por `curl` no endpoint de uma página privada.

**Causa:** `can_read()`/`current_user_can()` aplicado **dentro** do valor guardado em transient ou cache de objetos, com chave sem usuário ou papel; endpoint `wp_ajax_nopriv_` servindo a página privada; coluna escondida só no HTML/JS, com o dado ainda no JSON.

**Correção:** cachear o resultado bruto (guardando o que for preciso para filtrar, como o id da coleção relacionada) e filtrar por permissão **depois** de ler o cache; ou incluir o nível de acesso na chave. O payload é decidido no servidor por uma função central (`viewer_can_*`), não pelo JS. Fechar exposições laterais: média que, com filtros, vira o valor de uma só pessoa; campos mortos no payload.

**Detecção:** TNC-S03 cobre nonce/capability do handler; manual: `set_transient`/`wp_cache_set` com valor calculado por `current_user_can`/`can_read()` e chave sem id/papel do usuário.

**Vista em:** plugin de busca segmentada, plataforma de mapeamento comunitário.

### L-SEG-14 · Diagnóstico interno vazando ao visitante
**Sintoma:** resposta pública trazia o motivo da queda (endereço do motor de busca, nome do índice), o motor usado e `took_ms`.

**Causa:** campos de diagnóstico sempre incluídos.

**Correção:** um `strip_diagnostics()` na saída: esses campos só para `manage_options`.

**Detecção:** manual: chaves como `fallback`, `engine`, `es_url`, `error.reason` em resposta REST pública.

**Vista em:** plugin de busca segmentada.

## 4. Rotas públicas, limite de taxa e IP atrás de proxy

### L-SEG-15 · IP do cliente: `REMOTE_ADDR`, proxies confiáveis e cabeçalhos forjáveis
**Sintoma:** atrás de proxy reverso, CDN, WAF ou controlador de entrada de contêineres, todos os visitantes caíam num balde só (o país inteiro bloqueado junto); quem trocava `X-Forwarded-For`/`X-Real-IP` escapava da cota e falsificava o IP do registro de auditoria; em servidor com `set_real_ip_from 0.0.0.0/0` (padrão de um painel de hospedagem, regravado em atualização) até o `REMOTE_ADDR` era forjável, burlando fail2ban e limitador de login; seed por WP-CLI recusada pelo próprio limitador.

**Causa:** sem `mod_remoteip`/`real_ip` configurado, o PHP vê o IP do proxy; cabeçalhos do cliente tratados como verdade; IPv6 contado por endereço inteiro (contornável trocando o sufixo).

**Correção:**
- Um helper único de IP, igual em todos os plugins irmãos: base `REMOTE_ADDR`; só se ele estiver numa lista de proxies confiáveis usar o **último** salto de `X-Forwarded-For` (`X-Real-IP` só sem XFF); lista numa constante comum (`TAINACAN_TRUSTED_PROXIES`, IPs/CIDRs) no `wp-config.php`, além de loopback e redes privadas.
- Sem IP público confiável, não limitar por IP (balde único vira arma de DoS): contar só tentativas sem correspondência, ou limitar por hash do identificador (e-mail, código).
- IPv6 agrupado por /64; não gravar contador quando já bloqueado; checar o limite antes de montar o trabalho caro; contar só cache miss; 429 com `Retry-After`.
- Auditoria (quem exportou dados pessoais) grava o IP do helper, nunca o cabeçalho cru.
- No servidor web, `set_real_ip_from` só para loopback e as redes do proxy; conferir de novo após atualizar o painel de hospedagem.
- Limitador vale também no WP-CLI: oferecer filtro explícito, válido só no processo, para seeds e testes.
- O contador não fica dentro de `permission_callback` (roda mais de uma vez por requisição): TNC-W10.

**Detecção:** manual: `$_SERVER['HTTP_X_FORWARDED_FOR']`/`HTTP_X_REAL_IP` lido sem conferir `REMOTE_ADDR` contra a lista; primeiro elemento de XFF usado como IP; `grep 'set_real_ip_from 0.0.0.0/0'` na configuração do Nginx. TNC-W10 para efeito colateral em `permission_callback`.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de busca segmentada (política comum a cinco plugins irmãos), indexador de busca, plugin de impressão de itens, plugin de eventos e certificados em PDF, plataforma de mapeamento comunitário, gerador de comprovantes, plugin de pesquisas, servidor de testes.

### L-SEG-16 · Rota pública cara: limite, vaga, trava e cache, sobretudo na queda do motor
**Sintoma:** com o índice fora, cada pedido anônimo repetido rodava de 6 a 73 s de `LIKE '%termo%'` em 13 milhões de linhas de postmeta; impressão e exportação em lote abusáveis; planilha de item único sem cota; `set_time_limit(0)` e `wp_raise_memory_limit('admin')` alcançáveis por anônimo; `/count` e `POST /batch` rodando a consulta pesada do Tainacan de graça.

**Causa:** guarda aplicada só a uma das rotas; resposta do fallback SQL deliberadamente não cacheada ("não congelar resultado degradado"); cota ler-somar-gravar não atômica.

**Correção:**
- Guarda em **todas** as rotas que computam (contagem, lote, exportação, prévia), com `permission_callback` real (token validado), limite por IP (L-SEG-15) e cota por conta e por perfil.
- Vagas simultâneas como limite duro: linhas `_slot_N` em `wp_options` com `INSERT IGNORE`; no máximo poucos cálculos SQL caros simultâneos no site.
- Trava por termo (`wp_cache_add` atômico, com cache de objetos persistente) devolvendo `busy`; fallback cacheado por 60 s; TTL anônimo de até 120 s quando não há cache de objetos; falha vira resposta vazia com cache negativo de 1 min.
- Teto de ids por requisição, pedaço máximo (ex.: 20) e `offset` máximo; orçamento de tempo com o resto devolvido para repetir; orçamento SQL por pedido (`MAX_EXECUTION_TIME`/`max_statement_time`).
- Elevar limite de tempo/memória só depois da guarda e do tamanho da vaga; gerar em fluxo (primeiro byte rápido).

**Detecção:** TNC-P01/TNC-P03 para a consulta em si; manual: rota com `__return_true` que executa `LIKE '%...%'` sem cache nem limite; `set_time_limit(0)`/`wp_raise_memory_limit` em caminho anônimo. Ver também: desempenho-e-escala.md.

**Vista em:** plugin de busca segmentada, plugin de impressão de itens, painel público de indicadores.

### L-SEG-17 · Formulários e verificadores públicos: defesa em camadas e dado mínimo
**Sintoma:** formulários públicos sujeitos a robôs; validador público de documentos podendo virar oráculo de força bruta ou vetor de XSS; captcha baseado em transient abrindo DoS em `wp_options`.

**Causa:** rota anônima por natureza; um transient por visitante.

**Correção:**
- Nonce + honeypot + tempo mínimo de preenchimento + limite por IP + captcha opcional, replicados em **toda** rota pública nova (um verificador central chamado por todas).
- Captcha local sem estado (HMAC com `wp_salt`), sem gravar por visitante.
- Verificador público: `permission_callback` exige que o código exista; limite (ex.: 30/min) em janela fixa, contado uma vez por requisição e compartilhado com a página; página `noindex`; mostra só metadados mínimos (primeiro nome e iniciais), nunca o conteúdo.
- HTML montado escapando cada valor e impresso por `wp_kses` com lista própria, sem `phpcs:ignore` de escape.
- Idempotência por janela (reaproveitar comprovante recente em vez de numerar de novo); numeração por exercício que nunca reaproveita; `wp_safe_redirect` em handlers `admin_post_*`.

**Detecção:** TNC-S03 (nonce em handler); manual: `set_transient(` com chave por requisição em handler anônimo; `wp_redirect(` em `admin_post_*`; `phpcs:ignore WordPress.Security.EscapeOutput` em saída de dados.

**Vista em:** gerador de comprovantes, painel central de métricas, plugin de processos de gestão de acervo, colaboração do público em metadados.

### L-SEG-18 · Resposta idêntica contra enumeração em autoatendimento
**Sintoma:** risco de descobrir quem participou de um evento pela reemissão de certificado.

**Causa:** mensagens diferentes conforme o cadastro existe ou não.

**Correção:** resposta sempre idêntica; envio só ao endereço cadastrado; honeypot; limite por hash do e-mail. Ver L-SEG-55 para usuários do WordPress.

**Detecção:** manual.

**Vista em:** plugin de eventos e certificados em PDF.

### L-SEG-19 · Endpoint de setup sem shell: chave, idempotência e remoção
**Sintoma:** necessidade de montar conteúdo em hospedagem sem SQL nem shell.

**Causa:** ambiente restrito.

**Correção:** plugin de setup com rota REST idempotente cuja permissão confere uma chave com `hash_equals()`; remover o plugin depois do uso.

**Detecção:** TNC-S04 se for rota de escrita com `__return_true`; manual: ausência de `hash_equals` no `permission_callback`.

**Vista em:** ferramenta de deploy em hospedagem compartilhada.

## 5. Segredos, cifra e assinaturas

Credencial literal no código é TNC-S08; as lições abaixo tratam do que acontece depois que o segredo existe.

### L-SEG-20 · Segredo por constante, sem regravar no banco, redigido em logs
**Sintoma:** senha do motor de busca e chaves de API legíveis em `wp_options` e em logs, mesmo com a constante definida no `wp-config.php`.

**Causa:** segredo gravado em opção; `Settings::update()` partia de `all()` (já com as constantes aplicadas) e regravava tudo a cada atualização de carimbo de tempo; logger recebendo o array de configurações inteiro.

**Correção:** ler de constante ou variável de ambiente, com precedência sobre a opção; o campo fica bloqueado e a tela mostra quais chaves estão fixas; o `update` parte da opção **crua** e pula as chaves sobrepostas por constante; formulário vazio = manter, valor especial (`__clear__`) = apagar; todo log passa por redação de segredos.

**Detecção:** TNC-S08 (literal no código); manual: `update_option(` com array montado por função que mescla constantes; `update_option` com chaves `*password*`/`*secret*`/`*token*`; `error_log`/logger recebendo configurações com `*_api_key`.

**Vista em:** indexador de busca, plugin de narrativas/exposições virtuais, plugin de processos de gestão de acervo.

### L-SEG-21 · Cifra de verdade, e senha não passa por `sanitize_text_field`
**Sintoma:** chave "cifrada" com AES-CBC e IV fixo derivado do salt (ofuscação); senha SMTP deixava de funcionar depois de salva.

**Causa:** IV fixo; `sanitize_text_field` remove e normaliza caracteres da senha.

**Correção:** `sodium_crypto_secretbox` com nonce aleatório por valor, prefixo de versão no texto cifrado e leitura do formato legado na migração; nenhuma sanitização de texto em senha (validar só tamanho/tipo); campo `type="password"` vazio = manter; nunca devolver o valor na API (só um booleano "configurada").

**Detecção:** manual: `openssl_encrypt(... 'aes-256-cbc' ...)` com IV derivado de `wp_salt`/constante; `sanitize_text_field(` em campo `password`/`senha`/`secret`/`api_key`.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de processos de gestão de acervo.

### L-SEG-22 · Máscara do segredo reenviada como valor novo
**Sintoma:** integração de IA passou a dar 401 depois de salvar as configurações.

**Causa:** o endpoint devolvia `********` como máscara e o front reenviou a máscara, gravada como chave.

**Correção:** não ecoar segredo nem mascarado (preferir o booleano da L-SEG-21); se houver máscara, o backend detecta o padrão e mantém o valor gravado.

**Detecção:** manual: rota de settings que devolve máscara e cujo save grava o campo sem compará-lo com ela.

**Vista em:** plugin de biblioteca.

### L-SEG-23 · Chave derivada dos salts morre quando os salts mudam
**Sintoma:** após troca dos salts do `wp-config.php`, senhas e chaves privadas cifradas deixam de abrir.

**Causa:** material de chave derivado de `wp_salt('auth')`.

**Correção:** documentar que trocar salts invalida os segredos (ou usar chave própria em constante); para chave de assinatura, se a privada não abrir, gerar nova e **manter as públicas antigas** para que assinaturas antigas continuem verificáveis; nunca apagar a opção de chaves, nem no `uninstall.php`.

**Detecção:** manual: `wp_salt(` como material de chave; `uninstall.php` com `delete_option` de opção `*keys*`/`*sign*`.

**Vista em:** plugin de processos de gestão de acervo.

### L-SEG-24 · A coluna precisa caber o texto cifrado antes de cifrar
**Sintoma:** risco de truncamento e perda do dado na migração para cifra.

**Causa:** `varchar(50)` não cabe um texto cifrado de 81 bytes.

**Correção:** alargar a coluna **antes** de cifrar; colunas usadas em cálculo ou ordenação (DATE) ficam fora da cifra.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-SEG-25 · Escrita direta fora do repositório pula cifra e selo
**Sintoma:** dado pessoal gravado em claro; varredura de integridade acusaria violação em linha legítima.

**Causa:** `$wpdb->insert/update` direto numa tabela cujo repositório cifra e re-sela.

**Correção:** escrever sempre pelo repositório, ou repetir explicitamente `encrypt_row` + `seal_row`.

**Detecção:** manual: `$wpdb->insert|update` em tabela cifrada/selada fora da classe repositório.

**Vista em:** plugin de biblioteca.

### L-SEG-26 · Selo HMAC por linha: modo estrito depois do selamento inicial
**Sintoma:** apagar `row_sig` junto com o dado fazia a linha ser re-selada como nova.

**Causa:** assinatura ausente tratada como linha nova.

**Correção:** após o selamento inicial, linha sem assinatura ou assinada por outra chave = violação; chave fora do banco; colunas novas ficam fora do selo até a normalização.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-SEG-27 · Selo conferível por terceiros: Ed25519 sobre JSON canônico
**Sintoma:** selo HMAC com `wp_salt('auth')` só é conferível pelo próprio servidor e morre na troca de salts; `wp_json_encode` sem ordenar chaves gera hash instável.

**Causa:** segredo simétrico atrelado à instalação; serialização não canônica.

**Correção:** JSON canônico (chaves ordenadas recursivamente), SHA-256 e assinatura Ed25519 com chave pública publicada (conferência independente, inclusive de recibo fora do banco); cadeia por `prev_hash` sob trava; tabela só de inserção; rotação de chave sem perder as antigas (L-SEG-23).

**Detecção:** manual: `hash_hmac(` com `wp_salt(` usado como selo exposto a terceiros; `wp_json_encode(` alimentando hash sem `ksort` recursivo.

**Vista em:** plugin de processos de gestão de acervo.

### L-SEG-28 · Assinatura HMAC entre instalações: texto canônico, não a URL crua
**Sintoma:** assinaturas entre sites parceiros não conferiam.

**Causa:** a ordem dos parâmetros muda e o WordPress acrescenta `rest_route`/`_locale`.

**Correção:** assinar um texto canônico: método + rota + query ordenada sem esses parâmetros + timestamp + SHA-256 do corpo; janela de 5 min; o `permission_callback` é a conferência; resposta do parceiro é entrada não confiável (sanitizar).

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-SEG-29 · Token de integração nunca vai ao front
**Sintoma:** token do site exposto no JS e necessidade de CORS no servidor central.

**Causa:** o rastreador do navegador falava direto com a central.

**Correção:** o JS posta na REST local (mesma origem); o PHP grava numa fila de saída e um cron encaminha com o cabeçalho de token, com nova tentativa e descarte após algumas falhas.

**Detecção:** manual: token/segredo em `wp_localize_script`/`wp_add_inline_script` de front.

**Vista em:** painel central de métricas e coletor nos sites.

### L-SEG-30 · Token de autoatendimento no fragmento da URL
**Sintoma:** risco de token em logs do servidor, no `Referer` e no retorno de gateway de pagamento.

**Causa:** token em query string.

**Correção:** token no fragmento (`#t=`), lido pelo JS e enviado em cabeçalho; só o hash fica guardado; retorno do gateway sem token, recuperado de `sessionStorage`.

**Detecção:** manual: `add_query_arg('token'` em links de autoatendimento.

**Vista em:** plugin de agendamento.

### L-SEG-31 · Teste de vazamento procura o segredo, não o formato
**Sintoma:** falso positivo "chave vazou" por causa de `placeholder="sk-..."`.

**Causa:** busca por prefixo.

**Correção:** comparar a saída com o valor real (e com frações dele e com a versão cifrada), redigindo antes de imprimir o resultado do teste.

**Detecção:** manual.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-SEG-32 · Credenciais fora de chat, memória e repositório
**Sintoma:** senhas em texto em notas de contexto, configurações e conversas com agentes.

**Causa:** falta de canal seguro.

**Correção:** credenciais com o responsável, nunca em repositório nem em arquivo de contexto de agente; trocar as que circularam; repositório com endereços internos fica privado e o arquivo de contexto sai antes de abrir o código.

**Detecção:** TNC-S08 para código; manual: scanner de segredos também sobre `.md` de contexto versionados.

**Vista em:** vários projetos.

### L-SEG-33 · Login por OIDC: allowlist de algoritmo e vínculo por provedor+sub
**Sintoma:** escalada de privilégio via SSO, achada em auditoria.

**Causa:** riscos clássicos de OIDC e vínculo automático por e-mail com conta privilegiada.

**Correção:** allowlist RS256/384/512 antes de tudo (`alg:none` e HS256 caem); JWKS → PEM + `openssl_verify`; `state` de uso único; PKCE; vínculo por `provider|sub`, nunca por e-mail não verificado nem com conta de equipe.

**Detecção:** manual: decodificação de JWT sem checar `alg` contra allowlist.

**Vista em:** plugin de biblioteca.

## 6. SSRF, PDFs e redirecionamentos

### L-SEG-34 · URL configurável: SSRF e o comportamento de `wp_http_validate_url`
**Sintoma:** URL de provedor configurável podia apontar para a rede interna; no sentido oposto, endereço legítimo de web service institucional (rede interna) era recusado como inválido.

**Causa:** requisição HTTP a endereço informado; `wp_http_validate_url` (usado por `wp_safe_remote_*`) rejeita hosts internos/privados por desenho.

**Correção:** padrão: `wp_safe_remote_request()` + validação de esquema/host/porta, `redirection => 0`, timeout curto, `limit_response_size`; XML com `LIBXML_NONET` (sem entidades externas). Rede privada só com opção explícita de quem gerencia o site (`manage_options`), com validação própria (esquema http/https, host) e os mesmos limites na chamada. A chave do serviço nunca volta pela API.

**Detecção:** manual: `wp_remote_get/post(` com URL vinda de opção ou parâmetro, sem `wp_safe_remote_*`, sem `redirection => 0` ou sem `limit_response_size`.

**Vista em:** plugin de narrativas/exposições virtuais, plugin de processos de gestão de acervo.

### L-SEG-35 · Geradores de PDF (Dompdf, mPDF): recursos remotos e arquivos locais
**Sintoma:** `<img src="file://...">` lido pelo mPDF (SSRF/LFI); no Dompdf seguro, o logotipo não aparecia.

**Causa:** mPDF busca recursos referenciados no HTML; Dompdf roda sem recursos remotos.

**Correção:** `htmlspecialchars` final em todo dado do usuário que entra no HTML do PDF (testar com `<script>`, `<img onerror>`, `file://`); `tempDir` do mPDF em `uploads/`; no Dompdf manter `isRemoteEnabled` desligado e embutir o logotipo como `data:` (PNG, JPEG, GIF ou SVG); validar gerando um PDF real e conferindo as páginas como imagem (`pdftoppm -png`).

**Detecção:** manual: `->WriteHTML(` com variável não escapada; `isRemoteEnabled => true`; `<img src="http` em template de PDF.

**Vista em:** plugin de processos de gestão de acervo, plataforma de mapeamento comunitário.

### L-SEG-36 · Imagem remota baixada para miniatura
**Sintoma:** capa importada por URL.

**Causa:** risco de SSRF e de arquivo inválido ou disfarçado.

**Correção:** allowlist de hosts filtrável; conferir content-type, tamanho e `getimagesizefromstring()`; meta de origem no anexo para não duplicar; miniatura pela REST = `PATCH` com `_thumbnail_id`.

**Detecção:** manual: `download_url`/`wp_remote_get` com URL do request sem allowlist de host.

**Vista em:** plugin de biblioteca.

### L-SEG-37 · Regras de redirecionamento seguras
**Sintoma:** risco de vazar token ou sessão para domínio externo e de laços.

**Causa:** repasse da query para domínio externo, curingas, autorização conferida só na gravação da regra.

**Correção:** reavaliar a autorização do domínio externo a cada requisição; repassar query só no próprio domínio; correspondência exata; regra insegura é gravada desligada, com explicação; limite de quantidade de regras.

**Detecção:** manual: `wp_redirect($dest . '?' . $_SERVER['QUERY_STRING'])` para host externo.

**Vista em:** plugin de redirecionamento.

## 7. Uploads, arquivos e servidor web

### L-SEG-38 · Arquivo privado em `uploads/`: `.htaccess` não vale em Nginx
**Sintoma:** arquivos de pastas "privadas" servidos diretamente; PDF de etapa baixável sem permissão por GET e por nome adivinhável.

**Causa:** a proteção era um `.htaccess` (só Apache); endpoint de download sem checagem; nome derivado de id sequencial.

**Correção:** entregar só por PHP com checagem de permissão; guardar fora da raiz web quando possível; senão, subpasta com segmento aleatório e nome com bytes aleatórios (nunca gravar nada novo em pasta sem esse segmento), regra de negação no servidor web para as pastas do plugin, e o `.htaccess` só como camada a mais.

**Detecção:** manual: `.htaccess` com `Deny from all` como única proteção; arquivos em `wp_upload_dir()` com nome derivado de id.

**Vista em:** plugin de processos de gestão de acervo, plugin de eventos e certificados em PDF.

### L-SEG-39 · Filtro `upload_dir` escopado ao próprio anexo
**Sintoma:** arquivos indo para a pasta do mês, ou a pasta própria do plugin recebendo uploads alheios.

**Causa:** filtro global, ou ausente (código velho em opcache).

**Correção:** `add_filter('upload_dir', ...)` imediatamente antes e `remove_filter` imediatamente depois do anexo (em `finally`); registrar em log se o filtro não estava ativo.

**Detecção:** manual: `add_filter('upload_dir'` sem `remove_filter('upload_dir'` correspondente.

**Vista em:** importador de pacotes de preservação (DIP).

### L-SEG-40 · Proxy de anexos: cabeçalhos conforme o tipo
**Sintoma:** prévia em modal precisava de iframe sem abrir XSS.

**Causa:** SVG, HTML e XML servidos inline executam script na origem do site.

**Correção:** tipos inertes (PDF, imagem raster) com `X-Frame-Options: SAMEORIGIN` + CSP `frame-ancestors 'self'`; tipos executáveis com `DENY` + CSP `sandbox` + download forçado (`Content-Disposition: attachment`).

**Detecção:** manual: proxy de arquivo com `Content-Disposition: inline` para `image/svg+xml`/`text/html`.

**Vista em:** plataforma de mapeamento comunitário.

### L-SEG-41 · Backups, scripts de teste e cópias antigas na raiz web
**Sintoma:** `wp-config.php.bak` e backup `.html`/`.bak` respondendo HTTP 200 (um ficou dias no ar); scripts de teste acessíveis; gerenciador de arquivos com histórico de RCE ativo; cópia antiga do site, sem patches, apontando para o mesmo banco.

**Causa:** resíduos de manutenção; pasta de backup dentro do webroot; `.tar.gz` dando 404 por extensão mascara o risco dos demais.

**Correção:** backups e temporários fora do servidor web (no contêiner, `/tmp`); remover `wp-config.php.*`, `*.bak`, `test_*.php`; avaliar a necessidade do gerenciador de arquivos; identificar e desativar instalações paralelas no mesmo banco.

**Detecção:** TNC-B06 para PHP de plugin executável direto; manual: procurar `wp-config.php.*`, `*.bak`, `*.sql`, `test_*.php` sob o docroot.

**Vista em:** plataforma de mapeamento comunitário, plugin de pesquisas.

### L-SEG-42 · Proxy de autenticação sobre `/wp-admin/` derruba o CSS do login
**Sintoma:** `wp-login.php` e telas de senha sem formatação.

**Causa:** o proxy de autenticação redireciona também os estáticos de `/wp-admin/css/`.

**Correção:** isentar estáticos no proxy; assets do plugin em `/wp-content/`; estilizar o login por `login_enqueue_scripts`.

**Detecção:** manual.

**Vista em:** plataforma de mapeamento comunitário.

### L-SEG-43 · Nome de índice padrão igual entre sites que dividem o motor de busca
**Sintoma:** site novo escreveria no índice de outro; dois sites no padrão apagavam o índice um do outro pelo botão "Apagar índice".

**Causa:** nome de índice padrão fixo e uma credencial de superusuário única para todos os sites.

**Correção:** padrão derivado do endereço do site, lido de `get_option('home')` (não de `home_url()`, filtrado por plugins multilíngues); instalações existentes mantêm o nome gravado; usuário do motor por site, restrito ao próprio prefixo de índice.

**Detecção:** manual: valor padrão literal para nome de índice; `home_url()` usado como identificador estável. Ver também: busca-e-indice.md.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch).

## 8. SQL além do `prepare()`

SQL com valor variável sem `$wpdb->prepare()` é TNC-S01. As duas lições abaixo são os casos em que o `prepare()` não basta.

### L-SEG-44 · `ORDER BY`, `GROUP BY`, `LIMIT`, `IN` e `WHERE` montado
**Sintoma:** achados de auditoria em busca de leitores e em painéis: `WHERE {$where}`, `orderby`/`group_by` vindos da requisição.

**Causa:** identificadores não passam por placeholder; arquivos da camada de dados com `phpcs:disable` no topo não recebem aviso dos sniffs.

**Correção:** allowlist de colunas e direções com valor padrão para `orderby`/`group_by`; `LIMIT %d` dentro do `prepare`; `IN (...)` com `implode(',', array_fill(0, n, '%d'))`; fragmentos de `WHERE` montados só com peças preparadas; comentário de aviso no topo de arquivo com `phpcs:disable`.

**Detecção:** TNC-S01 (bloqueia valor da requisição; avisa fragmento não provado); manual: confirmar a allowlist.

**Vista em:** plugin de biblioteca, painel central de métricas, agregador de acervos.

### L-SEG-45 · `LIKE` com `_` ou `%` no valor sem `esc_like()`
**Sintoma:** busca por chave composta (`1_2_3_4`) casava registros errados.

**Causa:** `_` e `%` são curingas no `LIKE`; `prepare()` não os escapa.

**Correção:** `'%' . $wpdb->esc_like($valor) . '%'` passado como `%s`.

**Detecção:** manual: `LIKE %s` com valor que não passou por `esc_like(`.

**Vista em:** agregador de acervos.

## 9. Saída e escape

### L-SEG-46 · JSON dentro de `<script>` (JSON-LD, dados iniciais) e query vars
**Sintoma:** risco de tag-breakout (`</script>`) ou de JSON corrompido; warning com query var em array; botão "Voltar" com `javascript:`.

**Causa:** JSON escapado como HTML (corrompe), `str_replace('</script'...)` como remendo, JSON inline sem escapar `<`; `get_query_var()`/`$_GET` usado como string sem conferir tipo.

**Correção:** `wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP)` e nada mais; conferir `is_scalar()` antes de usar query var; "Voltar" por `wp_get_referer()`.

**Detecção:** TNC-S02 para entrada sem sanitização; manual: bloco `application/ld+json` com `esc_html(`/`esc_attr(` ou `str_replace('</script'`; `wp_json_encode` em `<script>` sem `JSON_HEX_TAG`; `href="javascript:`.

**Vista em:** plugin de biblioteca, plugin de impressão de itens.

### L-SEG-47 · HTML montado no JS: DOM puro, nunca `innerHTML` nem `dangerouslySetInnerHTML`
**Sintoma:** conteúdo de documento ou comentário executando script; risco de XSS em visualizações com dados do acervo.

**Causa:** HTML montado com dados; `content.rendered` injetado cru no React.

**Correção:** DOM puro (`textContent`, `createElement`) sobre lista renderizada no servidor, com dados em `data-*`; em React, sanitizar no servidor e não usar `dangerouslySetInnerHTML` com conteúdo de usuário.

**Detecção:** TNC-W09 (`innerHTML`/`insertAdjacentHTML`/`v-html` com variável); manual: `dangerouslySetInnerHTML={{__html: ...}}` com conteúdo de usuário.

**Vista em:** busca semântica por embeddings, plugin de narrativas/exposições virtuais, plugin de processos de gestão de acervo.

### L-SEG-48 · CSV e XLSX exportados: injeção de fórmula
**Sintoma:** fórmula executando ao abrir a planilha exportada.

**Causa:** valores começando com `=`, `+`, `-`, `@`, tab ou CR.

**Correção:** prefixar apóstrofo nesses valores; `;` como separador e BOM para o Excel; XLSX gerado em fluxo (ver desempenho-e-escala.md para ZIP em fluxo, `X-Accel-Buffering: no` e `flush()` periódico).

**Detecção:** manual: `fputcsv(` com valores do usuário sem neutralizar o prefixo.

**Vista em:** plugin de impressão de itens.

### L-SEG-49 · `wp_kses_post` sobre HTML com `<style>`/`<svg>` vira texto
**Sintoma:** página "Meus dados" mostrava código CSS como texto.

**Causa:** `wp_kses_post` remove as tags `<style>`/`<svg>` e mantém o conteúdo.

**Correção:** helper de saída próprio que imprime o componente com o escape correto por parte (ou lista de `wp_kses` própria que inclua o necessário).

**Detecção:** manual: `wp_kses_post(` aplicado a retorno de função que produz `<style>` ou `<svg>`.

**Vista em:** plugin de biblioteca.

### L-SEG-50 · `wp_kses` em URL troca `&` por `&amp;`
**Sintoma:** links com query string corrompidos.

**Causa:** `wp_kses` normaliza entidades.

**Correção:** tratar URL à parte: `esc_url_raw` ao gravar, `esc_url` ao imprimir. Os valores de `metaquery`/`taxquery` da REST do Tainacan também passam por `wp_kses` (`classes/api/class-tainacan-rest-controller.php:259-268`).

**Detecção:** manual: `wp_kses(`/`wp_kses_post(` aplicado a variável de URL.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-SEG-51 · Código de verificação: alfabeto seguro e aritmética correta
**Sintoma:** dois PDFs sumiram em silêncio do ZIP entregue (iam no pacote mas não extraíam no Windows); variantes do dígito verificador não detectavam trocas de caracteres.

**Causa:** `*` no alfabeto do dígito (proibido em nome de arquivo no Windows); módulo não primo ou menor que o alfabeto; multiplicador congruente a 0.

**Correção:** módulo primo e maior que o alfabeto do corpo; multiplicador nem ≡ 0 nem ≡ 1; alfabeto de saída com tantos símbolos quanto o módulo; só caracteres seguros em arquivo e URL. Mudar o algoritmo invalida códigos emitidos: reemitir todos.

**Detecção:** manual: `* ? : < > | " \` em alfabeto usado para compor nome de arquivo.

**Vista em:** plugin de eventos e certificados em PDF.

## 10. Contas, dados pessoais e LGPD

### L-SEG-52 · Comentários internos vazam por widget, feed e `/wp/v2/comments`
**Sintoma:** discussões internas de processos apareciam no widget Atividade do painel e na home pública, e poderiam aparecer em blocos, feeds e REST.

**Causa:** comentários gravados com `comment_approved = 1` são listados por todo o núcleo do WordPress.

**Correção:** status próprio (`comment_approved = '<prefixo>_internal'`): o WordPress só lista `1` (e a tela Comentários, `0`), então nada os mostra, nem com o plugin desativado. Ler com `get_comments(['status' => [...]])` explícito; migrar os antigos no upgrade; defesa em profundidade filtrando widget, bloco, feed e `/wp/v2/comments`, e ocultando autores em `/wp/v2/users` para anônimo; exclusão lógica, nunca definitiva.

**Detecção:** manual: `wp_insert_comment(` com `comment_approved => 1` em post type interno.

**Vista em:** plugin de processos de gestão de acervo.

### L-SEG-53 · Convite com link de definir senha, nunca com a senha
**Sintoma:** convite enviando usuário e senha por e-mail.

**Causa:** senha gerada e transmitida em claro.

**Correção:** `get_password_reset_key()` → link `wp-login.php?action=rp` (vale 24 h); senha nunca em chat, log ou repositório.

**Detecção:** manual: `wp_mail(` cujo corpo interpola `$password` ou `wp_generate_password(`.

**Vista em:** plugin de processos de gestão de acervo.

### L-SEG-54 · Conta fictícia nunca com papel que edita acervo real
**Sintoma:** risco de pessoa de teste editar coleção real compartilhada.

**Causa:** o papel de editor do Tainacan vale para toda coleção (supercaps `tnc_col_all_*`, `classes/class-tainacan-roles.php:238-505`).

**Correção:** contas de teste com papel sem edição no acervo (participante) + só as capacidades do plugin.

**Detecção:** manual.

**Vista em:** plugin de processos de gestão de acervo.

### L-SEG-55 · Enumeração de usuários e `user_nicename` derivado do e-mail
**Sintoma:** alerta de enumeração pública de usuários (CVE-2017-5487).

**Causa:** `/wp/v2/users`, `?author=N`, sitemap de usuários e slugs de autor derivados do e-mail.

**Correção:** bloquear `/wp-json/wp/v2/users` para anônimo (inclusive via `?rest_route=`), `?author=N` sem revelar o slug e o sitemap de usuários; remediar slugs existentes; XML-RPC desligado; bloquear leitura anônima de itens privados (L-SEG-05). Ao receber novo alerta, conferir o horário da observação contra a data da correção.

**Detecção:** manual: `user_nicename` montado com `sanitize_title($email)`/`sanitize_user` sobre e-mail.

**Vista em:** plataforma de mapeamento comunitário.

### L-SEG-56 · Dados pessoais completos só para quem gerencia
**Sintoma:** CPF/CNPJ, e-mail e telefone visíveis a qualquer usuário do fluxo.

**Causa:** uma única formatação de pessoa para todos os perfis.

**Correção:** dados completos só com capacidade de gestão; os demais buscam só por nome; cadastro com CPF/CNPJ existente e outro nome é recusado (409).

**Detecção:** manual.

**Vista em:** plugin de processos de gestão de acervo.

### L-SEG-57 · Contas de equipe protegidas em anonimização, retenção e vínculos
**Sintoma:** operador que também é leitor poderia se anonimizar; retenção ou importação vincularia conta privilegiada a cadastro de leitor.

**Causa:** tratar toda conta do WordPress como dado de leitor.

**Correção:** lista de capacidades privilegiadas; anonimização recusa conta de equipe (409); retenção exclui essas contas já no SQL; vínculo leitor↔conta só por quem tem `edit_users`, nunca por cron; trocar e-mail exige senha atual, com ordem de checagem que não permita enumeração.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-SEG-58 · Criar conta direto no banco (receita segura)
**Sintoma:** contas que precisaram ser criadas à mão após um erro fatal.

**Causa:** correção de dados fora do código.

**Correção:** `user_pass` com valor propositalmente inutilizável (não `$P$`, `$2y$` nem MD5 hex) e senha definida por "Esqueci minha senha"; copiar nome e e-mail por `SELECT` (nunca digitar); `user_nicename` opaco (L-SEG-55); metadados mínimos `{prefixo}capabilities`, `{prefixo}user_level`, `nickname` e o papel do plugin.

**Detecção:** manual.

**Vista em:** plataforma de mapeamento comunitário.

### L-SEG-59 · Detecção de dado pessoal por palavra, não por substring
**Sintoma:** 42 avisos de privacidade, só 2 verdadeiros ("idade" em "diversidade", "cep" em "percepção").

**Causa:** casamento por substring sem fronteira de palavra.

**Correção:** casar por palavra (`\b...\b`, com `u` para acentos).

**Detecção:** manual: `str_contains`/`strpos` com listas de termos curtos sobre texto livre.

**Vista em:** plugin de pesquisas.

## 11. IA e modelos de linguagem

### L-SEG-60 · LLM escolhe, não escreve; parâmetros que modelos novos recusam
**Sintoma:** 400 (`unsupported_parameter`) em modelos de raciocínio; lista fixa de modelos envelhecida; custo desconhecido mostrado como US$ 0; resposta do modelo gravando valor fora das opções.

**Causa:** `temperature`, `max_tokens` e `response_format` fixos (alguns modelos exigem `max_completion_tokens` e não aceitam `temperature`); `<select>` estático de modelos; texto livre do modelo tratado como dado.

**Correção:** o modelo escolhe entre candidatos que o servidor enviou e a resposta é conferida contra essa lista (fora dela = descartada); aprovação humana obrigatória antes de gravar; corpo montado por família de modelo, removendo o parâmetro reclamado e repetindo uma vez; listar os modelos da conta pela API; "não sei estimar" em vez de zero.

**Detecção:** manual: lista literal de nomes de modelo em `<select>`; `temperature`/`max_tokens` fixos sem tratamento de erro de parâmetro.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de narrativas/exposições virtuais.

### L-SEG-61 · Injeção de instruções pelo conteúdo do acervo
**Sintoma:** texto de documento do acervo podia conter instruções ao modelo.

**Causa:** fontes concatenadas ao prompt.

**Correção:** delimitar cada fonte (`<<<SOURCE id>>>...<<<END_SOURCE>>>`), neutralizar os delimitadores dentro do texto, instruir o modelo a ignorar instruções vindas das fontes e remover delimitadores vazados na saída. Só metadados públicos entram no corpus (L-SEG-03).

**Detecção:** manual.

**Vista em:** plugin de narrativas/exposições virtuais.

## 12. Processo de trabalho

### L-SEG-62 · Testar como não administrador
**Sintoma:** bug de etapas em paralelo passou despercebido; rotas liberadas demais só apareceram com outro papel.

**Causa:** administrador passa por cima das permissões (e `manage_tainacan` ganha toda cap `tnc_*`, `classes/class-tainacan-roles.php:686-690`).

**Correção:** contas de teste por perfil real (participante, editor, anônimo) e conferência da REST como cada uma (`rest_do_request()` com `wp_set_current_user()`), incluindo a listagem com `?status=private` e item publicado de coleção privada.

**Detecção:** manual.

**Vista em:** plugin de processos de gestão de acervo, plugin de biblioteca.

### L-SEG-63 · Falha de segurança em plugin de terceiros só por canal privado
**Sintoma:** risco de divulgar IDOR ou XSS de plugin de outra equipe.

**Causa:** notas de contexto e changelogs versionados com o detalhe da falha.

**Correção:** reportar só em canal privado ao mantenedor; a versão versionada das notas fica sem detalhes da falha até a correção ser publicada.

**Detecção:** manual.

**Vista em:** plugin de processos de gestão de acervo.
