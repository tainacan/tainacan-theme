# Revisão técnica do tema pela Stack de IA do Tainacan — 4.4.6 → 4.4.7

Data: 08/11/2026. Alvo: o tema inteiro no ramo `feature/interface-4.0` (commit `0ac8c46`, 4.4.6), 89 arquivos PHP, 10 JS, CSS, `readme.txt`, `languages/`. Método da skill `tnc-revisao-tecnica`: triagem automática, leitura por seis papéis em paralelo (segurança, API do Tainacan, convivência, desempenho, release, WordPress.org) e segunda camada de correção com `/code-review high` sobre o tema e `/code-review medium` sobre o diff da correção. Cada achado abaixo diz de qual camada veio.

**Veredito:** aprovar com ressalvas. Nenhum achado de segurança confirmado; os defeitos de comportamento confirmados foram corrigidos na 4.4.7; ficam decisões de produto e de equipe (seção 4).

## 1. Triagem automática

| Ferramenta | Resultado |
|---|---|
| `tnc-check .` (40 regras) | PASS, 0 bloqueios, 0 avisos, 175 arquivos |
| `tnc-check --release` | 1 aviso TNC-R04 (Text Domain `tainacan-interface` ≠ pasta local `tema-tainacan-2-0`; nos sites a pasta é `tainacan-interface`) |
| `phpcs` (ruleset estrito do tema) | 1 erro: `lib/class-wp-bootstrap-navwalker.php` sem comentário de arquivo (o sniff trata o docblock seguido de `require_once` como comentário do `require`) |
| `php -l` | 89 arquivos sem erro |
| PHPCompatibilityWP 7.4- | 0 erros |
| i18n | 780 msgids no POT = 780 no pt_BR, 0 fora do POT |
| Theme Check | não rodou: não há WP-CLI nem MySQL de pé nesta máquina |

## 2. Corrigido na 4.4.7

| # | Achado | Papel / camada | Arquivo |
|---|---|---|---|
| 1 | Modo Miniatura enviava `source_list=repository` (ou `term`) e nunca `collection`: o anterior/próximo da página do item andava pelo repositório inteiro e a navegação sem filtro de quem edita nunca valia. A 1ª correção (`tainacan_get_collection_id()`) era inerte dentro da rota REST, onde `post_type` é array; a coleção passou a vir do `$request` da rota, em cujo escopo o Tainacan inclui o template | API do Tainacan; corrigida pelo code-review do diff | `tainacan/view-mode-grid.php` |
| 2 | `skeleton_template` sem `implements_skeleton` escondia o indicador de carregamento; com a flag o Tainacan esconde o contêiner que guarda o esqueleto. Saiu o esqueleto, fica o indicador do Tainacan | API do Tainacan + code-review do diff | `functions.php` |
| 3 | "Primeira seção aberta" usava `--index-0`, mas o Tainacan numera também as seções vazias que `hide_empty` pula: coleção com a 1ª seção vazia ficava sem seção aberta e, nas abas, sem painel. A 1ª correção (filtro genérico com estática) falhou no biblio: o TainacanBiblio remove a 1ª seção depois de renderizada. A marca passou a ser aplicada no HTML final, depois de todos os filtros, sem filtro do tema pendurado | API do Tainacan, convivência, code-review; conferência no ar | `template-parts/single-items-metadata.php` |
| 4 | Ganchos `-metadata-begin/-end` concatenados em strings (`do_action()` devolve null) nos leiautes abas/sanfona/recolhíveis/2.x: disparavam antes do invólucro. Agora disparam uma vez em volta das seções em todo leiaute | API do Tainacan | idem |
| 5 | Visualizador só cedia ao Memorial COVID Toolkit no documento PDF; o listener de captura do tema, registrado antes, abria o diálogo do tema em vez da sobreposição do Toolkit. Gate único em `tainacan_interface_viewer_enabled()` | Convivência (quebra) | `inc/item-viewer.php` |
| 6 | Busca do site ampliava `post_type` mesmo quando o pedido já vinha restrito (bloco Busca com um tipo, formulário de plugin) | Convivência; code-review removeu uma guarda morta | `functions.php` |
| 7 | Página de coleções forçava `orderby` da opção por cima do da URL (links de ordenação de tema filho 2.x). A 1ª versão do ajuste pulava também o filtro por taxonomia; corrigido | code-review high; code-review do diff | `functions/archive-functions.php` |
| 8 | Troca do `bootstrap4JS` de um filho 2.x descartava a dependência de jQuery que o filho declarou; alias `RobotoFonts` do 2.x recriado; `foundation.css` enfileirado pelo próprio handle | code-review high; release; convivência | `functions/enqueues.php` |
| 9 | Gaveta: item com senha podia sair com `Cache-Control: public` para quem já tinha o cookie da senha. A 1ª versão barrava no predicado compartilhado e tirava também a coluna lateral e a citação da própria página; movido para a rota da gaveta | Segurança (hipótese média); code-review do diff | `inc/item-drawer.php`, `inc/context.php` |
| 10 | Prévia do Personalizar forçava `--tt2-ink` para a cor principal (ignorando "cor dos títulos") e não repintava cabeçalho/rodapé derivados | code-review high | `assets/js/customizer-preview-design.js` |
| 11 | Fragmentos da referência ABNT fixos em português; agora `__()` com `translators:` e pt_BR | code-review high; WordPress.org | `inc/item-citation.php`, `languages/` |
| 12 | Precedência em `modern.js` (`t.fieldsOne` indefinido quebrava `sectionCounts`) | code-review high | `assets/js/modern.js` |
| 13 | `_prime_post_caches` das coleções do submenu automático (1 consulta em vez de N por página) | Desempenho; code-review high | `inc/nav-menus.php` |
| 14 | Coluna lateral: teto de `$term->count` (2.000, filtro `tainacan_interface_aside_term_ceiling`) e `_prime_post_caches` das taxonomias; `fetch_by_collection()` com 2 parâmetros | Desempenho; API do Tainacan | `inc/item-aside.php`, `inc/item-highlight.php` |
| 15 | Gaveta: metadados já listados no cartão e não marcados pulados antes de renderizar o valor | Desempenho | `inc/item-drawer.php` |
| 16 | Miúdos: `ABSPATH` no shim (resolve o erro do phpcs), `wp_kses_post` no fallback do walker, `copy-link.js` em IIFE, `pointer-events: none` na barra de progresso, argumento morto em `child-theme.php`, readme sem créditos a `bootstrap-legacy.css` e Spectrum | WordPress.org; convivência; code-review | vários |

Revertido depois do code-review do diff: parar de apagar o transient de contagens por transição de status (o número ficaria 10 min diferente do mosaico da mesma coleção). Fica como estava: apagado uma vez por requisição, no `shutdown`.

## 3. Hipóteses e dívidas herdadas, sem alteração

- **Segurança (hipóteses baixas):** metadado de relacionamento em seção privada pode aparecer como rótulo na coluna lateral (`Items::fetch_related_items` do núcleo não filtra seção; a seção nativa do Tainacan faz o mesmo: reportar no `tainacan-achados-core-tema`); limite por IP da gaveta só existe com cache de objeto persistente (decisão documentada); `aria-labelledby` do walker sem `esc_attr` (valor já escapado); `get_avatar_url()` em `comments.php` sem `esc_url` (valor do WordPress).
- **Desempenho (a medir no bpb/Brasiliana, com Query Monitor):** busca nativa do site inclui os itens de todas as coleções (`LIKE` sobre 100 mil linhas, duas vezes); relacionamento de repositório faz o núcleo percorrer todas as coleções e o template repete a passada em `tainacan_has_related_items()`; `found_posts` nas 5 consultas de termos e chave de cache com `last_changed('posts')`; quatro `fetch_by_collection` na mesma página de item; navegação `?pos=` com `OFFSET` grande; lista de todos os anexos como JSON inline no visualizador; transient por coleção no mosaico (100 consultas sem Redis); dois scripts no `<head>` sem `defer`.
- **API do Tainacan:** `$wp_term->count` como total do termo pode divergir do total da lista Vue; `taxquery[compare]` na URL da coluna é lido pelo Vue, não pela REST (funciona; não "corrigir" para `operator`).

## 4. Decisões (equipe ou coordenador)

1. **Busca nativa sobre os itens** (desempenho ALTO em acervo grande): manter só com motor de busca ativo, limitar a `post_title` (`post_search_columns`) ou redirecionar `?s=` para a lista do repositório. Muda o que o visitante encontra.
2. **Desfazer a importação de ajustes** (release): "Trazer os ajustes" substitui `theme_mods` inteiro, inclusive menus e logo; o backup `mods_before_import` existe, mas não há botão nem comando de volta.
3. **Território de plugin** para o diretório de temas: rota REST da gaveta, post meta nas coleções, opção de emoji, criação de menus. Documentar no readme ou mover para plugin companheiro.
4. **Licença da fonte TainacanIcons** (bloqueia envio ao diretório), **Theme URI**, **captura de tela** sem a barra do navegador.
5. **`searchform.php`**: `onsubmit` inline, `method="post"`, `style` inline, herdados do oficial; e `tainacan_customize_editor_css()`/`tainacan_interface_print_style()` imprimem `<style>` à mão em vez de `wp_add_inline_style()`.
6. **Gaveta em sobreposição** aplica `inert` a `body > *`: elementos fixos de plugins pendurados em `body` ficam inertes. Restringir a `main`/`#page`.
7. **Readmore** (`is_single()`) corta o texto de todo `get_value_as_html()` de textarea/descrição na página do item, inclusive de plugins; ligar o filtro só em volta da chamada do tema.
8. **Invalidação de transients na atualização** (`mosaic`, `counts`, `menu_collections` sem versão na chave): uma correção na consulta chega ao site só quando o transient expira (até 24 h).
9. **Cabeçalhos do POT** (`Project-Id-Version`, `Report-Msgid-Bugs-To`) e `es_ES` pendente; `Tested up to: 7.1` a confirmar.

## 5. Checklist da entrega

- [x] `php -l` nos 24 PHP alterados → 0 erros
- [x] `phpcs --standard=phpcs.xml.dist` nos arquivos alterados → 0 erros, 0 avisos (phpcbf alinhou arrays)
- [x] `tnc-check --changed` → PASS 0/0; `--release` → só TNC-R04 (pasta local)
- [x] `/code-review high` sobre o tema (9 achados) e `/code-review medium` sobre o diff (8 achados, todos tratados: 6 corrigidos, 1 revertido, 1 claim do CHANGELOG estreitado)
- [x] Conferência no ar, 4.4.7 no catalogacao e no darkgreen-yak (`deploy.sh`, home 200, tema ativo 4.4.7; `modern.css?ver=4.4.7`):
  - Miniatura: a REST da coleção 202 com `view_mode=grid` traz `source_list=collection&source_entity_id=202&pos=N` nos links;
  - 1ª seção: `<details open class="tt2-section">` no item do catalogacao e `<input checked="checked" name="collapses"` na 1ª seção visível do item do biblio (a "Ficha catalográfica", que o TainacanBiblio remove, não leva a marca);
  - busca: `?s=a&post_type=post` devolve só posts (7) e `?s=a` posts + itens; `post_type=page` não restringe porque o próprio WordPress descarta tipos que não são `publicly_queryable`, antes do tema (conferido por `wp eval` com `$_GET`);
  - gaveta: `/wp-json/tainacan-interface/v1/item/1774` → 200 com `title`, `collection`, `meta_line`, `cover`, `media`, `description`; não há item com senha nos dois sites para o 404;
  - citação ABNT em pt_BR ("Acesso em") presente no item do catalogacao; `item-viewer.js?ver=4.4.7` enfileirado.
