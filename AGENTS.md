# Tainacan Interface: notas para agentes e mantenedores

Contexto portátil para quem continuar este trabalho em outra sessão. O [README.md](README.md) explica o uso; aqui está o que não se deduz do código.

## O que é

Tema WordPress para acervos Tainacan, derivado do tema oficial **Tainacan Interface 2.10.0** (`github.com/tainacan/tainacan-theme`, branch `develop`, commit `84b9a20`). O primeiro commit deste repositório é a cópia sem alteração do `src/` de lá; tudo depois dele é trabalho deste tema, e `git diff <primeiro commit>` mostra a diferença inteira.

**Decisão de 30/10/2026 (Marcos): este tema é a evolução do Tainacan Interface, a versão 3.0.0, não um tema novo.** Ele será apresentado à equipe do Tainacan para virar a versão oficial (publicada por eles no WordPress.org; a atualização oficial leva os sites para este código). Por isso tem a identidade do Tainacan Interface em tudo, e uma atualização da 2.x no lugar não pode mudar a página do visitante (inc/upgrade.php: recursos novos desligados + aviso no painel). Nada de `Update URI`/canal próprio de atualização.

Repositório de desenvolvimento: `marcossigismundo/tema-tainacan-2-0` (privado; o nome é o provisório "Tema Tainacan 2.0" das versões 2.0.0–2.2.1). Entrega para a equipe: ramo `tainacan-interface-3.0` do fork `marcossigismundo/tainacan-theme`, a partir do develop oficial, com o tema em `src/` (formato do repositório oficial), `TAINACAN-INTERFACE-3.md`, `CHANGELOG.md` e `docs/3.0/` na raiz. Push e PR para `tainacan/tainacan-theme` só com confirmação do Marcos.

Pasta do tema no site: `tainacan-interface`. Domínio de texto: `tainacan-interface`. Prefixo do código novo: `tainacan_interface_` (funções e ganchos), `TAINACAN_INTERFACE_` (constantes), `tt2-` (classes CSS, handles), `tt2…` (objetos JS: `tt2Drawer`, `tt2Viewer`).

## Versão 4: design novo (03/11/2026)

**Pedido do Marcos (03/11/2026):** trazer para o Interface tudo do "Tainacan sem tema" e do leiaute do cienciaemsintese, sem depender do Bootstrap, altamente ajustável. Isso **substitui** a decisão de "enxuto" registrada abaixo: agora há ajustes por coleção e muitas opções, porque ele pediu. O fork `tainacan-core-display` não recebe mais investimento.

- `inc/settings.php`: **o esquema é a fonte de tudo.** Opção nova = uma entrada no esquema; ela aparece sozinha no Personalizar (painel `tainacan_interface`) e, com `collection => true`, no formulário da coleção (meta `tainacan_interface_opt_<chave>`). Lê com `tainacan_interface_opt( 'chave' )`.
- `inc/design.php`: tokens CSS em `body.tt2-modern`, classes do body, mosaicos (transient `tt2_mosaic_<id>`, 12 h) e arte gerada. `assets/css/modern.css`: todas as regras escopadas por `body.tt2-modern`.
- `inc/collection-pages.php`: cabeçalho, trilha, título do item. `template-parts/collections-grid.php`: lista de coleções. `inc/nav-menus.php`: caixa de menus, submenu automático, locais por coleção (registrados só no painel; no site o menu é lido direto de `nav_menu_locations`). `inc/item-citation.php`: como citar.
- **Clássico continua existindo:** `tainacan_interface_is_modern()` decide. Atualização no lugar grava `design = classic` e avisa; ativação nova entra no moderno. Cada template novo se desvia cedo do clássico, e o clássico tem de seguir igual: comparar em pixels antes de mexer em `foundation.css`.
- **2.12.0 (19/11/2026): painel da coleção** (`inc/collection-form.php` + `assets/{css,js}/collection-form.*`). O gancho `end-right` do formulário da coleção é HTML em v-html: o Tainacan lê **todo** elemento de `form-collection-end-right` (campo sem `name` vira `data[""]`), marca rádio sozinho a partir da meta e só preenche valor não vazio. Por isso: escolhas = rádios com valor `""` = "Como no site"; controles auxiliares (seletor de cor, botões) com `form="tt2-cform-none"` para ficarem fora de `form.elements`; script só enfeita (evento `tainacan-collection-hook-reload` + `toggle`). Seções de metadados: uma escolha (`tainacan_interface_sections`) que grava as duas metas antigas (`tainacan_interface_section_layout` + `tainacan_interface_opt_item_sections`); etapa 3 da migração converte; ativação vinda de outro tema marca etapa 2 para a 3 rodar.
- **2.11.0 (19/11/2026): numeração voltou para a série 2** (decisão do Marcos: "a versão 4 não está boa"). As 3.0.0–4.10.2 viram builds de desenvolvimento; 2.11.0 continua a 2.10.0 oficial. **Nunca comparar versão para decidir migração**: `inc/upgrade.php` conta etapas (`migration` na option `tainacan_interface`, constante `TAINACAN_INTERFACE_MIGRATION_STEP`; site que viu 4.3.0+ = etapa 1, 4.9.0+ = 2). Etapa nova = incrementar a constante e testar `$step < N`. Na VPS a passagem 4.10.2 → 2.11.0 exigiu `deploy.sh --force` uma vez por site.
- **4.10.1 (19/11/2026).** `h1_size`…`h6_size` (vars `--tt2-h1..6`; o H1 é o teto do título grande das páginas), `item_label_size`/`item_value_size` em px (substituem `item_meta_size`); filtro `tainacan-default-view-mode-for-themes` → `records` se o site não escolheu padrão no Tainacan (coleções mantêm o próprio). Lição dos lápis: abrir seção leva a prévia ao tipo de página dela (`customizer-controls.js`); lápis não pode disparar isso, senão a prévia recarrega e os cliques seguintes se perdem. Teste de lápis com mouse real por CDP (`Input.dispatchMouseEvent`), o `click()` programado não pega sobreposição.
- **4.9.0 (19/11/2026).** `item_order` deixou de existir: a ordem faz parte de `item_layout` (stacked, stacked-split, stacked-record, media-left, media-right). Conversão única em `tainacan_interface_merge_item_order()` (upgrade.php, quem vem de < 4.9.0), no site e nas coleções.
- **4.8.0 (19/11/2026).** Explorador de termos: Mapa e Radial retirados a pedido do Marcos (ilegíveis com centenas de termos num nível); entram Colunas (estilo Finder), índice A–Z e páginas de 100 por nível. Lista de coleções ganha as taxonomias abaixo (`list_taxonomies`, `list_taxonomies_heading`, `template-parts/collections-taxonomies.php`).
- **4.7.0 (19/11/2026).** `item_meta_size` (escala `--tt2-meta-scale` dos nomes e valores da ficha); lápis do Personalizar em `inc/customizer-shortcuts.php` (parcial presa a uma configuração-âncora que nunca muda + `primary_setting` = opção alvo: o lápis abre a opção sem estragar a prévia ao vivo; filtro `tainacan_interface_customizer_shortcuts`); painel renomeado "Aparência do site e das coleções". Lição: o esquema de opções era montado e guardado antes do `init` (quando o tema carrega as traduções), e o Personalizar inteiro saía em inglês; agora só fica em cache depois do `init`.
- **4.6.0: explorador de termos (19/11/2026).** A página da taxonomia (`tainacan/archive-terms.php`) trocou fichas/miniaturas/tabela por `template-parts/terms-explorer.php` + `inc/terms-explorer.php` + `assets/js|css/terms-explorer.*`: Árvore (padrão), Mapa (treemap) e Radial, painel do termo (caminho, imagem, descrição, filhos, mesmo nível, 8 itens pela REST `tainacan/v2/items` com `taxquery`, total real pelo `X-WP-Total`), link `#termo-<id>`, gaveta no celular. Estrutura da taxonomia vai no HTML como JSON (transient que expira quando qualquer termo muda); sem JS, lista aninhada de links. `tainacan_terms_viewmode` aceita tree/map/radial; valores do 2.x e `termsparent`/`search` continuam funcionando. Lições: plural de `_n_noop` precisa ir traduzido para o JS (`translate_nooped_plural`); títulos do painel com classe `label`; radial com mais de 48 termos no 1º anel usa anel único; captura de celular só com emulação (CDP), o `--window-size=390` do headless não é celular.
- **4.4.0: título de página (07/11/2026).** `template-parts/page-title.php` + `inc/page-title.php` (`tainacan_interface_page_title_parts()`, filtro de mesmo nome): sobretítulo (categoria do post, página-mãe, tipo de lista), título grande, cápsula com contagem, descrição do termo/autor, botão Voltar com endereço real (o JS só volta no histórico se a página anterior for do site e diferente desta). Opções `page_title_style` (editorial/centered/compact, classe `tt2-title-*`), `page_title_meta`, `page_title_back`. Usado por loop.php, loop-singular.php e tainacan/archive-terms.php; as classes do 2.x (`tainacan-title`, `title-page`, `title-back`) ficam, mas a estrutura deixou de ser lista.
- **4.3.0: um design só (07/11/2026, decisão do usuário).** O visual clássico saiu, com as regras antigas do Bootstrap e o jQuery. Isso REVOGA a regra de que uma atualização no lugar não muda o que o visitante vê; continua valendo não quebrar tema filho: nomes de template-parts, ações, handles `bootstrap4CSS`/`bootstrap4JS`, `lib/class-wp-bootstrap-navwalker.php` e classes do grid ficam.
- **4.2.1: opções do Blocksy.** Comparado com o Personalizar do Blocksy 2.1 (grátis) pelos arquivos de opções dele; entrou o que serve a um tema de acervo, cada opção como variável CSS ou classe no `body`. Fora de propósito: construtor de cabeçalho por arrastar, mega menu, modo escuro, valor por dispositivo em toda opção. O design moderno não carrega jQuery: `modern.js` faz o que `js.js` e `tainacan-interface-truncate.js` faziam; não reintroduzir dependência de jQuery no design moderno.
- **4.1.0: revisão opção por opção.** O que uma opção faz é conferido no navegador: `deploy`-se e compara-se valor a valor. Regras de quando uma opção aparece em `tainacan_interface_option_rules()`; o que muda a prévia sem recarregar em `tainacan_interface_live_options()`. Toda opção nova precisa de efeito medido e de regra se depender de outra. Estado do tema numa opção só (`tainacan_interface_state()`); nada de opção nova que fique no banco: o diretório pede uma só (a trava `tainacan_interface_updating` dura um pedido e é apagada).
- **Bootstrap saiu.** `assets/css/foundation.css` foi extraído uma vez do CSS compilado do Bootstrap 4.6.1 (mesmas classes e medidas) e agora é arquivo nosso, editado à mão. Só `.navbar-collapse.collapse` esconde: o `.collapse` global colidia com o Tainacan.

**Pegadinhas da 4.0:**
- `.dropdown-item` e `.dropdown-menu` também são classes do Tainacan (botões de visualização). Regra do tema para menu tem de ser escopada em `nav.navbar`/`.tt2-menu-bar`; uma `min-width` solta esticou os botões da lista e empurrou a barra para duas linhas.
- `body h1:not(...)` do tema força cor e tamanho: os títulos novos levam `!important`.
- Imagens com `loading=lazy` saem em branco em captura com `captureBeyondViewport`, e viewport esticado para a página inteira deforma galeria e filtros. Conferir com rolagem real antes de achar que é defeito.
- O Chrome de teste guarda o CSS pela `?ver=`: com a mesma versão, desligar o cache (`Network.setCacheDisabled`) ou a comparação mente.
- O `sed` e o heredoc do Bash comem barras invertidas: um regex de geração virou `.(?:order|offset)-` e apagou `.border-*`. Scripts com regex se escrevem por ferramenta de arquivo.

**Vitrine no catalogacao:** `deploy/servidor/catalogacao/interface-4-vitrine.php` no repo de infra liga o design, gera banners e ícones, dá a cada coleção um leiaute e cria os menus. A coleção 202 é a referência do anexo.

## A decisão que organiza o projeto (até a versão 3)

O usuário pediu (30/10/2026) um projeto **mais enxuto** que o `tainacan-tema-acervo` (3.6.2, 17 recursos, ~20 mil linhas de PHP). Por isso este tema é o Tainacan Interface **com quatro mudanças e nada mais**:

1. as falhas de segurança do tema oficial corrigidas (as já conhecidas e as de uma auditoria nova, ver "Segurança");
2. a **gaveta lateral de prévia do item** nas listas, portada do TainacanBiblio (`Item_Peek`);
3. o **visualizador de documentos e imagens** na página do item, refeito a partir da galeria do Memorial COVID Toolkit;
4. a **ficha em destaque** (CSS do trabalho para o acervo do MHN, a "pele L12" do Acervo em Cena), nas listas e na página do item.

**Compatibilidade com o Tainacan Interface é requisito**: nomes de função, `theme_mods`, ações `tainacan-interface-*`, classes CSS e templates continuam os mesmos, porque há temas filhos e plugins (Item Print, Busca Segmentada, LOD) pendurados neles. Na primeira ativação, `tainacan_interface_import_tainacan_interface_mods()` copia os ajustes do Personalizar do `tainacan-interface`, se o tema novo ainda não tiver os seus.

Não entram aqui: a tela de Recursos com interruptores, perfis, ajuste por coleção, comparação, folhear, frase de recorte, abas, citação. Quem quiser isso tem o `tainacan-tema-acervo`.

## Mapa do código

- `functions.php`: setup herdado + carga dos módulos. `TAINACAN_INTERFACE_VERSION` é a versão; `TAINACAN_INTERFACE_VERSION` continua definida (igual) para temas filhos.
- `inc/request.php`: **toda** leitura de `$_GET` passa por aqui (`tainacan_interface_query_arg`, `tainacan_interface_query_flag`, `tainacan_interface_query_url`, `tainacan_interface_list_query_args`, `tainacan_interface_internal_url`).
- `inc/context.php`: detecção de página (`tainacan_interface_is_list_page`, `tainacan_interface_is_item_page`), permissão de leitura de item (`tainacan_interface_readable_item`) e limite por IP (`tainacan_interface_rate_limit`).
- `inc/item-drawer.php` + `assets/js/item-drawer.js` + `assets/css/item-drawer.css`: gaveta. Rota `GET tainacan-interface/v1/item/<id>?meta=1,2`. Abre por clique em qualquer parte do cartão ou da linha da tabela (links de termo/autor seguem). Caixa de mídia com o documento (imagem, vídeo/áudio com `preload="none"`, PDF por capa).
- `inc/drawer-metadata-option.php`: opção "Mostrar na caixa lateral" no formulário de cada metadado (gancho `metadatum` do Tainacan, meta plana `tainacan_interface_drawer` = yes/no no post do metadado, declarada em `tainacan-api-response-metadatum-meta`). Pedido do usuário em 30/10/2026: a gaveta não pode repetir o cartão. Com algum metadado marcado, mostra só os marcados (a descrição só se marcada); sem nenhum, mostra o que a página do item mostra menos o que o cartão já mostra.
- `inc/item-viewer.php` + `assets/js/item-viewer.js` + `assets/css/item-viewer.css` + `assets/vendor/pdfjs/` (PDF.js 4.10.38, legacy, Apache-2.0): visualizador.
- `inc/item-highlight.php` + `assets/css/item-highlight.css`: ficha em destaque e linha de identificação (coleção + nº de registro).
- `inc/share.php`: links de compartilhar (um lugar só para os seis modelos).
- `functions/customizer/tainacan-interface.php`: seção "Tainacan Interface" do Personalizar, três interruptores (`tainacan_interface_item_drawer`, `tainacan_interface_item_viewer`, `tainacan_interface_item_highlight`), todos ligados por padrão. Cada recurso também tem filtro (`tainacan_interface_drawer_enabled`, `…_viewer_enabled`, `…_highlight_enabled`).
- O resto (`functions/`, `template-parts/`, `tainacan/`) é o Tainacan Interface, escapado e corrigido.

## Segurança

Auditoria de 30/10/2026 sobre o `develop` do tema oficial. Corrigido aqui, com o caminho do dado:

| # | Falha no original | Onde | Correção |
|---|---|---|---|
| A1 | XSS refletido: `?search=` impresso cru no `value` da busca de termos | `tainacan/archive-terms.php` | `esc_attr()` + query var sanitizada no filtro `request` |
| A2 | XSS refletido: `?s=` cru na busca de coleções | `archive-tainacan-collection.php` | `get_search_query( false )` + `esc_attr()` |
| A3 | XSS armazenado: nome de termo com aspas fecha o `href` do link de compartilhar (o banco guarda as aspas) | `template-parts/headertaxonomy.php` | `inc/share.php`, `rawurlencode` + `esc_url` |
| M1 | `?ref=` impresso no "voltar à lista": link para fora e HTML injetado no breadcrumb | `functions/single-functions.php`, `breadcrumb.php` | só endereço deste host (`tainacan_interface_query_url`) e `esc_url` |
| M2 | Cor da coleção gravada crua e impressa num `<style>`: qualquer editor de coleção punha CSS nas páginas públicas | `class-tainacan-interface-collection-settings.php` | `sanitize_hex_color` ao gravar e ao imprimir, lista fechada para texto e leiaute |
| M3 | Navegação entre itens calculada até 3 vezes por página, cada uma com 2 consultas montadas do `$_GET` | header, breadcrumb, navegação | memo por requisição (`tainacan_interface_adjacent_items`), chamada morta removida |
| H1 | `?pos=0&status=draft`: o Tainacan traduz `status` em `post_status` e o `WP_Query` não confere permissão, então o título e a miniatura de rascunho apareciam para anônimo | navegação entre itens | `status`/`post_status` retirados antes do Tainacan ler o pedido, para quem não edita |
| B1 | `?termspaged=abc` dava TypeError (HTTP 500) no tema e no plugin | listas de termos | query vars normalizadas no filtro `request` |
| B2 | `$_SERVER['QUERY_STRING']` colado no `href` do modo Miniatura | `tainacan/view-mode-grid.php` | `add_query_arg()` com argumentos sanitizados |
| B3/B4 | Números e logo do Personalizar salvos com `sanitize_text_field` e impressos em `style`/`src` (`javascript:x//a.png` passava) | customizer, `footer.php` | `absint`, `esc_url_raw` com esquema http(s) |
| B5–B10 | `$_GET` sem `wp_unslash`, `get_template_part()` com slug da URL, links de compartilhar sem codificação, permalink em `onclick`, saídas sem escape, "Ler mais" sem `wp_kses` | vários | ver CHANGELOG 2.0.0 |
| — | Google Fonts carregado de CDN (IP do visitante ao Google) | `enqueues.php`, editor | removido; Roboto do aparelho ou fonte do sistema |

O `unserialize` cru e o `IN` com `implode` que a memória citava **não existem no tema** (eram do núcleo do Tainacan).

Fica de fora, por ser do núcleo: anônimo listar rascunho pela **REST** com `status=any` (relatório privado `tainacan-achados-core-tema`, item 1). O tema fecha só o caminho da navegação entre itens.

## Pegadinhas deste projeto

- **A lista do Tainacan emite `data-tainacan-item-id`** no `li`/`tr` de cada modo de visualização. O `tainacan-acervo` resolvia o item pela URL com `url_to_postid()` achando que o id não existia; a gaveta daqui usa o atributo, como o Biblio. O modo Miniatura (PHP do tema) também imprime o atributo.
- **Gaveta: o Biblio e o Acervo em Cena têm a deles.** Quando `window.tainacanBiblioPeek` ou `window.AEC.drawer` existe, a do tema cede a vez.
- **Visualizador: os slides da galeria do Tainacan não trazem o id do anexo.** O Memorial casava o caminho da imagem no navegador; aqui o PHP faz isso no filtro `get_tainacan_item_gallery` (duas passadas de `WP_HTML_Tag_Processor`: acha o arquivo de cada slide pelo caminho decodificado ou por `tainacan_attachment_html/<id>`, depois grava `data-tt2-file` no slide). Caminho com acento só casa decodificado (`rawurldecode`).
- O visualizador é um `<dialog>` com `showModal()`: fica na camada superior do navegador, acima do PhotoSwipe, sem z-index, com foco preso e página inerte. O botão Voltar do celular fecha (há `pushState` com `#tt2-file-<id>`, que também abre o arquivo direto).
- **PDF.js vai com extensão `.js`**, não `.mjs`: há servidor que entrega `.mjs` como `application/octet-stream` e o `import()` falha em silêncio.
- **Pele das listas usa `!important`** de propósito: os seletores do Vue levam `[data-v-…]` (0-3-0) e o bloco de Busca facetada imprime cores inline. A cor de destaque é a cor de link do tema e **não** é devolvida às variáveis do Tainacan (`--tainacan-secondary`): as duas se realimentariam.
- O nº de registro da linha de identificação vem do primeiro metadado cujo nome diga registro/tombo/inventário/identificador; filtro `tainacan_interface_identifier_metadatum` escolhe outro (ou `-1` para nenhum).
- **O heredoc do Bash come `\\` nesta máquina.** PHP e JS com regex se escrevem por ferramenta de arquivo.
- **Menu lateral do celular preso no cabeçalho.** O painel (`#menubelowHeader`, `position: fixed`) fica dentro do `nav.navbar`. Se o CSS do site der ao `nav` `backdrop-filter`, `filter` ou `transform` (o `bpb-portal` do bpb desfoca o cabeçalho fixo), o `nav` vira o bloco de referência do `fixed` e o painel, a sobreposição `::after` e o botão fechar ficam presos nos ~78 px do cabeçalho. Desde a 4.4.9 o tema zera esses efeitos no `nav` enquanto o painel está aberto; o `bpb-portal-4.css` também só desfoca com o menu fechado. Testar o menu de celular com `scratchpad/menu2.js` (CDP) a cada mudança de cabeçalho.
- **Cor de link sem peso de classe.** A regra global de link (`style.scss` → `style.css`/`style.min.css`) é `:where(:not(.has-link-color)) a:where(:not(.has-link-color):not(.wp-block-button__link))`, peso (0,0,1). Até a 4.4.5 os dois `:not()` ficavam fora do `:where()` (peso 0,2,1, herdado do 2.10.0 `develop` do oficial; o 2.8.3 publicado não tem) e ganhavam de qualquer componente que pinta o próprio link com classe: o botão "Abrir ficha completa" da gaveta do TainacanBiblio saía azul sobre azul (1,3:1) no bpb, e os títulos da coluna lateral perdiam o `color: inherit` de item-aside.css. Ao mexer em cor de link, conferir o contraste dos botões que são `<a>` (gaveta do Biblio, botões de bloco).

## Estado

**4.5.0** (17/11/2026): fonte e peso do corpo do texto e dos títulos na seção Tipografia (`body_weight` novo, `--tt2-body-weight`; mais 6 fontes OFL variáveis em `assets/fonts/`, só baixadas quando usadas) e `item_order` (documento/anexos antes ou depois da ficha, por site e por coleção; `tainacan_interface_item_sections_order()` respeita a escolha antiga do 2.x enquanto a nova não for feita; o controle do 2.x saiu do Personalizar).

**4.4.8** (08/11/2026): decisões do coordenador sobre a revisão: busca aberta do site só no título (`search_columns`; restrita a posts/páginas segue completa) e botão "Desfazer" da importação de ajustes (aviso após importar; um registro `pending_import` = {from, mods, css} gravado a cada importação, inclusive vazio, esquecido ao dispensar ou ao trocar de tema; desfazer repõe o CSS adicional antes dos theme_mods, para não herdar o `custom_css_post_id`; widgets não entram nem na importação nem no desfazer).

**4.4.7** (08/11/2026): revisão do tema inteiro pela stack de IA (seis papéis + `/code-review high`), relatório em `docs/REVISAO_STACK_4.4.7.md`: o que foi corrigido está no CHANGELOG; o que ficou como decisão ou pendência está na seção "Pendentes" do relatório (busca nativa sobre os itens em acervo grande, desfazer a importação de ajustes, território de plugin para o diretório, licença da fonte TainacanIcons, `searchform.php` com `onsubmit` inline, `inert` em `body > *` na gaveta, readmore fora da chamada do tema).

**4.4.6** (08/11/2026): cor de link sem peso de classe (ver Pegadinhas). Desde 08/11/2026 o ramo também está no ar no **bpb** (site real da Biblioteca Pública de Brasília), fixo no commit ensaiado (`manual:tema` no `projetos.map`; ponto de restauração e volta em `deploy/servidor/bpb/interface-4/` do repo de infra): versão nova só vai ao bpb depois de ensaiada.

**4.0.0** (03/11/2026), ramo `feature/interface-4.0`, no ar no catalogacao. Ver a seção "Versão 4" acima e o CHANGELOG. Pendentes: `es_ES` dos textos novos; levar ao ramo de entrega `tainacan-interface-3.0` do fork, que está na 3.0.3.

**3.0.3** (30/10/2026): compatibilidade com tema filho e blocos (`inc/child-theme.php`; estudo `docs/ESTUDO_LEGADO_BOOTSTRAP_BLOCOS_FILHOS.md`).
- **Tema filho.** Os `_new` do filho voltam a valer. O `style.css` do filho entra depois do pai, sem duplicar e com o CSS do Personalizar depois dele. Há um aviso de cópias de modelos do 2.10.0 com falha.
- **Blocos.** Links sublinhados, tabela com as bordas do editor, botão com cor escolhida respeitado.
- **Testes no catalogacao.** Dois filhos de laboratório ficam instalados e inativos: `tainacan-interface-filho-lab-a` (com `@import`, cópia antiga de `archive-tainacan-collection.php`, `_new` como envelope) e `-b` (enfileira o próprio CSS e o do pai). Para testar sem ativá-los, use um mu-plugin que troca `stylesheet` e `option_stylesheet` por requisição.
- **Pegadinha.** `get_theme_mods()` lê `get_option('stylesheet')`. Trocar só o filtro `stylesheet` lê os ajustes do pai.

**3.0.2** (30/10/2026): painel lateral de filtros com a pele dos cartões (CSS em `item-highlight.css`, só com a ficha em destaque ligada; filtros horizontais do bloco ficam como o Tainacan desenha). Filtros de teste 2249 (Assunto, caixas) e 2251 (Tipo de documento, etiquetas) criados na coleção 202 do catalogacao para cobrir esses tipos.

**3.0.1** (30/10/2026, segurança): a navegação entre itens (`?pos=`) filtra os argumentos que o Tainacan monta (`tainacan-api-prepare-items-args`, só durante a chamada) a menos que o visitante edite a coleção de onde vem a lista ou tenha `manage_tainacan` — sem status, sem autor, metaquery/taxquery só sobre metadados e taxonomias que ele pode ler (seção e pai incluídos); o "mostrar mais" das descrições recorta o DOM em vez de reinterpretar HTML cortado. Testado no catalogacao (SQL de cada desvio) e no Chrome. Abertos no core (no relatório `tainacan-achados-core-tema/TEMA-TAINACAN-INTERFACE.md`): os mesmos filtros na rota REST de itens e a busca textual com login.

**3.0.0** (30/10/2026), ativo no **catalogacao** na pasta `tainacan-interface`, publicado por cima do 2.10.0 oficial como atualização no lugar (o caminho real dos sites): as 12 marcações "Mostrar na caixa lateral" do nome provisório foram migradas sozinhas, e os ajustes curados vieram do `tema-tainacan-2-0` (que fica inativo, na 2.2.1). O Tainacan Acervo segue instalado inativo (o mapa 3D em `/mapa/` era dele). Os três caminhos de inc/upgrade.php foram conferidos no catalogacao: atualização com dados do nome provisório (recursos como estavam), site 2.x oficial limpo (recursos desligados + aviso em pt_BR, a 2ª requisição não refaz) e ativação vindo de derivado (ajustes importados, cópia anterior guardada). Backup do estado anterior: `/home/catalogacao/backups/pre-3.0-20261030-141359/`.

Conferido no ar com Chrome headless (`C:\tmp\chrdump\tt2test.js`, `tt2drawer.js` e `tt2reader.js`): gaveta abre sem sair da lista, anda com seta e botão mantendo o foco, Esc devolve o foco; abre pela célula da Tabela e pelo cartão do Mosaico; vídeo toca na gaveta e pausa ao trocar de item; visualizador abre o PDF pelo botão, desenha as páginas sob demanda, Esc fecha e limpa o `#tt2-file-`.

2.1.0: o documento PDF é desenhado pelo leitor do tema na própria página (filtro `tainacan-get-the-document` troca o iframe do navegador por uma casca `[data-tt2-reader]` que o item-viewer.js monta), como no Memorial COVID Toolkit; se o Toolkit estiver ativo (`MCTK_VERSION`), o tema não mexe. Pegadinhas: o Tainacan toma o primeiro `a[href]` do slide como gatilho do lightbox (por isso o baixar é botão e o link de reserva fica em `<noscript>`); cliques no leitor param na casca para não abrirem o PhotoSwipe; `scrollbar-gutter: stable` mantém fixa a largura de ajuste. Conferido em 1366 e 390 px: 8 páginas sem rolagem lateral, campo de página, zoom sem corte, tela cheia na mesma página e com o mesmo documento PDF.js.

2.2.0: coluna lateral ao lado dos metadados (inc/item-aside.php, ligada por padrão; Personalizar → "Tainacan Interface: coluna lateral do item"). Fios: relacionamentos nos dois sentidos (get_related_items com post_status publish + valores do próprio metadado), termos em comum (o mais raro primeiro; taxonomias escolhidas por "Usar para sugerir itens relacionados" no formulário do metadado, meta tainacan_interface_related) e "Mais desta coleção" só quando não há outro. Só itens publicados de coleções públicas; cache de objeto só para visitante anônimo (quem está logado pode ler seção privada). A seção nativa do fim sai só se a coluna mostrou relacionamentos. O gancho after-metadata fica fora da grade (largura cheia). Pegadinhas: o tema força tamanho de h2/h3 por `body h2:not(...)` — títulos da coluna levam a classe `label`, que a regra exclui; metadado de relacionamento criado por código precisa de `search` = id do título, senão o valor sai vazio na ficha.
Estudo de impacto da troca Interface → 2.0 em docs/ESTUDO_IMPACTO_ATUALIZACAO.md; dele vieram na 2.2.0: importação dos ajustes também de tema filho/Tainacan Acervo, tradução de reserva pelos pacotes do tainacan-interface, Roboto local (fontFace no theme.json) e inc/compat.php (custom_class, tainacan_customizer_gutenberg_colors). Propostos e não feitos: canal de atualização pelo GitHub, rotina de acompanhar o tema oficial, bloco da coluna para item em blocos, es_ES.
Teste no catalogacao: metadado de relacionamento 2227 "Ver também (teste)" na coleção 202 (itens 958 e 974 → 941; 941 → 990). Conferido em 1366/1600/390 px com `C:\tmp\chrdump\tt2aside.js`.

Pendente: teste em navegador real com teclado e leitor de tela; `screenshot.png` já é do design 4 (obras CC0 creditadas no readme), mas traz a barra de rolagem do navegador e o nome do site de laboratório, e o diretório pede captura sem cromo; o achado H1 (rascunho pela navegação) também existe no tainacan-acervo e não foi corrigido lá.

## Comandos

- Verificação: `php .agents/tainacan-stack/bin/tnc-check .` (0 bloqueios, 0 avisos na 2.0.0)
- PHPCS: `vendor/bin/phpcs` (ruleset `phpcs.xml.dist`); nesta máquina o binário está em `plugins/oraculo_tainacan/vendor/bin/phpcs`.
- Sass: `./compile-sass.sh` (o CSS compilado vai versionado).
- Deploy: `bash ~/vps-kvm1-infra-privado/deploy/deploy.sh <pasta-do-repo>` (modo `auto:tema` no `projetos.map`).

## Não regredir

- Nada de servidor externo (fonte, script, CSS).
- Nada de leitura direta de `$_GET`/`$_SERVER` em template: tudo por `inc/request.php`.
- Rota da gaveta: permissão perguntada à coleção e ao item (`can_read()`), item com senha nunca resumido, mesmo 404 para inexistente e proibido, limite por IP **no callback** (o `permission_callback` pode rodar duas vezes por pedido).
- Texto do servidor entra no DOM por `textContent`; valor de metadado vem filtrado por `wp_kses_post()` e é montado num documento inerte (`DOMParser`), nunca por `innerHTML` na página.

<!-- tainacan-stack:inicio -->
## Stack de IA do Tainacan

Este repositório usa a Stack de IA do Tainacan, instalada em `.agents/tainacan-stack/` (versão em `.agents/tainacan-stack/VERSION`). As regras abaixo valem para qualquer agente (Claude, Codex, Copilot, Cursor, Gemini, opencode) e para quem trabalha sem agente. O que é específico deste projeto fica na seção do projeto, fora deste bloco; em conflito, a seção do projeto vale para o projeto e este bloco vale para o resto.

### Como trabalhar

1. **Pensar antes de agir.** Declare as premissas. Se o pedido admite mais de uma leitura, apresente as leituras e pergunte; não escolha em silêncio. Leia o arquivo inteiro antes de editar.
2. **O mínimo que resolve.** Nada além do pedido, nenhuma abstração para uso único, nenhuma opção que ninguém pediu.
3. **Mudança cirúrgica.** Toque só no que o pedido exige e siga o estilo do código ao redor. Problema fora do escopo vira observação na resposta, não edição.
4. **Critério verificável.** Antes da primeira edição, escreva o plano como checklist `- [ ] passo → verificação: <comando ou observação>`. Na resposta final, repita o checklist marcando `[x]` só o que foi verificado, com o comando e o resultado; o que depende de teste manual fica `[ ]` com o motivo. "Parece funcionar" não conta.
5. **Subagente simples, modelo mais barato.** Busca, localização de arquivos, leitura em lote e conferência mecânica rodam num modelo menor (no Claude Code, `model: haiku` no subagente ou no parâmetro `model` da chamada; nas outras ferramentas, o modelo leve equivalente). Revisão de segurança e de correção, migração de dados e decisões de arquitetura ficam no modelo principal.
6. **Tarefa nova, contexto novo.** Ao trocar de tarefa, comece uma sessão limpa (`/clear` no Claude Code, conversa nova nas outras ferramentas) e retome pelo arquivo de contexto do repositório, não pelo histórico da tarefa anterior.

### Roteamento

Escolha a skill pela demanda (todas em `.agents/skills/`; no Claude Code aparecem como `tainacan-stack:<nome>`):

| Demanda | Skill |
|---|---|
| Implementar ou corrigir código em plugin ou no núcleo | `tnc-guardrails` |
| Criar plugin novo | `tnc-novo-plugin` |
| Tipo de metadado ou filtro, gancho de admin, busca facetada, página do item, importador, exportador, exposer, processo em segundo plano, bloco | `tnc-estender-tainacan` |
| Tela no admin do Tainacan, campo no formulário de item/coleção/metadado, drawer ou assistente ao lado do item | `tnc-ui-admin` |
| Tema do Tainacan: template, modo de visualização, recurso que liga e desliga, fork do tema oficial | `tnc-tema` |
| Passar no Plugin Check, publicar no WordPress.org, limpar achados do PHPCS | `tnc-wordpress-org` |
| Rota REST própria ou consumo da REST do Tainacan | `tnc-rest-api` |
| Lentidão, acervo grande, consultas, índice de busca | `tnc-desempenho` |
| Bug sem causa conhecida, comportamento estranho | `tnc-diagnostico` |
| Revisão de diff, PR, branch ou módulo | `tnc-revisao-tecnica` |
| Plugins que se afetam, CSS ou JS vazando, ganchos disputados | `tnc-convivencia` |
| Importação ou migração de acervo | `tnc-migracao-acervo` |
| Publicar versão | `tnc-release` |
| Contribuir com o núcleo (repositório tainacan/tainacan) | `tnc-contribuir-nucleo` |
| Problema novo que vale lembrar | `tnc-registrar-licao` |
| Rodar as verificações antes de entregar | `tnc-validacao` |

A skill principal conduz; `tnc-validacao` roda sempre que houver código alterado.

### Verificações obrigatórias

- Antes de declarar pronto: `php .agents/tainacan-stack/bin/tnc-check --changed`. Bloqueio impede a entrega; aviso se corrige ou se justifica na própria linha com `// tnc-check-ignore TNC-XNN: motivo` (sem motivo a supressão não vale).
- `php -l` em todo PHP alterado (o `tnc-check` já roda) e os testes do projeto quando existirem.
- Nunca use `--no-verify`, `git push --force` em branch compartilhada nem desligue o gancho para passar. Commits em Conventional Commits (`fix(area): ...`), em inglês. Nunca `push` em branch de repositório upstream sem confirmação explícita.
- Explicação de qualquer achado: `php .agents/tainacan-stack/bin/tnc-check --explain TNC-XNN`.

### Regras do WordPress que valem sempre

- Diante de aviso de lint ou segurança: **refatorar > usar a API canônica > suprimir** com justificativa técnica (`phpcs:ignore <sniff> -- por que a alternativa não serve`). "Is safe" não é justificativa. (TNC-B07, B05)
- Escape **na linha do echo**, no contexto certo (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, `esc_html__`); `printf` com formato e argumentos escapados. Escape feito antes não conta. (TNC-S05)
- Entrada: `sanitize_*( wp_unslash( $_POST[...] ) )`; nonce e capability em todo handler que muta. (TNC-S02, S03)
- `gmdate()`/`wp_date()`, `wp_json_encode()`, `wp_remote_get()`, `wp_delete_file()`, `wp_rand()`, `WP_Filesystem` no lugar das funções nativas; `error_log()` só sob `WP_DEBUG`; biblioteca em `assets/vendor/`, nunca CDN. (TNC-W13, B04, W14)
- i18n: domínio único do plugin, `translators:` e `%1$s` em toda string com marcador, `wp_set_script_translations()` para JS. (TNC-W03, W15, R07)
- Revisão sempre em **duas camadas**: checklist dos padrões + `tnc-check` + `phpcs` e, no Claude Code, `/code-review` (`medium` uma vez por entrega; `high` em segurança, migração, transação ou release; `xhigh` só a pedido) para bugs de correção. Em conflito, valem estes padrões. Detalhe: `referencias/padroes-wordpress-org.md`.

### Regras do Tainacan que valem sempre

- Repositórios são singletons: `\Tainacan\Repositories\Items::get_instance()` ou `tainacan_items()`; nunca `new`. Entidade publicada e todo `Item_Metadata_Entity` precisam de `validate()` antes do `insert()`, senão há exceção; registre `get_errors()`, nunca engula. (TNC-T05)
- Opções de tipo de metadado estão em `_option_<chave>` e em `metadata_type_options`; `get_post_meta( $id, 'taxonomy_id' )` devolve vazio. Taxonomia do metadado: `_option_taxonomy_id`, slug `tnc_tax_<id>`. Valor do item: `meta_key` = ID numérico do metadado. (TNC-T04)
- Callback de `tainacan_register_admin_hook()` devolve string (`ob_start` + `return ob_get_clean()`), é registrado dentro da ação `tainacan-register-admin-hooks` e não leva `<script>`: o HTML entra por `v-html`. (TNC-T01, T02, T03)
- Busca facetada, depois do item: registre `tainacan_faceted_search_item_after` e `tainacan_faceted_search_collection_item_after`. (TNC-T06)
- REST do Tainacan: `taxquery` descarta `include_children` e usa `metadatum` no lugar de `field`; `metaquery` usa `metadatumtype`, não `type`. Em listagens use `fetch_only` e `fetch_only_meta`. (TNC-T07)
- Itens são `tnc_col_<id>_item` com `exclude_from_search`: `'post_type' => 'any'` não os encontra. Filtro genérico `tainacan-item-metadata-get-value-as-html` não roda para tipos com HTML próprio; use as variantes `--type-<tipo>`. (TNC-T09)
- Acervos têm dezenas de milhares de itens: nada de consulta sem limite, consulta dentro de laço nem `meta_query` com `LIKE` sem medir. (TNC-P01, P02, P03)
- Nunca altere `meta_value` em massa sem filtrar por `meta_key`: PHP serializado guarda o tamanho das strings e corrompe. Para troca de texto use `wp search-replace --dry-run`. Faça backup antes de SQL destrutivo. (TNC-P04)
- Um plugin não desfaz o que outro registrou (`remove_all_filters`, desregistro de assets alheios, `$wp_filter`), não usa CSS global e prefixa funções, classes, opções, handles e rotas. (TNC-C01, C02, W10)
- Rotas REST sempre com `permission_callback`; escrita nunca aberta sem justificativa; handlers AJAX e admin-post com nonce e capability; SQL com `$wpdb->prepare()`. (TNC-S01 a S08)

### Dados, segurança e ambientes

- Achado de segurança só é confirmado com o caminho do dado demonstrado, da entrada ao uso, citando arquivo e linha de cada passo. Sem isso é hipótese e se reporta como tal.
- Nunca grave senha, token ou chave no código, no histórico do git ou em mensagem. Encontrou um? Avise antes de qualquer outra ação.
- Dados pessoais (LGPD) não vão para log, para a resposta de API pública nem para o prompt de serviço externo.
- Em migração de acervo, o valor da origem chega intacto: nenhuma normalização silenciosa de caixa, espaço ou duplicado. Reconcilie as contagens origem × banco × índice separadamente.
- Teste em ambiente de testes com dados reais antes de produção. Mudança em produção só com pedido explícito.

### Conhecimento e lições

- Ordem de consulta: a seção do projeto neste arquivo; `.agents/tainacan-stack/licoes/` (uma lição por regra); `.agents/tainacan-stack/referencias/` (por tema; consulte sob demanda, não carregue tudo); o código do núcleo.
- Problema novo, com causa entendida e que pode se repetir: use `tnc-registrar-licao`. Se dá para detectar no código, a lição vira regra do `tnc-check`, e a mesma verificação roda nos outros plugins: `tnc-check --workspace=<pasta> --rules=<ID>`.
- Lição que só vale para este projeto vai para a seção do projeto deste arquivo, na mesma entrega da correção.

### Escrita

Português brasileiro claro e direto, voz ativa, frases completas, sem travessão nem floreio. Código, comandos, identificadores e nomes de tecnologias ficam literais. Resposta final: o que mudou, o checklist verificado, o que ficou pendente e por quê.
<!-- tainacan-stack:fim -->
