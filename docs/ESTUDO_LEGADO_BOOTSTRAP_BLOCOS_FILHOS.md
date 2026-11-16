# Legado, Bootstrap, blocos e temas filhos (estudo, 30/10/2026)

Avaliação da 3.0.2 contra o 2.10.0 oficial, em quatro frentes: classes e funções herdadas, Bootstrap, blocos do Gutenberg e temas filhos.

**Método.**
- Comparação automática do código: classes nos templates, `do_action`/`apply_filters`, templates, funções e funções substituíveis (`function_exists`).
- Página de teste com blocos do núcleo no catalogacao, medida no Chrome com e sem o CSS do Bootstrap; foi apagada depois.
- Clique em todos os menus suspensos do Bootstrap.

## 1. Classes e funções herdadas

| O quê | Resultado |
|---|---|
| Classes CSS dos templates | Preservadas. Algumas agora saem por variável e não aparecem numa busca literal, mas estão na página. Saíram de verdade: `tainacan-embed-container` (só existia no caminho `_old`, para Tainacan < 0.19) e `tainacan-icon-36px` das setas grandes da navegação. |
| Ganchos (`do_action`) | Todos preservados. |
| Filtros (`apply_filters`) | Todos preservados, inclusive `tainacan_color_schemes`. |
| Funções substituíveis (`if ( ! function_exists() )`) | As 90 do 2.10.0 continuam substituíveis. A única que falta, `tainacan_the_collection_url`, é do plugin Tainacan, não do tema. |
| Funções globais removidas | `get_item_link_for_navigation()`, definida dentro de `tainacan/view-mode-grid.php` e que imprimia `QUERY_STRING` sem escape. Um filho dificilmente a chama fora daquele arquivo. |
| Templates removidos | `single-items-metadata_new.php`, `single-items-metadata_old.php`, `single-items-attachments_new.php`, `single-items-attachments_old.php`. **Ver 4.1: quebra tema filho.** |

## 2. Bootstrap

- **Versão.** Bootstrap 4.6.1, completo, em toda página: CSS de 191 kB (29 kB compactado) e JS de 83 kB (22 kB compactado), mais o jQuery do WordPress.
- **Uso no código.** Os templates usam cerca de 150 classes utilitárias e de grade (`row`, `col-*`, `d-flex`, `mt-*`, `btn`, `dropdown-*`, `navbar*`, `media`, `input-group`…). Do JavaScript, o tema usa só `dropdown` (6 menus) e `collapse` (menu no celular).
- **Popper.** Não é carregado. Os três menus suspensos testados abrem sem erro.
- **Colisões com o Tainacan.** O Bootstrap tem `.collapse:not(.show)`, `.modal` e `.tooltip` globais, e o app Vue do Tainacan usa as mesmas classes (`#filters-modal.modal`, `.collapse` dos filtros, dicas v-popper). Hoje funciona porque o Tainacan põe `.show` e sobrescreve o resto, mas é um ponto frágil a cada versão do Tainacan.
- **Efeito sobre os blocos** (medido; o mesmo bloco com e sem o CSS do Bootstrap):
  - **Links no texto perdem o sublinhado** (`a { text-decoration: none }` do reboot). Só a cor separa o link do texto, o que fere o critério 1.4.1 do WCAG em texto corrido.
  - **Tabela do bloco perde as bordas** (de 1 px para 0), e o cabeçalho passa de centralizado para alinhado à esquerda.
  - Colunas, grupo de largura total, citação, lista e título: iguais com e sem.
- **Botões.** O `js.js` acrescenta `btn btn-jelly-bean` a todo `.wp-block-button a`. É herança do 2.x e passa a pele do tema por cima do botão do bloco.

**Recomendação.**
- **Não remover o Bootstrap na série 3.0.** Os templates dependem da grade e das utilitárias, e um filho também pode depender. Tirar seria quebra para tema filho e CSS adicional.
- **Corrigir os dois efeitos medidos, com CSS do próprio tema:**
  - sublinhado de link dentro de `.entry-content` / blocos de texto;
  - bordas da tabela do bloco.
- **Rumo de longo prazo (4.0, a combinar com a equipe do Tainacan):** um Bootstrap reduzido (grade, utilitárias, dropdown, collapse) ou a troca pelas utilitárias do próprio tema. O ganho fica entre 150 e 250 kB por página.

## 3. Blocos do Gutenberg

- **`theme.json`.** Versão 2, com `appearanceTools`, `layout` (contentSize/wideSize), tipografia e a Roboto local. O alinhamento largura total funciona: o grupo ocupa os 1366 px da janela.
- **Paleta.** Não está escrita no `theme.json`, mas **chega aos estilos globais**: o WordPress junta o `add_theme_support( 'editor-color-palette' )` aos dados do tema. Conferido no catalogacao: 12 cores em `wp_get_global_settings()` e as variáveis `--wp--preset--color--*` geradas. O `white` sai por ter o mesmo slug da paleta padrão do núcleo. Escrevê-la no `theme.json` congelaria a cor `default`, que segue a cor dos links do Personalizar, por isso ficou como está. (Correção feita na 3.0.3: a primeira versão deste estudo dizia que a paleta não aparecia.)
- **Editor.** `editor-style.css` (16 kB) é carregado no editor. Há `block-template-parts`: cabeçalho e rodapé podem vir de partes de bloco, se a opção do Personalizar estiver ligada.
- **Padrões.** Os padrões de bloco do tema estão registrados. A checagem `$wp_version >= '5'` compara textos; funciona, mas é frágil.
- **Botão do bloco:** ver 2.

**Recomendação.**
- Restringir o `btn btn-jelly-bean` aos botões **sem** cor escolhida no editor (`:not(.has-background)`), para a escolha do editor valer.

## 4. Temas filhos

### 4.1 Quebra: sobrescrita de `single-items-metadata_new.php` e `single-items-attachments_new.php`

No 2.10.0, `single-items-metadata.php` só escolhia entre `_new` e `_old`, e o conteúdo real estava no `_new`. Um filho que personalizou a ficha do item copiou o `_new`. Na 3.0 o conteúdo está no próprio `single-items-metadata.php`, então **a cópia do filho é ignorada sem aviso**. O mesmo vale para os anexos.

**Correção proposta.** No começo dos dois templates: se o tema filho tem o `_new` correspondente, usar o dele. Pouco código, e devolve o comportamento do 2.10.0.

### 4.2 Defeito herdado do 2.10.0: o `style.css` do filho não é carregado

- **O defeito.** `tainacan_enqueues()` registra `get_stylesheet_uri()` (o `style.css` do filho), mas quando existe `style.min.css` troca pelo minificado do pai. Resultado: fora do modo debug, o `style.css` do filho nunca entra.
- **Quem quebra.** Filhos feitos até a 2.9, que confiavam nisso (e faziam `@import` do pai), perdem o próprio CSS desde a 2.10.0.
- **O contrário.** Filhos que enfileiram o próprio `style.css` e também o do pai carregam o CSS do pai duas vezes: 125 + 143 kB.

**Correção proposta.**
- O pai continua no minificado (`tainacan_tainacanStyle`).
- Num tema filho, o `style.css` do filho entra depois, com handle próprio (`tainacan-interface-child`), a não ser que o filho já o tenha enfileirado; isso se confere no fim de `wp_enqueue_scripts`.
- Se o filho enfileirou o `style.css` do pai por conta própria, esse pedido é retirado, porque o minificado já cobre.

### 4.3 Filhos com cópias de templates do 2.10.0 que tinham falha de segurança

Um filho que copiou `archive-tainacan-collection.php`, `tainacan/archive-terms.php`, `template-parts/headertaxonomy.php` ou `tainacan/view-mode-grid.php` continua com o código antigo, que tem script injetável pela busca e link de compartilhar quebrável. A atualização do pai não alcança essas cópias.

**Proposta.** Um aviso no painel, só para administradores e só quando o filho tem uma dessas cópias. O aviso diz qual arquivo e qual falha, e sugere copiar de novo da versão 3. Nada é trocado sozinho.

### 4.4 O que já funciona com filhos

- **Ajustes do Personalizar.** São do filho, e a migração da atualização grava nos ajustes do tema ativo, então no filho.
- **Tradução.** O domínio é o mesmo; o filho que usa `tainacan-interface` continua traduzido.
- **`theme.json`.** O do filho se combina com o do pai. A fonte (`file:./assets/fonts/...`) é resolvida no pai quando o filho não tem o arquivo.
- **Recursos novos.** Cada um tem filtro (`tainacan_interface_*_enabled`), e o filho pode ligar ou desligar por código.
- **Filho que sobrescreve `tainacan/single-items.php`.** Fica sem a coluna lateral: basta trocar o bloco dos metadados por `tainacan_interface_the_item_metadata()`. Já documentado no README.

## Proposta de entrega (3.0.3)

1. **Tema filho.**
   - Usar os `_new` do filho (4.1).
   - Carregar o `style.css` do filho sem duplicar o do pai (4.2).
   - Aviso de templates antigos com falha (4.3).
2. **Blocos.**
   - Sublinhado dos links no texto.
   - Bordas da tabela do bloco.
   - Botão do bloco respeitando a cor escolhida.
3. ~~Paleta no `theme.json`~~: desnecessária, ver 3.

**Feito na 3.0.3** (CHANGELOG): os itens 1 e 2. O `style.css` do filho entra sempre depois do pai, inclusive em modo debug, e o pai é sempre o arquivo do pai. Quando o filho faz `@import` do pai, o handle do pai fica sem arquivo, para não haver duplicação.

Para testar, criar um tema filho de laboratório no catalogacao com três casos:
- um `_new` sobrescrito;
- um `style.css` próprio;
- uma cópia de `archive-tainacan-collection.php` do 2.10.0.
