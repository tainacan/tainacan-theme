# Núcleo do Tainacan: índice

Referência do núcleo do Tainacan (1.0.3 e 1.4.1) com citações `arquivo:linha` relativas a `src/`, mais as lições do modelo de dados e da API interna. Dividida em três partes para leitura sob demanda; cada parte tem sumário próprio. **Δ** marca diferença 1.0.3 × 1.4.1.

- [Parte 1: fontes e arquitetura](nucleo-tainacan-parte-1.md) (seções 0 e A)
- [Parte 2: ganchos, REST, ferramental, desempenho, modelos de código](nucleo-tainacan-parte-2.md) (seções B a G)
- [Parte 3: lições do modelo de dados e da API](nucleo-tainacan-parte-3.md) (L-NUC-01 a L-NUC-40)

## Comece por aqui

- Verificações rápidas: [F. Lista rápida](nucleo-tainacan-parte-2.md#sF)
- Lições vistas em mais de um projeto: [Comece por aqui da parte 3](nucleo-tainacan-parte-3.md#comece)
- Mudanças 1.0.3 × 1.4.1: logs em tabela própria ([A.8](nucleo-tainacan-parte-1.md#a8)), `perpage<1` ([C.3](nucleo-tainacan-parte-2.md#c3)), `tainacan_get_the_document` ([A.14](nucleo-tainacan-parte-1.md#a14)), versões mínimas ([D.5](nucleo-tainacan-parte-2.md#d5))

## Sumário geral

**Parte 1**
- [0. Fontes, versões e como ler as citações](nucleo-tainacan-parte-1.md#s0)
- [A. Arquitetura](nucleo-tainacan-parte-1.md#sA)
  - [A.1 Carregamento do plugin](nucleo-tainacan-parte-1.md#a1)
  - [A.2 Entidades](nucleo-tainacan-parte-1.md#a2)
  - [A.3 Repositórios](nucleo-tainacan-parte-1.md#a3)
  - [A.4 Tipos de metadado](nucleo-tainacan-parte-1.md#a4)
  - [A.5 Opções de tipo de metadado (prefixo `_option_`)](nucleo-tainacan-parte-1.md#a5)
  - [A.6 Valores dos itens (item metadata)](nucleo-tainacan-parte-1.md#a6)
  - [A.7 Tipos de filtro](nucleo-tainacan-parte-1.md#a7)
  - [A.8 Logs](nucleo-tainacan-parte-1.md#a8)
  - [A.9 Capabilities e permissões](nucleo-tainacan-parte-1.md#a9)
  - [A.10 CPTs e taxonomias](nucleo-tainacan-parte-1.md#a10)
  - [A.11 Processos em segundo plano](nucleo-tainacan-parte-1.md#a11)
  - [A.12 Exposers e mapeadores](nucleo-tainacan-parte-1.md#a12)
  - [A.13 Blocos Gutenberg](nucleo-tainacan-parte-1.md#a13)
  - [A.14 Front-end: página do item e template tags](nucleo-tainacan-parte-1.md#a14)
  - [A.15 Busca facetada (Vue)](nucleo-tainacan-parte-1.md#a15)
  - [A.16 Páginas de admin (`\Tainacan\Pages`)](nucleo-tainacan-parte-1.md#a16)

**Parte 2**
- [B. API de ganchos para plugins](nucleo-tainacan-parte-2.md#sB)
  - [B.1 `tainacan_register_admin_hook`](nucleo-tainacan-parte-2.md#b1)
  - [B.2 Ganchos por item da busca facetada (JS)](nucleo-tainacan-parte-2.md#b2)
  - [B.3 Filtros `tainacan-item-metadata-get-value-as-html--type-*`](nucleo-tainacan-parte-2.md#b3)
  - [B.4 `tainacan-get-the-document` e outros filtros de front-end](nucleo-tainacan-parte-2.md#b4)
  - [B.5 Ações e filtros de persistência](nucleo-tainacan-parte-2.md#b5)
- [C. API REST](nucleo-tainacan-parte-2.md#sC)
  - [C.1 Namespace e controllers](nucleo-tainacan-parte-2.md#c1)
  - [C.2 Parâmetros de busca (`prepare_filters`)](nucleo-tainacan-parte-2.md#c2)
  - [C.3 Paginação (`perpage`)](nucleo-tainacan-parte-2.md#c3)
  - [C.4 `fetch_only` e `fetch_only_meta`](nucleo-tainacan-parte-2.md#c4)
  - [C.5 Como plugins acrescentam campos ou rotas](nucleo-tainacan-parte-2.md#c5)
  - [C.6 Facetas](nucleo-tainacan-parte-2.md#c6)
- [D. Ferramental de desenvolvimento do núcleo](nucleo-tainacan-parte-2.md#sD)
  - [D.1 Estrutura do repositório](nucleo-tainacan-parte-2.md#d1) · [D.2 Build](nucleo-tainacan-parte-2.md#d2) · [D.3 Testes](nucleo-tainacan-parte-2.md#d3) · [D.4 Lint](nucleo-tainacan-parte-2.md#d4)
  - [D.5 Versões mínimas](nucleo-tainacan-parte-2.md#d5) · [D.6 CI](nucleo-tainacan-parte-2.md#d6) · [D.7 Branches e PRs](nucleo-tainacan-parte-2.md#d7) · [D.8 i18n](nucleo-tainacan-parte-2.md#d8)
- [E. Armadilhas de desempenho conhecidas](nucleo-tainacan-parte-2.md#sE)
- [F. Lista rápida de verificações](nucleo-tainacan-parte-2.md#sF)
- [G. Modelos mínimos de código](nucleo-tainacan-parte-2.md#sG)
  - [G.1 Criar um item com valor de metadado](nucleo-tainacan-parte-2.md#g1)
  - [G.2 Ler a taxonomia de um metadado Taxonomy](nucleo-tainacan-parte-2.md#g2)
  - [G.3 Gancho de formulário no admin](nucleo-tainacan-parte-2.md#g3)
  - [G.4 Antes e depois de cada item na busca facetada (JS)](nucleo-tainacan-parte-2.md#g4)
  - [G.5 Listagem enxuta pela REST](nucleo-tainacan-parte-2.md#g5)

**Parte 3**
- [Comece por aqui](nucleo-tainacan-parte-3.md#comece)
- [Gravar valores e entidades](nucleo-tainacan-parte-3.md#gravar) (L-NUC-01 a 10)
- [Onde cada dado mora (leitura e SQL)](nucleo-tainacan-parte-3.md#onde) (L-NUC-11 a 24)
- [Identificar e configurar metadados](nucleo-tainacan-parte-3.md#identificar) (L-NUC-25 a 31)
- [Documento, anexos e miniatura](nucleo-tainacan-parte-3.md#anexos) (L-NUC-32 e 33)
- [Ciclo de vida, permissões e exclusão](nucleo-tainacan-parte-3.md#ciclo) (L-NUC-34 a 37)
- [Código do plugin e bugs de versão](nucleo-tainacan-parte-3.md#codigo) (L-NUC-38 a 40)
