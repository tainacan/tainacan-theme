# Ganchos e extensão do Tainacan (parte 2: persistência, configuração, REST do WordPress e boot)

Continuação de `ganchos-e-extensao-parte-1.md`: a infraestrutura WordPress de um plugin que estende o Tainacan. Cada seção abre com o funcionamento relevante (núcleo do Tainacan com citações relativas a `src/`, versão 1.4.1 salvo indicação, ou comportamento do WordPress) e segue com as armadilhas. A numeração continua da parte 1. Índice geral em `ganchos-e-extensao.md`.

## Sumário

9. [Ganchos de persistência e efeitos colaterais](#9-ganchos-de-persistência-e-efeitos-colaterais) (L-EXT-38 a 43)
10. [Opções e configuração do plugin](#10-opções-e-configuração-do-plugin) (L-EXT-44 a 52)
11. [REST do WordPress em plugins](#11-rest-do-wordpress-em-plugins) (L-EXT-53 a 64)
12. [Boot, ordem de carga e infraestrutura WordPress](#12-boot-ordem-de-carga-e-infraestrutura-wordpress) (L-EXT-65 a 79)

## Comece por aqui

- L-EXT-38: callback tipado em `tainacan-insert` derruba a gravação de itens.
- L-EXT-44: mapa de configuração guardado em opção precisa de número de versão.
- L-EXT-45: o `sanitize_callback` precisa saber se a entrada veio do formulário.
- L-EXT-53: `permission_callback` público, devolvendo `WP_Error`.
- L-EXT-54: `rest_url()` no `wp_localize_script` duplica a URL no `wp.apiFetch`.
- L-EXT-65: capturar `\Throwable` e registrar, sem engolir.

---

## 9. Ganchos de persistência e efeitos colaterais

**Como funciona.** Todo `insert()` de repositório dispara `tainacan-pre-insert` e `tainacan-pre-insert-{post_type}` com `$obj` (`classes/repositories/class-tainacan-repository.php:173-176`, `:209`), depois `tainacan-insert` com `($obj, $diffs, $is_update)` e `tainacan-insert-{post_type}` com `$obj` (`:238-240`). Valores de metadado têm `tainacan-pre-insert-Item_Metadata_Entity` e `tainacan-insert-Item_Metadata_Entity` (`classes/repositories/class-tainacan-item-metadata.php:38`, `:117`); exclusões têm `tainacan-pre-delete*` e `tainacan-deleted*` com `($entity, $permanent)` (`class-tainacan-repository.php:679-696`). O núcleo também grava log por esses mesmos ganchos (`classes/repositories/class-tainacan-logs.php:36-48`), então o ouvinte recebe também entidades `Log`. Os callbacks rodam **dentro** da gravação de quem chamou.

### L-EXT-38 · Callback tipado em `tainacan-insert` derruba a gravação de itens
**Sintoma:** uma coleta de 13.446 itens terminou com 0 criados, 950 fatais e 947 posts órfãos pela metade.

**Causa:** `add_action('tainacan-insert', [Cache::class, 'bump'])` sem `accepted_args` entrega 1 argumento, a entidade (inclusive `\Tainacan\Entities\Log`). Com `bump(bool $force)`, o PHP 8 lança `TypeError` dentro da gravação, e o fatal derruba a operação de quem chamou.

**Correção:** `accepted_args = 0`, ou callback com parâmetro sem tipo e com padrão (`on_tainacan_entity($entity = null)`) que ignora `Log` e entidades que não interessam. Ouvinte de gancho do Tainacan nunca deve lançar exceção (ver L-EXT-39).

**Detecção:** TNC-T09.

**Vista em:** painel público de indicadores.

### L-EXT-39 · Ouvintes de ação depois de uma gravação confirmada devem ser isolados
**Sintoma:** HTTP 500 para uma operação que já estava gravada; o balcão repetia a operação e duplicava o registro.

**Causa:** exceção num ouvinte (por exemplo, SMTP fora do ar ao notificar) sobe até o controlador REST depois do commit.

**Correção:** envolver cada ouvinte em `try { ... } catch (\Throwable $e) { /* registrar */ }`, ou disparar efeitos colaterais (e-mail, webhook) por fila/cron, fora da requisição.

**Detecção:** manual: `do_action` depois do commit dentro de callback REST sem `try/catch`.

**Vista em:** plugin de biblioteca.

### L-EXT-40 · Auto-criação em `wp_insert_post`/`save_post` cria registros fantasmas
**Sintoma:** três registros fantasmas (exemplares) criados no mesmo minuto para o mesmo item.

**Causa:** o callback criava o registro a cada salvamento de item publicado, sem checar se já existia nem se era atualização.

**Correção:** criar só se ainda não existe (checagem por chave única), considerar o `$update`; manter um relatório de itens sem o registro vinculado em vez de criar às cegas.

**Detecção:** manual: callback de `wp_insert_post`/`save_post` que insere registro sem checar existência nem `$update`.

**Vista em:** plugin de biblioteca.

### L-EXT-41 · Notificação disparada por gancho de meta lê o banco, não a requisição
**Sintoma:** e-mail de "não homologado" chega sem a justificativa.

**Causa:** a meta de status era gravada antes da meta de comentário; o e-mail disparava em `updated_post_meta` do status e relia o comentário do banco, ainda vazio.

**Correção:** gravar primeiro toda meta que a notificação consome; a meta-gatilho por último.

**Detecção:** manual: `update_post_meta($id, A)` seguido de `update_post_meta($id, B)` quando existe `add_action('updated_post_meta', ...)` que filtra por A e lê B.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-EXT-42 · `get_the_title(0)` devolve o título do post atual
**Sintoma:** a descrição mostra o próprio título no campo de relação (por exemplo, como entidade custodiadora) e a herança fica bloqueada.

**Causa:** relação vazia gravada como `0`; `get_the_title(0)` e `get_permalink(0)` usam o post global.

**Correção:** gravar vazio para "sem valor"; ignorar `""` e `"0"` na leitura (`(int) $id > 0`); limpar as linhas já gravadas com `0`.

**Detecção:** manual: `get_the_title(`/`get_permalink(` com argumento vindo de meta sem checar `> 0`.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-EXT-43 · Colunas inexistentes em SQL próprio: relatório vazio para sempre
**Sintoma:** devoluções e renovações sempre 0; relatórios de atrasos e de usuários vazios; cartão de multas zerado.

**Causa:** SQL escrito com nomes de coluna que não existem no esquema (`return_date` no lugar de `checkin_date`, `SUM(renewals)` no lugar de `renewal_count`, status fora do `ENUM`); o erro do `$wpdb` era engolido.

**Correção:** conferir os identificadores contra o `CREATE TABLE` do instalador; checar `$wpdb->last_error` depois de consultas de relatório; testes ponta a ponta contra banco real; validar status contra o `ENUM`.

**Detecção:** manual: comparar colunas citadas em SQL literal com o `CREATE TABLE` do plugin.

**Vista em:** plugin de biblioteca.

---

## 10. Opções e configuração do plugin

**Como funciona.** Comportamento do WordPress que causa as armadilhas abaixo: `register_setting($grupo, $opcao, ['sanitize_callback' => ...])` liga o sanitizador a `sanitize_option_{$opcao}`; `wp-admin/options.php` grava **todas** as opções registradas no grupo de `settings_fields($grupo)`, inclusive as que não vieram no POST (vão como `null`); `update_option()` de uma opção que ainda não existe chama `add_option()`, que sanitiza de novo. O próprio Tainacan guarda suas opções como `tainacan_option_*` (por exemplo `tainacan_option_search_results_per_page`, `tainacan.php:27`). IDs do Tainacan descobertos em tempo de execução (metadados, taxonomias, seções) mudam quando o formulário muda, por isso caches deles precisam de versão.

### L-EXT-44 · Mapa de configuração em opção precisa de número de versão (e mescla com padrões)
**Sintoma:** a correção na descoberta de metadados, ou um papel novo, não tem efeito em instalação existente; chaves novas ficam ausentes em instalações antigas.

**Causa:** o plugin guarda IDs descobertos (ou a configuração) numa `wp_option` e só recalcula se estiver vazia. O mapa velho continua sendo servido em silêncio. Pior: mapa vazio é resultado válido, e "redescobrir se vazio" roda a cada requisição.

**Correção:** constante de versão do mapa (`MAP_VERSION`) gravada junto e incrementada a cada mudança de regra ou de papel; invalidar por versão, não por "está vazio?"; guardar também o resultado vazio. Para configuração: campo `version` na opção e `wp_parse_args( get_option(X, []), defaults() )` a cada leitura; setup idempotente que não sobrescreve ajustes do usuário.

**Detecção:** manual: `get_option(` de mapa seguido de `if ( empty(...) )` para recalcular, sem comparar versão; `get_option(X)` sem mescla com padrões.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de agendamento, plugin de impressão de itens, vários projetos.

### L-EXT-45 · O sanitizador precisa distinguir formulário de escrita interna
**Sintoma:** escritas internas (WP-CLI, assistente de configuração, filtros) apagam campos, ou checkboxes ausentes viram 0 indevidamente.

**Causa:** o `sanitize_callback` não sabe se a entrada veio de formulário HTML (onde checkbox ausente significa desmarcado) ou de `update_option()` interno com parte das chaves.

**Correção:** campo oculto marcador no formulário (`__form=1`). Com ele, checkbox ausente vale 0; sem ele, chaves ausentes mantêm os valores atuais. Não remover o campo oculto dos templates.

**Detecção:** manual: `register_setting` cujo `sanitize_callback` faz `isset($input['x']) ? 1 : 0` sem checar marcador.

**Vista em:** catalogação assistida por IA local, plugin de narrativas/exposições virtuais.

### L-EXT-46 · `sanitize_option` roda duas vezes ao criar a opção
**Sintoma:** configuração por coleção corrompida ou mesclada na primeira gravação.

**Causa:** `update_option()` de opção inexistente chama `add_option()`, que sanitiza de novo; a segunda passada recebe a entrada já sanitizada e sem o marcador do formulário.

**Correção:** `sanitize()` idempotente: sem o marcador, só revalidar o que veio; nunca reler a opção atual e mesclar dentro do sanitizador.

**Detecção:** manual: `sanitize_callback` que chama `get_option(` da mesma opção e mescla com a entrada.

**Vista em:** colaboração do público em metadados.

### L-EXT-47 · `options.php` reescreve o grupo inteiro
**Sintoma:** salvar uma aba apaga a configuração das outras (por exemplo, desliga todos os módulos).

**Causa:** `options.php` percorre todas as opções registradas no grupo e chama `update_option()` com `null` para as que não vieram no POST; a tela publicava uma aba por vez.

**Correção:** cada aba declara num campo oculto (`_groups[]`) os grupos que carrega, e o sanitizador repõe os demais com o valor gravado; para opções escalares, `pre_update_option_{opcao}` devolvendo o valor antigo, registrado só na requisição que salva outra aba. Não deduzir posse pelas chaves do POST: um grupo só de checkboxes some quando tudo está desmarcado. Alternativa: um grupo por aba.

**Detecção:** manual: várias `register_setting('mesmo_grupo', ...)` num formulário com abas que imprime só parte dos campos.

**Vista em:** kit de memorial virtual.

### L-EXT-48 · `settings_fields()` sem `register_setting()` correspondente
**Sintoma:** a tela de configurações não salva, sem erro.

**Causa:** o grupo usado em `settings_fields('x')` nunca foi registrado; `options.php` recusa ou ignora.

**Correção:** registrar cada grupo com `register_setting` e sanitizador.

**Detecção:** manual: `settings_fields('x')` sem `register_setting('x'` no plugin.

**Vista em:** painel central de métricas.

### L-EXT-49 · Allowlist de configurações dessincronizada da interface
**Sintoma:** checkbox marcada nunca persiste `true` (aconteceu duas vezes no mesmo plugin).

**Causa:** `save_settings()` filtra com `array_intersect()` contra uma allowlist que não tinha a chave nova.

**Correção:** incluir a chave na allowlist, migração única para quem já salvou, e teste de paridade entre gates, allowlist, gravação, checkboxes da tela e `get_settings()`.

**Detecção:** manual: chave usada em checkbox ou `v-model` da tela ausente da allowlist PHP.

**Vista em:** plugin de biblioteca.

### L-EXT-50 · Opção gravada pela tela e nunca lida
**Sintoma:** vários interruptores (notificações, limiares, limite de empréstimos) sem efeito nenhum.

**Causa:** o serviço lia opções soltas que nenhuma tela grava, enquanto a tela gravava dentro de um array de configurações: três fontes de verdade para "habilitado".

**Correção:** unificar numa opção só, com um único leitor (`get_settings()`), e migrar as soltas.

**Detecção:** manual: `get_option('X')` sem nenhum `update_option('X')` no código, e vice-versa (`tnc-check --inventory=<pasta>` lista as opções).

**Vista em:** plugin de biblioteca.

### L-EXT-51 · `wp_http_validate_url()` não serve para URL de serviço interno
**Sintoma:** `http://127.0.0.1:9200` apagado no primeiro "Salvar" das configurações.

**Causa:** `wp_http_validate_url()` rejeita loopback, rede privada e portas fora de 80/443/8080.

**Correção:** validação própria (esquema `http`/`https` e host) para endereços de backend (Elasticsearch, OpenSearch, Redis, API interna).

**Detecção:** manual: `wp_http_validate_url(` aplicado a configuração de URL de backend.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch).

### L-EXT-52 · Funções de multisite num plugin single site
**Sintoma:** "Parar coleta" mostra sucesso, mas a coleta continua.

**Causa:** o cancelamento gravava com `update_site_option`/`delete_site_transient` e o processo lia `get_option`/`get_transient`. Além disso, a flag de parada era apagada antes de o processo verificá-la.

**Correção:** a mesma família de funções nos dois lados; manter a flag até o processo confirmá-la e limpá-la ao terminar com status `stopped`.

**Detecção:** manual: mistura de `*_site_option`/`*_site_transient` e `*_option`/`*_transient` para a mesma chave.

**Vista em:** agregador de acervos de vários sites.

---

## 11. REST do WordPress em plugins

**Como funciona.** O Tainacan não tem API própria para rotas novas: o caminho é `register_rest_route` em `rest_api_init`, no namespace do plugin (estender `\Tainacan\API\REST_Controller` em `tainacan/v2` é possível, mas não é apoio documentado; `classes/api/class-tainacan-rest-controller.php:24-28`). Comportamento do WordPress: `permission_callback` é chamado de fora da classe e mais de uma vez por requisição; a rota é compilada como regex com delimitador `@`; `rest_url()` devolve URL absoluta. Campos extras em respostas do Tainacan vão pelos filtros da seção 7 (parte 1).

### L-EXT-53 · `permission_callback` público, devolvendo `WP_Error`
**Sintoma:** a rota falha ao executar o callback de permissão; ou devolve um erro genérico pouco útil.

**Causa:** a REST API chama o callback de fora da classe; método `protected`/`private` não é invocável. Devolver `false` gera erro genérico.

**Correção:** `public function check_permissions( WP_REST_Request $r )` devolvendo `true` ou `new WP_Error( 'codigo', 'mensagem', [ 'status' => 403 ] )`; nunca `__return_true` em rota que lê ou grava dado não público (ver `seguranca-wordpress.md`).

**Detecção:** TNC-S04.

**Vista em:** OCR e busca em texto de PDFs/imagens, curadoria de notícias, painel central de métricas, kit de memorial virtual, vários projetos.

### L-EXT-54 · `rest_url()` no `wp_localize_script` duplica a URL no `wp.apiFetch`
**Sintoma:** requisições vão para uma URL com o prefixo duplicado.

**Causa:** `rest_url()` devolve URL completa e `wp.apiFetch({ path })` acrescenta a raiz de novo.

**Correção:** passar caminho relativo (`/meu-plugin/v1`) e usar `path`; se precisar de URL absoluta, usar a opção `url` do `apiFetch`, não `path`.

**Detecção:** manual: `wp_localize_script(` com `rest_url(` cujo valor é usado como `path` no JS.

**Vista em:** curadoria de notícias, SaaS de serviços agrícolas, plugin de agendamento, vários projetos.

### L-EXT-55 · `permission_callback` roda mais de uma vez: sem efeito colateral nele
**Sintoma:** limite de requisições por IP efetivamente pela metade.

**Causa:** o WordPress chama o `permission_callback` de novo para montar o cabeçalho `Allow`; o contador era incrementado ali.

**Correção:** efeito colateral (contador, transient, gravação) no callback principal, ou com marcação por requisição.

**Detecção:** TNC-W10.

**Vista em:** plugin de busca segmentada.

### L-EXT-56 · Rotas REST registradas dentro de `is_admin()` não existem
**Sintoma:** a tela de admin pede progresso a endpoints que respondem 404.

**Causa:** `is_admin()` é falso numa requisição REST; `register_rest_route` dentro desse bloco nunca roda.

**Correção:** `add_action('rest_api_init', ...)` fora de qualquer `if ( is_admin() )`.

**Detecção:** manual: `register_rest_route` ou `add_action('rest_api_init'` aninhado em `if ( is_admin() )`.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-EXT-57 · Listagem REST sem os filtros e `X-WP-Total` errado
**Sintoma:** painel do item mostrava a contagem do acervo inteiro ("59 disponíveis" num item de 3); filtros decorativos; paginação quebrada.

**Causa:** o callback ignorava os parâmetros declarados; o `COUNT` não aplicava os mesmos filtros; JOIN 1:N multiplicava linhas; nenhuma tela lia `X-WP-Total`.

**Correção:** filtros com placeholders no `$wpdb->prepare`, `COUNT` com os mesmos filtros, `EXISTS` em vez de JOIN para filtros 1:N, e ler `X-WP-Total` (total filtrado, não global).

**Detecção:** manual: callback de listagem que declara `args` não usados na consulta.

**Vista em:** plugin de biblioteca.

### L-EXT-58 · Duas registrações para a mesma rota: só uma responde
**Sintoma:** PATCH gravava direto, sem validação nem auditoria; a correção quase foi aplicada em código morto.

**Causa:** rota procedural e controller registravam o mesmo namespace, rota e método.

**Correção:** remover a duplicata; antes de corrigir comportamento, confirmar qual callback responde (por exemplo inspecionando `rest_get_server()->get_routes()`).

**Detecção:** manual: mesmo namespace, rota e método em dois `register_rest_route` (`tnc-check --inventory` lista as rotas).

**Vista em:** plugin de biblioteca.

### L-EXT-59 · `@` na regex de rota derruba todo o REST do site
**Sintoma:** `preg_match(): Unknown modifier` em toda requisição REST, de todos os plugins.

**Causa:** o WordPress compila a rota como `@^...$@i`; um `@` na rota fecha o delimitador.

**Correção:** nunca usar `@` em padrão de rota; validar e-mail ou similar no callback.

**Detecção:** TNC-W10.

**Vista em:** plugin de biblioteca.

### L-EXT-60 · Codificação dupla de query string
**Sintoma:** espaços viram `%2520`.

**Causa:** `rawurlencode()` antes de `add_query_arg()`, que já codifica.

**Correção:** codificar uma vez só.

**Detecção:** manual: `add_query_arg(` com argumento envolvido em `rawurlencode(`/`urlencode(`.

**Vista em:** plugin de biblioteca.

### L-EXT-61 · URL da REST com permalinks simples
**Sintoma:** chamadas falham em sites sem permalinks bonitos.

**Causa:** o JS concatenava `?` à URL base, que nesses sites já é `...?rest_route=/...`.

**Correção:** escolher o separador `?` ou `&` conforme a URL (`url.includes('?') ? '&' : '?'`), ou usar `wp.apiFetch` com `path`.

**Detecção:** manual: concatenação `restBase + '?'` em JS.

**Vista em:** painel público de indicadores.

### L-EXT-62 · Ids textuais com espaço exigem padrão de rota que aceite `%`
**Sintoma:** a rota não casa ids herdados como "Etapa 1".

**Causa:** padrão `(?P<id>[\w-]+)` não aceita o espaço codificado.

**Correção:** aceitar `%` (ou um conjunto explícito) no padrão e decodificar no callback; preferir ids estáveis sem espaço.

**Detecção:** manual: `register_rest_route` com `(?P<id>[\w-]+)` recebendo ids de origem textual.

**Vista em:** plugin de processos de gestão de acervo.

### L-EXT-63 · Shortcode dependente de endpoint REST opcional
**Sintoma:** o shortcode do painel não funciona quando o endpoint público está desligado.

**Causa:** o shortcode buscava os dados pela REST pública.

**Correção:** pré-computar no servidor (renderização no PHP) e usar a REST só como melhoria progressiva.

**Detecção:** manual.

**Vista em:** painel central de métricas.

### L-EXT-64 · Códigos posicionais: reordenar quebra referências
**Sintoma:** condicionais apontam para a pergunta errada depois de reordenar o questionário.

**Causa:** códigos de pergunta e opção eram gerados pela posição, e os informados pelo cliente eram descartados.

**Correção:** ids estáveis; validar a estrutura com códigos simulados antes de gravar.

**Detecção:** manual.

**Vista em:** plugin de pesquisas/questionários.

---

## 12. Boot, ordem de carga e infraestrutura WordPress

**Como funciona.** O Tainacan carrega tudo na inclusão de `tainacan.php` por `classes/tainacan-creator.php`: gerenciadores de segundo plano instanciados na inclusão (`:56-71`), repositórios e helpers de tipos (`:161-177`), CLI (`:194`), rotas REST (`:197`), páginas de admin (`:199`) e `Admin_Hooks` (`:203`); migrações em `init` (`tainacan.php:37-41`). Constantes úteis para detectar o Tainacan: `TAINACAN_VERSION`, `TAINACAN_BASE_DIR` (`tainacan.php:22-24`). A documentação recomenda chamar APIs do Tainacan a partir de `plugins_loaded` (`repo:docs/plugin-development-guidelines.md:32`), mas os ganchos de registro de tipo disparam antes disso (seção 3, parte 1). O que um plugin registra em `init` com prioridade N precisa ser ligado antes de `init` N rodar.

### L-EXT-65 · Capturar `\Throwable`, registrar, e nunca engolir em silêncio
**Sintoma:** `TypeError` e outros `Error` escapam de código crítico (importação, cron); ou o contrário: um `try/catch` amplo no boot engole a falha e o plugin segue meio desligado, com ganchos desregistrados sem aviso.

**Causa:** `catch (\Exception $e)` não pega `Error` do PHP 7+. E um `try { ... add_action ... } catch (\Throwable $e) {}` sem log esconde bugs reais (num caso, uma variável usada num método que não a recebia).

**Correção:** `catch (\Throwable $e)` em boot, laços de importação, cron e ouvintes; sempre registrar (`error_log` ou log do plugin) e, no admin, mostrar aviso; se o boot falhar, a página fica como estava (o plugin não pela metade). Não usar o `try/catch` do boot como rede para bugs: rodar análise estática (PHPStan/Psalm nível básico) para variáveis indefinidas.

**Detecção:** TNC-T05 (para o `catch` que engole a exceção de `validate()`); manual: `catch (\Exception` em código de importação/cron; `catch (\Throwable $e) {}` vazio.

**Vista em:** gestor de periódicos, plugin de dados ligados e enriquecimento, indexador de busca. Ver também: lição de coleta resiliente com `Throwable` em `migracao-de-acervos.md`.

### L-EXT-66 · `extract()` em template colide com variáveis locais
**Sintoma:** "Arquivo de importação indisponível" para um arquivo presente; a tela de importação "fecha sozinha".

**Causa:** `template(string $file, array $data)` fazia `extract($data, EXTR_SKIP)`; a chave `file` do array era descartada porque `$file` (o nome do template) já existia.

**Correção:** não usar `extract()`. Passar o array e ler `$data['file']`, ou isolar o `include` num método privado com parâmetros de nome improvável (`$__view_path`, `$__view_data`).

**Detecção:** TNC-S06.

**Vista em:** plugin de eventos e certificados em PDF.

### L-EXT-67 · Bootstrap em `init` 10 que registra ganchos de `init` 10 chega tarde
**Sintoma:** post meta e regras de reescrita do plugin ignorados.

**Causa:** o bootstrap rodava em `init` prioridade 10 e registrava ganchos de `init` 10 que já tinham passado.

**Correção:** `add_action('init', [Plugin::class, 'init'], 1)` (ou boot em `plugins_loaded`), para que os registros de `init` fiquem prontos antes.

**Detecção:** manual: callback de `add_action('init', ..., 10)` que chama `add_action('init', ...)` com prioridade menor ou igual a 10.

**Vista em:** busca semântica por embeddings.

### L-EXT-68 · Shortcode registrado só depois do boot sai cru
**Sintoma:** o código do shortcode aparece cru na página quando o boot falha ou o Tainacan está ausente.

**Causa:** `add_shortcode` dentro do boot condicionado a `class_exists('\Tainacan\...')`.

**Correção:** registrar shortcodes (e blocos) antes do boot, com saída vazia ou aviso discreto quando o Tainacan não estiver ativo.

**Detecção:** manual: `add_shortcode` dentro de bloco condicionado a `class_exists('\Tainacan\`.

**Vista em:** painel público de indicadores.

### L-EXT-69 · Pasta do Tainacan detectada pela classe, não pelo nome
**Sintoma:** filtros do núcleo suspensos por engano, porque o plugin não reconheceu o Tainacan.

**Causa:** a pasta nem sempre é `tainacan/` (ZIP do GitHub vira `tainacan-master/`, ou mu-plugin).

**Correção:** `dirname( ( new ReflectionClass('\Tainacan\Repositories\Items') )->getFileName(), 3 )`, ou `TAINACAN_BASE_DIR` quando definido.

**Detecção:** manual: literal `WP_PLUGIN_DIR . '/tainacan/'`.

**Vista em:** plugin de impressão de itens.

### L-EXT-70 · Mudança de assinatura com chamadas antigas passando `bool`
**Sintoma:** "Limpar cache", salvar configurações e destaques invalidavam só a coleção 1.

**Causa:** `bump(bool $force)` virou `bump(int $collection_id = 0)`; quatro chamadas `bump( true )` sobreviveram e, sem `strict_types`, `true` virou `1`.

**Correção:** ao mudar assinatura, procurar e corrigir todos os chamadores; considerar `declare(strict_types=1)` em arquivos novos.

**Detecção:** manual: literal `true`/`false` passado a parâmetro tipado `int` (PHPStan pega).

**Vista em:** painel público de indicadores.

### L-EXT-71 · `$this` em método estático: fatal só em tempo de execução
**Sintoma:** a criação de conta nova abortava (76 fatais); contas existentes não eram afetadas, o que mascarou o bug.

**Causa:** método `static` chamando `$this->helper()`; `php -l` não detecta.

**Correção:** helper `static` chamado com `self::`. Ao achar um fatal no meio de uma rotina, procurar os outros estados parciais que ela deixou pela metade (vínculos, autoria).

**Detecção:** manual: `$this->` dentro de `static function` (PHPStan/Psalm pegam).

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-EXT-72 · Variáveis `$s`, `$m`, `$post` em templates sobrescrevem globais
**Sintoma:** o sniff `WordPress.WP.GlobalVariablesOverride` acusa; risco de quebrar busca e arquivo.

**Causa:** templates incluídos rodam em escopo global ou de arquivo.

**Correção:** nomes descritivos e prefixados.

**Detecção:** manual: atribuição a `$s`, `$m`, `$post`, `$wp_query`, `$page` em templates (WPCS).

**Vista em:** colaboração do público em metadados.

### L-EXT-73 · Comando WP-CLI com posicional não declarado
**Sintoma:** `wp <cmd> queue work` recusado com "Too many positional arguments: work".

**Causa:** o docblock do método não declarava o posicional em `## OPTIONS`.

**Correção:** declarar `[<acao>]` (ou `<acao>`) no `## OPTIONS`.

**Detecção:** manual: método de comando WP-CLI que lê `$args[0]` sem `<...>` em `## OPTIONS`.

**Vista em:** plugin de eventos e certificados em PDF.

### L-EXT-74 · Escopo por usuário corrente quebra WP-CLI e WP-Cron
**Sintoma:** comando CLI recusa todos os registros.

**Causa:** a função de escopo devolve "acesso a nada" quando não há usuário corrente: correto para visitante anônimo, errado para CLI e cron.

**Correção:** tratar explicitamente `defined('WP_CLI') && WP_CLI` e `wp_doing_cron()`, ou exigir `--user=` no comando.

**Detecção:** manual: `get_current_user_id() === 0` levando a retorno vazio sem checar CLI/cron.

**Vista em:** plugin de eventos e certificados em PDF.

### L-EXT-75 · Flush de rewrite por assinatura de versão
**Sintoma:** rota pública nova não reconhecida depois de atualizar o plugin.

**Causa:** as regras de rewrite ficam em cache (`rewrite_rules`) e a ativação não roda em atualização.

**Correção:** `maybe_flush()` em `init` (depois de `add_rewrite_rule`) compara uma assinatura `slug|VERSION` guardada em opção e faz `flush_rewrite_rules(false)` só quando muda. Nunca dar flush em toda requisição.

**Detecção:** manual: `add_rewrite_rule(` sem flush condicionado a versão, ou `flush_rewrite_rules()` fora de ativação/versão.

**Vista em:** plugin de eventos e certificados em PDF.

### L-EXT-76 · `add_rewrite_rule(..., 'top')` não põe a regra na frente das outras do plugin
**Sintoma:** rota literal (`/x/emitir/`) lida como parâmetro de uma regra genérica (`/x/{codigo}/`); HTTP 200 sem erro.

**Causa:** `'top'` acrescenta ao fim de `extra_rules_top`; a ordem de registro é a ordem de avaliação.

**Correção:** registrar a regra mais específica primeiro.

**Detecção:** manual: duas `add_rewrite_rule` com `'top'` em que uma regex genérica `^x/([^/]+)/?$` vem antes de uma literal `^x/literal/?$`.

**Vista em:** plugin de eventos e certificados em PDF.

### L-EXT-77 · Página resolvida só por `$wp->request` falha em `?page_id=`
**Sintoma:** aviso de área restrita só funciona pelo permalink; links antigos `?page_id=` dão 404 do tema.

**Causa:** em acesso por query var, `$wp->request` vem vazio.

**Correção:** resolver primeiro por `get_queried_object_id()` ou `get_query_var('page_id')`/`p`, depois pelo caminho.

**Detecção:** manual: uso de `$wp->request` para identificar a página sem consultar a query var.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-EXT-78 · Corrigir no shortcode, não no mapa de páginas
**Sintoma:** "Você precisa estar logado" sem nada clicável numa das páginas.

**Causa:** o mesmo shortcode estava em duas páginas, mas só uma constava no mapa de redirecionamento.

**Correção:** o shortcode renderiza o login embutido quando não há sessão. Páginas privadas do plugin exibem aviso (em `template_redirect`, prioridade 20) com `<title>` neutro (o título real vazava), HTTP 200 e `noindex`, restrito às páginas cujo conteúdo tem shortcode do plugin.

**Detecção:** manual.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

### L-EXT-79 · Logs gravados mas nunca exibidos
**Sintoma:** operações de manutenção sem rastro na aba de logs do plugin.

**Causa:** a aba só mostrava entradas com `log_id`; a manutenção gravava com `log_id NULL`.

**Correção:** espelhar a manutenção num registro visível; testar a tela de logs com cada tipo de operação que grava log.

**Detecção:** manual.

**Vista em:** importador de pacotes de preservação (DIP).
