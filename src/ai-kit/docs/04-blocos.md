# 4. Blocos: o que escrever à mão e o que não

O editor de blocos guarda cada bloco como um comentário com atributos mais o HTML
que o bloco salvou. Ao abrir a página, o editor refaz esse HTML a partir dos
atributos e compara. Se não bater, mostra "Este bloco contém conteúdo inesperado
ou inválido". Por isso uma IA só deve escrever à mão blocos cujo HTML ela consegue
reproduzir exatamente, ou blocos que o servidor desenha sozinho.

## Grupo 1: escreva à vontade (núcleo do WordPress)

`core/group`, `core/columns` + `core/column`, `core/cover`, `core/heading`,
`core/paragraph`, `core/list` + `core/list-item`, `core/buttons` + `core/button`,
`core/image`, `core/separator`, `core/spacer`, `core/media-text`,
`core/quote`, `core/details`, `core/pattern`.

Copie o HTML exatamente como o editor salva: as classes na ordem do editor
(`wp-block-group alignfull has-…-background-color has-background`), os estilos
inline que os atributos geram (`style="min-height:560px"`). Os padrões de
`starter-child/patterns/` foram validados no editor e servem de molde.

Blocos dinâmicos do núcleo, desenhados pelo servidor (autocontidos, `/-->`):
`core/query` e os seus filhos `core/post-template`, `core/post-title`,
`core/post-featured-image`, `core/post-excerpt`, `core/post-date`,
`core/query-pagination`; `core/site-title`, `core/site-logo`,
`core/navigation` (prefira o menu do tema), `core/latest-posts`.

## Grupo 2: o Query Loop do Tainacan

Itens e coleções do Tainacan são tipos de post. O Query Loop do núcleo, com as
variações que o Tainacan registra, é **a forma mais robusta** de mostrá-los numa
página: desenhado pelo servidor, sem JavaScript, com o estilo do tema.

| O que mostrar | `postType` | `namespace` |
|---|---|---|
| Itens de uma coleção | `tnc_col_<id>_item` | `tainacan-items-tnc_col_<id>_item` |
| Coleções | `tainacan-collection` | `tainacan-collection` |
| Taxonomias | `tainacan-taxonomy` | `tainacan-taxonomy` |

Os ids e os tipos de post certos estão em `site-context.json` →
`tainacan.collections[]` (`id`, `post_type`).

```html
<!-- wp:query {"queryId":21,"query":{"perPage":10,"pages":0,"offset":0,"postType":"tnc_col_12_item","order":"desc","orderBy":"date","inherit":false},"namespace":"tainacan-items-tnc_col_12_item"} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"is-style-tt2-carousel is-style-tt2-cards","layout":{"type":"grid","columnCount":4}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"1","sizeSlug":"medium"} /-->

<!-- wp:post-title {"isLink":true,"level":3,"fontSize":"regular"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->
```

- `queryId` diferente para cada Query Loop da mesma página.
- `orderBy`: `date`, `title`, `modified`, `rand` (o sorteio muda a cada visita e
  atrapalha o cache).
- A miniatura do item é a imagem que o Tainacan gera a partir do documento. Item
  sem miniatura mostra só o título.
- Para filtrar por termo (ex.: só "Fotografias"), acrescente ao `query`
  `"taxQuery":{"tnc_tax_<id>":[<id-do-termo>]}`. O `slug` da taxonomia e os ids
  dos termos mais usados estão em `site-context.json` → `tainacan.taxonomies[]`
  (`slug`, `top_terms`). O filtro só vale para taxonomia pública; as do
  Tainacan são públicas por padrão. Numa taxonomia não pública, o filtro é
  ignorado e o Query Loop lista tudo.
- `core/post-excerpt` funciona na coleção (descrição). Metadados do item não
  aparecem no Query Loop: para eles, a página do item.

## Grupo 3: blocos e estilos do tema

**Buscar no acervo** (`tainacan-interface/search`), desenhado pelo servidor:

```html
<!-- wp:tainacan-interface/search {"size":"large","align":"center"} /-->
<!-- wp:tainacan-interface/search {"collectionId":12,"placeholder":"Search the photographs","buttonText":"Search"} /-->
```

| Atributo | Valor |
|---|---|
| `collectionId` | número; sem ele, busca em todo o acervo |
| `label` | rótulo para leitores de tela (padrão: "Search the collection") |
| `placeholder`, `buttonText` | textos do campo e do botão |
| `size` | `regular` ou `large` |
| `align` | `wide`, `full`, `center` |

Também aceita cor, espaçamento, tamanho de fonte e arredondamento da borda.

**Estilos de bloco** (`className`):

| Bloco | Classe | Efeito |
|---|---|---|
| `core/post-template` | `is-style-tt2-carousel` | Fila horizontal com rolagem e encaixe (4 por tela; 72% da largura no celular) |
| `core/post-template` | `is-style-tt2-cards` | Cada entrada numa caixa com o canto, a sombra e a superfície do tema |
| `core/group` | `is-style-tt2-panel` | Caixa de destaque com superfície, canto e sombra |
| `core/group` | `is-style-tt2-band` | A faixa decorativa do tema em cima do grupo |
| `core/cover` | `is-style-tt2-veil` | A cor principal sobre a imagem, com o texto na cor de contraste |

Carrossel e Cards podem ir juntos (`"className":"is-style-tt2-carousel is-style-tt2-cards"`).

**Paleta que acompanha Personalizar**: `site-brand`, `site-accent`, `site-text`,
`site-surface`, `site-page`.

```html
<!-- wp:group {"align":"full","backgroundColor":"site-surface","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull has-site-surface-background-color has-background">…</div>
<!-- /wp:group -->
```

## Grupo 4: blocos do Tainacan desenhados pelo servidor

Podem ser escritos à mão, mas dependem de contexto:

| Bloco | Uso |
|---|---|
| `tainacan/item-metadata-sections`, `item-metadata-section`, `item-metadata`, `item-metadatum`, `metadata-section-name`, `metadata-section-description`, `geocoordinate-item-metadatum` | Metadados de **um item**: precisam do item (na página do item, ou com `itemId` e `collectionId` nos atributos) |
| `tainacan/item-gallery` | Documento e anexos de um item |
| `tainacan/related-items-list` | Itens relacionados de um item |
| `tainacan/items-gallery` | Galeria de itens. Só com os atributos não mostrou nada nos testes: prefira o Query Loop |

O markup exato de cada um está em `reference/tainacan-blocks.json`
(`"made_by_editor": false`).

## Grupo 5: blocos do Tainacan montados pelo editor (não escreva o HTML)

`tainacan/carousel-items-list`, `carousel-collections-list`, `carousel-terms-list`,
`collections-list`, `dynamic-items-list`, `faceted-search`, `facets-list`,
`item-submission-form`, `search-bar`, `terms-list`.

O HTML que eles salvam é montado pelo script do editor com dados do site
(endereço da API, endereços de busca, ids). Escrito à mão, o bloco fica inválido
ou não mostra nada. Na entrega, faça assim:

1. **Melhor:** troque por um equivalente que se escreve à mão. Carrossel de itens
   = Query Loop com `is-style-tt2-carousel`; lista de coleções = Query Loop de
   `tainacan-collection`; barra de busca = `tainacan-interface/search`.
2. Se o bloco do Tainacan for indispensável (busca facetada numa página,
   formulário de submissão), diga a quem vai aplicar que o **insira pelo editor**,
   no lugar indicado.
3. Ou escreva **só o comentário** com os atributos (exemplos em
   `reference/tainacan-blocks.json`) e avise: "abra a página no editor e salve".
   Ao abrir, o tema refaz esses blocos sozinho e mostra um aviso. A
   `wp tainacan-interface kit blocks` e a tela de conferência acusam esses blocos
   como "sem o HTML do editor".

### Aparência desses blocos (tema 2.19)

Carrosséis, lista de itens (grade, lista, mosaico), listas de coleções e de termos e
lista de facetas ganham a aparência do tema. Não escreva CSS para isso; escolha assim:

- o padrão do site fica no Personalizar, em "Blocos do Tainacan nas páginas"
  (opções `tblocks_*` de `reference/options.json`);
- um bloco escolhe outra aparência pelo estilo (`className`): `is-style-tt2-card`,
  `is-style-tt2-clean`, `is-style-tt2-overlay`, `is-style-tt2-original` (a do plugin);
  nas facetas, `is-style-tt2-chips`, `is-style-tt2-cloud`, `is-style-tt2-original`;
- um detalhe de um bloco, com classes no mesmo `className`: `tt2-ratio-1-1|4-3|3-2|16-9|3-4|auto`,
  `tt2-fit-cover|contain`, `tt2-lines-1|2|3`, `tt2-hover-zoom|lift|none`,
  `tt2-radius-theme|square|round`, `tt2-arrows-round|accent|plain`;
- num tema filho, as variáveis `--tt2-tb-*` em `.tt2-tb` e o filtro
  `tainacan_interface_tblocks_classes`.

Exemplo no comentário do bloco: `"className":"is-style-tt2-overlay tt2-ratio-4-3"`.

## Alinhamento e largura

O tema pai dá margens negativas a `.alignwide` (herança do desenho antigo). Dentro
de um grupo, isso faz o bloco vazar para os lados, principalmente no celular.
Portanto:

- uma seção de página é um `core/group` com `"align":"full"` e
  `"layout":{"type":"constrained","contentSize":"1200px"}`;
- os blocos dentro dela **não** levam `align` (ficam na largura do conteúdo);
- o espaço lateral no celular vem do CSS do filho
  (`padding-inline: clamp(1rem, 4vw, 2rem)` na classe da seção).

Numa página feita só de seções, use o modelo de página **Landing**: sem banner,
sem título, e as seções `alignfull` ocupam a largura toda.

## Conferir

- `wp tainacan-interface kit blocks <id-da-página|arquivo.html>`: blocos que não
  existem no site, blocos do Tainacan sem o HTML do editor, Query Loop de tipo de
  post que não existe, endereços de CDN.
- Abrir no editor (ou `tools/check-editor.js`): nenhum bloco inválido, nenhum
  "Tentar recuperar o bloco".
- Ver a página em 1366 px e em 390 px.
