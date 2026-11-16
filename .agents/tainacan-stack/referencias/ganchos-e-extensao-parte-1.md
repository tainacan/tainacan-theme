# Ganchos e extensão do Tainacan (parte 1: pontos de extensão do Tainacan)

Como estender o Tainacan sem quebrá-lo: cada seção explica em poucas linhas como o ponto de extensão funciona no núcleo (citações relativas a `src/`, versão 1.4.1 salvo indicação; **Δ** marca diferença para a 1.0.3) e lista as armadilhas já vistas em plugins reais.

A parte 2 (`ganchos-e-extensao-parte-2.md`) cobre ganchos de persistência, opções e configuração de plugin, REST do WordPress em plugins e boot/infraestrutura WordPress. O índice geral está em `ganchos-e-extensao.md`.

## Sumário

1. [Ganchos de formulário do admin (`tainacan_register_admin_hook`)](#1-ganchos-de-formulário-do-admin-tainacan_register_admin_hook) (L-EXT-01 a 08)
2. [Páginas de admin (`\Tainacan\Pages`) e menu](#2-páginas-de-admin-tainacanpages-e-menu) (L-EXT-09 a 11)
3. [Registros: tipos de metadado e filtro, importadores, exportadores, processos](#3-registros-tipos-de-metadado-e-filtro-importadores-exportadores-processos) (L-EXT-12 a 14)
4. [Página do item: valor como HTML e ganchos de seção](#4-página-do-item-valor-como-html-e-ganchos-de-seção) (L-EXT-15 a 20)
5. [Documento do item, galeria e tema](#5-documento-do-item-galeria-e-tema) (L-EXT-21 a 24)
6. [Busca facetada (ganchos JS e modos de visualização)](#6-busca-facetada-ganchos-js-e-modos-de-visualização) (L-EXT-25 a 27)
7. [REST do Tainacan (`tainacan/v2`)](#7-rest-do-tainacan-tainacanv2) (L-EXT-28 a 34)
8. [Exposers e saída XML/RDF](#8-exposers-e-saída-xmlrdf) (L-EXT-35 a 37)

## Comece por aqui

- L-EXT-01: callback de gancho de admin devolve string e é registrado em `tainacan-register-admin-hooks`.
- L-EXT-02: `<script>` inline em HTML injetado por `v-html` nunca roda.
- L-EXT-09: `\Tainacan\Pages` é singleton e não chama `load_page()` sozinho.
- L-EXT-15: o filtro genérico de valor como HTML não roda para tipos com HTML próprio.
- L-EXT-17: o tipo URL faz autoembed por conta própria.
- L-EXT-25: os nomes do gancho "depois do item" da busca facetada são assimétricos.
- L-EXT-28: a REST do Tainacan descarta `include_children`, `field` e `type` nas cláusulas.
- L-EXT-35: exposer com `?exposer=` passa por `wp_kses`.

---

## 1. Ganchos de formulário do admin (`tainacan_register_admin_hook`)

**Como funciona.** `tainacan_register_admin_hook( $context, $callback, $position = 'end-left', $conditional = null )` delega para `Admin_Hooks::register()` (`views/admin/classes/hooks/admin-hooks-functions.php:8-11`). Os registros acontecem em `do_action('tainacan-register-admin-hooks')`, disparado em todo `admin_init`, inclusive em `admin-ajax.php` e `admin-post.php` (`views/admin/classes/hooks/class-tainacan-admin-hooks.php:12-18`). O callback é executado **na hora do registro** (`call_user_func`, `:48`) e só é aceito se devolver string (`:50-59`); contexto ou posição inválidos devolvem `false` (`:44-46`). O HTML chega ao JS por `tainacan_plugin['form_hooks']` (`views/class-tainacan-pages.php:384`) e é injetado com `v-html` em `<form class="form-hook-region">` (`views/admin/components/edition/item-edition-form.vue:198-202`, `:648-652`). Ao salvar, `fillExtraFormData()` lê os campos de `#form-{entidade}-{posição}` e os manda junto (`views/admin/js/mixins.js:108-139`); a gravação fica a cargo do plugin, por exemplo em `tainacan-insert-tainacan-collection`. Contextos: `collection`, `metadatum`, `item`, `taxonomy`, `term`, `filter`, `role`, `metadataSection`; posições: `begin-left`, `begin-right`, `end-left`, `end-right` (`class-tainacan-admin-hooks.php:20-26`).

### L-EXT-01 · Callback de gancho de admin deve devolver string e ser registrado no gancho oficial
**Sintoma:** dois sintomas com a mesma causa. (a) `wp_safe_redirect()` falha em silêncio: tela branca, a ação foi executada e o resultado só aparece no reload. (b) `headers already sent` quebra respostas REST e o admin inteiro. O HTML do painel vaza no stdout de `wp eval`. Registrado em `init`, o painel simplesmente não aparece.

**Causa:** `Admin_Hooks::register()` chama o callback na hora em que coleta os ganchos, cedo no `admin_init` e antes dos cabeçalhos, e só guarda o que for **devolvido** (`class-tainacan-admin-hooks.php:48-59`). Um `include` ou `echo` seco imprime no fluxo de saída e o gancho não é registrado. Registrar fora de `tainacan-register-admin-hooks` (em `init`, no carregamento do plugin) roda antes do `Admin_Hooks` estar pronto ou em toda requisição, gerando HTML à toa em REST, cron e WP-CLI.

**Correção:** `add_action('tainacan-register-admin-hooks', ...)` e, dentro, `tainacan_register_admin_hook(...)` com callback `ob_start(); include ...; return (string) ob_get_clean();`. Só registre quando `is_admin() && ! ( defined('REST_REQUEST') && REST_REQUEST ) && ! ( defined('WP_CLI') && WP_CLI )`. Prova rápida: `do_action('tainacan-register-admin-hooks')` por `wp eval-file` e inspecionar `\Tainacan\Admin_Hooks::get_instance()->get_registered_hooks()`; nada pode sair no stdout.

**Detecção:** TNC-T01 (imprime em vez de devolver), TNC-T03 (registrado fora de `tainacan-register-admin-hooks`).

**Vista em:** plugin de dados ligados e enriquecimento, plugin de agendamento, vários projetos.

### L-EXT-02 · `<script>` inline no HTML do gancho de admin não executa
**Sintoma:** "o painel não aparece", sem erro no console nem no log. O `<div>` chega ao DOM e, se nasceu `hidden` esperando o JS revelá-lo, fica invisível para sempre.

**Causa:** o HTML do gancho entra por `v-html` (innerHTML) (`item-edition-form.vue:648-652`; mixin em `views/admin/js/mixins.js:85-96`). O navegador não executa `<script>` inserido por innerHTML.

**Correção:** o template do gancho tem só marcação. JS e CSS vão em arquivos enfileirados em `admin_enqueue_scripts`, com URL da REST e nonce por `wp_localize_script` ou `wp_add_inline_script`. O formulário é um SPA: use um `MutationObserver` (sem desconectar) que religa o painel sempre que ele reaparece, ou o evento `tainacan-{entidade}-hook-reload` que o núcleo dispara no `document` a cada atualização (`views/admin/js/mixins.js:98-106`). Faça o painel nascer visível com estado "carregando", para não falhar em silêncio. Evite o truque de `<img onload>` chamando função global.

**Detecção:** TNC-T02.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de agendamento, vários projetos. Ver também L-EXT-27 (mesma causa nos modos de visualização).

### L-EXT-03 · Posições do gancho do item não correspondem à posição visual
**Sintoma:** painel registrado em `begin-left` aparece espremido na barra lateral de Status/Documento (à direita), e não no topo da coluna de metadados.

**Causa:** no formulário do item, `begin-right` e `end-right` são renderizados dentro de `.column.main-column` (metadados, que aparece à **esquerda**) e `begin-left`/`end-left` dentro de `.column.secondary-column` (à **direita**) (`item-edition-form.vue:189`, `:198-202`, `:510-514`; coluna secundária em `:618-623`, ganchos em `:648-652`, `:698-702`).

**Correção:** `begin-right` para o topo da coluna de metadados; `end-left` para o rodapé da coluna secundária. Escolha a posição pelo mapeamento real, não pelo nome.

**Detecção:** manual: conferir `tainacan_register_admin_hook('item', ..., '<posição>')` contra o lugar desejado na tela.

**Vista em:** plugin de biblioteca, vários projetos.

### L-EXT-04 · Condição do gancho é comparada com `===`
**Sintoma:** painel registrado com condição (por exemplo, por coleção) nunca aparece.

**Causa:** `checkFormConditionals()` compara `this.form[atributo] === valor`, tanto na forma antiga `['attribute'=>..., 'value'=>...]` quanto na nova `{atributo: valor}` (`views/admin/js/mixins.js:165-199`). O `collection_id` do formulário costuma ser string; um inteiro vindo do PHP não casa. Valor "falsy" no formulário também reprova a condição.

**Correção:** registrar sem condição e filtrar no JS (ou no callback PHP, se o contexto já for conhecido), ou passar o valor com o tipo exato que o formulário usa.

**Detecção:** manual: procurar o 4º argumento de `tainacan_register_admin_hook(` com `collection_id`/`collectionId` inteiro.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-EXT-05 · Nunca aninhar `<form>` no HTML do gancho
**Sintoma:** Enter num campo do painel submete o formulário do Tainacan por GET e a tela fica branca. Links do painel apagam a rota do SPA.

**Causa:** o HTML entra dentro de `<form class="form-hook-region">`; o parser descarta o `<form>` interno e o Enter submete o form externo. `href="#"` troca o hash e destrói a rota hash do admin.

**Correção:** sem `<form>` no template; tratar Enter no `keydown` dos inputs (`preventDefault`); todo botão com `type="button"`; nunca `href="#"` (use `<button>` ou `href` real).

**Detecção:** manual: grep no template do gancho por `<form`, `href="#"` e `<button` sem `type="button"`.

**Vista em:** plugin de biblioteca.

### L-EXT-06 · Gravar fora do formulário do item deixa guarda "não salvo" e dados velhos
**Sintoma:** ao recarregar, popup de "item não salvo"; o formulário mostra dados antigos; o botão "Criar outro item" nunca aparece.

**Causa:** o formulário mantém o item como `auto-draft` em memória e tem `beforeRouteLeave`. Não há evento para o plugin pedir "releia o item" (observado no 1.1). Trocar o hash `items/new` para `items/{id}/edit` dentro do SPA dispara a guarda. "Criar outro item" depende de `$route.query.recent`, que só o fluxo nativo produz.

**Correção:** o servidor promove `auto-draft` para `draft`; forçar recarga completa acrescentando um marcador na query (por exemplo `meu_reload=1`; mudança de query é navegação real) e removê-lo no boot com `history.replaceState`; nunca recarregar em `/items/new` (cria outro rascunho). Se precisar do botão de novo item, injetá-lo no rodapé de submissão quando o nativo não estiver. Recarregar só os metadados não atualiza miniatura nem capa.

**Detecção:** manual: JS que atribui `location.hash` para `.../items/{id}/edit` ou `.../items/new` depois de um POST próprio.

**Vista em:** plugin de biblioteca.

### L-EXT-07 · Opções `*InsideTabs` desmontam a coluna secundária e levam os ganchos junto
**Sintoma:** ligar `itemEditionPublicationSectionInsideTabs` (e similares) no desktop faz Documento/Anexos sumirem e, com eles, os painéis registrados em `*-left`, sem aviso.

**Causa:** o `v-if` da coluna secundária só testa Publicação, Documento e Anexos e ignora os ganchos (`item-edition-form.vue:618-623`); os seletores de aba ficam no cabeçalho móvel. As opções vêm do filtro `tainacan-admin-ui-options` (`views/class-tainacan-pages.php:850`).

**Correção:** não usar `tainacan-admin-ui-options` para esconder essas seções quando há ganchos `*-left`. Para colapsar, JS com alvo estrutural (filhos de `.secondary-column` que têm rótulo e caixa de seção), idempotente pela presença do próprio botão (o Vue remonta o rótulo).

**Detecção:** manual: filtro `tainacan-admin-ui-options` definindo `itemEdition*InsideTabs` como `true`.

**Vista em:** plugin de biblioteca.

### L-EXT-08 · Não há gancho de validação que bloqueie o salvamento no formulário
**Sintoma:** avisos de coerência (nível arquivístico incoerente, conflito, trava) só aparecem depois de salvar e não impedem a gravação.

**Causa:** o formulário não oferece gancho JS de validação antes do envio. No servidor existem `tainacan-pre-insert` e `tainacan-pre-insert-{post_type}` (`classes/repositories/class-tainacan-repository.php:173-176`), mas a pesquisa do núcleo não confirma que dá para abortar a gravação por eles de forma limpa.

**Correção:** validar depois de salvar e avisar no painel do gancho; registrar a limitação como pendência conhecida no contexto do projeto.

**Detecção:** manual.

**Vista em:** agregador arquivístico (AtoM/EAD).

---

## 2. Páginas de admin (`\Tainacan\Pages`) e menu

**Como funciona.** `abstract class Pages` exige `get_page_slug()` e `render_page_content()` (`views/class-tainacan-pages.php:10`, `:17`, `:447`). O construtor é `protected` e chama `init()`, que liga `admin_menu` e `admin_head` (`:36-52`). `load_page()` enfileira CSS e JS (`:63-70`), mas **não é chamado sozinho**: as páginas nativas fazem `add_action('load-' . $page_suffix, [$this, 'load_page'])` logo depois de `add_submenu_page()` (`views/admin/class-tainacan-admin.php:48`, `views/settings/class-tainacan-settings.php:46`) e usam o trait `Singleton_Instance` (`views/admin/class-tainacan-admin.php:7-8`). Guia oficial: `repo:docs/creating-tainacan-admin-pages.md:113-146`.

### L-EXT-09 · Subclasse de `\Tainacan\Pages`: singleton e `load_page()` manual
**Sintoma:** fatal ao instanciar a página com `new`; ou a página abre sem o CSS/JS próprios (e sem os do Tainacan).

**Causa:** construtor `protected` (`views/class-tainacan-pages.php:36-38`); `load_page()` só roda se a subclasse ligá-lo ao `load-{hook}` da página.

**Correção:** `use \Tainacan\Traits\Singleton_Instance;` (ou `get_instance()` próprio) e `MinhaPagina::get_instance()`. Depois de `$page_suffix = add_submenu_page(...)`, fazer `add_action('load-' . $page_suffix, [$this, 'load_page'])`.

**Detecção:** TNC-T08 (sem `load_page()`); manual para `new X()` onde `X extends \Tainacan\Pages`.

**Vista em:** OCR e busca em texto de PDFs/imagens, vários projetos.

### L-EXT-10 · Ícone inexistente em `get_svg_icon()` quebra o admin
**Sintoma:** warning no meio do HTML do menu e `headers already sent` logo após ativar o plugin.

**Causa:** `\Tainacan\Traits\SVG_Icon::get_svg_icon()` faz `file_get_contents()` do arquivo do ícone sem checar se existe (`classes/traits/class-tainacan-svg-icon.php:17-25`; pasta `assets/icons/`, filtrável por `tainacan-svg-icons-folder-path`). Um nome como `chart` não existe.

**Correção:** helper próprio que testa `is_readable()` antes, e usar só ícones confirmados na pasta do Tainacan (por exemplo `reports`, `settings`).

**Detecção:** manual: comparar os nomes passados a `get_svg_icon('...')` com os arquivos da pasta de ícones do Tainacan instalado.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch).

### L-EXT-11 · Menu do Tainacan: barra própria, classe `is-open` e página de topo
**Sintoma:** o grupo do menu não abre; reordenar submenu não tem efeito; o script mira o slug errado.

**Causa:** a seção do Tainacan vive numa lista própria (`UL#tainacan-root-menu`), não só no `#adminmenu`, e abre pela classe `is-open` no `<li>` (só `aria-expanded` não abre). No 1.1 o menu de topo do WordPress é a página `tainacan_dashboard`; a raiz é virtual.

**Correção:** JS que aplica `is-open` uma vez e respeita quem fechou; redirecionamento em `admin_init` com saída de emergência e filtro para desligar; CSS de rótulos de grupo mirando as duas barras.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

---

## 3. Registros: tipos de metadado e filtro, importadores, exportadores, processos

**Como funciona.**
- Tipo de metadado: `do_action('tainacan-register-metadata-type', $helper)` dispara dentro de `Metadata_Type_Helper::init()`, quando o singleton é criado **na inclusão do arquivo do Tainacan** (`views/admin/components/metadata-types/metadata-type-helper/class-tainacan-metadata-type-helper.php:41`, `:40` [1.0.3]; `classes/tainacan-creator.php:165`). O callback chama `$helper->register_metadata_type($handle, $class_name, $script_path, $args)` (`:43-53`, `:62-77`). Tipo de filtro: igual, com `tainacan-register-filter-type` (`views/admin/components/filter-types/filter-type-helper/class-tainacan-filter-type-helper.php:41`, `:63-77`). Ordem de carga (inferência da pesquisa): plugin que só faz o `add_action` dentro de `plugins_loaded` chega tarde; `add_action` no nível de cima do arquivo funciona quando o plugin carrega antes de `tainacan/` (plugins `tainacan-*` carregam antes pela ordem alfabética). O mesmo vale para `tainacan-register-vuejs-plugin`; `tainacan-register-vuejs-component` dispara em `init` prioridade 80 e não tem o problema (`views/admin/classes/hooks/class-tainacan-component-hooks.php:23-27`).
- Opções do tipo: canônicas em um único postmeta serializado `metadata_type_options` (`classes/repositories/class-tainacan-metadata.php:160-167`), com índice explodido `_option_{chave}` gravado depois de cada `insert` e **nunca limpo** (`:1733-1757`). Filtros não têm índice `_option_` (`classes/repositories/class-tainacan-filters.php:55`).
- Importadores: `tainacan-register-importers` em `init` e `register_importer([...])` (`classes/background-process/importer/class-tainacan-importer-handler.php:24`, `:83`, `:128-146`). Exportadores: `tainacan-register-exporters` (`classes/background-process/exporter/class-tainacan-exporter-handler.php:22`, `:54`, `:63-79`). Processos genéricos: `tainacan-register-generic_process` (com underscore) em `init`, e `register_generic_process(['name','slug','class_name'])`, os três obrigatórios (`classes/background-process/generic-process/class-tainacan-generic-handler.php:22`, `:34-46`); enfileirar com `add_to_queue()` (`:48-60`).

### L-EXT-12 · Opções de tipo de metadado: ler `_option_{chave}` ou `get_metadata_type_options()`
**Sintoma:** descobrir a taxonomia de um metadado Taxonomy devolve vazio, sem erro; um painel inteiro renderizou uma linha só.

**Causa:** `get_post_meta($id, 'taxonomy_id')` devolve vazio **sempre**: a chave não existe sem o prefixo. O índice é `_option_taxonomy_id` (`classes/repositories/class-tainacan-metadata.php:1733-1757`). Existe também `_option_taxonomy` (slug pronto), mas ele é derivado, vem do formulário Vue e pode estar vazio ou desatualizado (`views/admin/components/metadata-types/taxonomy/class-tainacan-taxonomy.php:618-626`; `FormTaxonomy.vue:579`). Como o índice nunca é limpo, `_option_<chave>` de uma opção removida continua no banco.

**Correção:** `get_post_meta($id, '_option_taxonomy_id', true)` ou `$metadatum->get_metadata_type_options()['taxonomy_id']`, e montar o slug como `'tnc_tax_' . (int) $id`. Para outras chaves, use o nome exato do tipo (o tipo URL usa hífen: `link-as-button`, `force-iframe`; `class-tainacan-url.php:20-26`).

**Detecção:** TNC-T04.

**Vista em:** vários projetos. Ver também: lição sobre valor de metadado Taxonomy fora de `postmeta` em `nucleo-tainacan.md`.

### L-EXT-13 · `get_metadata_section_id()` pode devolver array
**Sintoma:** "Array to string conversion"; todos os metadados caem sem seção ou na seção padrão.

**Causa:** para metadado **de repositório**, o método devolve o valor mapeado cru, que é uma lista com uma seção por coleção, por exemplo `["default_section","<id>"]`; para metadado de coleção devolve o primeiro elemento (`classes/entities/class-tainacan-metadatum.php:287-292`).

**Correção:** tratar o retorno como `(array)`; escolher a seção que existe na coleção atual, preferindo a específica à `default_section`.

**Detecção:** manual: `(string) $m->get_metadata_section_id()`, concatenação ou comparação direta com escalar.

**Vista em:** plugin de impressão de itens, vários projetos.

### L-EXT-14 · Descobrir os metadados da coleção em tempo de execução, não por lista fixa
**Sintoma:** campos novos do formulário ficam fora da exportação, em silêncio (o formulário mudou várias vezes).

**Causa:** listas fixas de IDs de metadado envelhecem a cada mudança de formulário.

**Correção:** descobrir os metadados da coleção a cada execução (seção, tipo, taxonomia, multivalorado) por `tainacan_metadata()->fetch_by_collection()`; nomear colunas como `SEÇÃO › Rótulo [id]`, porque há rótulos repetidos; publicar um dicionário de campos. Se o resultado da descoberta for guardado, ver L-EXT-44 (mapa com versão).

**Detecção:** manual: arrays literais com dezenas de IDs de metadado em código de exportação.

**Vista em:** plataforma de mapeamento comunitário sobre Tainacan.

---

## 4. Página do item: valor como HTML e ganchos de seção

**Como funciona.** `Item_Metadata_Entity::get_value_as_html()` usa o método do tipo quando ele existe e retorna cedo; só cai no filtro genérico `tainacan-item-metadata-get-value-as-html` quando o tipo não define o método (`classes/entities/class-tainacan-item-metadata-entity.php:146-156`, `:207`). Os tipos com HTML próprio aplicam `tainacan-item-metadata-get-value-as-html--type-{tipo}` com `($html, $item_metadata)`: compound, control, description, date, geocoordinate, relationship, taxonomy (`.../taxonomy/class-tainacan-taxonomy.php:429`, `:408` [1.0.3]), text, textarea, url (`.../url/class-tainacan-url.php:127`, `:112` [1.0.3]) e user. Numeric, selectbox e core-title não têm `--type-`. Depois, `Item::get_metadata_as_html()` passa tudo por `wp_kses(..., 'tainacan_content')` (`classes/entities/class-tainacan-item.php:776`, `:752` [1.0.3]). Por metadado há `tainacan-get-item-metadatum-as-html-{before|after}[--type-X|--id-N|--index-N]` e `-before-title`/`-after-title` (`:857-892`, `:870-872`); por seção, `tainacan-get-metadata-section-as-html-*` com variantes `--id-` e `--index-` (`:1296-1420`). Todos são filtros (`apply_filters`).

### L-EXT-15 · O filtro genérico de valor como HTML não roda para tipos com HTML próprio
**Sintoma:** filtro em `tainacan-item-metadata-get-value-as-html` registrado; `apply_filters()` chamado na mão transforma o valor, mas a página pública continua com o valor cru.

**Causa:** `get_value_as_html()` sai antes com `return $fto->get_value_as_html($this)` para text, textarea, date, taxonomy, compound, relationship, url, user e outros (`class-tainacan-item-metadata-entity.php:150-155`); o `apply_filters` genérico do fim (`:207`) nunca é alcançado.

**Correção:** enganchar nos filtros por tipo (`...--type-taxonomy`, `--type-url`, `--type-date`...) que interessam; o genérico só vale para numeric, selectbox e core-title.

**Detecção:** TNC-T09.

**Vista em:** plugin de dados ligados e enriquecimento, vários projetos.

### L-EXT-16 · Decorar termo a termo: `term_to_html()` é privado; casar por `data-id` com as duas aspas
**Sintoma:** impossível enganchar por termo individual na página do item; procurar `data-linkto='term'` (aspas simples) na página devolve 0, levando à conclusão errada de que não há âncoras de termo.

**Causa:** quem renderiza o termo na página do item é o tipo Taxonomy (`Taxonomy::term_to_html()`, `private`, `class-tainacan-taxonomy.php:581-614`), não `Term::_toHtml()`; o filtro `tainacan-term-to-html` (`classes/entities/class-tainacan-term.php:316-331`) não afeta essa saída. Os dois geram aspas **simples** (`class-tainacan-taxonomy.php:605`), e é assim que o HTML chega ao filtro `--type-taxonomy`. Na página final aparecem aspas **duplas** porque o `wp_kses` de `get_metadata_as_html()` reescreve os atributos (inferência da pesquisa).

**Correção:** filtro `tainacan-item-metadata-get-value-as-html--type-taxonomy` com `preg_replace_callback('#<a[^>]*data-id=["\'](\d+)["\'][^>]*>.*?</a>#is', ...)`, casando pelo `data-id` (= `term_id`) e aceitando os dois tipos de aspas, tanto no filtro quanto em testes que leem a página pronta.

**Detecção:** manual: regex ou `strpos` com o literal `data-linkto='term'` só com aspas simples; `tainacan-term-to-html` usado esperando efeito na página do item.

**Vista em:** plugin de dados ligados e enriquecimento.

### L-EXT-17 · O tipo URL faz autoembed sozinho
**Sintoma:** caixa cinza do tamanho de um vídeo no lugar do link (iframe `/embed/` de outro WordPress que não se deixa emoldurar), intermitente entre origens; iframes e embeds em saídas impressas.

**Causa:** `get_value_as_html()` do tipo URL tenta primeiro `$wp_embed->autoembed()` (`views/admin/components/metadata-types/url/class-tainacan-url.php:148`, `:185`). Uma URL de outro WordPress responde ao oEmbed e vira `<blockquote class="wp-embedded-content">` mais iframe.

**Correção:** no filtro `--type-url`, **restrito aos metadados que devem ser link**, trocar o embed por um link com rótulo (por exemplo o título remoto); não mexer nos outros campos URL, que podem querer embed de verdade. Em saídas para papel ou PDF, passar o HTML por `wp_kses` mantendo quebras, listas e negrito, transformar links em texto e imprimir o endereço. Verificar contando `wp-embedded-content` na página até dar zero.

**Detecção:** manual: contar `wp-embedded-content` e `<iframe` na página do item.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de impressão de itens.

### L-EXT-18 · Ganchos de seção disparam por seção e o `--index-` reinicia
**Sintoma:** bloco inserido duas vezes, ou perdido quando a última seção está vazia ou o tema divide as seções em blocos.

**Causa:** `tainacan-get-metadata-section-as-html-{before|after}--index-{n}` dispara por seção; o índice reinicia a cada chamada de `tainacan_the_metadata_sections()` e só conta seções com valor (`classes/entities/class-tainacan-item.php:1315-1317`, `:1417-1419`). Um tema pode renderizar a mesma seção duas vezes.

**Correção:** para "depois da ficha", usar `...-after--id-{id da última seção}`, com a última seção cacheada por coleção e invalidada quando as seções mudam. Para "antes da primeira", `...-before--index-0` com prioridade menor que a de outros plugins, para ordenar. Em ambos, guarda de "já impresso" (emissão única).

**Detecção:** manual: `add_filter` em `tainacan-get-metadata-section-as-html-*--index-` com índice calculado e sem guarda de execução única.

**Vista em:** plugin de dados ligados e enriquecimento, plugin de impressão de itens.

### L-EXT-19 · Rótulo legível do metadado na página pública
**Sintoma:** a lista pública mostra o nome técnico do metadado, por exemplo `[100] Autor Principal (Pessoa)`.

**Causa:** o núcleo imprime `get_metadatum()->get_name()` sem filtro para o texto do nome.

**Correção:** usar `tainacan-get-item-metadatum-as-html-before-title` (`classes/entities/class-tainacan-item.php:870`) para escrever o nome limpo e envolver o original num `<span hidden>`; só fora do admin e só nas coleções geridas pelo plugin. Dado e nome no admin ficam intactos.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-EXT-20 · Tema que imprime metadados um a um ignora `metadata__in`
**Sintoma:** a lista de metadados do tema repete a ficha do plugin, uma vez por seção.

**Causa:** o tema chama a impressão por metadado e não respeita `metadata__in`.

**Correção:** filtro `tainacan-fetch-args` no tipo `'metadata-section'` (`classes/repositories/class-tainacan-metadata-sections.php:215`) devolvendo `post__in = [-1]` só no front e só na página afetada: a seção sai vazia e `hide_empty` a esconde.

**Detecção:** manual.

**Vista em:** agregador arquivístico (AtoM/EAD).

---

## 5. Documento do item, galeria e tema

**Como funciona.** A página do item vem de `tainacan/single-items.php` do tema ou, por padrão (`tainacan_option_override_item_single_template`), de um filtro em `the_content` que usa `get_tainacan_item_single_content()`: galeria do documento, seções de metadados e anexos, com o filtro `tainacan_single_item_content` (`classes/theme-helper/class-tainacan-theme-helper.php:32-37`, `:167-182`, `:813-841`). O documento passa por `wp_kses` com `tainacan_content` e pelo filtro `tainacan-item-get-document-as-html` (`classes/entities/class-tainacan-item.php:949`). O filtro mais externo é `apply_filters('tainacan-get-the-document', $html, $item)` em `tainacan_get_the_document()` (`classes/theme-helper/template-tags.php:96-105`). **Δ** Na 1.0.3, `tainacan_get_the_document()` chama `get_document_as_html($item_id, $img_size)` contra a assinatura `get_document_as_html($img_size)`, e o `$item_id` vira tamanho de imagem (`:104` [1.0.3]; `classes/entities/class-tainacan-item.php:887` [1.0.3]); a 1.4.1 corrigiu.

### L-EXT-21 · Marcação própria do documento: filtro `tainacan-get-the-document`
**Sintoma:** a marcação customizada do visualizador de PDF volta a ser um `<iframe>` pelado.

**Causa:** `Item::get_document_as_html()` passa a saída por `wp_kses(..., 'tainacan_content')`; ganchos anteriores (inclusive `wp_embed_register_handler` para PDF ou `tainacan-item-get-document-as-html`) têm `<script>` e atributos `data-*` não permitidos removidos.

**Correção:** usar o filtro mais externo, `tainacan-get-the-document`, que roda depois do `wp_kses`. Na 1.0.3, não confiar no tamanho de imagem que chega por ali (**Δ** acima).

**Detecção:** manual: `wp_embed_register_handler(` para PDF, ou filtros anteriores ao `wp_kses` usados para injetar marcação rica.

**Vista em:** kit de memorial virtual.

### L-EXT-22 · Suprimir a seção "Documento" do tema pelo filtro, não por CSS
**Sintoma:** esconder com CSS deixa no DOM o carrossel, a moldura 4:3 (60vh reservados) e o link de download nativo.

**Causa:** `tainacan_has_document()` (`classes/theme-helper/template-tags.php:449`) é `! empty( tainacan_get_the_document() )`; só o filtro faz o tema achar que não há documento.

**Correção:** devolver vazio em `tainacan-get-the-document` só durante a janela aberta pela ação `get_template_part_template-parts/single-items-document` e fechada em `tainacan-interface-single-item-after-document` (tema Tainacan Interface); redes de segurança para fechar a janela (outra template part, `wp_footer`); desligar em modo galeria. Evitar `ob_start()` para isso: se a ação de fechamento não disparar, a página sai truncada.

**Detecção:** manual: CSS `display:none` na seção de documento do tema; `ob_start()` aberto em ação de template part sem fechamento garantido.

**Vista em:** kit de memorial virtual.

### L-EXT-23 · "Antes dos metadados" é um lugar, não um gancho fixo
**Sintoma:** a barra de ações cai no fim da página num site em que os metadados vêm primeiro.

**Causa:** no tema Tainacan Interface, a ordem das seções (`tainacan_single_item_layout_sections_order`, no Personalizar) muda qual ação corresponde a "antes dos metadados" (`after-title`, `after-document` ou `after-attachments`).

**Correção:** traduzir a ordem configurada para o gancho correto a cada boot; manter filtros de conteúdo como rede e garantir emissão única.

**Detecção:** manual: `add_action('tainacan-interface-single-item-after-attachments', ...)` fixo para posicionar conteúdo relativo aos metadados, sem ler a ordem configurada.

**Vista em:** kit de memorial virtual.

### L-EXT-24 · Galeria: configuração em dois lugares e link de download condicional
**Sintoma:** a interceptação de anexos funciona numa versão do Tainacan e falha em outra.

**Causa:** a configuração da galeria mudou de lugar entre versões: global `tainacan_plugin.tainacan_media_components[<id>]` numa e atributo `data-tainacan-media-component-config` em outra (1.1). O contêiner do PhotoSwipe é `.swiper-wrapper` numa e `.tainacan-media-items` na outra. O link `.tainacan-item-file-download` só existe com `show_download_button` ligado.

**Correção:** ler as duas fontes; configuração ausente significa lightbox ligado (padrão); não amarrar em seletor interno do contêiner.

**Detecção:** manual: JS que lê só uma das fontes de configuração ou ancora em `.swiper-wrapper`/`.tainacan-media-items`.

**Vista em:** kit de memorial virtual.

---

## 6. Busca facetada (ganchos JS e modos de visualização)

**Como funciona.** O bloco `tainacan/faceted-search` monta um app Vue (`views/gutenberg-blocks/blocks/faceted-search/theme.vue`). Os ganchos por item são filtros **JavaScript** de `wp.hooks`, definidos em `theme-search/js/view-modes-mixin.js:56-80` e injetados com `v-html` (`view-mode-cards.vue:37-39`, `:118-120`). Callback: `(html, item) => string`. `hasAfterHook()` testa `tainacan_faceted_search_collection_item_after` ou o nome por coleção; `getAfterHook()` aplica `tainacan_faceted_search_item_after` e depois o nome por coleção (`:62-78`). O filtro `tainacan_faceted_search_items_list_before` é lido em `theme.vue:1411`. As listas usam `:key="index"` (`view-mode-cards.vue:25` e outros), então o Vue reaproveita o elemento entre páginas. Modos de visualização: `tainacan_register_view_mode($slug, $args)`, com `type='template'` por padrão (`classes/theme-helper/class-tainacan-theme-helper.php:1111-1134`).

### L-EXT-25 · Gancho "depois do item" tem nomes trocados: registre os dois
**Sintoma:** botões ou selos nos cartões da busca não aparecem; o lugar reservado por item nunca é desenhado.

**Causa:** assimetria confirmada na 1.0.3 e na 1.4.1: `hasAfterHook()` checa `tainacan_faceted_search_collection_item_after` (sem id), `getAfterHook()` aplica `tainacan_faceted_search_item_after` (`view-modes-mixin.js:62-78`). Com só o segundo, o `v-if` fica falso.

**Correção:** registrar a mesma função nos dois nomes (o HTML é acrescentado uma vez só, porque `getAfterHook` não aplica o nome sem id), concatenando ao HTML recebido para conviver com outros plugins; ou usar só o nome por coleção, `tainacan_faceted_search_collection_{id}_item_after`, que é igual nos dois métodos. Como as listas usam `:key="index"`, não guarde estado no elemento esperando que ele mude de item.

**Detecção:** TNC-T06.

**Vista em:** página pública do item por tipo de mídia, plugin de busca segmentada, indexador de busca.

### L-EXT-26 · `tainacan_faceted_search_items_list_before` é lido uma vez no boot do app
**Sintoma:** o painel acima da lista de itens não aparece quando o filtro é registrado por script no rodapé.

**Causa:** o app lê o filtro uma única vez ao montar (`theme.vue:1411`); script que registra o filtro depois chega tarde.

**Correção:** público: registrar o filtro em script no `<head>` com dependência `wp-hooks`, e reagir ao evento `tainacan-items-list-is-loading-items`. Admin: ação JS `tainacan_navigation_path_updated` (`views/admin/admin.vue:38`, `:46`) mais `hashchange`, com o contêiner inserido antes de `#items-list-results`.

**Detecção:** manual: `addFilter('tainacan_faceted_search_items_list_before', ...)` em script enfileirado no rodapé.

**Vista em:** plugin de biblioteca.

### L-EXT-27 · Modo de visualização do tipo `template` não executa scripts
**Sintoma:** scripts inline dos modos de visualização do plugin não rodam.

**Causa:** o HTML do modo `template` é gerado no PHP e entra por `v-html` (innerHTML). Mesma causa de L-EXT-02.

**Correção:** registrar o modo como componente Vue mínimo que monta módulos JS puros enfileirados, ou manter o template sem script e ligar o comportamento por arquivo enfileirado com observador de DOM.

**Detecção:** TNC-W09 (quando houver `v-html`/innerHTML no código do plugin); manual: `<script>` inline no template do modo.

**Vista em:** página pública do item por tipo de mídia.

---

## 7. REST do Tainacan (`tainacan/v2`)

**Como funciona.** Namespace `tainacan/v2` (`classes/api/tainacan-rest-creator.php:12`). `prepare_filters($request)` traduz a URL para `WP_Query` (`classes/api/class-tainacan-rest-controller.php:94-187`, `:83-173` [1.0.3]): `id` vira `p`, `perpage` vira `posts_per_page`, `taxquery` vira `tax_query`, `metaquery` vira `meta_query`. Dentro de cada cláusula só passam chaves listadas (`:131-161`, copiadas em `prepare_meta()` `:280-330`): `taxquery` aceita `taxonomy`, `metadatum` (vira `field`), `terms`, `operator`, `relation`; `metaquery` aceita `key`, `value`, `compare`, `relation` e `metadatumtype` (vira `type`). No fim, `$args['perm'] = 'readable'` e o filtro `tainacan-api-prepare-items-args` (`:184-186`). Teto de página: `$TAINACAN_API_MAX_ITEMS_PER_PAGE` (constante `TAINACAN_API_MAX_ITEMS_PER_PAGE`, ou opção `tainacan_option_search_results_per_page`, padrão 96; `tainacan.php:27`). **Δ** Na 1.4.1, `perpage < 1` (inclusive `-1`) vira o teto (`class-tainacan-rest-controller.php:173-177`, `:196-202`); na 1.0.3, `perpage=-1` passa sem limite. Ponto de curto-circuito para motores externos: `tainacan-api-get-items-alternate` (`classes/api/endpoints/class-tainacan-rest-items-controller.php:680-683`). Campos extras: `tainacan-api-response-{entidade}-meta` e `tainacan-api-items-prepare-for-response` (`:280-292`, `:378`). Rotas novas: `register_rest_route` padrão do WordPress (parte 2, seção 11).

### L-EXT-28 · Chaves descartadas em silêncio: `include_children`, `field`, `type`
**Sintoma:** filtro de termo pela URL sempre inclui os termos filhos; a contagem por termo direto não bate; `metaquery[0][type]=NUMERIC` é ignorado.

**Causa:** `prepare_meta()` só copia as chaves listadas. `include_children` não está na lista, então a `WP_Tax_Query` usa o padrão `true`; `field` precisa ir como `metadatum`; `type` precisa ir como `metadatumtype` (`class-tainacan-rest-controller.php:131-161`). Além disso, o `type` do primeiro nível de `meta_query` é sobrescrito pelo tipo do filtro cadastrado (`class-tainacan-rest-items-controller.php:697-707`).

**Correção:** não contar com `include_children` pela REST. Se precisar de contagem "só o termo", recalcular no motor próprio (por exemplo com `get_term_children()` no SQL; num índice de busca com termos atribuídos, a contagem é direta) e fazer cada motor contar como a sua lista filtra. Limitação conhecida: termo-pai sem itens próprios fica de fora no SQL. Use `metadatum` e `metadatumtype`.

**Detecção:** TNC-T07.

**Vista em:** plugin de busca segmentada, vários projetos.

### L-EXT-29 · `taxquery` usa `operator`; contar e resolver IDs pela REST com `paged`
**Sintoma:** filtro por taxonomia ignorado; total ou intervalo divergindo da lista; páginas de termo sem filtro; pedaços da lista embaralhados.

**Causa:** no `taxquery` a chave é `operator` (`IN`, `NOT IN`, `LIKE`...), não `compare` (que é do `metaquery`). Em página de termo, a busca facetada põe o `taxquery` por dentro, sem aparecer na URL. `perpage` acima do teto (96 por padrão, configurável) é cortado. Empates na ordenação embaralham pedaços resolvidos separadamente.

**Correção:** `operator` no `taxquery`; acrescentar no servidor o `taxquery` da página de termo; chamar a REST internamente com `rest_do_request()`; resolver os IDs uma vez, por `paged` (não `offset`), numa lista única guardada em transient com token atrelado ao usuário; respeitar o teto e ler `X-WP-Total`.

**Detecção:** TNC-T07 (parcial); manual: `taxquery` com chave `compare`; `perpage` maior que o teto.

**Vista em:** plugin de impressão de itens.

### L-EXT-30 · `prepare_filters()` traduz o parâmetro `id` em `p`
**Sintoma:** rota própria `collection/{id}/ids`, que reaproveita `prepare_filters()`, filtra pelo post de id igual ao da coleção e devolve nada.

**Causa:** `id` vira `p` no mapeamento URL para `WP_Query` (`class-tainacan-rest-controller.php:95-131`).

**Correção:** em rota com parâmetro `{id}` que passa por `prepare_filters()`, descartar `p` (`unset($args['p'])`) ou dar outro nome ao parâmetro da rota.

**Detecção:** manual: rota REST com `{id}` cujos parâmetros vão a `prepare_filters(` sem `unset` de `p`.

**Vista em:** página pública do item por tipo de mídia.

### L-EXT-31 · Valor de taxonomia pela REST: `["7"]` cria termo chamado "7"
**Sintoma:** termos com nome numérico surgem na taxonomia.

**Causa:** com termos novos liberados, `{"values": ["7"]}` é interpretado como nome; `{"values": 7}` (id inteiro) ou o nome do termo funcionam. A rota de valores exige corpo JSON real ("body could not be empty" com parâmetros soltos).

**Correção:** enviar id inteiro ou nome; `allow_new_terms = no` em taxonomia controlada; em chamadas internas, `$request->set_body( wp_json_encode(...) )` com `Content-Type: application/json`.

**Detecção:** manual: payload para `/tainacan/v2/item/{id}/metadata/{mid}` com `values` como lista de strings numéricas.

**Vista em:** agregador arquivístico (AtoM/EAD).

### L-EXT-32 · `perm=readable` chega aos argumentos (inclusive das facetas)
**Sintoma:** tradutor de argumentos para um motor próprio (por exemplo um índice de busca) recusa a consulta e todas as facetas caem no SQL.

**Causa:** `prepare_filters()` acrescenta `$args['perm'] = 'readable'` (`class-tainacan-rest-controller.php:184`), e as facetas passam `current_query` por `prepare_filters` (`classes/api/endpoints/class-tainacan-rest-facets-controller.php:100-108`). Um tradutor em modo estrito recusa a chave não modelada.

**Correção:** modelar `perm`: traduzi-lo para os `post_status` visíveis ao usuário corrente. O caso "vê só os próprios privados" (condição de autoria) pode ficar no SQL de propósito. Status não público só deve ser honrado para quem pode ler privados (ver `seguranca-wordpress.md`).

**Detecção:** manual: lista branca de chaves de `WP_Query` num tradutor que não inclui `perm`.

**Vista em:** indexador de busca (Elasticsearch/OpenSearch).

### L-EXT-33 · Contexto por coleção em requisições REST
**Sintoma:** com várias coleções geridas, cada uma com seu mapeamento, leituras globais usam só a coleção primária.

**Causa:** a opção de coleção era singular e virou lista; as leituras continuaram globais.

**Correção:** `get_mapping($collection_id)` e um contexto de coleção por requisição, aberto em `rest_request_before_callbacks` e fechado em `rest_request_after_callbacks`, sem migrar cada leitura.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-EXT-34 · Testar pelo repositório pula a normalização do controlador
**Sintoma:** testes verdes, rota real falha.

**Causa:** normalização, sanitização e permissão estão no controlador REST (no do Tainacan, `prepare_filters()` e `sanitize_value()`, `class-tainacan-rest-controller.php:259-268`), não no repositório.

**Correção:** testar o caminho real com `rest_do_request( new WP_REST_Request(...) )`, por papel de usuário.

**Detecção:** manual.

**Vista em:** plugin de pesquisas/questionários.

---

## 8. Exposers e saída XML/RDF

**Como funciona.** `Exposers_Handler` dispara `tainacan-register-exposers` em `init`; o plugin chama `register_exposer($class_name)` com uma subclasse de `\Tainacan\Exposers\Exposer`, cujo método abstrato é `rest_request_after_callbacks($response, $handler, $request)` (`classes/exposers/class-tainacan-exposers-handler.php:31`, `:47`, `:55`; `classes/exposers/class-tainacan-exposer.php:13`, `:81`). O handler atua em `rest_request_after_callbacks` e em `tainacan-api-get-items-alternate` (`class-tainacan-exposers-handler.php:33-35`). Quando a requisição traz o parâmetro de URL do exposer, o handler imprime `wp_kses(stripcslashes($response->get_data()), wp_kses_allowed_html('tainacan_content'))` e dá `exit()` (`:144-151`). Mapeadores: `tainacan-register-mappers` e `register_mapper()` (`classes/mappers/class-tainacan-mappers-handler.php:46`, `:54`).

### L-EXT-35 · Saída de exposer por `?exposer=` passa por `wp_kses`
**Sintoma:** XML/RDF do exposer sai apagado, com `<iri>` e `&` destruídos; o exposer JSON-LD do núcleo chamou método inexistente na versão testada.

**Causa:** no caminho com parâmetro de URL, o handler passa o corpo por `wp_kses` com a lista `tainacan_content` (`class-tainacan-exposers-handler.php:149`), que remove tags XML desconhecidas.

**Correção:** no exposer próprio (registrado em `tainacan-register-exposers`), servir o documento pronto com os cabeçalhos certos e dar `exit` antes de o handler imprimir. Alternativa mais limpa: rotas REST próprias que servem cru por `rest_pre_serve_request`, com `Content-Type` de RDF/XML e `X-Content-Type-Options: nosniff`.

**Detecção:** manual.

**Vista em:** plugin de biblioteca, busca semântica por embeddings.

### L-EXT-36 · RDF/XML exige QName válido em todo predicado
**Sintoma:** exportação RDF/XML falha para algumas propriedades.

**Causa:** o serializador (EasyRdf) exige que todo predicado vire QName; IRI cujo último segmento não começa por letra (por exemplo `/property/123`) falha. Nó em branco vindo de fora também não é aceito.

**Correção:** cunhar IRIs de propriedade como `.../property/p{id}`; gerar nós em branco só internamente; metadados compostos viram IRIs com fragmento.

**Detecção:** manual: IRI de propriedade terminada em dígito.

**Vista em:** busca semântica por embeddings.

### L-EXT-37 · `esc_xml()` devolve vazio para texto longo
**Sintoma:** envio SOAP de documento em Base64 chega com o conteúdo vazio.

**Causa:** `esc_xml()` devolve `''` para textos longos (por exemplo, um arquivo inteiro em Base64).

**Correção:** escapar texto para XML com função própria baseada em `htmlspecialchars( $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' )`; Base64 nem precisa de escape.

**Detecção:** manual: `esc_xml(` aplicado a resultado de `base64_encode(` ou `file_get_contents(`.

**Vista em:** plugin de processos de gestão de acervo.
