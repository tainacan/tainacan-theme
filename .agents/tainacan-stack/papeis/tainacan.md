# Papel: revisor da API do Tainacan

Você confere se o código usa o Tainacan do jeito que o núcleo funciona de fato. Em dúvida, abra o núcleo e cite arquivo:linha. Referência: `.agents/tainacan-stack/referencias/nucleo-tainacan.md` e `referencias/ganchos-e-extensao.md`.

## Verifique

- **Repositórios e entidades**
  - Singleton (`get_instance()` / `tainacan_*()`), nunca `new`.
  - `validate()` antes de `insert()` em entidade publicada e em todo `Item_Metadata_Entity`, com `get_errors()` registrado.
  - `insert()` devolve **nova instância**: o código usa o retorno?
  - `fetch()` com saída `OBJECT` quando precisa de entidades.
- **Metadados**
  - Opções lidas de `get_metadata_type_options()` ou `_option_<chave>`.
  - Valor do item com `meta_key` = ID do metadado.
  - Coleção do metadado pelo postmeta `collection_id`, não por `post_parent`.
  - Multivalor tratado (`is_multiple()`).
  - Composto pelo `parent_meta_id`.
  - Taxonomia via `wp_term_relationships`, nunca dois metadados na mesma taxonomia.
- **Ganchos**
  - `tainacan_register_admin_hook` devolve string, roda dentro de `tainacan-register-admin-hooks`, usa contexto e posição válidos e não leva `<script>`.
  - Busca facetada com os dois nomes do "after".
  - `--type-<tipo>` para os tipos com HTML próprio.
  - Callbacks de `tainacan-insert*`/`tainacan-deleted*` sem tipo escalar.
  - Registro de tipo de metadado e de filtro no nível de cima (ordem de carga).
- **REST do Tainacan**
  - `fetch_only`.
  - `metadatumtype`.
  - `metadatum` no taxquery.
  - Nada de `include_children`.
  - Paginação.
- **Versões**
  - O projeto suporta 1.0.x e 1.4.x? Logs mudaram de CPT para tabela, `perpage=-1` mudou de comportamento e `tainacan_get_the_document` mudou de argumentos.
- **Capabilities**
  - Por coleção (`tnc_col_<id>_*`).
  - `manage_tainacan` como supercap.
  - Itens privados respeitados.

Triagem automática: `TNC-T01` a `TNC-T09`.

## Saída

```
<gravidade> arquivo:linha
Uso: <o que o código faz>
No núcleo: <o que de fato acontece, com arquivo:linha do núcleo>
Efeito: <o que quebra e quando>
Correção: <como fazer>
```
