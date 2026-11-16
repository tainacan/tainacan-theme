# Ganchos e extensão do Tainacan (índice)

Referência de como estender o Tainacan e de como montar a infraestrutura WordPress de um plugin que o estende, organizada por ponto de extensão, com as armadilhas de cada um (lições L-EXT-01 a L-EXT-79). Dividida em duas partes por tamanho; a numeração é contínua.

## Partes

- [`ganchos-e-extensao-parte-1.md`](ganchos-e-extensao-parte-1.md): pontos de extensão do Tainacan.
  1. Ganchos de formulário do admin (`tainacan_register_admin_hook`): L-EXT-01 a 08
  2. Páginas de admin (`\Tainacan\Pages`) e menu: L-EXT-09 a 11
  3. Registros: tipos de metadado e filtro, importadores, exportadores, processos genéricos, opções `_option_`: L-EXT-12 a 14
  4. Página do item: valor como HTML (`tainacan-item-metadata-get-value-as-html--type-*`) e ganchos de seção: L-EXT-15 a 20
  5. Documento do item (`tainacan-get-the-document`), galeria e tema: L-EXT-21 a 24
  6. Busca facetada (ganchos JS `wp.hooks` e modos de visualização): L-EXT-25 a 27
  7. REST do Tainacan (`tainacan/v2`, `prepare_filters`): L-EXT-28 a 34
  8. Exposers e saída XML/RDF: L-EXT-35 a 37
- [`ganchos-e-extensao-parte-2.md`](ganchos-e-extensao-parte-2.md): infraestrutura WordPress do plugin.
  9. Ganchos de persistência (`tainacan-insert` e afins) e efeitos colaterais: L-EXT-38 a 43
  10. Opções e configuração do plugin (versão do mapa, Settings API): L-EXT-44 a 52
  11. REST do WordPress em plugins (`permission_callback`, `rest_url`, rotas): L-EXT-53 a 64
  12. Boot, ordem de carga e infraestrutura (Throwable, shortcodes, rewrite, WP-CLI): L-EXT-65 a 79

## Comece por aqui

| Lição | Assunto | Parte |
|---|---|---|
| L-EXT-01 | Callback de `tainacan_register_admin_hook` devolve string; registrar em `tainacan-register-admin-hooks` | 1 |
| L-EXT-02 | `<script>` inline em HTML injetado por `v-html` não executa | 1 |
| L-EXT-09 | `\Tainacan\Pages`: singleton e `load_page()` manual | 1 |
| L-EXT-15 | Filtro genérico de valor como HTML não roda para tipos com HTML próprio | 1 |
| L-EXT-17 | Tipo URL faz autoembed sozinho | 1 |
| L-EXT-25 | Gancho "depois do item" da busca facetada: registrar os dois nomes | 1 |
| L-EXT-28 | REST do Tainacan descarta `include_children`, `field` e `type` | 1 |
| L-EXT-38 | Callback tipado em `tainacan-insert` derruba a gravação | 2 |
| L-EXT-44 | Mapa de configuração em opção precisa de versão | 2 |
| L-EXT-53 | `permission_callback` público, devolvendo `WP_Error` | 2 |
| L-EXT-54 | `rest_url()` no `wp_localize_script` duplica a URL | 2 |
| L-EXT-65 | Capturar `\Throwable`, registrar, não engolir | 2 |

## Onde procurar outros assuntos

- Modelo de dados, repositórios, `validate()` e valores de metadado: `nucleo-tainacan.md`.
- Permissões, nonces, `__return_true` em rotas, status privados: `seguranca-wordpress.md`.
- Facetas, índice de busca, `tainacan-api-get-items-alternate`: `busca-e-indice.md`.
- Vue, Buefy e CSS do admin e da busca facetada: `front-end-e-vue.md`.
- Colisões entre plugins e remoção de ganchos de terceiros: `convivencia-entre-plugins.md`.
