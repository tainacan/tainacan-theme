# Ganchos do Tainacan Interface

Use-os no `functions.php` do tema filho, com funções prefixadas pelo slug do
filho. Ganchos antes de cópias de template (`docs/02-tema-filho.md`).

## Ações: página do item (`tainacan/single-items.php`)

| Ação | Onde |
|---|---|
| `tainacan-interface-single-item-top` | Topo da página do item |
| `tainacan-interface-single-item-after-title` | Depois do título |
| `tainacan-interface-single-item-after-document` | Depois do documento |
| `tainacan-interface-single-item-after-attachments` | Depois dos anexos |
| `tainacan-interface-single-item-metadata-begin` | Início da lista de metadados |
| `tainacan-interface-single-item-metadata-end` | Fim da lista de metadados |
| `tainacan-interface-single-item-after-metadata` | Depois dos metadados |
| `tainacan-interface-single-item-after-reated-items` | Depois dos itens relacionados (o nome tem mesmo o erro "reated") |
| `tainacan-interface-single-item-bottom` | Fim da página do item |

Dentro delas, `get_the_ID()` é o item e `tainacan_get_collection_id()` a coleção.

## Ações: coleções, taxonomias e banner

| Ação | Onde |
|---|---|
| `tainacan-interface-collection-header` | Início do cabeçalho da coleção (lista de itens) |
| `tainacan-interface-collection-description` | Junto da descrição da coleção |
| `tainacan-interface-collection-header-bottom` | Fim do cabeçalho da coleção |
| `tainacan-interface-taxonomy-header` / `-description` / `-header-bottom` | O mesmo nas páginas de taxonomia |
| `tainacan-interface-term-description` | Junto da descrição do termo |
| `tainacan-interface-banner-header-description` | Abaixo do nome do site no banner do topo |

## Filtros

| Filtro | Recebe | Para quê |
|---|---|---|
| `tainacan_interface_settings_schema` | `$schema` | Mudar o valor inicial (`default`) das opções; acrescentar opções |
| `tainacan_interface_opt` | `$value, $key` | Mudar o valor de uma opção conforme o contexto |
| `tainacan_interface_presets` | `$presets` | Acrescentar combinações de cores em "Aparência geral" |
| `tainacan_interface_option_rules` | `$rules` | Regras de quando uma opção aparece no Personalizar |
| `tainacan_interface_page_title_parts` | `$parts` | O que o título das páginas diz (`kicker`, `title`, `meta`, `description`, `back`) |
| `tainacan_interface_is_list_page` | `$is_list` | Tratar uma página como lista do Tainacan |
| `tainacan_interface_drawer_enabled` | `$enabled` | Ligar ou desligar a gaveta de prévia do item |
| `tainacan_interface_viewer_enabled` | `$enabled` | Ligar ou desligar o visualizador do documento |
| `tainacan_interface_highlight_enabled` | `$enabled` | Ligar ou desligar o realce da página do item |
| `tainacan_interface_item_aside_enabled` | `$enabled` | Coluna lateral do item |
| `tainacan_interface_item_aside_threads` | `$threads, $item` | O que a coluna lateral do item lista |
| `tainacan_interface_aside_term_ceiling` | `$ceiling` (2000) | Limite de termos lidos pela coluna lateral |
| `tainacan_interface_identifier_metadatum` | `$id, $collection` | Metadado usado como identificador do item |
| `tainacan_interface_citations` | `$citations, $item_id` | Formatos de citação do item |
| `tainacan_interface_kit_main_collection` | `$collection` | A coleção que os padrões do kit usam como principal |
| `tainacan_interface_placeholder_script` | `$needed` | Carregar o script das imagens de "sem imagem" numa página que o tema não reconhece como do Tainacan (ex.: lista montada por código) |
| `tainacan-get-the-mime-type-icon` (do Tainacan) | `$src, $mime, $size` | A imagem de item, coleção ou termo sem miniatura. O tema já a troca pelo desenho do tipo de arquivo (opção `placeholder_style`); para imagens próprias do filho, ligue um filtro com prioridade acima de 10. No JavaScript, o equivalente é `wp.hooks` → `tainacan_get_the_mime_type_icon` |
| `tainacan-meta-date`, `tainacan-meta-author`, `tainacan-meta-date-author` | HTML | Linha de data e autor dos posts |

## Exemplos

```php
// Um selo de "domínio público" no topo do item, nas coleções 4 e 12.
function museu_da_cidade_public_domain() {
	if ( in_array( (int) tainacan_get_collection_id(), array( 4, 12 ), true ) ) {
		echo '<p class="museu-da-cidade-badge">' . esc_html__( 'Public domain', 'museu-da-cidade' ) . '</p>';
	}
}
add_action( 'tainacan-interface-single-item-after-title', 'museu_da_cidade_public_domain' );

// Uma combinação de cores a mais em Personalizar.
function museu_da_cidade_presets( $presets ) {
	$presets['museu-da-cidade'] = array(
		'label'  => __( 'Museu da Cidade', 'museu-da-cidade' ),
		'values' => array(
			'brand_color'  => '#14433a',
			'accent_color' => '#b5812a',
		),
	);
	return $presets;
}
add_filter( 'tainacan_interface_presets', 'museu_da_cidade_presets' );

// Sem botão "Voltar" no título das páginas.
function museu_da_cidade_title_parts( $parts ) {
	$parts['back'] = '';
	return $parts;
}
add_filter( 'tainacan_interface_page_title_parts', 'museu_da_cidade_title_parts' );
```
