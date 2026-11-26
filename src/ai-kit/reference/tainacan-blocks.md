# Blocos do Tainacan (1.4)

Resumo de `tainacan-blocks.json`, que traz o markup exato que o editor salva para
cada bloco. O markup foi gerado num site de teste e generalizado com os marcadores
`{{SITE_URL}}`, `{{COLLECTION_ID}}`, `{{COLLECTION_NAME}}` e `{{TAXONOMY_ID}}`.

| Bloco | Nome no editor (pt-BR) | Quem desenha | IA pode escrever? |
|---|---|---|---|
| `tainacan/carousel-items-list` | Carrossel de Itens | editor (JS) | não: use Query Loop + `is-style-tt2-carousel` |
| `tainacan/carousel-collections-list` | Carrossel de Coleções | editor (JS) | não: use Query Loop de `tainacan-collection` |
| `tainacan/carousel-terms-list` | Carrossel de Termos | editor (JS) | não |
| `tainacan/collections-list` | Lista de Coleções | editor (JS) | não: use Query Loop de `tainacan-collection` |
| `tainacan/dynamic-items-list` | Lista de Itens da Coleção | editor (JS) | não: use Query Loop dos itens |
| `tainacan/faceted-search` | Busca Facetada | editor (JS) | não: inserir pelo editor |
| `tainacan/facets-list` | Lista de Facetas | editor (JS) | não |
| `tainacan/item-submission-form` | Formulário de Submissão | editor (JS) | não: inserir pelo editor |
| `tainacan/search-bar` | Barra de Busca | editor (JS) | não: use `tainacan-interface/search` |
| `tainacan/terms-list` | Lista de Termos | editor (JS) | não |
| `tainacan/related-items-list` | Itens Relacionados | servidor | sim, numa página de item |
| `tainacan/item-gallery` | Galeria de Mídias do Item | servidor | sim, com contexto de item |
| `tainacan/item-metadata-sections` | Seções de metadados do item | servidor | sim, com contexto de item |
| `tainacan/item-metadata-section` | Seção de metadados do item | servidor | sim, dentro do anterior |
| `tainacan/item-metadata` | Metadados do item | servidor | sim, com contexto de item |
| `tainacan/item-metadatum` | Metadado do item | servidor | sim, com contexto de item |
| `tainacan/geocoordinate-item-metadatum` | Metadado de Geo Coordenadas | servidor | sim, com contexto de item |
| `tainacan/metadata-section-name` | Nome da seção de metadados | servidor | só dentro de `item-metadata-section` |
| `tainacan/metadata-section-description` | Descrição da seção | servidor | só dentro de `item-metadata-section` |
| `tainacan/items-gallery` | Galeria de Itens | servidor | evite: sem o editor não mostrou nada nos testes |

"Editor (JS)": o HTML salvo é montado pelo script do bloco no editor, com dados do
site (endereço da API, endereço de busca, ids). Escrito à mão, ele fica inválido ou
vazio. O Tainacan Interface refaz esses blocos quando a página é aberta no editor,
e então basta salvar.

## Variações do Query Loop

| Variação | `namespace` | `postType` |
|---|---|---|
| Coleções do Tainacan | `tainacan-collection` | `tainacan-collection` |
| Itens de uma coleção | `tainacan-items-tnc_col_<id>_item` | `tnc_col_<id>_item` |
| Taxonomias do Tainacan | `tainacan-taxonomy` | `tainacan-taxonomy` |

Exemplos completos em `docs/04-blocos.md` e em `starter-child/patterns/`.
