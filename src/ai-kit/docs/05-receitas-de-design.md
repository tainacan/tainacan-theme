# 5. Receitas de design

Um site de acervo bonito põe **o acervo em primeiro plano**: imagens grandes dos
itens, busca à mão, caminhos claros para explorar. O tema já cuida das páginas do
Tainacan (listas, item, coleções). As receitas abaixo são para as páginas feitas
com blocos: início, apresentação de coleção, exposição, sobre, visita.

## Princípios

1. **Comece pelas opções.** Parta de um preset (`museum`, `archive`, `nature`,
   `observatory`, `contrast`: copie os valores dele, ver `03-opcoes-sem-codigo.md`)
   e ajuste a cor principal e a de destaque. Fontes:
   uma serifada nos títulos (`playfair`, `lora`, `source-serif`) com uma sem
   serifa na interface (`inter`, `roboto`) dá ar de museu; duas sem serifa
   (`poppins` + `inter`) dão ar de projeto digital.
2. **Ritmo de seções.** Alterne o fundo das seções: `site-page` (o fundo da
   página), `site-surface` (a superfície das fichas), e no máximo uma faixa escura
   em `site-brand` (Capa sem imagem; a Capa da abertura, com imagem e véu, não
   conta). Três seções seguidas com o mesmo fundo parecem uma só.
3. **Uma estrela por seção.** Cada seção tem um título (`h2`), no máximo uma
   linha de apoio (o "sobretítulo", classe `<slug>-eyebrow`) e uma ação (botão).
   Um só `h1` por página: o da abertura.
4. **Imagens do acervo, sempre.** Use as miniaturas das coleções e os itens (pelo
   Query Loop). Nada de banco de imagens genérico nem de imagens de fora.
   Proporções fixas (`aspectRatio` 1, 4/3, 16/9) deixam as grades alinhadas.
5. **Espaço é desenho.** Seções com `padding-block: clamp(2.5rem, 6vw, 5rem)`;
   conteúdo de leitura com no máximo ~70 caracteres por linha (`contentSize`
   720–820px); grades com 1100–1200px.
6. **Menos JavaScript.** Query Loop e blocos do servidor em vez de carrosséis com
   script. Animação só com `transition` curta e respeitando
   `prefers-reduced-motion` (os estilos do tema já fazem isso).

## Acessibilidade (não opcional)

- Contraste AA: texto normal 4,5:1, texto grande (24px ou 19px negrito) e ícones
  3:1. Sobre imagem, use a Capa com véu (`is-style-tt2-veil`, opacidade ≥ 60%).
- Títulos em ordem (`h1` → `h2` → `h3`), sem pular nível para mudar o tamanho:
  tamanho se muda com `fontSize`.
- Links com texto que diga aonde levam ("Ver todos os itens da coleção", não
  "Clique aqui").
- `alt` descritivo nas imagens de conteúdo; `alt=""` nas decorativas (fundo de
  Capa).
- Alvo de toque com pelo menos 44px de altura em botões e links soltos.
- O bloco de busca do tema já traz rótulo para leitores de tela.

## Receita 1: página inicial

Modelo de página **Landing**. Sequência (padrões do `starter-child`):

| # | Seção | Blocos | Fundo |
|---|---|---|---|
| 1 | Abertura com busca | `core/cover` (`is-style-tt2-veil`, imagem de uma coleção) + sobretítulo + `h1` + parágrafo + `tainacan-interface/search` (`size: large`) | imagem + véu |
| 2 | Novidades | `core/query` dos itens (`is-style-tt2-carousel is-style-tt2-cards`) + botão para a coleção | `site-page` |
| 3 | Coleções | `core/query` de `tainacan-collection` em grade de 3 (`is-style-tt2-cards`) | `site-surface` |
| 4 | Sobre | `core/group` `is-style-tt2-panel` com `core/columns` (imagem + texto + botão) | `site-page` |
| 5 | Visita | `core/group` `is-style-tt2-band` com 3 colunas (endereço, horário, contato) | `site-page` |

Variações:
- **Uma coleção só:** troque a seção 3 por "Explore por tema": 3–4 botões
  (`core/buttons`) para as páginas dos termos mais usados (`site-context.json` →
  `tainacan.taxonomies[].top_terms[]`, com `name` e `url`), ou um Query Loop dos
  itens filtrado por um desses termos.
- **Muitas coleções:** seção 3 com `perPage` 6 e um botão "Todas as coleções"
  (`site-context.json` → `tainacan.collections_list_url`).
- **Destaque curatorial:** um `core/media-text` com um item escolhido (imagem
  grande, título, 2 linhas e "Ver o item").

## Receita 2: apresentação de uma coleção

Uma página (não a lista do Tainacan, que o tema já faz) que conta a coleção e leva
à lista:

1. `core/cover` com a miniatura da coleção, `h1` com o nome e uma linha.
2. Texto de apresentação (`contentSize` 760px): 2–4 parágrafos.
3. `core/query` dos itens da coleção filtrado por um termo forte (`taxQuery`), em
   carrossel.
4. `tainacan-interface/search` com `collectionId` da coleção.
5. Botões: "Ver todos os itens" (URL da coleção) e "Como citar" (página própria).

## Receita 3: exposição virtual

Narrativa em blocos, uma "sala" por tema. Modelo de página **Landing**.

1. Abertura: `core/cover` com a imagem de uma coleção, véu da marca, sobretítulo
   "Exposição virtual" e o `h1` com o título.
2. Apresentação: grupo com `contentSize` 760px, `h2` e 2–3 parágrafos.
3. Para cada sala: `core/group` `alignfull` (alternando `site-page` e
   `site-surface`), sobretítulo "Sala 1", `h2` com o nome, uma frase, um Query Loop
   dos itens filtrado pelo termo da sala (`taxQuery`, `perPage` 3, grade de 3,
   `is-style-tt2-cards`: as imagens dos itens vêm sozinhas) e um botão
   "Ver no acervo" para a página do termo (`top_terms[].url`).
4. Entre salas, se quiser, uma citação (`core/quote`).
5. Fecho: `tainacan-interface/search` e botões para a coleção e para o início.

O contexto não traz itens nem endereços de imagens de itens, por isso as salas
usam o Query Loop. Para um item escolhido a dedo (`core/media-text` com a imagem
dele), peça o endereço da imagem a quem administra o site, ou deixe a sala no
Query Loop e anote em "a criar no painel".

## Receita 4: sobre a instituição

1. `h1` com o nome e um parágrafo de missão em `fontSize` large.
2. `core/columns` 2 colunas: história / equipe ou parceiros.
3. `core/group` `is-style-tt2-panel` com números (itens, coleções, anos), escritos
   à mão a partir de `site-context.json` (`items` por coleção). Diga que precisam
   ser revistos depois.
4. Chamada para a busca ou para as coleções.

## Receita 5: visita e contato

`core/columns` com endereço, horários e contato; `core/details` para perguntas
frequentes (acessibilidade, ingressos, uso de imagens); botões de telefone
(`tel:`) e e-mail (`mailto:`). Mapa só com imagem estática e link para o
serviço de mapas: nada de `iframe` de fora sem pedido explícito.

## Juntar blocos diferentes sem quebrar

- Cada seção é um grupo `alignfull` + `constrained`. Nada de `alignwide` dentro.
- Um Query Loop por tipo de conteúdo; `queryId` único em cada um.
- No máximo um carrossel por página. Mais de um cansa e pesa.
- Cores só da paleta (`site-*` ou as do tema) ou dos tokens. Nenhum hex solto
  no markup, salvo numa decisão consciente registrada na entrega.
- Não misture o bloco "Barra de busca do Tainacan" com o do tema na mesma página:
  um campo de busca por página basta.
- Padrões reutilizáveis (`patterns/`) para tudo o que aparece em mais de uma
  página. A página fica feita de `core/pattern`, e uma mudança no padrão vale
  para todas.

## Desempenho

- Imagens pela biblioteca de mídia (o WordPress gera tamanhos e `srcset`), com
  `sizeSlug` adequado (`medium` em cards pequenos, `large` em destaques).
- Capa com imagem de no máximo ~1600px de largura.
- Nenhuma fonte, script ou estilo de fora.
- Query Loop com `perPage` pequeno (6–12) na página inicial.
