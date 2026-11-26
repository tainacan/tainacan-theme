# 3. Mudar o site só com opções

Antes de escrever CSS, procure a opção. `reference/options.json` tem cada uma com:

```json
"accent_color": {
	"theme_mod": "tainacan_interface_accent_color",
	"section": "look",
	"type": "color",
	"default": "#5b4fcf",
	"label": "Cor de destaque",
	"help": "Links, títulos das fichas, filtros, botões.",
	"collection": true
}
```

- `type`: `choice` (uma das chaves de `choices`), `bool`, `range` (inteiro entre
  `min` e `max`, com `step`), `color` (hex `#rrggbb`), `url`, `text`, `textarea`.
  Valor inválido volta ao padrão; número fora do intervalo é limitado.
- `collection: true`: a coleção pode ter valor próprio.
- `presets`: combinações prontas de cores (`observatory`, `museum`, `archive`,
  `nature`, `contrast`). **Não existe opção "preset ativo"**: o seletor da seção
  Cores do Personalizar só preenche os campos. Para aplicar um preset, copie os
  valores de `presets.<nome>.values` para as opções (no filtro do filho ou em
  Personalizar), mudando o que quiser.

## Seções (as mais pedidas)

| Seção | Para quê |
|---|---|
| `look` | Cor principal e de destaque, fundo, superfície, texto, faixa decorativa, presets |
| `typography` | Fontes (interface, títulos, leitura), tamanhos h1–h6, peso, entrelinha |
| `layout` | Largura, cantos, sombras, espaçamentos, moldura |
| `buttons` | Forma dos botões |
| `site_header`, `header_search`, `breadcrumb`, `footer`, `social`, `share` | Cabeçalho, busca do topo, trilha, rodapé, redes |
| `hero`, `page_title` | Banner do topo e título das páginas |
| `collections`, `collection`, `cards`, `items_search`, `filters`, `pagination` | Lista de coleções, página da coleção, fichas, busca e filtros |
| `item`, `document`, `aside`, `related`, `item_nav`, `drawer` | Página do item |
| `blog`, `post`, `sidebar` | Blog |

## Três formas de aplicar

**1. Personalizar** (quem administra o site): Aparência → Personalizar → seção →
opção. É o que a entrega deve listar para um site que já está no ar.

**2. WP-CLI** (agente com terminal), com o tema que vai receber a opção ativo:

```bash
wp theme mod set tainacan_interface_brand_color '#4a1525'
wp theme mod set tainacan_interface_font_heading playfair
wp theme mod set tainacan_interface_radius 6
wp theme mod get tainacan_interface_brand_color
```

Os `theme_mods` ficam guardados **por tema**: grave com o filho ativo, ou as
opções vão para o pai.

**3. Valor inicial no filho** (`tainacan_interface_settings_schema`, ver
`02-tema-filho.md`): vale para quem nunca mexeu na opção. Bom para um site novo e
para distribuir o filho.

## Por coleção

Nas opções com `collection: true`, a coleção pode ter valor próprio. Pelo painel:
Tainacan → a coleção → Configurações (o Personalizar avisa quais coleções têm
valor próprio e leva até lá). Por WP-CLI:

```bash
wp post meta update <id-da-colecao> tainacan_interface_opt_accent_color '#255F56'
```

## Filtro em código

Para uma regra que depende de contexto (ex.: outra cor na coleção 12 só nas
páginas de item):

```php
function museu_da_cidade_opt( $value, $key ) {
	if ( 'accent_color' === $key && is_singular( 'tnc_col_12_item' ) ) {
		return '#255F56';
	}
	return $value;
}
add_filter( 'tainacan_interface_opt', 'museu_da_cidade_opt', 10, 2 );
```

O tipo de post dos itens de cada coleção (`tnc_col_<id>_item`) está em
`site-context.json` → `tainacan.collections[].post_type`.
