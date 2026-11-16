# Estudo de impacto: trocar o Tainacan Interface pelo Tema Tainacan 2.0

30/10/2026 · Tema Tainacan 2.0 2.2.0 · base: Tainacan Interface 2.10.0

## Para quem é

É para quem administra um site que hoje usa:
- o **Tainacan Interface** (o "versão 1", tema oficial do Tainacan);
- um **tema filho** dele;
- ou o **Tainacan Acervo**, derivado dele.

O estudo diz o que pode quebrar e o que deixa de funcionar na troca para o Tema Tainacan 2.0, com a solução de cada ponto: feita no próprio tema ou proposta.

## A natureza da troca

Não é uma atualização, é uma **troca de tema**. O 2.0 tem outro nome de pasta (`tema-tainacan-2-0`) e outro domínio de tradução (`tema-tainacan-2-0`). Isso tem três consequências:
- **ajustes:** o WordPress guarda os ajustes do Personalizar por tema;
- **atualizações:** o WordPress.org não atualiza o 2.0 sozinho;
- **traduções:** as traduções baixadas para o `tainacan-interface` não valem para o 2.0.

O resto do contrato foi mantido de propósito:
- as funções `tainacan_interface_*`;
- os ganchos `tainacan-interface-*`, inclusive o `…-after-reated-items` com o erro de digitação original;
- os nomes dos ajustes (`tainacan_*`);
- os metadados de cor e banner por coleção;
- a constante `TAINACAN_INTERFACE_VERSION`, que segue em 2.10.0 como nível da API;
- os caminhos dos templates.

## Resumo

| # | Ponto | Gravidade | Situação |
|---|---|---|---|
| 1 | Ajustes do Personalizar ficam no tema antigo | alta | **resolvido**: importação na 1ª ativação, agora também de tema filho e do Tainacan Acervo (2.2.0) |
| 2 | CSS adicional fica no tema antigo | média | **resolvido**: importado junto |
| 3 | Menus e widgets | baixa | **resolvido**: posições de menu vêm com os ajustes; widgets são remapeados pelo WordPress (mesmos IDs de área) |
| 4 | Traduções além do pt_BR somem | média | **mitigado** (2.2.0): usa o pacote de idioma do Tainacan Interface se ele ainda estiver no site |
| 5 | Fonte Roboto deixava de carregar | média | **resolvido** (2.2.0): Roboto dentro do tema, sem Google Fonts |
| 6 | Tema filho do Tainacan Interface | alta | **proposta**: trocar o `Template:` do filho; ver os cuidados |
| 7 | Funções globais removidas | média | **resolvido em parte** (2.2.0): `custom_class` e `tainacan_customizer_gutenberg_colors` voltaram; `get_item_link_for_navigation` não volta |
| 8 | Atualizações automáticas e correções do tema oficial | alta | **proposta**: canal de atualização + acompanhar o tema oficial |
| 9 | Comportamento novo para o visitante | média | **por escolha**: cada recurso tem interruptor |
| 10 | Endurecimento de segurança muda casos de borda | baixa | **por escolha**: documentado |
| 11 | Requisitos | baixa | WordPress 6.5+ e PHP 7.4+, iguais aos do original; navegadores atuais |
| 12 | Página de item feita em blocos (template do Tainacan) | média | **proposta**: os recursos do tema só valem no template PHP |
| 13 | Recursos exclusivos do Tainacan Acervo | alta para quem usa | **por escolha**: ficam no Acervo |
| 14 | Cache de página e CDN | baixa | **procedimento**: limpar depois da troca |
| 15 | Volta atrás | — | **simples**: reativar o tema antigo |

## Detalhe

### 1. Ajustes do Personalizar

- **Problema:** o WordPress guarda os ajustes em `theme_mods_<pasta-do-tema>`. Ao ativar o 2.0, ele começaria do zero: cores, cabeçalho, ordem das seções do item, rodapé e posições de menu.
- **Feito:**
  - Na primeira ativação, o 2.0 copia os ajustes do **tema que estava ativo antes**, se ele for derivado do Tainacan Interface (reconhecido por ter ajustes `tainacan_*`: um tema filho ou o Tainacan Acervo).
  - Senão, copia do próprio `tainacan-interface`.
  - Se o 2.0 já tiver ajustes próprios, nada é copiado, para não sobrescrever o que alguém configurou depois.
- **Cuidado:** a cópia é feita uma vez. Uma mudança feita depois no tema antigo não chega ao 2.0.

### 2. CSS adicional

- **Problema:** o CSS do Personalizar → CSS adicional é um post ligado ao tema.
- **Feito:** é copiado do mesmo tema de origem do item 1, se o 2.0 ainda não tiver CSS próprio.
- **Cuidado:** um CSS que mira classes que o 2.0 mudou pode deixar de valer. Exemplos:
  - o leitor de PDF não é mais um `iframe`;
  - a página do item ganhou a grade `.tt2-item-body`.

  Revise o CSS adicional depois da troca.

### 3. Menus e widgets

- **Menus:** as posições de menu (`nav_menu_locations`) fazem parte dos ajustes e vêm no item 1. O nome da posição é o mesmo (`navMenubelowHeader`).
- **Widgets:** ficam no site, não no tema. Na troca, o WordPress remapeia as áreas pelo ID, e os IDs são os mesmos.
- **Conferir:** abrir Aparência → Menus e Widgets uma vez depois da troca.

### 4. Traduções

- **Problema:** o Tainacan Interface recebe traduções do translate.wordpress.org (es_ES, fr_FR…) no domínio `tainacan-interface`. O 2.0 usa o domínio `tema-tainacan-2-0` e só traz pt_BR. Num site em espanhol, o 2.0 apareceria em inglês.
- **Feito (2.2.0):** quando o 2.0 não tem a tradução de um texto, procura o mesmo texto no pacote de idioma do Tainacan Interface (`wp-content/languages/themes/tainacan-interface-<idioma>.mo`). Os textos herdados são os mesmos, então a maior parte da interface fica traduzida.
- **Limites:**
  - **Pacote apagado junto com o tema:** o WordPress apaga o pacote de idioma quando o tema é apagado. **Não apague o Tainacan Interface** (deixá-lo inativo também serve para voltar atrás, item 15). Outra saída é copiar o `.mo` para `wp-content/languages/themes/tema-tainacan-2-0-<idioma>.mo`.
  - **Textos novos:** os do 2.0 (gaveta, visualizador, leitor de PDF, coluna lateral) só existem em inglês e pt_BR.
- **Proposta:** gerar es_ES quando houver um site em espanhol. São cerca de 90 textos novos.

### 5. Fonte

- **Problema:** o Tainacan Interface carregava a Roboto do Google Fonts. A 2.0.0 tirou o Google Fonts (cada visita mandava o IP do visitante ao Google), e quem não tinha a Roboto instalada via a fonte do sistema. A mudança visual era pequena, mas real.
- **Feito (2.2.0):**
  - a Roboto (subconjunto latino, que cobre o português) vem dentro do tema e é declarada como `fontFace` no `theme.json`;
  - o WordPress gera os `@font-face` sozinho;
  - são 6 arquivos woff2 de cerca de 19 kB, baixados só os pesos usados.

### 6. Tema filho

- **Problema:** um tema filho declara `Template: tainacan-interface` e continua preso ao tema original. Não dá para "trocar a mãe" pelo painel.
- **Proposta, quando o filho existe:**
  1. **Pai:** trocar no `style.css` do filho `Template: tainacan-interface` por `Template: tema-tainacan-2-0`.
  2. **Textos:** chamadas de tradução com `'tainacan-interface'` continuam funcionando se o pacote de idioma existir (item 4). O certo é o filho usar o próprio domínio.
  3. **`single-items.php` sobrescrito:** um filho que sobrescreve `tainacan/single-items.php` não recebe a coluna lateral. Nos três lugares onde o filho chama `get_template_part( 'template-parts/single-items-metadata' )` seguido do `do_action( …after-metadata )`, trocar por `tema_tainacan_the_item_metadata();`. Os outros recursos (gaveta, visualizador, leitor de PDF, ficha em destaque) funcionam sem mexer no filho.
  4. **`tainacan/view-mode-grid.php` sobrescrito:** um filho que copia o template antigo traz de volta o problema de segurança do `ref`. Copiar o template novo.
  5. **Ajustes:** vêm do filho (item 1), desde que o filho estivesse ativo na hora da troca.
- **A testar:** o tema filho do usuário, se houver, num site de teste antes da produção.

### 7. Funções globais

Três funções do Tainacan Interface sumiram no 2.0. Um tema filho ou um trecho de código que as chame daria **erro fatal**.

| Função | Situação |
|---|---|
| `custom_class()` | **de volta** (2.2.0), em `inc/compat.php`: chama `tainacan_interface_body_class()`, que faz o mesmo |
| `tainacan_customizer_gutenberg_colors()` | **de volta** (2.2.0), em `inc/compat.php`, com a cor validada |
| `get_item_link_for_navigation()` | **não volta**: era declarada dentro de `tainacan/view-mode-grid.php` e montava o link do item com a query string crua, de onde vinha o redirecionamento pelo `ref`. Quem a usava deve ler os argumentos com `tema_tainacan_list_query_args()`. |

Busca feita: todas as funções de nível global do commit original (`6fa68a4`) comparadas com as do 2.2.0. Nenhuma outra sumiu. Os ganchos `do_action` e `apply_filters` originais continuam todos lá.

### 8. Atualizações

- **Problema:** o Tainacan Interface se atualiza pelo painel, inclusive com correções de segurança. O 2.0 não está no WordPress.org, então nada chega sozinho. E uma correção futura no tema oficial não entra no 2.0 automaticamente.
- **Proposta:**
  1. **Agora:** atualizar pelo `deploy.sh` (nos sites da VPS) ou pelo ZIP de release do GitHub.
  2. **Canal de atualização:** um cabeçalho `Update URI` e um verificador leve, que lê o último release do GitHub (uma requisição por dia, com transient), para o painel avisar da versão nova. Cerca de meia sessão.
  3. **Acompanhar o tema oficial:** a cada release do `tainacan/tainacan-theme`, ver o diff contra a base (develop 84b9a20) e portar o que for correção. Fica anotado no AGENTS.md como rotina.

### 9. O que muda para o visitante

Todos são recursos novos, com interruptor no Personalizar → **Tema Tainacan 2.0** e → **coluna lateral do item**:

| Recurso | Diferença para quem vinha do Interface | Desligar |
|---|---|---|
| Gaveta de prévia nas listas | o clique no cartão abre a gaveta em vez de ir para o item (Ctrl+clique e o botão "Ver item" vão direto) | sim |
| Visualizador de arquivos | substitui o lightbox do Tainacan | sim |
| Leitor de PDF na página | substitui o leitor do navegador (iframe) | junto com o visualizador |
| Ficha em destaque (pele do MHN) | barra de destaque no título, rótulos discretos, termos como "chips", número de registro sob o título | sim |
| Coluna lateral | a ficha fica mais estreita: 1 coluna de metadados (2 em telas de 1600 px ou mais), mesmo que o Personalizar peça 3 | sim |
| Seção "Itens relacionados" no fim | sai quando a coluna mostra os relacionamentos | sim (opção própria) |

**Proposta:** num site que quer a cara do Interface, desligar os interruptores e ligar um por um, conferindo cada um.

### 10. Endurecimento de segurança

As correções de segurança mudam o comportamento em casos de borda. Quem dependia do comportamento antigo pode estranhar:

| Antes | Agora | Quem nota |
|---|---|---|
| `?status=draft` mostrava rascunhos na navegação entre itens para qualquer um | só para quem pode `edit_others_posts` na coleção | autor que revisava os próprios rascunhos pela navegação |
| `?ref=` aceitava qualquer endereço no "voltar à lista" | só endereço do próprio site | links externos antigos com `ref` caem na lista padrão |
| `?perpage=` da lista de termos sem limite | entre 1 e 96 | quem montava links pedindo 200 termos por página |
| Busca de termos e coleções devolvia o texto cru | texto sem HTML | ninguém, a não ser quem buscava com tags |
| Cores da coleção aceitavam qualquer texto | só cor válida; o resto é descartado | coleção com cor inválida volta à cor padrão |
| Compartilhar no Twitter sempre presente | só no estilo de ícones e com usuário do Twitter configurado | site que não configurou o usuário |

### 11. Requisitos

- **WordPress 6.5 ou mais** e **PHP 7.4 ou mais**, os mesmos do Tainacan Interface 2.10.0. O 2.0 depende disso: o `fontFace` do `theme.json` e o `WP_HTML_Tag_Processor`, que marca a galeria.
- **Navegador:** o tema usa `<dialog>`, `:has()`, `color-mix()` e `scrollbar-gutter`, todos presentes nos navegadores de 2023 em diante. Num navegador mais velho, o que se perde é de enfeite:
  - o fundo da coluna fica branco;
  - o botão de baixar do Tainacan aparece repetido sob o leitor de PDF.

  Gaveta e visualizador sem `<dialog>` não abrem e o clique segue normal.

### 12. Página do item em blocos

- **Problema:** se o site monta a página do item com o template em blocos do Tainacan, e não com o `tainacan/single-items.php` do tema:
  - a coluna lateral e a ficha em destaque não aparecem;
  - o leitor de PDF vale, porque entra pelo filtro `tainacan-get-the-document`;
  - o visualizador vale onde houver a galeria do Tainacan.
- **Proposta:** se algum site usar o template em blocos, fazer da coluna um bloco ("Itens relacionados do Tema 2.0") que reaproveita `tema_tainacan_aside_threads()`. Cerca de meia sessão. Hoje não há esse caso nos sites da VPS.

### 13. Quem vem do Tainacan Acervo

- **Os ajustes:** o Tainacan Acervo também é derivado do Interface, e seus ajustes vêm na importação (item 1, 2.2.0).
- **O que se perde:** os recursos exclusivos dele, como o mapa 3D em `/mapa/` e os demais dos 17 interruptores. O caso real é o catalogacao, onde `/mapa/` passou a dar 404.
- **Proposta:** quem usa esses recursos fica no Acervo, ou eles viram plugins independentes do tema. É decisão de produto, não de código.

### 14. Cache

- **Problema:** depois da troca, um cache de página ou CDN pode servir HTML do tema antigo com CSS do novo, ou o contrário.
- **Procedimento:** limpar o cache de página e o CDN logo depois da ativação. O cache da coluna lateral é de objeto e se renova sozinho: a chave muda quando qualquer post ou termo muda.

### 15. Volta atrás

- **Como:** reativar o Tainacan Interface (ou o tema anterior) em Aparência → Temas, ou com `wp theme activate tainacan-interface`.
- **O que fica:**
  - os ajustes do tema antigo estão intactos, porque a importação copia e não move;
  - as opções que o 2.0 grava nos metadados (`tema_tainacan_drawer`, `tema_tainacan_related`) ficam no banco, sem efeito: o tema antigo não as lê.
- **Por isso:** manter o tema antigo instalado e inativo, também pelas traduções (item 4).

## Roteiro de troca recomendado

1. Fazer backup (banco + `wp-content`).
2. Num site de teste com cópia dos dados, ativar o 2.0 e conferir: home, lista de uma coleção em cada modo de visualização, página de item com imagem, PDF, vídeo e itens relacionados, busca e menus.
3. Se houver tema filho, seguir o item 6 antes.
4. Revisar o CSS adicional (item 2).
5. Decidir os interruptores (item 9).
6. Ativar em produção e limpar cache e CDN (item 14).
7. Manter o tema antigo instalado e inativo (itens 4 e 15).

## Feito nesta versão (2.2.0) por causa deste estudo

- importação dos ajustes também do tema anterior (filho ou Tainacan Acervo);
- traduções do Tainacan Interface como reserva;
- Roboto dentro do tema;
- `custom_class()` e `tainacan_customizer_gutenberg_colors()` de volta.

## Proposto, ainda não feito

- canal de atualização pelo GitHub (item 8.2);
- rotina de acompanhar o tema oficial (item 8.3);
- bloco da coluna lateral para a página de item em blocos (item 12);
- es_ES dos textos novos (item 4).
