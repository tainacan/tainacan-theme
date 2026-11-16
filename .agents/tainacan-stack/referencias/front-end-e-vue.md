# Front-end e Vue

Pegadinhas de JavaScript, Vue e CSS em plugins que estendem o Tainacan: o formulário do item e a busca facetada (apps Vue do núcleo), CSS que vaza entre tema, admin e plugins, camadas e eventos, carga de scripts, impressão, PDF e tabelas.

Sumário:
- [1. O Vue do núcleo: o que um plugin precisa saber](#1-o-vue-do-núcleo-o-que-um-plugin-precisa-saber)
- [2. Formulário do item no admin (SPA)](#2-formulário-do-item-no-admin-spa)
- [3. Busca facetada e modos de visualização](#3-busca-facetada-e-modos-de-visualização)
- [4. CSS que vaza: Bulma, wp-admin, tema e o próprio plugin](#4-css-que-vaza-bulma-wp-admin-tema-e-o-próprio-plugin)
- [5. Camadas, posicionamento e overflow](#5-camadas-posicionamento-e-overflow)
- [6. Eventos e DOM](#6-eventos-e-dom)
- [7. Carga de scripts e dados entre PHP e JS](#7-carga-de-scripts-e-dados-entre-php-e-js)
- [8. Impressão e PDF](#8-impressão-e-pdf)
- [9. Tabelas com DataTables](#9-tabelas-com-datatables)
- [10. Telas e formulários próprios](#10-telas-e-formulários-próprios)

## Comece por aqui

- L-FRO-01: o formulário do item é SPA; contexto pelo evento `tainacan_item_edition_item_loaded`, com fallback pela URL.
- L-FRO-06: `:key="index"` faz o Vue reaproveitar o cartão entre páginas.
- L-FRO-07: na busca facetada, observe o pai; o marcador `[data-module="faceted-search"]` é trocado na montagem.
- L-FRO-08: HTML de gancho entra por `v-html` e a altura do cartão é medida antes do conteúdo chegar.
- L-FRO-09: filtros JS da busca facetada são lidos uma vez no boot.
- L-FRO-13: tema claro fixo em painel embutido; cores do site, não do sistema.
- L-FRO-26: ordem de z-index com o lightbox do Tainacan.
- L-FRO-39: ordem de carga: script de que um inline depende vai antes dele.
- L-FRO-49 e L-FRO-50: impressão: paginação e folha montada fora do tema.

---

## 1. O Vue do núcleo: o que um plugin precisa saber

Fatos conferidos no código do núcleo (caminhos relativos a `src/`; linha da 1.4.1 salvo indicação).

- **Versão do Vue.** Admin e busca facetada são Vue 3 nas duas versões examinadas. **Δ** [1.4.1]: `vue ^3.5.44`, `vuex ^4.1.0`, `vue-router ^5.4.0`, `buefy ^3.1.0`, `bulma ^1.0.4` (`repo:package.json`). [1.0.3]: bundles com as strings `"3.5.25"` e `"3.5.27"` (`assets/js/4367.*.js`), ou seja, Vue 3.5.x por inferência; versão do Buefy não conferida. Não presuma Vue 2 no núcleo. Plugins podem ter apps próprios em Vue 2 (ver L-FRO-04): APIs como `this.$set` e `Vue.extend` só existem lá.
- **Build.** Webpack 5 com `vue-loader ^17` (`repo:package.json`); saída em `src/assets/js/`. O `build.sh` apaga `.vue`, `.js`, `.scss` e `.sass` de `views/` no pacote: a instalação não tem fontes Vue, então não há como "remendar o componente" do núcleo; estenda pelos pontos abaixo (`repo:build.sh:1-130`).
- **Componentes e plugins Vue de terceiros.** `window.tainacan_extra_components` e `window.tainacan_extra_plugins` (`views/admin/js/admin-main.js:105-107`, `:218-220`), alimentados pelos ganchos PHP `tainacan-register-vuejs-component` e `tainacan-register-vuejs-plugin` (`views/admin/classes/hooks/class-tainacan-component-hooks.php:27`, `:60`; `views/admin/classes/hooks/class-tainacan-plugin-hooks.php:24`, `:46`).
- **Ganchos de formulário do admin.** O HTML devolvido por `tainacan_register_admin_hook` entra por `v-html` (`views/admin/components/edition/item-edition-form.vue:648-652`; mixin `views/admin/js/mixins.js:85-96`): `<script>` inline não executa. A cada atualização o núcleo dispara no `document` o evento `tainacan-{entityName}-hook-reload` (`views/admin/js/mixins.js:98-106`); ao salvar, `fillExtraFormData()` lê os campos de `#form-{entity}-{posição}` (`:108-139`). Detalhes em `ganchos-e-extensao.md`.
- **Evento de item carregado.** `wp.hooks.doAction('tainacan_item_edition_item_loaded', collection, item)`, com cópias JSON da coleção (ou `false`) e do item (`views/admin/components/edition/item-edition-form.vue:1461-1469`, de novo em `:1995-2000`). O próprio comentário do núcleo avisa que a coleção pode não estar carregada.
- **Busca facetada.** App em `views/gutenberg-blocks/blocks/faceted-search/theme.js` e `theme.vue`; modos em `theme-search/components/view-mode-*.vue`. Ganchos por item são filtros `wp.hooks` injetados por `v-html` (`view-modes-mixin.js:56-80`; `view-mode-cards.vue:37-39`, `:118-120`), com nomes do "after" trocados (ver `ganchos-e-extensao.md`). Filtros de área como `tainacan_faceted_search_items_list_before` são lidos por `applyFilters` uma vez (`theme.vue:1411`). Evento de carga: `tainacan-items-list-is-loading-items`, com `detail: { isLoading, collectionId, termId, taxonomy }` (`theme.vue:1230-1236`).
- **Chave dos cartões.** `:key="index"` em cartões, lista, masonry, mosaico e fichas (`view-mode-cards.vue:25`, `view-mode-list.vue:26`, `view-mode-masonry.vue:31`, `view-mode-mosaic.vue:28`, `view-mode-records.vue:27`); no mapa a chave é `item.id` (`view-mode-map.vue:15`).
- **Galeria do item.** `views/gutenberg-blocks/blocks/item-gallery/theme.js` prende `stopPropagation()` nos botões `.tainacan-item-file-download` do carrossel (`:448-452`).

---

## 2. Formulário do item no admin (SPA)

### L-FRO-01 · O formulário do item é SPA: religar a cada troca e saber em que item está
**Sintoma:** o painel funciona na primeira carga e para ao navegar entre itens; na tela "Criar item" (`#/collections/C/items/new`) o painel não sabe qual é o item; marcas nos campos caem no elemento errado; às vezes o script perde o evento de carga; em item com vídeo a região do gancho leva dezenas de segundos para existir.

**Causa:** o painel entra no DOM depois do `load` e é recriado pelo Vue a cada troca de rota; na criação o item é auto-draft e o hash não traz id; o id `tainacan-item-metadatum_id-<id>` fica num `<div>` interno (rótulo e ajuda estão no `.field` que o envolve); `tainacan_item_edition_item_loaded` pode disparar antes de o script registrar o ouvinte.

**Correção:** `MutationObserver` que religa o painel sempre que ele reaparece, sem desconectar; escutar `wp.hooks` `tainacan_item_edition_item_loaded` (dispara para item existente e auto-draft) e usar a URL como segunda fonte, lendo coleção e item separadamente (aceitar `/items/new`); agir sobre `closest('.field')` e achar seções por `.metadata-section-slug-<slug>`; esperar a região até cerca de 45 s; também ouvir o evento `tainacan-{entityName}-hook-reload` (seção 1).

**Detecção:** manual: JS de painel que só faz binding em `DOMContentLoaded`/`load`; regex de hash que exige `/items/(\d+)`; `getElementById('tainacan-item-metadatum_id-` seguido de mexida no rótulo sem `closest(`.

**Vista em:** plugin de dados ligados, plugin de biblioteca, agregador arquivístico.

### L-FRO-02 · Não mutar inputs do Buefy achando campos pelo rótulo
**Sintoma:** barra de sugestões preenchia campos de forma frágil: dependia do idioma do rótulo e o Vue não percebia o valor (sumia ao salvar).

**Causa:** manipulação direta do DOM do Vue do núcleo, localizando inputs pelo texto do `label` e escrevendo `.value`.

**Correção:** gravar pelo servidor (REST própria chamando a API de metadados do Tainacan) e recarregar o formulário; zero escrita no DOM do núcleo.

**Detecção:** manual: JS que procura `label` por `textContent` e escreve `.value` dentro de `.tainacan-form`/`item-edition-form`.

**Vista em:** plugin de biblioteca.

### L-FRO-03 · Sugestão automática deve ler a ficha aberta, não o item salvo
**Sintoma:** botões de sugestão (classificação, resumo) inúteis num rascunho preenchido e ainda não gravado.

**Causa:** liam o item salvo pela REST.

**Correção:** ler o estado da ficha aberta e devolver a sugestão aos campos da ficha; gravar no item só no "preencher".

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-FRO-04 · Vue (Options API): propriedade atribuída em método precisa existir em `data()`
**Sintoma:** a API respondia 200 em 0,4 s e a tela ficava girando, com console limpo.

**Causa:** `consistency`/`loadingConsistency` não declarados em `data()`, fora da reatividade; template iterava um computed inexistente (`v-for` sobre `undefined`).

**Correção:** declarar todas as propriedades em `data()`. Em Vue 2 há também `this.$set`; em Vue 3 (o do núcleo, seção 1) `$set` não existe e a declaração em `data()` é o único caminho na Options API.

**Detecção:** manual: `this.X = ...` em `methods` de SFC sem `X` em `data()`/`computed`/`props`.

**Vista em:** plugin de biblioteca (app Vue 2 próprio).

### L-FRO-05 · Contrato UI × backend desencontrado falha em silêncio no `|| 0`
**Sintoma:** cartão "Em atraso: 0" com 13 atrasos listados logo acima; "[object Object]"; relatórios vazios; filtros ignorados; recomendação mostrando "-".

**Causa:** a tela lia chaves que o endpoint nunca produziu (`due_today_count`), esperava arrays planos e recebia objetos aninhados, enviava `start_date` quando o PHP esperava `date_from`, ou guardava o envelope `{success,data}`.

**Correção:** chaves planas e aliases no backend, desembrulhar no front num lugar só; teste de contrato entre as duas pontas.

**Detecção:** manual: chaves lidas como `response.data.X || 0` que não aparecem em nenhum `'X' =>` do PHP do endpoint.

**Vista em:** plugin de biblioteca.

Ver também: `ganchos-e-extensao.md` (gancho de admin com `<script>` inline não executa; callback deve devolver string).

---

## 3. Busca facetada e modos de visualização

### L-FRO-06 · `:key="index"` faz o Vue reaproveitar o elemento do item
**Sintoma:** ao trocar de página, item novo herdava (ou deixava de receber) a decoração injetada, como a linha "Encontrado em" ou um selo do item anterior.

**Causa:** os modos de visualização usam `:key="index"` (seção 1): o Vue reaproveita o nó DOM pelo índice e só troca o conteúdo.

**Correção:** decoração pós-render carimba o id do item no elemento (`data-<prefixo>-stamp="<id>"`) e refaz quando o id do hospedeiro muda; nunca marcar só "já processado"; teste jsdom cobrindo a troca de página.

**Detecção:** manual: decoração DOM que marca o elemento como processado sem guardar o id do item.

**Vista em:** plugin de busca segmentada, vários projetos que decoram resultados.

### L-FRO-07 · Observar o pai da busca facetada, com intervalo máximo
**Sintoma:** botão injetado sumia ao trocar visualização ou largura; selos nunca apareciam nos resultados.

**Causa:** o app Vue redesenha a barra `.search-control`; depois de montado, o app vira irmão de `[data-module="faceted-search"]` (o marcador é trocado por `#tainacan-items-page`); varredura por "silêncio" (debounce com `clearTimeout` a cada mutação) nunca roda numa busca que muda sem parar.

**Correção:** `MutationObserver` no elemento-pai (ou no `body`) com `requestAnimationFrame` recolocando o elemento; buscar a barra a partir do pai; intervalo máximo (throttle) em vez de debounce; queda para posição `inline` após cerca de 8 s; gatilho também no evento `tainacan-items-list-is-loading-items` com `isLoading: false`. Ancorar elementos sobrepostos na imagem do resultado (funciona em tabela, cartões, masonry e fichas).

**Detecção:** manual: `MutationObserver` observando o próprio `[data-module="faceted-search"]`.

**Vista em:** plugin de impressão de itens, plugin de dados ligados.

### L-FRO-08 · HTML do gancho entra por `v-html`; a altura é medida antes do conteúdo
**Sintoma:** "Encontrado em" cortado na borda do cartão.

**Causa:** os modos de visualização medem a altura ao desenhar e o cartão tem `overflow:hidden`; o conteúdo chegava depois, pela API.

**Correção:** reservar a altura no HTML estático devolvido pelo gancho; recorte de exatamente duas linhas; preencher depois sem mudar a altura.

**Detecção:** manual. (TNC-W09 lembra que o HTML de `v-html` precisa vir sanitizado.)

**Vista em:** plugin de busca segmentada.

### L-FRO-09 · Filtros JS da busca facetada são lidos uma vez no boot
**Sintoma:** painel registrado por `wp.hooks.addFilter('tainacan_faceted_search_items_list_before', ...)` não aparecia.

**Causa:** o app aplica esses filtros uma vez, ao montar (`theme.vue:1411`); script enfileirado no rodapé registra tarde demais.

**Correção:** carregar o script no `<head>` (`$in_footer = false`) com dependência de `wp-hooks`; para saber a coleção, usar o `collectionId` do evento `tainacan-items-list-is-loading-items` (`theme.vue:1230-1236`).

**Detecção:** manual: `wp.hooks.addFilter('tainacan_faceted_search_*')` em script enfileirado no rodapé.

**Vista em:** plugin de busca segmentada.

### L-FRO-10 · Gancho por item injeta dentro do link do cartão
**Sintoma:** botão dentro do `<a>` do cartão (clique abre o item); selos com dados de outro item após paginar.

**Causa:** `tainacan_faceted_search_item_before` injeta HTML dentro do link; o Vue reaproveita elementos entre páginas (L-FRO-06).

**Correção:** para elemento interativo, inserir como irmão do `<a>` no elemento com `data-tainacan-item-id`; gatilho em `tainacan-items-list-is-loading-items` (`isLoading: false`) mais `MutationObserver` restrito à raiz da busca; o selo guarda `data-item-id` e é refeito quando o id do hospedeiro muda.

**Detecção:** manual: botão ou link devolvido por filtro `tainacan_faceted_search_item_before`.

**Vista em:** busca semântica. Ver também: `convivencia-entre-plugins.md` (selo de um plugin cobrindo o de outro).

### L-FRO-11 · Fichas e masonry são posicionados por JS a partir do tamanho renderizado
**Sintoma:** cartões sobrepostos quando a "pele" do plugin mudava tamanhos.

**Causa:** o leiaute é calculado medindo os elementos depois de desenhados.

**Correção:** a pele altera só cor, borda, raio, sombra e família de fonte, nunca `width`, `height`, `padding` ou `font-size`; CSS no `<head>` para a medição já incluí-lo.

**Detecção:** manual: CSS de plugin que mexe em tamanhos dentro de seletores dos modos masonry/fichas.

**Vista em:** página pública do item por tipo de mídia.

### L-FRO-12 · Bloco Busca facetada: cores inline e atributo sem `data-`
**Sintoma:** estilos do plugin não venciam as cores do bloco; modos de visualização fixados pelo bloco ignoravam o plugin.

**Causa:** as cores vêm como variáveis inline no invólucro (`--tainacan-secondary` etc.); o bloco usa o atributo `enabled-view-modes`, sem `data-`.

**Correção:** só `!important` na folha de estilo vence variável inline; ler e alterar o atributo pelo nome real.

**Detecção:** manual.

**Vista em:** página pública do item por tipo de mídia, plugins que estilizam a busca facetada.

Ver também: `ganchos-e-extensao.md` (nomes trocados do gancho "after", TNC-T06); `convivencia-entre-plugins.md` (tema que desloca a busca facetada e estica inputs).

---

## 4. CSS que vaza: Bulma, wp-admin, tema e o próprio plugin

### L-FRO-13 · Painel embutido: tema claro fixo e cores do site, não do sistema
**Sintoma:** painel escuro destoando dentro da lista do Tainacan; rótulos invisíveis (texto claro sobre `Canvas` branco).

**Causa:** o painel seguia `prefers-color-scheme` do sistema e desenhava sobre cores de sistema (`Canvas`, `CanvasText`), enquanto a página hospedeira é sempre clara.

**Correção:** painel embutido em página do Tainacan com tema claro fixo; copiar as cores do `body` e declarar `color-scheme: light` no contêiner; modo escuro só em app de página inteira que controla o próprio fundo.

**Detecção:** manual: `prefers-color-scheme: dark` ou cores de sistema em CSS de painel injetado.

**Vista em:** plugin de biblioteca, busca semântica.

### L-FRO-14 · Nome de classe CSS genérico colide com componente existente
**Sintoma:** botões segmentados de várias telas ficaram achatados depois de uma nova barra de etapas.

**Causa:** a barra nova reutilizou `.tp-seg`, nome que já existia para outro componente. Entre plugins sem prefixo, o mesmo acontece em escala maior.

**Correção:** antes de criar classe, procurar o nome (`grep -rn "\.prefixo-nome"`); prefixo do plugin e nome específico (`.tp-bar-seg`).

**Detecção:** manual: mesmo seletor de classe em dois arquivos CSS do plugin com propósitos diferentes. TNC-C02 cobre o caso extremo (seletor de elemento solto).

**Vista em:** plugin de fluxos de gestão de acervo.

### L-FRO-15 · Contraste: tokens do `wp-components` e paleta herdada do tema
**Sintoma:** botões terciários do `wp-components` a 4,04:1 e links a 4,31:1, abaixo do WCAG AA, quando o app passou a rodar dentro do tema.

**Causa:** token `--wp-admin-theme-color` (`#007cba`) sobre fundo claro; `<a>` sem cor própria herda a paleta do tema, que ninguém mede.

**Correção:** redefinir no contêiner do app `--wp-admin-theme-color`, `--wp-components-color-accent` e as variantes `-darker-10/20`; paleta medida no app; usar variáveis do tema (`var(--theme-palette-*)`, largura de contêiner publicada) só em página pública, depois de medir o contraste.

**Detecção:** manual: medir contraste; `<a>` sem regra de cor dentro do contêiner do plugin.

**Vista em:** plugin de pesquisas com portal no tema.

### L-FRO-16 · Cache do navegador e captura sem estilo
**Sintoma:** captura de tela logo após o botão aparecer saía "sem estilo"; CSS novo não aplicava com a mesma versão do asset.

**Causa:** CSS ainda não aplicado no momento da captura; cache do navegador servindo a folha antiga.

**Correção:** esperar cerca de 3 s ou conferir `getComputedStyle` antes de capturar; perfil limpo; versão do asset muda a cada release (`filemtime` em desenvolvimento ou constante de versão).

**Detecção:** manual: `wp_enqueue_style` com versão fixa que não muda entre releases.

**Vista em:** plugin de impressão de itens, plugin de fluxos de gestão de acervo.

### L-FRO-17 · Bulma devolve `display` a elementos com `hidden`
**Sintoma:** colunas que deviam abrir só depois de uma escolha nasciam abertas e vazias.

**Causa:** o Bulma redefine `display` e anula o atributo `hidden`; o CSS scoped das páginas Vue não alcança HTML injetado por outro módulo; o Bulma zera estilos de campos.

**Correção:** regra própria `[hidden] { display: none !important }` no escopo do app; módulo injetado desenha e estiliza os próprios controles.

**Detecção:** manual: atributo `hidden` em HTML injetado num app com Bulma sem a regra acima.

**Vista em:** plugin de biblioteca.

### L-FRO-18 · `!important` antigo na folha base vence o redesenho
**Sintoma:** interruptor novo saiu como caixa verde.

**Causa:** `!important` antigo (defesa contra o tema sobrescrever `button`) na mesma classe, em outra folha do plugin.

**Correção:** procurar `!important` na folha base antes de redesenhar; sobrescrita escopada em vez de mais `!important`.

**Detecção:** manual: mesma classe com `!important` em duas folhas do plugin.

**Vista em:** plugin de dados ligados.

### L-FRO-19 · Checkbox esticada por regra genérica de campo
**Sintoma:** o gestor achava que a chave "não persistia"; ações eram recusadas.

**Causa:** `input { width:100% }` numa coluna transformou o checkbox numa barra irreconhecível; o interruptor estava desligado.

**Correção:** regra de campo exclui `[type=checkbox]`/`[type=radio]`; classe própria para checkbox; selo de estado para campo secreto que volta vazio do servidor.

**Detecção:** TNC-C02 (seletor de elemento global); manual para `... input { width: 100% }` sem exclusão.

**Vista em:** plugin de dados ligados.

### L-FRO-20 · CSS escorado em classe de `body` morre em silêncio
**Sintoma:** estilos e correções de contraste somem ao integrar ao tema.

**Causa:** o `body` deixou de ser do plugin; seletor sem alvo não gera erro.

**Correção:** escopar no contêiner do plugin; ao remover uma casca própria, procurar todo seletor que dependia dela.

**Detecção:** manual: seletores `body.<classe-do-plugin>` em plugin que renderiza dentro do tema.

**Vista em:** plugin de pesquisas com portal no tema.

### L-FRO-21 · Regra genérica com especificidade alta anula o leiaute de um componente
**Sintoma:** `display:flex` do leitor de PDF ignorado.

**Causa:** regra genérica do palco (canvas) com especificidade maior que a do componente.

**Correção:** envolver regras genéricas em `:where()` (especificidade zero) para que qualquer regra de componente vença.

**Detecção:** manual.

**Vista em:** página pública do item por tipo de mídia.

### L-FRO-22 · Títulos de blocos de módulos diferentes dimensionados pelo tema
**Sintoma:** num cartão de 300px, um título saía com 22,8px e o vizinho com 16px.

**Causa:** cada bloco usava um nível de heading e o tema dimensiona `<h3>` pelo corpo da página.

**Correção:** tamanho de título único definido pela casca (contêiner), independente do nível semântico.

**Detecção:** manual.

**Vista em:** plugin de biblioteca.

### L-FRO-23 · Bloco injetado depois do template vive fora do contêiner do tema
**Sintoma:** seção sangrava de ponta a ponta da janela; miniaturas gigantes.

**Causa:** o gancho dispara depois do invólucro de conteúdo do tema.

**Correção:** o bloco se contém sozinho: `max-width: var(--theme-content-width, <padrão>)`, `margin-inline:auto`, padding próprio, teto na grade; `line-clamp` em títulos longos para alinhar cartões.

**Detecção:** manual.

**Vista em:** plugin de dados ligados.

### L-FRO-24 · `@media` mede a janela; dentro do tema use `@container`
**Sintoma:** leiaute em duas colunas espremido dentro do tema.

**Causa:** a coluna de conteúdo é bem menor que a janela.

**Correção:** `container-type: inline-size` no contêiner e `@container` nas regras que dependem da largura do componente.

**Detecção:** manual (heurística: plugin embutido em tema usando só `@media`).

**Vista em:** plugin de pesquisas com portal no tema, plugin de biblioteca.

### L-FRO-25 · Emoji sem glifo na fonte do plugin
**Sintoma:** ícone de cadeado (emoji) não aparecia.

**Causa:** a fonte do plugin não tem o glifo e o fallback do sistema varia.

**Correção:** ícone por máscara SVG em CSS (`mask-image`) com `currentColor`.

**Detecção:** manual: emoji literal em templates, rótulos ou `content:` de CSS.

**Vista em:** plugin de fluxos de gestão de acervo.

Ver também: `convivencia-entre-plugins.md` (`.wp-core-ui .button` × Bulma; CSS do admin do Tainacan sobre painéis injetados; CSS do tema vencendo o do plugin).

---

## 5. Camadas, posicionamento e overflow

### L-FRO-26 · Ordem de z-index com o lightbox do Tainacan
**Sintoma:** a janela de condições abria por baixo do visualizador de imagens.

**Causa:** modal em `z-index: 10000`, abaixo do lightbox do Tainacan (100000) e do overlay (100010); o botão que abre o modal mora dentro do overlay.

**Correção:** ordem fixa lightbox (100000) < overlay (100010) < modal (100020), documentada nos arquivos de estilo; teste com `getComputedStyle` sobre os CSS reais. No admin, mover modais para filho de `<body>` por JS e usar z-index alto para escapar dos contextos de empilhamento.

**Detecção:** manual: z-index de modal menor que 100000 em plugin que convive com a galeria do Tainacan.

**Vista em:** kit de memorial virtual, plugin de CRM.

### L-FRO-27 · `position: fixed` falha dentro de ancestral com `transform`
**Sintoma:** barra de navegação inferior sumia no celular, dentro do tema.

**Causa:** ancestral com `transform`, `filter` ou `contain` torna `fixed` relativo a ele.

**Correção:** mover a barra para filho direto de `<body>`; marcar a classe que esconde a navegação alternativa só depois de montar; checagem pós-pintura com `getBoundingClientRect()` que desfaz se a barra não estiver visível.

**Detecção:** manual.

**Vista em:** plugin de biblioteca (portal público).

### L-FRO-28 · Menu suspenso cortado dentro de contêiner com rolagem lateral
**Sintoma:** menu "..." cortado na lista e barra de rolagem vertical extra no quadro.

**Causa:** contêiner com `overflow-x: auto` recorta filhos com `position: absolute`.

**Correção:** o menu abre com `position: fixed` calculada a partir do botão (abre para cima se não houver espaço; fecha ao rolar a página).

**Detecção:** manual: menu `position: absolute` dentro de contêiner com `overflow-x: auto`.

**Vista em:** plugin de fluxos de gestão de acervo.

### L-FRO-29 · `overflow-x: auto` torna o eixo Y rolável também
**Sintoma:** barra de rolagem vertical ao lado das abas.

**Causa:** com `overflow-x` diferente de `visible`, `overflow-y: visible` é computado como `auto`; o recuo de 2px da aba ativa já gera rolagem.

**Correção:** declarar `overflow-y: hidden` explicitamente ou eliminar o deslocamento.

**Detecção:** manual: regra com `overflow-x: auto|scroll` sem `overflow-y`.

**Vista em:** plugin de biblioteca.

### L-FRO-30 · `<dialog>` dentro da coluna herda as regras da coluna
**Sintoma:** modal com estilos da ficha de metadados.

**Causa:** o `<dialog>` estava dentro da coluna de metadados.

**Correção:** mover para o fim do `<body>`, devolver o foco ao gatilho ao fechar, esconder o gatilho se o navegador não tiver `showModal`.

**Detecção:** manual.

**Vista em:** plugin de dados ligados.

### L-FRO-31 · Componente que mede o contêiner só pode montar visível
**Sintoma:** escala inútil do leitor de PDF.

**Causa:** contêiner com largura zero (overlay ainda oculto) no momento da montagem.

**Correção:** montar depois de o overlay ficar visível; o `resize` global ignora instâncias invisíveis.

**Detecção:** manual.

**Vista em:** kit de memorial virtual.

---

## 6. Eventos e DOM

### L-FRO-32 · Interceptar cliques da galeria do Tainacan na fase de captura
**Sintoma:** a janela de condições nunca aparecia no "Baixar" da galeria com lightbox; o lightbox abria antes do listener.

**Causa:** a galeria prende `click → stopPropagation()` direto nos botões `.tainacan-item-file-download` (`views/gutenberg-blocks/blocks/item-gallery/theme.js:448-452`), e o PhotoSwipe tem listener delegado no invólucro, que roda antes de um listener em `document` na fase de bolha. O Swiper, por sua vez, suprime cliques de arrasto num listener de captura no próprio invólucro, que roda depois do de `document`.

**Correção:** `document.addEventListener('click', fn, true)` sem depender de seletor interno do invólucro; guarda de arrasto (distância `pointerdown → click` maior que 8 px não conta como clique).

**Detecção:** manual: `document.addEventListener('click'` sem `true`/`{capture:true}` para interceptar `.tainacan-item-file-download`.

**Vista em:** kit de memorial virtual.

### L-FRO-33 · Trava de reenvio: capturar antes do Vue e overlay como irmão
**Sintoma:** a proteção contra clique duplo no "Enviar" do formulário público do Tainacan era inócua; inscrições duplicadas (até 12 cópias do anexo).

**Causa:** o overlay inserido dentro do bloco disparava o próprio `MutationObserver` de "concluído" e liberava a trava; o handler Vue do formulário rodava antes da trava.

**Correção:** listener na fase de captura (antes do handler Vue); overlay como irmão do formulário; liberar por sinais independentes (bloco saiu do DOM, link manual após 20 s, teto de 3 min); cooldown no servidor por e-mail (transient) contra corrida entre abas.

**Detecção:** manual: `MutationObserver` observando um nó em que o próprio script faz `appendChild`.

**Vista em:** plataforma de mapeamento comunitário.

### L-FRO-34 · Mover nó preserva listeners; clonar perde
**Sintoma:** (prevenção) player sem comportamento depois de reposicionado.

**Causa:** `cloneNode` não copia listeners ligados por `addEventListener`.

**Correção:** mover a seção existente (`appendChild`/`append`) para o painel.

**Detecção:** manual: `cloneNode(true)` sobre elemento cujo comportamento é ligado por outro script.

**Vista em:** kit de memorial virtual.

### L-FRO-35 · Superfícies de download que nenhum clique alcança
**Sintoma:** botão nativo de download dos players e clique do meio ou "salvar link como" escapavam da interceptação.

**Causa:** controles nativos de `<video>`/`<audio>`; arquivo envolto em `<a href>`.

**Correção:** `controlslist="nodownload noplaybackrate noremoteplayback"` e `disablepictureinpicture`; trocar `href` por `data-*`, com `role`, `tabindex` e `keydown` (Enter e Espaço); reconhecer o controle de download pelo que ele é, não por ter `href`. Isso é consentimento, não controle de acesso.

**Detecção:** manual.

**Vista em:** kit de memorial virtual.

### L-FRO-36 · Campo `required` dentro de aba `hidden` bloqueia o envio
**Sintoma:** "Salvar" não fazia nada (só um aviso no console).

**Causa:** campo `required` em painel de aba oculto; o Chrome aborta o envio sem conseguir focar o campo.

**Correção:** no clique, `form.checkValidity()` e trazer à frente a aba do primeiro campo inválido.

**Detecção:** manual: `required` em controle dentro de elemento `hidden`/painel de aba.

**Vista em:** plugin de eventos e certificados em PDF.

### L-FRO-37 · Botão fora do formulário e guarda de "não salvo"
**Sintoma:** o script não achava o botão de envio; mover campos não armava o aviso de saída.

**Causa:** botão ligado por `form="id"` fica fora do `form.querySelector`; o guarda escuta `input` no formulário e o inspetor está fora dele.

**Correção:** `document.querySelector('[form="id"][type="submit"]')`; disparar um `input` sintético no formulário em `markDirty()`.

**Detecção:** manual: `form.querySelector('[type=submit]')` quando o template tem `<button form=`.

**Vista em:** plugin de eventos e certificados em PDF.

### L-FRO-38 · Autopreenchimento do navegador apagando campos
**Sintoma:** campos apagados depois do autopreenchimento.

**Causa:** o estado do formulário em JS não acompanhava valores preenchidos pelo navegador (que nem sempre disparam `input`).

**Correção:** o projeto corrigiu no teste real sem registrar o detalhe. Caminho seguro: ler os valores do DOM no envio em vez de confiar só no estado em JS, e testar sempre com autopreenchimento real do navegador.

**Detecção:** manual.

**Vista em:** plugin de agendamento.

---

## 7. Carga de scripts e dados entre PHP e JS

### L-FRO-39 · Ordem de carga: o script de que um inline depende vai antes dele
**Sintoma:** scripts inline das views rodavam antes do `admin.js` (objeto global indefinido); um rastreador lia `undefined` na configuração e abortava.

**Causa:** dependência enfileirada no rodapé enquanto o inline está no corpo; configuração impressa em `wp_footer` (prioridade 99) depois do `<script src>`.

**Correção:** mover a lógica para arquivo e anexar o inline ao handle com `wp_add_inline_script('handle', $js, 'before'|'after')`; quando o inline precisa ficar na view, enfileirar a dependência no cabeçalho (`$in_footer = false`) ou envolver o inline em `jQuery(function($){ ... })`.

**Detecção:** manual: `<script>` inline em view que usa um global localizado de handle enfileirado com `$in_footer = true`; `add_action('wp_footer', ...)` que imprime `window.<cfg> =`.

**Vista em:** curadoria de notícias, plugin de CRM, coletor de métricas, vários projetos.

### L-FRO-40 · `wp_localize_script` converte todo escalar em string
**Sintoma:** a pré-visualização nunca funcionou: `cfg.previa === true && typeof cfg.pesquisaId === 'number'` jamais era verdadeiro.

**Causa:** `true` vira `"1"` e `9` vira `"9"`; o verificador de tipos do JS valida um lado, o do PHP valida o outro, e ninguém valida a costura.

**Correção:** `wp_add_inline_script($handle, 'var cfg = ' . wp_json_encode($dados) . ';', 'before')`.

**Detecção:** manual: `wp_localize_script(` com booleanos ou inteiros e JS comparando com `=== true` ou `typeof ... === 'number'`.

**Vista em:** plugin de pesquisas.

### L-FRO-41 · `jQuery` em `noConflict` e estado preso em escopo
**Sintoma:** `$ is not a function`; modal bloqueando a aba por variável inacessível.

**Causa:** `$` não é global no admin do WordPress; variável declarada dentro de um `DOMContentLoaded` é invisível para outro IIFE.

**Correção:** `jQuery(...)` ou `(function($){ ... })(jQuery)`; estado compartilhado em `window` com nome prefixado (ou módulo único).

**Detecção:** manual: `$(` em arquivo de admin sem invólucro que receba `jQuery`.

**Vista em:** agregador de acervos.

### L-FRO-42 · `localStorage` lança exceção em aba privada
**Sintoma:** script inteiro quebra em aba privada ou com cookies bloqueados.

**Causa:** o acessor de `localStorage` lança em vez de devolver `null`.

**Correção:** todo acesso em `try/catch`; controle que só funciona com JS nasce `hidden` e é revelado pelo script.

**Detecção:** manual: `localStorage.` fora de `try`.

**Vista em:** plugin de dados ligados.

### L-FRO-43 · Chave numérica de array PHP vira `int` no JSON
**Sintoma:** ids de metadado chegavam como número; o JS comparava com string e não casava.

**Causa:** o PHP converte a chave `"123"` em inteiro.

**Correção:** emitir `'id' => (string) $d['id']` a partir do dado, nunca da chave do array.

**Detecção:** manual: `foreach ($x as $id => ...)` produzindo campo `id` em JSON consumido com `===` contra string.

**Vista em:** painel público de indicadores.

### L-FRO-44 · `String.replace` com texto do usuário interpreta `$`
**Sintoma:** termos digitados com `$&` ou `$1` eram realçados errado.

**Causa:** o segundo argumento string de `replace()` interpreta padrões `$`.

**Correção:** replacer em função: `s.replace(re, () => texto)`.

**Detecção:** manual: `.replace(<regex>, <variável vinda de input>)` com segundo argumento que não é função.

**Vista em:** plugin de busca segmentada.

### L-FRO-45 · `encodeURIComponent` não codifica `! ' ( ) * ~`
**Sintoma:** filtros com esses caracteres quebravam o estado guardado no fragmento da URL.

**Causa:** comportamento padrão do `encodeURIComponent`, e o formato usava esses caracteres como separadores.

**Correção:** função própria (`encPart()`) que também codifica esses caracteres; o servidor faz `rawurldecode` por parte.

**Detecção:** manual.

**Vista em:** painel público de indicadores.

### L-FRO-46 · Regex com lookbehind derruba o script inteiro em Safari antigo
**Sintoma:** player inteiro não carregava em Safari anterior a 16.4.

**Causa:** `SyntaxError` de parse (lookbehind `(?<=`/`(?<!`) invalida o arquivo todo.

**Correção:** JS público em ES5 estrito (sem lookbehind, sem arrow functions) ou transpilado para os alvos.

**Detecção:** manual: `(?<=` ou `(?<!` em JS de front-end.

**Vista em:** plugin de narrativas.

### L-FRO-47 · `ClipboardItem` com HTML só em contexto seguro
**Sintoma:** "copiar com formatação" caía para texto puro.

**Causa:** a API exige HTTPS (`window.isSecureContext`).

**Correção:** checar `isSecureContext` e cair para texto puro em HTTP.

**Detecção:** manual: `new ClipboardItem(` sem checagem de contexto seguro.

**Vista em:** kit de memorial virtual.

### L-FRO-48 · Web Speech API no Chrome
**Sintoma:** sílabas engolidas, fala morrendo após cerca de 15 s, `onend` que não disparava.

**Causa:** comportamentos do Chromium: `speak()` logo após `cancel()` corta o início; fala longa é interrompida; o coletor de lixo recolhe o utterance.

**Correção:** `speak()` cerca de 120 ms após `cancel()`; `pause()/resume()` a cada 10 s no Chromium de desktop; manter referência ao utterance atual; contador de geração para ignorar callbacks de utterances canceladas; utterances de até 170 caracteres; escolher voz por idioma e qualidade (Natural, Online, Neural) e penalizar vozes "Desktop" (preferir `localService` escolhia a voz legada no Windows).

**Detecção:** manual: `speechSynthesis.speak(` logo após `speechSynthesis.cancel(` sem `setTimeout`.

**Vista em:** plugin de narrativas.

---

## 8. Impressão e PDF

### L-FRO-49 · Paginação de impressão: tabela-moldura, medida em JS e `@page`
**Sintoma:** coluna vazia nas páginas seguintes; meia página em branco; cada bloco numa folha (19 páginas).

**Causa:** grid para imagem lateral; linhas longas com `break-inside: avoid`; medida de estouro com `scrollHeight` (que nunca é menor que `clientHeight`); números de página confiados a margin boxes de `@page`, que só alguns motores desenham.

**Correção:** duas técnicas que funcionaram. (a) Tabela-moldura: cabeçalho em `<thead>` (repete por página), rodapé `position: fixed` mais espaçador no `<tfoot>`; float em vez de grid para a imagem lateral; texto acima de 350 caracteres com `column-span: all`; permitir quebra em citações; `.folha + .folha { break-before: page }` no lote. (b) Paginar em JS medindo `getBoundingClientRect().bottom` do último filho menos o `top` do corpo, com a geometria fora de `@media print` (tela = papel). Numeração por `@page { @bottom-right { content: counter(page) } }` só como extra: Chromium recente desenha margin boxes, outros motores não; não dependa delas para conteúdo essencial.

**Detecção:** manual: `scrollHeight >` comparado com `clientHeight` para detectar estouro; `@top-center`/`counter(page)` usados como única fonte de número de página.

**Vista em:** plugin de impressão de itens, kit de memorial virtual.

### L-FRO-50 · Montar a folha de impressão em vez de CSS de impressão sobre o tema
**Sintoma:** impressões inconsistentes entre temas; esconder por seletor nunca fecha (barras, embeds, faixas de outros plugins).

**Causa:** cada tema e cada plugin acrescenta elementos à página do item.

**Correção:** HTML próprio montado com os dados do Tainacan, fora do tema (`?<param>=<modelo>`), com `noindex`/`X-Robots-Tag`; imagens `loading="eager"` e `print()` só depois de carregarem; no Ctrl+P da página do tema, só uma limpeza de reserva (`body > *:not(.folha)` escondido).

**Detecção:** manual.

**Vista em:** plugin de impressão de itens. Ver também: `convivencia-entre-plugins.md` (suspender filtros de decoração durante a montagem).

### L-FRO-51 · Chrome e Edge não renderizam PDF de `data:` URI em iframe
**Sintoma:** pré-visualização em branco, sem erro no console; o servidor devolve PDF válido.

**Causa:** os navegadores bloqueiam `data:application/pdf;base64` em navegação de iframe.

**Correção:** `URL.createObjectURL(new Blob([bytes], {type:'application/pdf'}))` e `revokeObjectURL` ao fechar.

**Detecção:** manual: `data:application/pdf;base64` atribuído a `src` de `iframe`/`embed`.

**Vista em:** plugin de eventos e certificados em PDF.

### L-FRO-52 · PDF gerado: recurso de fonte com nome diferente do usado no conteúdo
**Sintoma:** acentos trocados em alguns visualizadores (ç→Á, á→·, ã→„, padrão MacRoman); outros disfarçavam o erro.

**Causa:** o dicionário `/Font` usava a chave da família (`/helvetica`) e o conteúdo emitia `/F1 Tf`; visualizadores caíam numa fonte substituta sem `/Encoding`.

**Correção:** o mesmo nome no recurso e no operador `Tf`; declarar `/Encoding /WinAnsiEncoding`; diagnosticar inflando o stream e comparando nomes; testar a coerência recurso × conteúdo, não só a estrutura (xref, trailer); regerar os PDFs antigos.

**Detecção:** manual: em gerador de PDF próprio, chave de `/Font << >>` diferente do nome usado em `Tf`.

**Vista em:** plugin de eventos e certificados em PDF.

---

## 9. Tabelas com DataTables

### L-FRO-53 · DataTables Responsive: extensão carregada, prioridades e célula de controle intocada
**Sintoma:** (a) `responsive: true` sem efeito; (b) colunas de nota "sumiam" e pareciam não calculadas; (c) o clique de expandir deixou de funcionar.

**Causa:** (a) a extensão não estava enfileirada; (b) sem `responsivePriority`, a extensão escolhe sozinha o que colapsar; (c) CSS alterando `display`, dimensões ou `padding` de `td.dtr-control`.

**Correção:** hospedar a extensão localmente e enfileirá-la; prioridade explícita (`<th data-priority>` ou `responsivePriority`); mudar só o glifo por `td.dtr-control:before` (`content`, `color`).

**Detecção:** manual: `responsive: true` sem `dataTables.responsive` enfileirado; init sem prioridades; regra em `td.dtr-control` (não `:before`) com `display`/`width`/`height`/`padding`.

**Vista em:** plataforma de mapeamento comunitário.

### L-FRO-54 · DataTables server-side sobre dados que não estão no post listado
**Sintoma:** ordenação e busca nunca funcionaram; "50 por página" devolvia 25; exportação só da página visível; menos linhas que o pedido.

**Causa:** nome, UF e cidade vivem no item Tainacan vinculado ou na taxonomia, e o `WP_Query` não expressa o JOIN; o handler fazia `min($length, 25)`; a exportação usava `dt.rows()` (no modo server-side só vê o DOM); filtro aplicado depois da paginação.

**Correção:** para centenas de linhas, modo client-side com o conjunto completo em cache transitório; exportar todas as linhas filtradas; filtrar antes de paginar.

**Detecção:** manual: `serverSide: true` com export por `dt.rows()`; `min( $length,` no handler; filtro PHP sobre resultado já paginado.

**Vista em:** plataforma de mapeamento comunitário.

### L-FRO-55 · Dados ortogonais e `defaultContent` no DataTables
**Sintoma:** "02/01/2026" ordenava antes de "15/12/2025"; filtro por coluna com selo nunca casava; tabela morria com "Requested unknown parameter" quando a sessão expirava.

**Causa:** ordenação e busca contra HTML ou data formatada; chave ausente no JSON.

**Correção:** o servidor envia `<campo>_txt` (busca e filtro) e `<campo>_ts`/`_num` (ordenação) usados por `render` ortogonal; `defaultContent: ''` em todas as colunas; i18n inline em vez de buscar o arquivo de idioma em CDN (dependência externa e corrida na inicialização).

**Detecção:** manual: coluna com `render` HTML sem dado ortogonal; colunas sem `defaultContent`; `language: { url: 'https://cdn.datatables.net`.

**Vista em:** plataforma de mapeamento comunitário.

---

## 10. Telas e formulários próprios

### L-FRO-56 · Telas de admin que perdem o contexto (página, filtro, ação)
**Sintoma:** "a tela parece ter fechado sozinha"; formulário de edição aberto vazio; editar alguém da página 2 criava duplicata.

**Causa:** a edição procurava o registro só entre as linhas da página carregada; links, `redirect()` e abas montados sem `paged`, filtros, `acao` e `id`.

**Correção:** buscar por ID no banco com checagem explícita de escopo; reconstruir os argumentos da tela a cada requisição e propagá-los em links (querystring) e em POST (campos ocultos); cabeçalho de abas recebe os mesmos argumentos.

**Detecção:** manual: `foreach ($rows as $row) { if ($row['id'] === $edit_id)`; `add_query_arg` de abas só com `['tab' => ...]`.

**Vista em:** plugin de eventos e certificados em PDF.

### L-FRO-57 · Checkbox `disabled` não vai no POST
**Sintoma:** salvar as configurações desligava de vez um módulo bloqueado por um requisito temporário.

**Causa:** campo desabilitado não é enviado; o sanitizador o lê como desmarcado.

**Correção:** `<input type="hidden">` com o valor gravado ao lado de todo checkbox `disabled` (ou o sanitizador preserva o valor de campos bloqueados).

**Detecção:** manual: `<input type="checkbox"` com `disabled` sem `hidden` de mesmo `name`.

**Vista em:** kit de memorial virtual.

### L-FRO-58 · Visualização em blocos não serve para hierarquia desbalanceada
**Sintoma:** mapa em blocos (icicle) ilegível num fundo arquivístico real.

**Causa:** uma parte concentrava 98% dos nós.

**Correção:** barras ordenadas ("Composição do fundo"); testar visualizações hierárquicas com dados reais, não com amostra equilibrada.

**Detecção:** manual.

**Vista em:** agregador arquivístico.

### L-FRO-59 · Harness de navegador com `file://`
**Sintoma:** URLs quebradas e harness navegando para fora durante o teste.

**Causa:** URL relativa à raiz sob `file://` resolve sob a letra do drive (`/C:/wp-content/...`); clique sintético em `<a href>` navega de verdade.

**Correção:** URLs absolutas no markup do harness; listener do harness que barra navegação de links não interceptados.

**Detecção:** manual.

**Vista em:** kit de memorial virtual.
