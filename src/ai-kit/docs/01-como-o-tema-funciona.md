# 1. Como o tema funciona

Antes de mudar qualquer coisa, saiba o que o Tainacan Interface já faz sozinho.
Quase tudo que um site de acervo pede já é uma opção.

## Peças

| Peça | Onde | O que faz |
|---|---|---|
| Tema clássico em PHP | `header.php`, `page.php`, `tainacan/*.php` | Monta as páginas. Não é tema de blocos (FSE): o cabeçalho e o rodapé vêm do PHP e do Personalizar, não do editor de site. |
| Opções | `inc/settings.php` | 147 opções em 28 seções de Aparência → Personalizar. Lidas com `tainacan_interface_opt( $chave, $id_da_colecao )`. |
| Desenho | `inc/design.php`, `assets/css/modern.css` | Transforma as opções em variáveis de CSS (`--tt2-*`) em `body.tt2-modern`. Todo o CSS do tema usa essas variáveis. |
| Páginas do Tainacan | `tainacan/` | Lista de itens, página do item, coleções, taxonomias e termos, com ganchos em cada parte. |
| Blocos | `inc/blocks.php`, `blocks/` | Bloco de busca do tema, estilos de bloco, paleta que acompanha Personalizar, reconstrução de blocos do Tainacan no editor. |
| Sem imagem | `inc/placeholders.php` | Desenha a imagem de item, coleção ou termo sem miniatura conforme o tipo de arquivo (imagem, áudio, vídeo, PDF, texto, planilha, apresentação, compactado, modelo 3D, link), numa cor por tipo ou nas cores do site (opção `placeholder_style`). |
| Tema filho | `inc/child-theme.php` | Carrega o `style.css` do filho depois do pai; avisa sobre cópias antigas de templates. |
| Kit para IA | `inc/ai-kit*.php`, `ai-kit/` | Esta pasta, a tela Aparência → Kit para IA e os comandos `wp tainacan-interface kit`. |

## As opções

- Cada opção é um `theme_mod` com o nome `tainacan_interface_<chave>`. O WordPress
  guarda os `theme_mods` **por pasta de tema**: o filho tem os seus. Na ativação de
  um filho, o tema copia para ele as opções do pai (com registro para desfazer).
- As opções marcadas `"collection": true` em `reference/options.json` podem ter
  valor próprio em cada coleção, no metadado `tainacan_interface_opt_<chave>`.
  São elas: cor principal, cor de destaque, leiaute do cabeçalho, banner, ícone,
  descrição, menu da coleção, modo da gaveta, leiaute do item e seções do item.
- Há **combinações prontas** (presets) na seção Cores (`look`): `observatory`,
  `museum`, `archive`, `nature`, `contrast`. O seletor só preenche os campos; não
  fica gravado qual foi escolhido. São um bom ponto de partida: copie os valores
  e ajuste.
- O tema tem **um só desenho**. A função `tainacan_interface_is_modern()` existe
  só por compatibilidade, gera aviso e não deve ser chamada.

## As páginas que o tema monta

| Página | Template | O que dá para mudar sem código |
|---|---|---|
| Início, páginas, posts | `page.php`, `single.php`, `index.php` | Banner do topo (`hero_*`), título da página (`page_title_*`), blog (`blog_*`), barra lateral |
| Página sem banner nem título | modelo **Landing** (`page-templates/landing.php`) | É a base da página inicial feita com blocos |
| Lista de coleções | `archive-tainacan-collection.php` | Seção `collections` |
| Coleção e lista de itens | `tainacan/archive-items.php` | Seções `collection`, `cards`, `items_search`, `filters`, `pagination` |
| Item | `tainacan/single-items.php` | Seções `item`, `document`, `aside`, `related`, `item_nav`, `drawer` |
| Taxonomia e termo | `tainacan/archive-taxonomy.php`, `archive-terms.php` | Mesmas opções da coleção |

A busca do cabeçalho e o bloco de busca do tema enviam para a lista de itens do
Tainacan (`/<slug-da-lista>/?search=…`; o endereço está em `site-context.json` →
`tainacan.items_list_url`).

## Cores

- O `theme.json` do pai está na versão 2 e **não** tem paleta. A paleta vem de
  `add_theme_support( 'editor-color-palette' )` (default, carmine, cherry, mustard,
  mintgreen, darkturquoise, turquoise, blueheavenly, purple, violet, white,
  lightgray, darkgray).
- O tema acrescenta cinco cores que acompanham Personalizar: `site-brand`,
  `site-accent`, `site-text`, `site-surface`, `site-page`. Use-as nos blocos
  (`"backgroundColor":"site-surface"`) e o site muda de cor junto com as opções.
- Uma paleta declarada no `theme.json` do filho **substitui** tudo isso. Não
  declare.

## Fontes

Nove famílias locais (`assets/fonts/`): Poppins, Roboto, Inter, Open Sans,
Montserrat, Lora, Source Serif 4, Merriweather e Playfair Display, mais Georgia e
a fonte do sistema. Escolha pelas opções `font_ui`, `font_heading` e
`font_reading`. Não carregue fontes de fora.

## O que o tema faz no editor de blocos

- Aplica os mesmos tokens de cor e forma dentro do editor.
- Registra o bloco **Buscar no acervo** (`tainacan-interface/search`) e os estilos
  Carrossel e Cards (Query Loop), Painel e Faixa no topo (Grupo) e Véu da marca
  (Capa).
- Refaz sozinho, ao abrir a página, os blocos do Tainacan cujo HTML só o editor
  sabe montar. Depois disso, basta salvar.
