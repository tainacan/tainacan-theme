# AGENTS.md: Kit for AI of Tainacan Interface

> **English summary.** You are building or changing a website made with the
> WordPress theme **Tainacan Interface** (2.17 or later) and the **Tainacan**
> plugin (digital collections). Never edit the parent theme: all work goes into a
> **child theme**. Follow the order of decisions below (options → tokens →
> patterns → hooks → template copies), write only blocks that work when written by
> hand, and check your work with the checklist at the end. The detailed guides
> are in `docs/` (Brazilian Portuguese) and the exact data in `reference/`
> (JSON). This file applies to every AI: Claude, ChatGPT, Gemini, Codex, Cursor,
> Copilot or any other.

Este arquivo é a instrução principal do kit. Vale para qualquer IA que receba a
tarefa de criar ou mudar um site feito com o tema **Tainacan Interface** e o
plugin **Tainacan**. `CLAUDE.md` e `GEMINI.md` apontam para cá.

## 1. O que você vai entregar

Um **tema filho** do Tainacan Interface, completo e instalável:

```
<slug-do-site>/
  style.css        cabeçalho do tema (Template: tainacan-interface) + CSS com os tokens
  functions.php    valores iniciais das opções, categoria de padrões, ganchos
  theme.json       versão 2; ajustes de blocos (sem paleta própria, ver 04-blocos)
  patterns/*.php   seções do site em blocos (padrões de bloco)
  languages/       traduções do tema filho (opcional)
  screenshot.png   1200×900 (opcional)
```

Mais, quando o pedido incluir páginas:

- o **markup de blocos** de cada página (o que vai no editor, em "Editor de
  código"), ou páginas montadas só com referências a padrões
  (`<!-- wp:pattern {"slug":"<slug>/home-hero"} /-->`);
- a **lista de opções** a ajustar em Aparência → Personalizar (chave e valor, de
  `reference/options.json`);
- os **passos de instalação** para quem vai aplicar (ver seção 6).

Parta sempre do modelo em `starter-child/`. Ele já segue todas as regras abaixo e
foi validado no editor de blocos. Os marcadores `{{THEME_NAME}}`, `{{THEME_SLUG}}`,
`{{PARENT_FOLDER}}`, `{{PARENT_VERSION}}`, `{{SITE_NAME}}` e `{{DATE}}` precisam
ser trocados em todos os arquivos, e o prefixo `starter_child_` das funções de
`functions.php` vira o prefixo do filho (o slug com `_`). Pela tela do kit no painel,
ou com `wp tainacan-interface kit child`, a troca é feita sozinha.

## 2. Ordem de leitura

1. `site-context.json`, se veio junto: o site real. Traz as coleções (com ids,
   tipos de post e metadados públicos), as taxonomias, as páginas, os menus, as
   opções já mudadas, os plugins e as versões. **Use os ids e nomes daqui; nunca
   invente.** Sem ele, pergunte ao usuário o que faltar ou use marcadores
   explícitos (`{{COLLECTION_ID}}`). Se o pedido descrever um site diferente do
   contexto (o contexto pode ser de um site de teste), use os ids do contexto,
   que são os que existem, e registre o conflito na entrega.
2. `docs/01-como-o-tema-funciona.md`: o que o tema já faz sozinho.
3. `docs/02-tema-filho.md`: o padrão do tema filho.
4. `docs/04-blocos.md`: quais blocos você pode escrever à mão e quais não.
5. `docs/05-receitas-de-design.md`: como compor páginas bonitas e acessíveis.
6. Sob demanda: `docs/03-opcoes-sem-codigo.md`, `reference/options.json`,
   `reference/css-tokens.md`, `reference/hooks.md`,
   `reference/tainacan-blocks.md`, `reference/tainacan-blocks.json`.
7. Antes de entregar: `docs/06-validacao.md`.

## 3. A ordem das decisões

Para cada mudança pedida, use o **primeiro** degrau que resolve:

1. **Opção do tema.** São 147 opções (cores, fontes, cabeçalho, cards, página do
   item, banner…). Estão em `reference/options.json`, com o valor padrão e os
   valores aceitos. No tema filho, mude o *valor inicial* pelo filtro
   `tainacan_interface_settings_schema`, como no `functions.php` do modelo. Para
   um site já no ar, a mudança vai para Personalizar ou para o WP-CLI.
   Algumas opções valem por coleção (`"collection": true`).
2. **Tokens de CSS.** As regras do `style.css` do filho usam as variáveis do tema
   (`var(--tt2-brand)`, `var(--tt2-radius)`…, lista em `reference/css-tokens.md`).
   Assim Personalizar continua mandando nas cores e nas formas. Cada regra fica
   restrita a uma classe do filho (`.<slug>-section …`).
3. **Padrões de bloco.** Seções do site em `patterns/*.php`, com blocos do núcleo,
   o Query Loop do Tainacan e os blocos do tema (`docs/04-blocos.md`).
4. **Ganchos.** Ações e filtros do tema (`reference/hooks.md`) e do WordPress, em
   `functions.php`.
5. **Cópia de template do tema pai.** Só como último recurso, com um comentário
   no topo do arquivo dizendo por que nenhum gancho servia. Cópia antiga perde
   cada correção do pai, e o tema avisa no painel quando reconhece uma cópia
   defeituosa.

## 4. Regras que não se negociam

**Estrutura**
- `style.css` com `Template: tainacan-interface` (o nome da **pasta** do tema pai,
  que vem em `site-context.json` → `versions.parent_theme` / o marcador
  `{{PARENT_FOLDER}}`) e `Text Domain: <slug-do-filho>`.
- Não enfileire o `style.css` do pai nem o do filho: o tema pai carrega os dois,
  na ordem certa.
- Nunca altere arquivos do tema pai nem do plugin Tainacan.
- `theme.json` na versão 2. **Não declare `settings.color.palette`**: uma paleta
  no `theme.json` substitui a paleta inteira do tema, e somem as cores
  `site-brand`, `site-accent`, `site-text`, `site-surface` e `site-page`, que
  acompanham Personalizar. Para uma cor fixa a mais (um segundo dourado, por
  exemplo), declare uma variável própria no CSS do filho
  (`body.tt2-modern { --<slug>-gold: #c9a227; }`) e use-a só no CSS, nunca como
  hex solto no markup.
- Slugs de cor, de tamanho e de padrão sem dígitos colados em letras
  (`site-brand`, nunca `tt2-brand`): o WordPress reescreve `tt2` como `tt-2` e a
  classe não bate mais.

**Código**
- Textos da interface sempre com o text domain do filho e escapados na saída:
  `esc_html_e( 'About us', '<slug>' )`, `esc_url()`, `esc_attr()`. Filho que
  vai ser distribuído: textos em inglês e tradução em `languages/<locale>.po/.mo`.
  Filho de um site só, num idioma só: pode escrever direto no idioma do site
  (`esc_html_e( 'Sobre o museu', '<slug>' )`), sem `.po`.
- Funções com o prefixo do filho (`<prefixo>_`), sem colisão com o pai.
- Nada de CDN: fontes, scripts e imagens ficam no tema ou na biblioteca de mídia.
  O tema já traz nove fontes locais (opções `font_ui`, `font_heading` e
  `font_reading`).
- Nada de `$_GET` ou `$_POST` sem `wp_unslash()` + sanitização, e nada que mude
  dados sem nonce e checagem de permissão.
- Nada de SQL direto. Para itens e coleções, use o Query Loop (blocos) ou a API
  do Tainacan.
- Não chame `tainacan_interface_is_modern()`: foi descontinuada e gera aviso. O
  tema tem um só desenho.

**Blocos**
- Escreva à mão só os blocos que o servidor desenha a partir dos atributos:
  núcleo, `core/query` com as variações do Tainacan, `tainacan-interface/search`
  e os blocos de item do Tainacan marcados `"made_by_editor": false` em
  `reference/tainacan-blocks.json`.
- Os 10 blocos Tainacan cujo HTML é montado pelo editor (carrosséis, listas
  dinâmicas, busca facetada, facetas, barra de busca, lista de coleções e de
  termos, formulário de submissão) **não** se escrevem à mão. Indique a quem vai
  aplicar que os insira no editor. Ou escreva só o comentário do bloco com os
  atributos e diga que a página precisa ser aberta no editor e salva: o tema
  refaz esses blocos ao abrir.
- Não ponha blocos `alignwide` dentro de grupos. Seções de página são grupos
  `alignfull` com layout `constrained` (contentSize 1100–1200px), e o conteúdo
  de dentro fica sem alinhamento próprio.
- Imagens: só endereços do próprio site (miniaturas das coleções em
  `site-context.json`, ou a biblioteca de mídia), sempre com `alt` (vazio só se
  for decorativa). Para mostrar itens com imagem, use o Query Loop (filtrado por
  termo, se for o caso): as imagens vêm sozinhas. Nunca um `core/image` com
  endereço inventado ou marcador.
- Seção escura (fundo na cor principal): use `core/cover` sem imagem, com
  `overlayColor` `site-brand` e `dimRatio` 100. A Capa já põe o texto e os
  títulos na cor clara de contraste; um grupo com `backgroundColor` não faz isso.

**Páginas**
- O que tiver PHP (ler a coleção principal, traduzir textos) fica nos padrões do
  filho. A página entregue para colar no Editor de código não pode ter PHP:
  prefira montá-la só com referências aos padrões
  (`<!-- wp:pattern {"slug":"<slug>/home-hero"} /-->`), ou escreva o HTML já
  resolvido, sem `<?php`.
- Página cujo primeiro bloco é uma Capa com o `h1` (a inicial, uma exposição)
  usa o modelo **Landing**; as outras, o modelo padrão, que já mostra o título.

**Escopo**
- Não reestilize as listas do Tainacan nem a página do item com seletores
  amplos. Elas têm dezenas de opções próprias: use-as.
- Não invente coleções, metadados, páginas nem menus. O que não existir vai para
  a lista "a criar no painel" da entrega.
- Opções já salvas no site (`site-context.json` → `theme.changed_options`) vencem
  o valor inicial do filho. Liste para mudar em Personalizar só as que mudam a
  identidade pedida (cores, fontes, cabeçalho, rodapé, faixa); as outras foram
  escolha de quem cuida do site e ficam.

## 5. Duas formas de trabalhar

**A. Assistente de conversa** (claude.ai, ChatGPT, Gemini), sem acesso ao site:
recebeu o ZIP do kit, com `site-context.json` dentro. Entregue o tema filho
completo em um ZIP (se puder gerar arquivos) ou como arquivos separados, cada um
com o caminho como título, mais o markup das páginas e os passos de instalação.
Rode você mesmo a checagem da seção 7, item por item, e diga o resultado.

**B. Agente com terminal** (Claude Code, Codex, Gemini CLI, Cursor, Copilot), com
WP-CLI no servidor do site:

```bash
wp tainacan-interface kit context              # o site em JSON (o mesmo site-context.json)
wp tainacan-interface kit child museu --name="Museu da Cidade"   # cria o filho a partir do modelo
wp tainacan-interface kit check museu           # confere o filho (erros e avisos)
wp tainacan-interface kit blocks 123            # confere o markup de blocos de uma página (id ou arquivo)
wp tainacan-interface kit reference ./ref       # regera options.json desta versão
wp theme activate museu                         # ativa (as opções do pai são copiadas para o filho)
```

O agente também pode abrir a página no editor com um navegador sem tela e ver se
algum bloco ficou inválido (`tools/check-editor.js`). Antes de ativar num site em
produção, pergunte ao usuário. A tela Aparência → Personalizar mostra o filho
antes de ativar, com "Pré-visualizar".

## 6. Instalação (para incluir na entrega)

1. Aparência → Temas → Adicionar novo → Enviar tema → o ZIP do filho → Instalar.
2. Aparência → Kit para IA → "Conferir um tema filho": escolha o filho e veja se
   não há erros.
3. Pré-visualize em Personalizar; depois ative. As opções do tema pai são
   copiadas para o filho na ativação, e dá para desfazer.
4. Páginas: crie a página, escolha o modelo **Landing** (sem banner nem título)
   para a página inicial, cole o markup no Editor de código ou insira o padrão
   pela aba Padrões. Em Configurações → Leitura, defina a página inicial.
5. Abra cada página no editor uma vez e salve, se ela tiver blocos do Tainacan
   montados pelo editor.

## 7. Checagem antes de entregar

- [ ] `style.css`: `Theme Name`, `Template: <pasta do pai>`, `Text Domain: <slug>`,
      `Version`, `Requires at least: 6.5`.
- [ ] Nenhum `{{MARCADOR}}` sobrando em nenhum arquivo.
- [ ] `php -l` sem erro em cada `.php` (ou releitura atenta, se não puder rodar).
- [ ] Nenhum `wp_enqueue_style` do pai; nenhum endereço de CDN; nenhum
      `tainacan_interface_is_modern`.
- [ ] `theme.json` válido, `"version": 2`, sem `settings.color.palette`.
- [ ] Cada padrão com `Title`, `Slug: <slug>/<nome>`, `Categories`, e só blocos
      permitidos; cada texto com `esc_html_e( '…', '<slug>' )`.
- [ ] Query Loops com `postType` existente (de `site-context.json`) e o
      `namespace` da variação do Tainacan.
- [ ] Contraste AA (4,5:1 no texto, 3:1 em texto grande e ícones) nas cores
      escolhidas; um só `h1` por página.
- [ ] Layout pensado para 390 px de largura: nada de largura fixa, colunas que
      empilham, imagens com `aspectRatio`.
- [ ] Entrega com: arquivos, páginas, opções a ajustar, "a criar no painel",
      instalação, e o que você não pôde verificar.

Os detalhes de cada item estão em `docs/06-validacao.md`.
