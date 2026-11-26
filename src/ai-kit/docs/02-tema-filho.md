# 2. O padrão do tema filho

Todo trabalho de IA neste tema vai para um **tema filho**. O pai é atualizado pelo
WordPress, e qualquer mudança nele se perde na próxima versão.

## Como criar

Sempre a partir de `starter-child/`, por um destes caminhos:

- **Painel:** Aparência → Kit para IA → "Tema filho modelo" (nome e pasta) →
  baixar o ZIP já com os marcadores trocados.
- **WP-CLI:** `wp tainacan-interface kit child <pasta> --name="Nome do site"`
  (grava em `wp-content/themes/<pasta>`; `--activate` ativa em seguida).
- **À mão** (assistente sem acesso ao site): copie os arquivos e troque os
  marcadores:

| Marcador | Valor | Exemplo |
|---|---|---|
| `{{THEME_NAME}}` | Nome do tema | `Museu da Cidade` |
| `{{THEME_SLUG}}` | Pasta e text domain: minúsculas, números, hífens | `museu-da-cidade` |
| `starter_child_` (em `functions.php`) | Prefixo das funções: o slug com `_` no lugar de `-` (começando por letra) e `_` no fim | `museu_da_cidade_` |
| `{{PARENT_FOLDER}}` | Pasta do tema pai | `tainacan-interface` |
| `{{PARENT_VERSION}}` | Versão do pai | `2.17.0` |
| `{{SITE_NAME}}` | Nome do site | `Museu da Cidade` |
| `{{DATE}}` | Data (AAAA-MM-DD) | `2026-11-23` |

A pasta do pai está em `site-context.json` → `versions.parent_theme`. Se o nome
da pasta estiver errado no `Template:`, o WordPress mostra "tema quebrado".

## Arquivos

### `style.css`

```css
/*
Theme Name: Museu da Cidade
Template: tainacan-interface
Text Domain: museu-da-cidade
Version: 1.0.0
Requires at least: 6.5
Requires PHP: 7.4
License: GPL-3.0-or-later
*/
```

Depois do cabeçalho vem o CSS do site. Regras:

- Use os tokens: `color: var(--tt2-accent)`, `border-radius: var(--tt2-radius)`.
  Uma cor escrita direto no CSS não muda quando alguém troca a cor em
  Personalizar.
- Restrinja cada regra a uma classe do filho (`.museu-da-cidade-section h2`),
  nunca a `h2`, `a` ou `.tainacan-*` soltos.
- O arquivo carrega sozinho depois do CSS do pai. **Não** enfileire nem o
  `style.css` do pai nem o do filho.

### `functions.php`

O modelo traz três funções. Acrescente só o necessário.

```php
// 1. Traduções do filho.
function museu_da_cidade_setup() {
	load_child_theme_textdomain( 'museu-da-cidade', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'museu_da_cidade_setup' );

// 2. Identidade: o valor INICIAL das opções do pai. Personalizar continua mudando.
function museu_da_cidade_defaults( $schema ) {
	$defaults = array(
		'brand_color'  => '#4a1525',
		'accent_color' => '#8c442c',
		'font_heading' => 'playfair',
		'radius'       => 6,
	);
	foreach ( $defaults as $key => $value ) {
		if ( isset( $schema[ $key ] ) ) {
			$schema[ $key ]['default'] = $value;
		}
	}
	return $schema;
}
add_filter( 'tainacan_interface_settings_schema', 'museu_da_cidade_defaults' );

// 3. Categoria dos padrões do filho no editor.
function museu_da_cidade_pattern_category() {
	register_block_pattern_category( 'museu-da-cidade', array( 'label' => __( 'Site sections', 'museu-da-cidade' ) ) );
}
add_action( 'init', 'museu_da_cidade_pattern_category' );
```

As chaves e os valores aceitos estão em `reference/options.json`. Valor fora da
lista de `choices`, ou cor que não seja hex, volta ao padrão; número fora do
intervalo de um `range` é limitado a `min`/`max`.

**Atenção:** o filtro muda o *padrão*. Se a opção já foi salva em Personalizar,
vale o valor salvo. Na ativação do filho, as opções salvas no pai são copiadas
para ele. Num site que já tem identidade, portanto, mudar o padrão não muda nada:
diga na entrega quais opções ajustar em Personalizar (ou com WP-CLI,
`docs/03-opcoes-sem-codigo.md`).

### `theme.json`

Versão 2. Serve para escalas de espaçamento e para o estilo de blocos do núcleo:

```json
{
	"version": 2,
	"settings": { "spacing": { "spacingSizes": [ { "name": "M", "slug": "50", "size": "clamp(1.25rem, 3vw, 2rem)" } ] } },
	"styles": { "blocks": { "core/button": { "border": { "radius": "var(--tt2-button-radius, 999px)" } } } }
}
```

- **Sem `settings.color.palette`.** Uma paleta aqui substitui a do tema e as
  cores `site-*`.
- Valores de cor e fonte por token (`var(--tt2-accent)`), não fixos.
- Slugs sem dígito colado em letra.

### `patterns/*.php`

O WordPress registra sozinho cada arquivo de `patterns/` do tema ativo.

```php
<?php
/**
 * Title: Home: opening with search
 * Slug: museu-da-cidade/home-hero
 * Categories: museu-da-cidade, featured
 * Keywords: hero, search
 * Viewport Width: 1400
 * Description: One line about what the section is.
 *
 * @package museu-da-cidade
 */
?>
<!-- wp:group {"align":"full", ...} --> ... <!-- /wp:group -->
```

- `Slug` começa com o slug do filho.
- O PHP pode ler o site (a coleção principal com
  `tainacan_interface_kit_main_collection()`, sempre dentro de
  `function_exists()`), mas tudo o que imprime passa por `esc_html`, `esc_attr` ou
  `esc_url`.
- Textos com `esc_html_e( 'Texto em inglês', '<slug>' )`.
- Um padrão de página inteira usa `Block Types: core/post-content` e
  `Post Types: page`: o editor o oferece ao criar uma página. Ele pode ser feito
  só de referências a outros padrões
  (`<!-- wp:pattern {"slug":"museu-da-cidade/home-hero"} /-->`).
- Blocos permitidos e proibidos: `docs/04-blocos.md`.

### `languages/`

`<locale>.po` e `.mo` com o text domain do filho (ex.: `pt_BR.po`). O cabeçalho
`Text Domain` do `style.css` e o domínio nas funções precisam ser iguais ao slug.

## Ganchos antes de cópias

Para pôr algo na página do item, na coleção ou no banner, use as ações de
`reference/hooks.md`:

```php
// Um aviso de direitos abaixo dos metadados de todo item.
function museu_da_cidade_rights_note() {
	echo '<p class="museu-da-cidade-rights">' . esc_html__( 'Images may be reused with credit.', 'museu-da-cidade' ) . '</p>';
}
add_action( 'tainacan-interface-single-item-after-metadata', 'museu_da_cidade_rights_note' );
```

Copiar um template do pai para o filho (`tainacan/single-items.php`, `header.php`…)
é o último recurso. Quando inevitável:

- copie da versão **instalada** do pai, nunca de memória;
- ponha no topo um comentário dizendo de que versão veio e por que nenhum gancho
  servia;
- mude o mínimo.

O tema reconhece cópias de templates da versão 2.10.0 que quebram páginas e
avisa no painel.

## Segurança e qualidade

- Cada entrada do visitante pelas funções do pai (`tainacan_interface_query_arg( 'chave' )`,
  `tainacan_interface_query_flag()`, em `inc/request.php`), ou com `wp_unslash()`
  mais sanitização (`sanitize_text_field`, `absint`). Se mudar algo: nonce mais
  `current_user_can()`.
- Sem SQL direto, sem `eval`, sem `file_get_contents` de URL; para HTTP use
  `wp_remote_get()`.
- Sem scripts ou fontes de CDN; JavaScript só se for indispensável, enfileirado
  só onde é usado, com a versão do filho.
- Datas com `wp_date()`; JSON com `wp_json_encode()`.

## O que acontece na ativação

1. O WordPress ativa o filho; as opções do pai são copiadas para ele (com
   registro para desfazer). Se o filho já tinha opções próprias, o painel oferece
   a importação em vez de copiar.
2. Os padrões do filho aparecem na aba Padrões do editor, na categoria do filho.
3. O pai continua cuidando das páginas do Tainacan.

Para testar sem ativar: Aparência → Personalizar com o filho em
pré-visualização, ou Aparência → Kit para IA → "Conferir um tema filho".
