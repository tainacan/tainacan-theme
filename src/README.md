# Tainacan Interface 3

A próxima versão do **Tainacan Interface**, o tema WordPress padrão do [Tainacan](https://tainacan.org). Não é um tema novo:
- mesma pasta (`tainacan-interface`) e mesmo domínio de tradução;
- mesmos ajustes do Personalizar, ganchos (`tainacan-interface-*`), classes CSS e caminhos de template da 2.10.0.

Um site atualiza da 2.x para a 3 como atualiza qualquer tema: ajustes, menus, CSS adicional, temas filhos e traduções do WordPress.org continuam valendo.

## O que muda

### Segurança (sempre ligado)

Corrige as falhas do 2.10.0:
- XSS na busca de termos, na busca de coleções e no nome de termo nos links de compartilhar;
- `?ref=` levando o "voltar à lista" para fora do site;
- CSS injetado pela cor da coleção;
- rascunhos e itens privados visíveis na navegação entre itens por `?status=`;
- filtros da navegação entre itens que respondiam sobre o que o visitante não vê: metadado privado, campo interno, autor;
- o "mostrar mais" das descrições, que recortava o HTML como texto e o interpretava de novo;
- entradas sem validação que derrubavam a página (`?termspaged=abc`).

A lista completa, com a linha de cada correção, está no CHANGELOG.

### Privacidade

A fonte Roboto vem no tema (`theme.json`). Antes ela era carregada do Google Fonts, e cada visita mandava o IP do visitante ao Google.

### Recursos novos, cada um com interruptor

Ficam em Aparência → Personalizar → **Tainacan Interface: listas e página do item**:

- **Prévia do item em caixa lateral:** nas listas, em todos os modos de visualização (inclusive a Tabela), um clique no item abre um painel com a mídia (imagem, vídeo e áudio tocam ali), a descrição e os metadados escolhidos. Cada metadado ganha a opção "Mostrar na caixa lateral" no formulário do Tainacan.
- **Visualizador de arquivos:** na página do item, no lugar do lightbox. Imagem com zoom e arrasto; vídeo e áudio; PDF num leitor próprio (PDF.js, sem CDN), que também substitui o leitor do navegador dentro da página.
- **Ficha em destaque:** cartões e ficha com hierarquia mais clara; número de registro e coleção sob o título.
- **Coluna lateral de itens relacionados:** ao lado dos metadados, com:
  - os itens ligados por metadado de relacionamento, nos dois sentidos;
  - os itens com o mesmo termo;
  - "Mais desta coleção", quando não há outra sugestão.

  As taxonomias usadas são escolhidas com a opção "Usar para sugerir itens relacionados" do metadado.

### Ao atualizar um site da 2.x

Os quatro recursos chegam **desligados**: a página fica igual, só com as correções. Um aviso no painel mostra o que há de novo e leva ao Personalizar. Em instalação nova, eles vêm ligados.

## Compatibilidade

- WordPress 6.5+, PHP 7.4+, Tainacan 1.x.
- **Temas filhos** da 2.x continuam funcionando.
  - Um filho que sobrescreve `tainacan/single-items.php` não recebe a coluna lateral: basta trocar o bloco dos metadados por `tainacan_interface_the_item_metadata();`.
  - Duas funções globais de 2.x que um filho poderia chamar continuam existindo, em `inc/compat.php`.
  - Se o filho personalizou a ficha do item nos arquivos que a guardavam no 2.10.0 (`single-items-metadata_new.php`, `single-items-attachments_new.php`), esses arquivos continuam sendo usados.
  - O `style.css` do filho é carregado depois do CSS do pai, sem duplicar o CSS do pai. No 2.10.0 ele deixava de ser carregado.
  - Se o filho tem cópias de modelos do 2.10.0 que ainda carregam uma falha de segurança, o painel avisa o administrador de quais arquivos são (`inc/child-theme.php`).
- **Plugins** que usam os ganchos `tainacan-interface-*` (Memorial COVID-19 Toolkit, Acervo em Cena) continuam funcionando. A caixa lateral e o leitor de PDF cedem a vez quando um plugin já faz o mesmo.

## Desempenho

Medido contra o 2.10.0 nas mesmas páginas:
- **Tempo de servidor:** igual (diferença dentro do ruído).
- **Consultas ao banco:** até 9 a mais na página do item, por causa da coluna lateral, e iguais na lista e na home.
- **No navegador:** +55 a 110 kB, quase tudo a Roboto, que antes vinha do Google.
- **Página de item com PDF:** carrega 1,3 s mais rápido, porque o PDF só é baixado quando chega perto da tela.

## Desenvolvimento

- `docs/`:
  - [estudo de impacto da atualização](docs/ESTUDO_IMPACTO_ATUALIZACAO.md);
  - [atualização sem troca de tema](docs/ESTUDO_ATUALIZACAO_SEM_TROCA.md);
  - [coluna lateral](docs/ESTUDO_RELACIONADOS_LATERAL.md).
- Verificação: PHPCS com o `phpcs.xml.dist` (WordPress Coding Standards) e `tnc-check` (regras da stack de IA do Tainacan), ambos sem nenhum aviso.
- Sass: `./compile-sass.sh`. O CSS compilado vai versionado.
