# TNC-P03 · meta_query com LIKE/REGEXP (varredura completa de wp_postmeta)

**Severidade:** aviso (sempre) · **Categoria:** desempenho · **Arquivos:** .php

## Sintoma
Buscas e filtros por texto parcial ficam lentos (segundos ou dezenas de segundos) em acervos grandes, e o banco aparece sobrecarregado com consultas em `wp_postmeta`. Em servidor compartilhado, uma única busca pode atrasar os outros sites.

## Causa
No esquema padrão do WordPress, `wp_postmeta` só tem índices em `post_id` e em `meta_key`, nenhum em `meta_value` (WordPress 6.9.1, `wp-admin/includes/schema.php:150-158`). Mesmo em instalações que acrescentam um índice com prefixo de `meta_value`, `LIKE '%termo%'` e `REGEXP` não o usam, então o MySQL lê a tabela inteira, que num acervo do Tainacan tem milhões de linhas: cada valor de metadado é uma linha com `meta_key` igual ao ID do metadado (`classes/repositories/class-tainacan-item-metadata.php:62-109`). O próprio mecanismo de busca do Tainacan já usa `EXISTS (... meta_value LIKE '%termo%')` na busca textual (`classes/class-tainacan-search-engine.php:305-333`), o que mostra o custo desse padrão.

## O que a regra detecta
- Par `'compare' => 'LIKE'`, `'NOT LIKE'`, `'REGEXP'`, `'NOT REGEXP'` ou `'RLIKE'` (maiúsculas ou minúsculas) num enunciado que contém `meta_query` ou `metaquery`.
- Só roda em arquivos que mencionam `meta_query` ou `metaquery`.
- Não dispara para `compare` com `=`, `IN`, `>=` e outros operadores exatos.
- Não dispara quando o array com `compare` é montado em outro enunciado, separado do `meta_query` (por exemplo `$clausula = [ 'compare' => 'LIKE' ]` usado depois).
- Não analisa SQL escrito à mão com `meta_value LIKE`.

## Como corrigir
1. Com o Tainacan Index Manager (ou outro índice de busca), faça a busca textual pelo índice.
2. Sem índice, prefira comparação exata (`=`, `IN`) ou um termo de taxonomia, e sempre filtre por `meta_key`.
3. Se a busca parcial for indispensável, limite o conjunto antes (por coleção, por metadado, por data) e pagine.

```php
// errado
$args['meta_query'] = [ [ 'key' => $id_autor, 'value' => $termo, 'compare' => 'LIKE' ] ];

// certo (valor controlado, comparação exata)
$args['meta_query'] = [ [ 'key' => $id_autor, 'value' => $autor_escolhido, 'compare' => '=' ] ];
```

## Quando suprimir
Consulta sobre conjunto pequeno e já limitado (por exemplo, metas de um único item ou de uma coleção com poucas centenas de itens), ou ferramenta administrativa rara: `// tnc-check-ignore TNC-P03: busca restrita aos itens de um lote de importação`.

## Origem
Num plugin de dados interligados, consultas por `meta_value` sem `meta_key` faziam o banco varrer milhões de linhas, porque não havia índice que servisse a `meta_value` sozinho. A regra também segue a boa prática geral do WordPress sobre `meta_query`.
