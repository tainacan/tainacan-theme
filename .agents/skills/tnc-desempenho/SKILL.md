---
name: tnc-desempenho
description: Diagnosticar e otimizar desempenho em sites Tainacan com acervos grandes (dezenas de milhares de itens): listagens e facetas lentas, busca textual, contagens, importação em massa, cron, índice Elasticsearch/OpenSearch. Use para "está lento", "timeout", "memória esgotada", "otimize a consulta", "muitos itens", antes de mudar consulta em código que roda em listagem, ou ao planejar recurso para acervo grande.
---

# tnc-desempenho

Referência: `.agents/tainacan-stack/referencias/desempenho-e-escala.md` e `referencias/busca-e-indice.md`.

## 1. Medir antes de mudar

- Escolha uma medida reproduzível: tempo da requisição (`curl -w "%{time_total}"`), `X-Tainacan-Query-Time` da REST, número e tempo de consultas (Query Monitor ou `SAVEQUERIES` + `$wpdb->queries`), memória (`memory_get_peak_usage()`).
- Use o acervo real ou uma cópia de mesmo tamanho. Teste com 50 itens não prova nada.
- Para SQL lento: `EXPLAIN` da consulta capturada.

## 2. Suspeitos conhecidos do Tainacan

- **Listagem REST sem `fetch_only`**: cada item renderiza todos os metadados. Use `fetch_only` + `fetch_only_meta`.
- **Facetas com contagem**: uma `WP_Query` por valor de faceta; desligar com `tainacan_option_facets_enable_count_items` ou `TAINACAN_FACETS_DISABLE_COUNT_ITEMS`, ou responder pelo filtro `tainacan-fetch-all-metadatum-values`.
- **Busca textual padrão**: `LIKE '%termo%'` em `wp_postmeta` (sem índice). Com acervo grande, busque por índice externo (Elasticsearch/OpenSearch) e mantenha fallback SQL.
- **`meta_query` com `CAST`** (NUMERIC, DATE): anula índice. `meta_query` com LIKE: TNC-P03.
- **`perpage=-1` / `posts_per_page => -1`**: TNC-P01. Pagine em lotes de 100 a 500 e use `'fields' => 'ids'` quando bastar.
- **Consulta dentro de laço (N+1)**: TNC-P02. Carregue tudo antes, numa consulta (`post__in`, `IN (...)`), e use `update_meta_cache()`.
- **Registro de CPTs em todo `init`**: o núcleo busca todas as coleções e taxonomias a cada requisição; em repositório com muitas coleções, isso pesa em toda página.
- **Contagem total**: toda listagem paga `found_posts`. Onde o total não é exibido, `no_found_rows => true`.
- **Logs**: todo `insert()` gera log e diff. Em importação, `disable_logs()` e reative.
- **Cache invalidado por item**: em importação ou colheita, não derrube o cache de tudo a cada item; invalide por coleção, com debounce.

## 3. Mudar com segurança

- Uma otimização por vez, medindo depois com o mesmo comando.
- Cache: chave que inclui tudo que muda o resultado (usuário/papel, idioma, parâmetros); expiração; nunca guardar erro por muito tempo (TNC-W10).
- Processos longos: em lotes, retomáveis, com trava (transient) e limite de tempo; nada de apagar a fila global de background process.
- Índice externo: o índice é derivado; a fonte da verdade é o banco. Contagem que não bate: compare origem × banco × índice separadamente antes de culpar o índice.

## 4. Relato

Medida antes, medida depois (mesmo comando), o que mudou, efeito colateral possível (cache, consistência) e como reverter.
