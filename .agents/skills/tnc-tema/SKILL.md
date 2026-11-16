---
name: tnc-tema
description: Cria, refatora ou revisa um TEMA WordPress para acervos Tainacan, em vez de um plugin: hierarquia de templates e as partes que o Theme Helper espera (tainacan/archive-items, single-items, archive-terms, view-mode-*), registro de modo de visualização, enfileiramento condicional por contexto, recursos que se ligam e desligam numa tela só, ajuste por coleção pelo gancho de formulário de coleção, papéis de metadado, escape em template, Sass com o Bootstrap como dependência de compilação, release de tema (style.css, readme.txt, Stable tag). Use para "tema do Tainacan", "tainacan-interface", "template do item", "view mode do tema", "o tema não mostra", "refatorar tema", "fork do tema", "tema filho", e quando o projeto tem style.css com cabeçalho Theme Name em vez de cabeçalho de plugin.
---

# tnc-tema

Tema é outro contrato que plugin. O que num plugin é vazamento (estilizar `body`, declarar função de template, assumir a página inteira) num tema é a função do arquivo; e o que num plugin é opcional (escape em template, contexto no enfileiramento) num tema é o que separa um tema que convive de um tema que atropela os plugins da página.

O verificador reconhece o projeto como tema por `"tipo": "tema"` no `.tnc-check.json` ou pelo cabeçalho `Theme Name:` do `style.css`, e a partir daí **desliga a TNC-C02** (seletor global) e troca as conferências de release pelas de tema.

## Decidir onde o código vai

| Pedido | Lugar | Por quê |
|---|---|---|
| Nova seção na página do item | parte em `template-parts/`, chamada de `tainacan/single-items.php` | é a hierarquia que o Theme Helper espera |
| Novo modo de ver a lista | `tainacan_register_view_mode()` no `after_setup_theme` + template em `tainacan/view-mode-<slug>.php` | o modo aparece no menu do Tainacan e em qualquer coleção que o habilite |
| Estilo sobre a lista do Tainacan | folha própria em `assets/css/`, enfileirada só na página de lista | o HTML da lista é Vue, do plugin; o tema controla o CSS, não o markup |
| Ajuste que varia por acervo | `tainacan_register_admin_hook( 'collection', ... )` + meta plana + `tainacan-api-response-collection-meta` | é a única forma de o formulário Vue da coleção pré-preencher o campo |
| Opção de aparência | `customize_register`, com `transport => postMessage` e prévia ao vivo | aparência se escolhe vendo |
| Liga/desliga de funcionalidade | tela própria em `add_theme_page()`, não o Personalizar | a coluna do Personalizar é estreita e não cabe explicação; foi assim que um tema chegou a 108 controles sem nenhum texto de ajuda |

## Regras que não se negociam

- **Escape no ponto de saída, em todo template.** Template de tema é o lugar onde o escape é mais esquecido e mais explorável: o valor vem do banco, do `$_GET` ou de um metadado, e vai direto para o HTML. `esc_html()` em texto, `esc_attr()` em atributo, `esc_url()` em `href`/`src`, `wp_kses_post()` quando o HTML é intencional. Escapar antes, numa variável, não conta.
- **Nunca ler superglobal direto no template.** Um utilitário do tema (`<prefixo>_query_arg()`) faz `sanitize_text_field( wp_unslash( $_GET[...] ) )` num lugar só. Vale também para `$_SERVER['QUERY_STRING']` e `$_SERVER['REQUEST_URI']`, que vêm no pedido de quem visita (TNC-S02).
- **URL que veio da URL passa por validação de origem.** Um parâmetro como `?ref=` que volta impresso num `href` é XSS refletido se aceitar qualquer valor. Aceite só endereço do próprio site (`wp_http_validate_url()` + comparação de host) e imprima com `esc_url()`.
- **Nada de servidor externo.** Nenhuma fonte, nenhum script, nenhum CSS de CDN (TNC-W14): o visitante tem o IP enviado a terceiros e o site passa a depender de quem não é o administrador. Tipografia padrão é a do sistema; webfont vai vendorizada.
- **Enfileiramento condicional.** Cada arquivo entra só na página onde serve, e a detecção de contexto fica num lugar só, não espalhada por módulo. Carregar o leiaute inteiro na home de um blog sem acervo é o padrão a não repetir.
- **`get_template_part()` só para arquivo que existe** (TNC-B08): ela não avisa quando falta, a seção só desaparece.
- **`MutationObserver` no documento não escreve no DOM sem se calar** (TNC-W16). A lista do Tainacan é um aplicativo Vue que muda o DOM o tempo todo, e observar `document.body` é a maneira natural de esperar por ela — mas escrever é mutar, inclusive `elemento.textContent = 'igual ao que já estava'`. O callback acorda a si mesmo e a aba congela sem um erro no console. Desconecte antes de escrever e religue depois, junte as mutações num `requestAnimationFrame`, escreva só o que mudou e, assim que der, observe o nó certo em vez do documento.
- **Callback de rota aponta para método que existe** (TNC-B09). `array( __CLASS__, 'validar_x' )` sem o método é 500 em **todo** pedido da rota, porque o `validate_callback` roda antes do callback — e o teste que chama a função do callback na mão passa. Rota se testa por `rest_do_request()`.
- **Argumento de rota que carrega endereço não se sanitiza com `sanitize_text_field` nem com `esc_url_raw`.** O primeiro apaga toda sequência `%XX` e o segundo apaga as aspas; um endereço de lista do Tainacan é percent-encoded e leva JSON (`taxquery`, `metaquery`), de modo que os dois o destroem em silêncio. Tire só caracteres de controle, valide a origem (`wp_http_validate_url()` + host) e sanitize cada pedaço da consulta onde ele é usado.
- **Variável de template não colide com global do WordPress.** `$post_type`, `$order`, `$post` e `$query` atribuídos num template sobrescrevem a global; use prefixo.
- **Ganchos herdados não se renomeiam.** Num fork, as ações do tema de origem (`tainacan-interface-single-item-*`) continuam a ser disparadas nas mesmas posições: há plugins pendurados nelas.
- **Toda versão de asset vem da constante do tema.** Sem isso, o navegador serve o CSS antigo depois da atualização.
- Callback de gancho de admin do Tainacan **devolve** o HTML, nunca imprime (TNC-T01).

## O que o Theme Helper do Tainacan espera

Templates em `tainacan/` na raiz do tema, carregados pelo plugin quando existem:

| Arquivo | Quando |
|---|---|
| `tainacan/archive-items.php` | lista de itens de uma coleção |
| `tainacan/archive-repository.php` | lista de itens de todo o repositório |
| `tainacan/archive-taxonomy.php` | itens de uma taxonomia |
| `tainacan/archive-terms.php` | lista de termos de uma taxonomia |
| `tainacan/single-items.php` | página de um item |
| `tainacan/view-mode-<slug>.php` | um modo de visualização registrado pelo tema |

Constantes que o tema declara para o plugin não montar a página por filtro de `the_content` (e que ficam com o nome do Tainacan, não com o prefixo do tema): `TAINACAN_DISABLE_ITEM_THE_CONTENT_FILTER` e `TAINACAN_DISABLE_TAXONOMY_THE_CONTENT_FILTER`.

Filtros úteis: `tainacan-default-view-mode-for-themes`, `tainacan-enabled-view-modes-for-themes`, `tainacan-get-item-metadatum-as-html-before/after-title`, `tainacan-get-metadata-section-as-html-before/after--index-N`.

## Três assinaturas do Tainacan que não dão erro, só não funcionam

As mais caras de todas, porque passam por `php -l`, por PHPCS e pelo
`tnc-check`, e só aparecem conferindo a saída no ar. Custaram quatro
correções num dia na refatoração do tema oficial.

| Chamada | Errado | Certo | O que acontece com o errado |
|---|---|---|---|
| `tainacan_get_collection()` | `tainacan_get_collection( $id )` | `tainacan_get_collection( array( 'collection_id' => $id ) )` | recebe um **array de argumentos**. Com um id, ignora o argumento, cai na coleção do contexto da página e devolve `false` fora dela. Tudo que dependia da coleção para de funcionar em silêncio: papéis de metadado, seções da ficha, permissão de rota. |
| `tainacan_get_the_metadata()`, um metadado | `'metadata' => array( $id )` | `'metadata' => (int) $id` | `metadata` aceita **um** metadado (objeto, id inteiro ou slug). Com array, o núcleo não reconhece o argumento e devolve string vazia. |
| `tainacan_get_the_metadata()`, vários | `'metadata' => $ids` | `'metadata__in' => $ids` | idem: a lista tem argumento próprio. |

E uma quarta, de forma: o HTML de `tainacan_get_the_metadata()` traz **rótulo e
valor juntos**, e não há argumento para pedir só o valor — zerar
`before_title`/`after_title` faz o rótulo colar no valor ("Suportepapel
fotográfico" numa referência em APA). Para o valor sozinho, use a entidade:
`Item_Metadata_Entity::get_value_as_html()`, e `get_value()` quando o metadado
é de vários valores (um array de nomes não se reconstrói partindo o texto por
vírgula: a vírgula também separa sobrenome de prenome em nome invertido).

**A conclusão de método:** nenhuma verificação estática pega erro de contrato
de API. Depois de publicar, confira **a saída** da funcionalidade que mudou —
não o código, não o HTTP 200 da home. Foi assim que as quatro apareceram.

## Pegadinhas do tema sobre o Tainacan

- **A lista de itens não expõe o id do item no cartão.** O markup é Vue e os cartões carregam só o endereço. Para resolver o item a partir de um clique, use `url_to_postid()` sobre uma URL validada como interna; não procure `data-item-id`.
- **Permissão de item se pergunta à coleção.** `is_post_publicly_viewable()` responde "sim" para item de coleção privada, porque o post type dos itens é registrado como público. Pergunte `$item->get_collection()->can_read()`.
- **Os valores de faceta saem ordenados por nome em SQL**, e o único gancho (`tainacan-fetch-all-metadatum-values`) é pré e curto-circuita o cálculo inteiro. Reordenar sem reimplementar a consulta se faz na resposta da rota REST (`rest_post_dispatch`), e só quando toda entrada trouxe contagem.
- **A página de termo é montada sem coleção em contexto**, então o Tainacan cai no modo repositório: todos os modos de visualização e só os metadados comuns. Informar a coleção (quando uma única usa a taxonomia) vale só enquanto a lista é montada, e tem de ser desfeito antes do rodapé.
- **O bloco Busca facetada imprime as próprias cores inline** no invólucro: uma regra de folha de estilo não ganha dele sem `!important`. É a única situação do tema em que `!important` se justifica, e com comentário dizendo por quê.

## Sass e Bootstrap

Quando o tema herda Bootstrap, ele é **dependência de compilação**, não um arquivo servido: entra no `style.css` com um arquivo de imports enxuto, com os componentes que o markup usa. Duas consequências:

1. classe de componente que não estiver na lista de imports não existe no CSS — ao usar um componente novo, acrescente o `@import`;
2. o `bootstrap.min.js` e o jQuery atrás dele quase sempre existem para abrir o menu recolhido e os menus suspensos, que são algumas dezenas de linhas de JavaScript próprio.

O CSS compilado vai versionado, para o tema funcionar sem passo de build. A conferência de release avisa quando o Sass é mais novo que o CSS compilado (TNC-R09).

## Release de tema

`tnc-check --release` confere, em tema:

- `Version` no cabeçalho do `style.css` (sem isso não há atualização nem quebra de cache);
- a mesma versão no cabeçalho, na constante do `functions.php`, no `Stable tag` do `readme.txt` e no `package.json` (TNC-R01, bloqueio);
- `readme.txt` com `Requires at least`, `Tested up to`, `Requires PHP`, `Stable tag` e `License` (TNC-R02);
- changelog mencionando a versão (TNC-R03);
- `Text Domain` igual ao nome da pasta — o WordPress carrega a tradução de um tema pelo nome da pasta (TNC-R04);
- `phpcs.xml.dist` com `WordPress-Extra` e `WordPress.Security`; **sem** exigir `PluginCheck`, que é padrão do diretório de plugins (TNC-R08);
- Sass mais novo que o CSS compilado (TNC-R09).

Modelo de ruleset: `.agents/tainacan-stack/templates/phpcs-tema.xml.dist`.

## Terminar

Toda alteração termina em `tnc-validacao`: `php -l`, `tnc-check` na pasta, `vendor/bin/phpcs`, e `tnc-check --release` quando for publicar.
