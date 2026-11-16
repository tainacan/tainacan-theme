# TNC-T07 · Parâmetro de busca que a REST do Tainacan descarta

**Severidade:** aviso (sempre) · **Categoria:** tainacan · **Arquivos:** .js, .jsx, .ts, .tsx, .vue, .php

## Sintoma
Um filtro montado pelo plugin na URL da REST do Tainacan não tem o efeito esperado, sem erro: o filtro por termo continua trazendo itens dos termos filhos, o tipo de comparação pedido (por exemplo `NUMERIC`) é ignorado, o campo do termo informado é ignorado. As contagens do plugin não batem com a lista.

## Causa
`REST_Controller::prepare_filters()` copia, de cada cláusula, só as chaves de uma lista fixa (`classes/api/class-tainacan-rest-controller.php:131-161`, cópia em `prepare_meta()`, `:280-330`). No `taxquery`, aceita `taxonomy`, `metadatum` (que vira `field`), `terms`, `operator` e `relation`: `include_children` e `field` são descartados. Sem `include_children`, a `WP_Tax_Query` usa o padrão do WordPress (`true`) e o filtro inclui os filhos. No `metaquery`, aceita `key`, `value`, `compare`, `relation` e `metadatumtype` (que vira `type`); `type` é descartado. Além disso, a listagem de itens sobrescreve o `type` do primeiro nível pelo tipo do filtro cadastrado para o metadado (`classes/api/endpoints/class-tainacan-rest-items-controller.php:697-707`).

## O que a regra detecta
Verificação por linha, em arquivos que mencionam `taxquery` ou `metaquery`:
- `include_children` numa linha que contém `taxquery` (por exemplo `taxquery[0][include_children]=0`, ou na mesma linha de um array PHP).
- `taxquery[..][field]`.
- `metaquery[..][type]`.
- Não dispara para `metadatum`, `metadatumtype` e as demais chaves aceitas.
- Não dispara para `tax_query`/`meta_query` do `WP_Query` (com sublinhado), que aceitam essas chaves.
- Não acompanha estruturas em várias linhas: um array PHP ou objeto JavaScript com `taxquery` numa linha e `include_children` em outra não é acusado.

## Como corrigir
1. Troque `field` por `metadatum` e `type` por `metadatumtype`.
2. Para excluir filhos, filtre pelos IDs exatos dos termos desejados (`operator=IN`) ou use um índice de busca que trate isso, como o Tainacan Index Manager.
3. Em contagens feitas por SQL que precisam bater com a lista filtrada pela REST, considere os filhos (por exemplo com `get_term_children()`).

```js
// errado
'?taxquery[0][taxonomy]=tnc_tax_9&taxquery[0][terms][]=55&taxquery[0][include_children]=0'
'?metaquery[0][key]=12&metaquery[0][value]=1900&metaquery[0][type]=NUMERIC'

// certo
'?taxquery[0][taxonomy]=tnc_tax_9&taxquery[0][terms][]=55&taxquery[0][operator]=IN'
'?metaquery[0][key]=12&metaquery[0][value]=1900&metaquery[0][compare]=>=&metaquery[0][metadatumtype]=NUMERIC'
```

## Quando suprimir
Quando a URL não vai para a REST do Tainacan, e sim para um endpoint próprio do plugin que aceita essas chaves: `// tnc-check-ignore TNC-T07: endpoint próprio do plugin, não passa por prepare_filters`.

## Origem
Num plugin de busca segmentada, a contagem por termo direto não batia com a lista porque a REST descartava `include_children` e o filtro por termo incluía os filhos. Um plugin de biblioteca registrou o mesmo cuidado ao montar filtros reais do Tainacan pela URL.
