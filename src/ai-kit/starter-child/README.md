# {{THEME_NAME}}

Tema filho do Tainacan Interface para {{SITE_NAME}}, feito a partir do modelo do
Kit para IA (Tainacan Interface {{PARENT_VERSION}}, {{DATE}}).

## O que tem

| Arquivo | Para quê |
|---|---|
| `style.css` | Cabeçalho do tema e o CSS das seções (usa os tokens `--tt2-*`) |
| `functions.php` | Valores iniciais das opções, traduções, categoria dos padrões |
| `theme.json` | Espaçamentos e estilo de botões e títulos |
| `patterns/home-hero.php` | Abertura com a imagem da coleção principal e a busca |
| `patterns/featured-items.php` | Itens recentes da coleção principal em carrossel |
| `patterns/collections-grid.php` | As coleções em cards |
| `patterns/about-split.php` | Sobre a instituição, imagem e texto |
| `patterns/visit-band.php` | Visita: endereço, horário, contato |
| `patterns/page-home.php` | A página inicial inteira (oferecida ao criar uma página) |

## Página inicial

1. Páginas → Adicionar nova → escolha "Home page of the collection" (ou monte com
   os padrões da categoria do tema).
2. No painel da página: Modelo → **Landing** (sem banner nem título).
3. Publique e, em Configurações → Leitura, escolha a página como inicial.
4. Troque os textos de exemplo e o link de "Learn more".

## Regras para mudar

Siga `ai-kit/AGENTS.md` do Tainacan Interface: opções antes de CSS, tokens no
CSS, padrões com blocos que se escrevem à mão, ganchos antes de cópias de
template. Confira em Aparência → Kit para IA → Conferir um tema filho.
