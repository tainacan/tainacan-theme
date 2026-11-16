# Estudo: o 2.0 como atualização do Tainacan Interface, sem troca de tema

30/10/2026 · base: Tema Tainacan 2.0 2.2.1 e Tainacan Interface 2.10.0

> **Decidido em 30/10/2026:** o tema é a evolução do Tainacan Interface e será apresentado à equipe do Tainacan para virar a versão oficial, publicada pelo WordPress.org. Vale do estudo a parte sobre identidade (mesma pasta, domínio, ajustes) e o modo compatível na atualização, implementados na 3.0.0. **Não valem** o canal de atualização pelo GitHub (`Update URI`) nem a manutenção de um fork paralelo: a atualização é a oficial.

## Problema

Hoje o 2.0 é **outro tema**: outra pasta (`tema-tainacan-2-0`) e outro domínio de tradução. Para adotá-lo, um site precisa trocar de tema, e o [estudo de impacto](ESTUDO_IMPACTO_ATUALIZACAO.md) lista o que isso custa:
- ajustes do Personalizar e CSS adicional precisam ser importados;
- temas filhos precisam ser editados;
- as traduções do WordPress.org se perdem;
- volta-se atrás trocando de tema de novo.

O pedido é que o 2.0 chegue a quem usa o Tainacan Interface **como atualização**, sem que as pessoas troquem de tema.

## O que prende um site ao tema

No WordPress, a identidade de um tema é o **nome da pasta**, que é o mesmo nome dado ao tema no WordPress.org. Tudo o que um site acumula está pendurado nesse nome:

| O que o site tem | Onde está guardado | Chave |
|---|---|---|
| Ajustes do Personalizar, posições de menu | `wp_options` | `theme_mods_tainacan-interface` |
| CSS adicional | post `custom_css` | nome da pasta |
| Tema filho | `style.css` do filho | `Template: tainacan-interface` |
| Traduções (es_ES, fr_FR…) | `wp-content/languages/themes/` | domínio `tainacan-interface` |
| Atualizações automáticas | API do WordPress.org | nome `tainacan-interface` |
| Padrões de bloco | registro | `tainacan-interface/…` (o 2.0 já manteve) |
| Ganchos usados por plugins (Memorial Toolkit, Acervo em Cena…) | código | `tainacan-interface-*` (o 2.0 já manteve) |

**Conclusão:** só é "atualização" se o 2.0 for entregue **na pasta `tainacan-interface`, com o domínio `tainacan-interface`**. Aí ajustes, CSS, tema filho e traduções continuam valendo sem copiar nada, porque continuam sendo do mesmo tema.

## Caminhos possíveis

### A. O 2.0 como nova versão do próprio Tainacan Interface

O mesmo código do 2.0, empacotado na pasta `tainacan-interface`, com versão **3.0.0**, maior que a 2.10.0 oficial.

- **Instalar:** em Aparência → Temas → Adicionar → Enviar tema, envia-se o ZIP. O WordPress (desde a 5.5) detecta que o tema já existe e oferece **"Substituir o atual pelo enviado"**. É uma atualização: a pasta é trocada e todo o resto fica. Pela linha de comando: `wp theme install tainacan-interface-3.0.0.zip --force`.
- **Atualizações seguintes:** chegam pelo painel, como hoje. Um cabeçalho `Update URI` no `style.css` diz ao WordPress que as atualizações deste tema vêm do GitHub, não do WordPress.org; o núcleo dá suporte a isso para temas, com o filtro `update_themes_{hostname}`. Um verificador de poucas linhas lê o último release do GitHub uma vez a cada 12 horas e oferece a versão nova no painel.
- **O que continua valendo sem fazer nada:**
  - ajustes, menus, CSS adicional;
  - **temas filhos**, porque o `Template:` continua `tainacan-interface`;
  - **traduções do WordPress.org**: o pacote `tainacan-interface-es_ES.mo` segue sendo carregado, e o tema completa com as traduções próprias dos textos novos;
  - ganchos e padrões de bloco.
- **O que muda para quem mantém:**
  - **Divergência do oficial:** o site deixa de receber as versões oficiais do Tainacan Interface. Uma correção que a equipe do Tainacan publique precisa ser trazida para cá. É a rotina já proposta no estudo de impacto (item 8): comparar cada release oficial com a base.
  - **Proteção contra sobrescrita:** se o WordPress.org oferecer uma versão oficial maior que a nossa, ela sobrescreveria o 3.0. Dupla proteção: a versão 3.0.0 fica acima da série 2.x oficial, e o tema descarta, no próprio painel, a oferta do WordPress.org quando tem `Update URI`. Esse filtro é o mesmo que o WordPress usa para montar a lista de atualizações.
  - **Nome e identidade:** o tema aparece como "Tainacan Interface 3.0" (mantido por Marcos Sigismundo). Distribuir para outras instituições com o mesmo nome do tema oficial pede **conversa com a equipe do Tainacan**. É GPL, então juridicamente pode, mas convém combinar para não confundir quem procura suporte.
- **Voltar atrás:** enviar o ZIP oficial 2.10.0 com "Substituir o atual pelo enviado". O WordPress avisa que é uma versão menor e deixa seguir. Ajustes e tema filho não mudam. As opções gravadas nos metadados (`tema_tainacan_drawer`, `tema_tainacan_related`) ficam sem efeito.

### B. Os recursos do 2.0 como plugin, com o tema oficial intacto

Um plugin ("Tainacan Interface+") acrescenta os recursos ao tema oficial pelos ganchos que ele já tem. O site **não troca nem atualiza o tema**: instala o plugin.

| Recurso | Dá para fazer por plugin? |
|---|---|
| Gaveta de prévia nas listas | sim: JS, CSS e rota REST, independem do tema |
| Visualizador de arquivos e leitor de PDF | sim: filtros `get_tainacan_item_gallery` e `tainacan-get-the-document`, do Tainacan |
| Ficha em destaque | sim: CSS, classe no `body` e o gancho `…-after-title` |
| Coluna lateral | sim, sem gambiarra: a ação do núcleo `get_template_part_template-parts/single-items-metadata` abre a grade e `…-after-metadata` a fecha |
| Roboto local no lugar do Google Fonts | sim: tira da fila o `RobotoFonts` do tema e põe a cópia local |
| **Correções de segurança dos templates** | **só em parte**: um plugin consegue limpar a entrada (`?search`, `?ref`, `?status`, `?perpage`) antes de o tema ler, e limpar as cores da coleção na leitura. Mas o escape que falta *dentro* dos templates continua faltando. É uma mitigação, não a correção. |

- **A favor:**
  - zero troca ou atualização de tema;
  - o tema oficial continua recebendo as atualizações do WordPress.org, inclusive as de segurança da equipe do Tainacan;
  - funciona com qualquer tema filho.
- **Contra:**
  - as falhas de segurança do tema oficial ficam mitigadas, não corrigidas;
  - os recursos dependem de os ganchos do tema oficial continuarem iguais;
  - são dois produtos para manter (este tema e o plugin), ou o plugin vira a única fonte e o 2.0 deixa de ser tema.

### C. Levar o 2.0 para o tema oficial

Propor à equipe do Tainacan, no `tainacan/tainacan-theme`, primeiro as correções de segurança (já documentadas no relatório privado de 19/10) e depois os recursos, com interruptor e desligados por padrão.

- **A favor:** é o único caminho em que **todo mundo** recebe pelo WordPress.org, sem ZIP, fork ou plugin.
- **Contra:** o ritmo é o da equipe do núcleo. As PRs de 11/10 no plugin ainda aguardam revisão.

## Comparação

| | A. Nova versão do Interface | B. Plugin | C. Tema oficial |
|---|---|---|---|
| Troca de tema | não | não | não |
| Ajustes, CSS, tema filho, traduções | mantidos | mantidos | mantidos |
| Correções de segurança | completas | parciais (mitigação) | completas |
| Todos os recursos do 2.0 | sim | sim | depende da equipe |
| Atualizações do tema oficial | trazidas à mão | automáticas | automáticas |
| Primeira instalação | enviar o ZIP uma vez | instalar o plugin | nenhuma |
| Prazo | ~1 sessão | ~1,5 sessão | indefinido |
| Depende de terceiros | combinar o nome | não | sim |

## Proposta

**A agora, C em paralelo.** B fica só como saída para um site que não possa substituir o tema.

**Por que A:** é a única opção que é de fato uma atualização, mantém tudo o que o site tem e entrega as correções de segurança inteiras. O custo dela é trazer à mão as correções futuras do oficial, uma rotina que o 2.0 já precisava ter.

**Por que C em paralelo:** reduz esse custo com o tempo. Cada correção aceita no oficial é uma diferença a menos para manter.

### O que a proposta A muda no código

1. **Pasta e domínio:** o pacote sai com a pasta `tainacan-interface`, e o domínio de tradução volta a ser `tainacan-interface` (565 ocorrências, troca mecânica). A tradução de reserva da 2.2.0 deixa de ser necessária, porque o pacote do WordPress.org passa a valer direto; as traduções próprias completam o que ele não tem.
2. **Cabeçalho:** `Theme Name: Tainacan Interface`, `Version: 3.0.0`, `Update URI: https://github.com/marcossigismundo/tema-tainacan-2-0`. A descrição diz que é a edição mantida por Marcos Sigismundo.
3. **Verificador de atualização:**
   - lê o último release do GitHub, com cache de 12 horas;
   - oferece o ZIP do release;
   - descarta a oferta do WordPress.org para este tema;
   - o repositório precisa ser **público**, ou os ZIPs ficam num endereço público. Hoje ele é privado.
4. **Atualização sem surpresa ("modo compatível"):**
   - Num site que **já usava** o Tainacan Interface, a primeira carga da 3.0 grava os recursos visíveis (gaveta, visualizador, ficha em destaque, coluna lateral) como **desligados**. A página continua igual, só que com as correções de segurança e a Roboto local.
   - Um aviso no painel lista os recursos novos, com link para ligá-los no Personalizar.
   - Em **instalação nova**, os recursos vêm ligados.
5. **Correções e fonte:** ficam sempre ligadas; não têm interruptor.
6. **Importação de ajustes:** a importação na ativação (2.0/2.2) passa a valer só para quem vem de **outro** tema (Tainacan Acervo, tema filho) e para o catalogacao, que usa hoje a pasta `tema-tainacan-2-0`.
7. **Empacotamento:** um script gera `tainacan-interface-3.0.0.zip` a partir do repositório (`git archive` com prefixo `tainacan-interface/`). O `projetos.map` da VPS passa a publicar na pasta `tainacan-interface`.
8. **Migração do catalogacao:** instalar a 3.0 por cima do `tainacan-interface` que está lá, inativo. Os ajustes de hoje estão em `theme_mods_tema-tainacan-2-0` e são copiados uma vez. Ativar e conferir. O `tema-tainacan-2-0` fica inativo até tudo ser conferido.

**Testes antes de liberar:**
- num site com o Tainacan Interface 2.10.0 oficial e **um tema filho**: substituir pelo ZIP da 3.0 e conferir ajustes, menus, CSS, tema filho e a tradução es_ES. Confirmar que os recursos chegaram desligados e que o aviso aparece;
- conferir que o painel oferece a 3.0.1 vinda do GitHub e não oferece a versão do WordPress.org;
- voltar para a 2.10.0 oficial e conferir que o site fica como estava.

**Esforço:** cerca de 1 sessão para os itens 1 a 7 e mais meia sessão para o item 8 e os testes.

### O que a proposta C pede (em paralelo, com confirmação)

1. **Abrir o assunto com a equipe do Tainacan** antes de qualquer PR: apresentar a 3.0, combinar o nome e perguntar se aceitam as correções.
2. **PRs das correções de segurança:** no fork `marcossigismundo/tainacan-theme`, uma PR por correção, na mesma ordem do relatório privado. Pela regra, nada vai para o repositório oficial sem a sua confirmação. As falhas ainda não divulgadas seguem o caminho privado combinado no relatório.
3. **Depois, os recursos**, um por PR, com interruptor e desligados por padrão.

## Decisões em aberto

1. **Nome:** "Tainacan Interface" com a nota de edição mantida, ou um nome próprio na mesma pasta, por exemplo "Tainacan Interface 3 (Marcos Sigismundo)"? O nome exibido pode mudar sem quebrar nada; a pasta e o domínio não.
2. **Repositório público:** é necessário para as atualizações automáticas pelo GitHub. Alternativa: publicar só os ZIPs num endereço público, por exemplo na VPS.
3. **Abrir conversa com a equipe do Tainacan** antes de distribuir a 3.0 para outras instituições?
