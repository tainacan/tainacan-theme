# Estudo: itens relacionados ao lado dos metadados

30/10/2026 · Tema Tainacan 2.0 (2.1.1)

## Pedido

No Tainacan Acervo em Cena, os itens relacionados ficam numa coluna à direita dos metadados. A pessoa termina de ler a ficha e já vê por onde continuar. No Tema Tainacan 2.0 eles ficam no fim da página, depois de tudo. O pedido é estudar como trazer essa coluna para o tema e deixá-la configurável.

## O que existe hoje

### Tema Tainacan 2.0, herdado do Tainacan Interface

- **Ordem da página do item** (`tainacan/single-items.php`):
  1. cabeçalho;
  2. bloco `.single-item-data-section`, com documento, anexos e metadados na ordem escolhida em `tainacan_single_item_layout_sections_order`;
  3. **itens relacionados**, numa largura só, embaixo;
  4. navegação e comentários.
- **A seção "Itens relacionados" é só a nativa do Tainacan** (`tainacan_the_related_items_carousel`). Ela mostra os itens que apontam para este item por um **metadado de relacionamento**, em carrossel, galeria ou modo de visualização, com 10 opções no Personalizar.
- **No catalogacao essa seção nunca aparece.** Nenhuma das 5 coleções tem metadado de relacionamento (medido em 30/10: 0 metadados do tipo Relationship). Hoje a página do item termina sem sugerir nada.

### O que o Acervo em Cena faz

- **Layout:** `.aec-body` é uma grade de duas colunas a partir de 960 px: ficha `minmax(0, 1fr)` e coluna de **320 px**, com 56 px de espaço entre elas. Abaixo de 960 px a coluna desce para depois da ficha. A coluna tem fundo próprio e cantos arredondados, e cada fio é uma grade de 3 miniaturas quadradas com título em 2 linhas.
- **Conteúdo:** não usa a seção nativa. São "fios" calculados pelo plugin (`includes/Front/Threads.php`), carregados por REST depois da página:

| Fio | Como calcula | Depende de |
|---|---|---|
| Semelhantes | produto escalar de vetores | índice próprio + vetores (IA local ou termos) |
| Também "termo" | termos em comum, o mais raro primeiro, até 2 fios | índice compacto da coleção |
| Mesma década | ano do item ± década | data extraída no índice |
| Por perto | distância ≤ 60 km | coordenadas no índice |

Os quatro fios saem de um **índice da coleção** que o plugin mantém (`IndexBuilder`). Os dois primeiros dependem também de vetores. Trazer isso para o tema contraria o objetivo de um tema enxuto: seria duplicar o motor do Acervo em Cena.

## O que dá para fazer no tema sem índice próprio

| Fonte | Custo por página | Vale para | Observação |
|---|---|---|---|
| **A. Termos em comum** ("Também em *Rio de Janeiro*") | 1 consulta por fio, só IDs, com LIMIT 7; contagem grátis por `term->count`; os termos do item já estão em cache porque a ficha os carregou | qualquer coleção com taxonomia | Fio mais útil. Prioriza o termo mais raro (`count` menor e > 1), que é o mais específico. |
| **B. Relacionamentos do Tainacan** | o que a seção nativa já custa (`$item->get_related_items()`) | coleções com metadado de relacionamento | Nos dois sentidos: quem aponta para o item (nativo) e para quem ele aponta (valores do próprio metadado). |
| **C. Mais desta coleção** | 1 consulta de IDs | sempre | Evita que a página termine sem caminho: só aparece se A e B não renderem nada. |
| D. Mesma década | 1 consulta com `meta_query` no metadado de data | coleções com data limpa | `meta_value` não tem índice por valor. Em 42 mil itens (Brasiliana) é varredura do metadado. Fica para depois e opcional. |
| E. Semelhantes / Por perto | — | — | **Fora.** É o que o Acervo em Cena faz; se ele estiver ativo, o tema não compete. |

**Dados do catalogacao (30/10):**
- a taxonomia mais útil tem 5 termos, com média de 9,8 e máximo de 36 itens;
- outra tem 144 termos, quase todos com 1 item, que é o próprio item, então não dão fio. O critério "count > 1" já descarta esses.

O fio A funciona bem nos dados reais. O B não tem o que mostrar lá.

## Proposta

### Layout

- A coluna fica **ao lado dos metadados**, não da página inteira: documento e anexos continuam largos, como hoje. O tema envolve a parte `single-items-metadata` numa grade `.tt2-item-body`: `minmax(0, 1fr)` mais a coluna `clamp(260px, 26vw, 340px)` a partir de 960 px.
- Abaixo de 960 px a coluna desce para logo depois dos metadados, não para o fim da página.
- Funciona com as 3 ordens de seção que existem (documento-anexos-metadados, metadados-documento-anexos, documento-metadados-anexos).
- **Colunas da ficha:** os metadados hoje podem estar em 2 ou 3 colunas no desktop (`tainacan_single_item_metadata_columns_count_*`). Com a coluna lateral, o tema limita a ficha a no máximo 2 colunas, senão cada coluna fica estreita demais.
- **Visual:** é o do Acervo em Cena, nas cores do tema: fundo suave, título "Continue explorando", fios com título e contagem, grade de 3 miniaturas e o link "Ver todos" para a lista da coleção já filtrada pelo termo.
- Opção de **coluna fixa ao rolar** (`position: sticky`), útil em fichas longas.

### Renderização

- **No servidor**, junto com a página, não por REST:
  - são poucas consultas baratas;
  - funciona sem JavaScript e é indexável;
  - não "pula" o layout quando carrega.
- Miniaturas com `loading="lazy"` e tamanho `thumbnail`, com `update_post_thumbnail_cache` para não fazer uma consulta por imagem.
- **Cache:** resultado guardado em cache de objeto por item (grupo próprio); onde houver cache persistente, por 12 horas. O cache é invalidado quando o item é salvo (`tainacan-insert-tainacan-item`). Sem cache persistente, cada visita custa as 2 ou 3 consultas.

### Segurança

- **Fios A e C:** só buscam itens **da mesma coleção** e com `post_status = publish`. Quem está vendo a página já pode ler a coleção, então nada de fora dela aparece.
- **Fio B:** relacionamentos podem cruzar coleções. Usa `get_related_items()`, que já passa pelo repositório do Tainacan e respeita a permissão de leitura. Os valores de saída são conferidos com `tema_tainacan_readable_item()`, a mesma regra da gaveta.
- Títulos com `esc_html`, URLs com `esc_url`. O link "Ver todos" é montado com `add_query_arg` sobre a URL da coleção, com taxonomia e termo validados (`taxonomy_exists`, `absint`).

### Configuração

**No Personalizar**, em Tema Tainacan 2.0 → Coluna lateral do item:

| Opção | Padrão | Observação |
|---|---|---|
| Mostrar coluna lateral ao lado dos metadados | desligado no tema, ligado no catalogacao | Não muda a cara de quem só atualiza o tema. |
| Lado | direita | direita / esquerda |
| Título da coluna | "Continue explorando" | vazio = sem título |
| Fontes, na ordem | relacionamentos, termos em comum, mais da coleção | caixas de marcar; a ordem é a da lista |
| Itens por fio | 6 | 3 a 9, em múltiplos de 3 por causa da grade |
| Máximo de fios de termos | 2 | 1 a 3 |
| Coluna fixa ao rolar | ligado | |
| Tirar a seção "Itens relacionados" do fim da página | ligado quando "relacionamentos" estiver na coluna | evita mostrar a mesma coisa duas vezes |

**No formulário de cada metadado de taxonomia**, a opção **"Usar para sugerir itens relacionados"** (Sim/Não), no mesmo padrão do "Mostrar na caixa lateral" da 2.0.2. Sem nenhum marcado, valem todas as taxonomias da coleção.
- **Por quê:** algumas taxonomias geram fios inúteis, como "Tipo de documento: Ofício" com 5 mil itens, e só quem cataloga sabe quais.
- **Custo:** um meta no metadado, sem consulta a mais.

**Para desenvolvedores:**
- filtro `tema_tainacan_item_aside_threads`, para acrescentar, tirar ou reordenar fios;
- filtro `tema_tainacan_item_aside_enabled`.

### Convivência

- **Acervo em Cena ativo na coleção:** ele monta a página do item à sua maneira, e o tema não imprime a coluna.
- **Gaveta lateral (2.0.2):** não é afetada, porque só existe nas listas.
- **Leitor de PDF (2.1):** não é afetado, porque fica no bloco do documento, fora da grade.

## Plano

| Fase | Entrega | Esforço |
|---|---|---|
| 1 | Grade ao lado dos metadados, fio A (termos) + fio C (coleção), Personalizar, cache, CSS, traduções | ~1 sessão |
| 2 | Fio B (relacionamentos nos dois sentidos) + opção "tirar a seção do fim" + opção por metadado de taxonomia | ~½ sessão |
| 3 (opcional) | Fio "mesma década" com o metadado de data escolhido por coleção | ~½ sessão, medir custo na Brasiliana antes |

**Teste de cada fase:**
- `tnc-check`, PHPCS e `/code-review medium`;
- no ar no catalogacao com o Chrome sem janela em 1366 e 390 px: a coluna fica ao lado e desce no celular; o fio não inclui o próprio item; "Ver todos" abre a lista filtrada; item de coleção privada não vaza para visitante;
- contagem de consultas por página com `SAVEQUERIES`, antes e depois.

## Decisões em aberto

1. **Padrão desligado** no tema e ligado só no catalogacao? Recomendo que sim: quem atualiza não é surpreendido.
2. **Fio C (mais da coleção) só quando não há outro fio**, ou sempre por último? Recomendo só quando não há outro: com fios bons, ele vira ruído.
3. **Fase 2 junto com a 1?** Recomendo não: o catalogacao não tem relacionamentos para testar. Antes disso, criar um metadado de relacionamento numa coleção de teste.
