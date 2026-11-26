# Tokens de CSS (`--tt2-*`)

O tema escreve estas variáveis em `body.tt2-modern` a partir das opções
(`inc/design.php`). Dentro do editor de blocos, as mesmas variáveis valem em
`.editor-styles-wrapper`. Use-as no CSS do tema filho, sempre com um valor
reserva: `var(--tt2-accent, #187181)`.

## Cores

| Token | Opção | Uso |
|---|---|---|
| `--tt2-brand` | `brand_color` | Cor principal: títulos de destaque, cabeçalho em cor, véu da Capa |
| `--tt2-on-brand` | (calculado) | Texto sobre `--tt2-brand`: `#ffffff` se a cor for escura (luminância 0,2126 R + 0,7152 G + 0,0722 B abaixo de 150, de 0 a 255), senão `#111111` |
| `--tt2-accent` | `accent_color` | Links, botões, filtros, títulos das fichas |
| `--tt2-on-accent` | (calculado) | Texto sobre `--tt2-accent`, pela mesma regra |
| `--tt2-ink` | `heading_color` | Cor dos títulos |
| `--tt2-text` | `text_color` | Texto corrido |
| `--tt2-surface` | `surface_color` | Fundo de fichas, painéis, cards |
| `--tt2-page` | `page_background` | Fundo da página |
| `--tt2-soft` | (derivado) | Fundo suave: 10% do destaque sobre a superfície |
| `--tt2-line` | (derivado) | Linhas e bordas |
| `--tt2-muted` | (derivado) | Texto secundário |
| `--tt2-header-bg`, `--tt2-header-fg` | `header_style` | Cabeçalho do site |
| `--tt2-footer-bg`, `--tt2-footer-fg` | `footer_tone` | Rodapé |
| `--tt2-band` | `band_style`, `band_1`…`band_4` | A faixa decorativa (um `linear-gradient` pronto para `background`). As quatro cores não têm token próprio: para uma delas no CSS do filho, declare uma variável sua (`--<slug>-gold`) |

Os mesmos valores estão na paleta do editor como `site-brand`, `site-accent`,
`site-text`, `site-surface` e `site-page` (classes
`has-site-brand-background-color`, `has-site-accent-color`…).

## Forma

| Token | Opção | Uso |
|---|---|---|
| `--tt2-radius` | `radius` | Arredondamento de cards, painéis, imagens |
| `--tt2-button-radius` | `button_shape` | Arredondamento dos botões |
| `--tt2-shadow` | `shadow` | Sombra de cards e painéis |
| `--tt2-width` | `page_width` | Largura máxima do conteúdo |
| `--tt2-narrow` | `narrow_width` | Largura de leitura (textos) |
| `--tt2-edge` | `edge_spacing` | Espaço nas laterais da página |
| `--tt2-space` | `content_spacing` | Espaço entre as partes de uma página |
| `--tt2-frame` | `frame_size` | Moldura em volta do site |

## Tipografia

| Token | Opção | Uso |
|---|---|---|
| `--tt2-font-ui` | `font_ui` | Interface, menus, botões |
| `--tt2-font-heading` | `font_heading` | Títulos |
| `--tt2-font-reading` | `font_reading` | Textos longos |
| `--tt2-u` / `--tt2-u-mobile` | `font_size` / `font_size_mobile` | Tamanho base do texto |
| `--tt2-h1` … `--tt2-h6` | `h1_size` … `h6_size` | Tamanho dos títulos |
| `--tt2-heading-weight` | `heading_weight` | Peso dos títulos |
| `--tt2-heading-spacing` | `heading_spacing` | Espaçamento entre letras dos títulos |
| `--tt2-leading` | `line_height` | Entrelinha |

## Páginas do Tainacan (para ler, não para redefinir)

`--tt2-list-columns`, `--tt2-list-ratio`, `--tt2-title-lines` (fichas da lista),
`--tt2-d-width` (gaveta de prévia), `--tt2-banner-height`, `--tt2-banner-veil`,
`--tt2-icon-size` (banner da coleção), `--tt2-meta-label`, `--tt2-meta-value`
(metadados do item), `--tt2-blog-columns`, `--tt2-sidebar-width`. Para mudá-los,
use as opções correspondentes (`reference/options.json`), nunca o CSS do filho.

## Exemplo

```css
.museu-da-cidade-section .museu-da-cidade-quote {
	padding: clamp(1.5rem, 4vw, 3rem);
	border-left: 4px solid var(--tt2-accent, #187181);
	border-radius: var(--tt2-radius, 8px);
	background: var(--tt2-soft, #f2f4f8);
	color: var(--tt2-ink, #111);
	font-family: var(--tt2-font-heading, Georgia, serif);
}
```
