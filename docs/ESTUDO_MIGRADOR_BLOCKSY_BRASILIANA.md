# Estudo: migrador Blocksy → Tainacan Interface, com a Brasiliana Museus como caso

Data: 08/11/2026. Base: Tainacan Interface 4.4.5 e a Brasiliana Museus (https://brasiliana.museus.gov.br/) lida só pelo lado público: HTML de 9 páginas, o CSS que o Blocksy gera a partir dos ajustes (`uploads/blocksy/css/global.css`, 228 regras) e capturas em 1440 e 390 px. Cerca de 30 pedidos, espaçados. Nada foi alterado em produção. Continua o [estudo de 07/11](ESTUDO_BLOCKSY_PARA_INTERFACE.md), que tratava dos sites da VPS.

## 1. Resposta curta

- **Dá para usar a Brasiliana na avaliação?** Para o "antes", sim, e já foi feito: o CSS gerado pelo Blocksy entrega quase todos os ajustes do Personalizar sem acesso ao painel. Para o "depois", não: o tema não pode ser trocado em produção. É preciso uma cópia no laboratório da VPS (seção 6).
- **Integridade completa do leiaute é possível?** Para tudo o que não é página do Tainacan (cabeçalho, rodapé, início, curadorias, museus, blog, posts, páginas), sim, com duas condições:
  - o migrador gera uma camada de compatibilidade;
  - o Interface ganha cinco recursos que hoje não tem (seção 4).
  Só o mapeamento de opções do estudo de 07/11 não basta: a Brasiliana personaliza muito mais que os acervos da VPS.
- **Lista e página do item:** aqui "integridade completa" contradiz o motivo da troca. O ganho do Interface está justamente nessas duas páginas. A recomendação é ficar com as do Interface vestidas com a identidade da Brasiliana (seção 5).

## 2. O que a Brasiliana usa

| Peça | Versão / uso |
|---|---|
| Tema | Blocksy 2.1.61, sem tema filho |
| Plugins ligados ao leiaute | Blocksy Companion, Tainacan Support for Blocksy (`tainacan-blocksy`), Greenshift (página inicial), Getwid (curadorias), `brasiliana-lod` (página do item), Tainacan Item Print, Newsletter |
| Tipos de conteúdo com ajustes próprios no Blocksy | página, post, blog, `curadorias`, `museu`, `ema-post`, taxonomia do Tainacan, coleção 5658688 (lista e item), busca, autor (22 contextos `data-prefix`) |
| Barra gov.br e régua de logotipos | scripts externos (`analitica.museus.gov.br/.../barra-ibram` e `regua-ibram`) no `<head>`, independentes do tema, com uma ressalva (seção 7) |

Os ajustes realmente personalizados (o resto é o padrão do Blocksy repetido por contexto):

- **Paleta de 27 cores** (`--theme-palette-color-1..27`). As cores 1 a 8 apontam para as 9 a 27. Bordôs #a03c50 → #381b31, beges #ece7dc / #e7dfd1, dourado #f8e29e, cinzas.
- **Três fontes do Google:**
  - Raleway no texto (16 px, altura 1,6, espaço entre letras 0,3 px);
  - Agdasima nos títulos (h1 40 px maiúsculo, h2 35, h3 30, h4 25 maiúsculo);
  - Barlow Condensed 700 no nome do site (38 px, maiúsculo, dourado #ecd297).
- **Cabeçalho:**
  - uma linha de 60 px (70 px no celular) em #61283d, com logotipo e nome, menu (12 px, 700, maiúsculo, branco, passa a dourado) e busca e menu em dourado;
  - busca em tela cheia com fundo branco a 96%;
  - menu lateral escuro de 500 px (65 vw no celular).
- **Rodapé:** duas faixas vazias, uma bordô (espaçamento de 70 px) e outra #212121, e depois a régua de logotipos.
- **Contêiner:** 1290 px, texto estreito 750 px, recuo largo de 130 px, espaço vertical de 60 px.
- **Botões:** raio de 8 px, fundo #8b354a que passa a #a03c50, altura mínima de 40 px.
- **Blog:** cartões em 3 colunas (2 no tablet), fundo branco, raio de 5 px, sombra suave, título de 25 px. Banner do título em #772f44, título dourado de 45 px.
- **Curadorias:**
  - banner em fundo #ece7dc com véu de cor sobre a imagem, título dourado de 35 px;
  - compartilhar com botões cheios;
  - posts relacionados em 4 colunas sobre bege;
  - comentários sobre bege.
- **Museus:**
  - artigo em caixa branca com sombra, imagem destacada com cantos inferiores de 20 px;
  - banner bege com título bordô;
  - relacionados em 4 colunas;
  - compartilhar cheio.
- **Taxonomia do Tainacan:** banner com imagem e véu bordô a 40%, título de 50 px.

## 3. O que o mapeamento do estudo de 07/11 já cobre

| Ajuste | Opção do Interface |
|---|---|
| Cores-base | principal, destaque, texto, fundo, cartões |
| Corpo do texto | tamanho, altura da linha, espaço entre letras |
| Medidas | largura da página e largura estreita |
| Botões | forma e cor |
| Blog | cartões em 2 a 4 colunas, proporção, resumo |
| Navegação | trilha, voltar ao topo |
| Redes e rodapé | redes sociais, copyright |

Isso é **cerca de metade** do que se vê na Brasiliana. O resto cai nas seções 4 e 5.

## 4. O que falta para a integridade completa

### 4.1 Camada de compatibilidade (o migrador gera; não exige opção nova)

**Paleta do Blocksy dentro do conteúdo: obrigatório.**
- As páginas usam as classes `has-palette-color-N-color` e `-background-color`: 8 cores diferentes na página inicial, 3 em cada curadoria, 3 em "Sobre".
- O CSS do Greenshift na página inicial usa `var(--theme-palette-color-N)` (7 cores).
- Sem o Blocksy, essas variáveis e classes deixam de existir, e as faixas bordô da página inicial ficam sem fundo.
- O migrador precisa gravar as 27 variáveis e as classes, com os valores do site.
- São cerca de 3 kB de CSS gerado e guardado (não CSS adicional do usuário).

**A conferir no ensaio:**
- **Largura total e larga** (`alignfull`): 35 blocos na página inicial e 5 numa curadoria.
- **Getwid:** o Blocksy traz um CSS próprio para ele (`ct-getwid-styles`), que deixa de vir.

### 4.2 Recursos novos no Interface

| Recurso | Por que a Brasiliana precisa | Tamanho |
|---|---|---|
| **Fonte própria** (envio de arquivo ou importação de uma fonte do Google para `uploads`, servida localmente) | Raleway, Agdasima e Barlow Condensed não estão entre as 5 do tema; pela regra de não usar CDN, o migrador baixa os arquivos (licença OFL permite) e o tema os serve | médio |
| **Título por tipo de conteúdo** (fundo, cor e tamanho do título, véu sobre a imagem, alinhamento) | blog, curadorias, museus e taxonomia têm banners diferentes; o título editorial da 4.4 é um só para tudo | médio |
| **Posts relacionados** (colunas, fundo) | curadorias e museus em 4 colunas | pequeno |
| **Artigo em caixa por tipo de conteúdo** e cantos da imagem destacada | museus | pequeno |
| **Rodapé em duas faixas** de cor própria; fonte e cor do nome do site; cor dos ícones do cabeçalho | cabeçalho e rodapé idênticos | pequeno |

Recursos que servem a qualquer site que venha do Blocksy, não só à Brasiliana.

### 4.3 Diferença que fica, por desenho

A busca em tela cheia e o menu lateral escuro do Blocksy dariam lugar aos do Interface, recoloridos. Reproduzi-los é possível, mas não vale o código.

## 5. Lista e página do item

**Hoje, a lista** vem do Blocksy com o `tainacan-blocksy`:
- uma caixa branca com título, trilha, descrição e barra de busca;
- filtros numa caixa à esquerda;
- fichas em 3 colunas.

**Hoje, o item** é desenhado pelo `brasiliana-lod` (classes `blod-*`) sobre o leiaute "dam" do `tainacan-blocksy`:
- imagem à esquerda e cartão à direita, com a proveniência;
- metadados em cartões, "Sobre a técnica" e "Relacionados no acervo".

**Com o Interface:**
- **Lista:** passa à do Interface, com a prévia lateral e os filtros revisados no tablet na 4.4.
- **Item:** os blocos de LOD continuam pelo **tainacan-lod**, sucessor do brasiliana-lod e independente do Blocksy. Eles entram pelos filtros de seção do Tainacan, como já roda no catalogacao. O arranjo do topo da página muda.

**Recomendação:** identidade da Brasiliana (cores, fontes, cabeçalho, rodapé) sobre as páginas do Interface. Copiar a lista e o item do Blocksy anularia o ganho da troca.

**Decisão do coordenador:** se o topo do item deve manter o arranjo atual (imagem à esquerda, cartão de proveniência à direita). Se sim, isso é trabalho do tainacan-lod (um leiaute para o Interface), não do migrador.

## 6. Como montar o "depois" sem tocar em produção

1. **Laboratório `brasiliana-lab` na VPS.** Mesma pilha:
   - WordPress 7.1.3, Tainacan 1.4, Blocksy 2.1.61 e Companion;
   - tainacan-blocksy, Greenshift, Getwid e tainacan-lod.
2. **Conteúdo:**
   - **Pelo coordenador:** em Ferramentas → Exportar → "Todo o conteúdo" (páginas, posts, curadorias, museus e menus com a marcação dos blocos). Mais os ajustes do tema, com o comando `wp option get theme_mods_blocksy --format=json` na instância de testes dele, ou uma cópia do banco dela.
   - **Itens:** uma amostra de cerca de 500 vem pela API pública com o importador que já existe (`lab/import-from-tainacan.php` do Acervo em Cena).
3. **Conferência do "antes":** o `global.css` gerado no laboratório deve ser **igual byte a byte** ao de produção. Isso prova que os ajustes foram reproduzidos. Depois, as capturas das 8 páginas devem bater com as de produção.
4. **Migração e medida:**
   - trocar para o Interface com o migrador;
   - capturar de novo em 1440, 820 e 390 px;
   - comparar por região (cabeçalho, título, conteúdo, rodapé), com diferença de pixels e tolerância declarada;
   - voltar ao Blocksy para provar que a reversão funciona.

Sem a exportação, dá para reconstruir os ajustes a partir do `global.css` público (o próprio teste byte a byte diz quando acertou). Mas o conteúdo da página inicial e das curadorias viria só renderizado, sem a marcação dos blocos, e a medida perderia valor.

## 7. Achados em produção, fora do tema

1. **Régua de logotipos fora do lugar.** O `regua-ibram/embed.js` procura `document.querySelector('footer')` antes de `.ct-footer` e `#footer`, e pega o **primeiro** `<footer>` da página:
   - **no item,** cai dentro do `footer.blod-explica__rodape` do brasiliana-lod, com altura 0, invisível;
   - **num post com comentário,** cai dentro do rodapé do comentário (`footer.ct-comment-meta`).

   Visto no DOM de "Ladeira do Castelo" e de "O Manto Tupinambá no Brasil". Correção: inverter a ordem no script, `#footer` e `.ct-footer` primeiro e depois o último `footer` da página. O Interface tem o mesmo risco (`template-parts/single-post.php` usa `<footer>` dentro do post), então a correção no script resolve os dois temas.
2. **Menu "Acervos"** aponta para `/acervos`, que não é uma página. O WordPress adivinha e redireciona para o post "Acervos digitais de cultura: hóspedes indesejados…".
3. **Blocos vazios** vistos na captura:
   - "Esta curadoria conta com itens dos seguintes museus: No terms found" em Curadorias → Pintura;
   - "Neste museu: No items found" em Museu do Ouro.

   Além de vazios, aparecem em inglês.

## 8. Estimativa

| Etapa | Prazo |
|---|---|
| Laboratório com a exportação e conferência byte a byte | 1 dia |
| Migrador: mapeamento, camada de compatibilidade, importação das fontes e relatório do que não veio | 2 a 3 dias |
| Recursos novos do Interface (4.2) | 4 a 6 dias |
| Ensaio, comparação por captura e ajustes | 2 dias |
| **Total** | **cerca de 2 semanas** |

**Forma de entrega:** o migrador como **plugin à parte**, ativado só na troca. Ele lê `theme_mods_blocksy`, mostra o que vai trazer, pede confirmação e grava. Assim o tema fica sem código que só serve uma vez, e a regra do diretório de temas fica atendida com folga. Os recursos da 4.2 entram no tema, porque servem a qualquer site.
