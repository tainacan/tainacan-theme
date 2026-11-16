# Migração de acervos para o Tainacan (índice)

Integridade da origem ao Tainacan: nunca alterar valores, reconciliar contagens, gravar pelo caminho certo e fechar com um checklist verificável (lições L-MIG-01 a L-MIG-54). Dividida em duas partes por tamanho; a numeração é contínua.

## Partes

- [`migracao-de-acervos-parte-1.md`](migracao-de-acervos-parte-1.md)
  1. Fidelidade à origem: L-MIG-01 a 05
  2. Reconciliação e verificação (origem × arquivo gerado × banco × índice): L-MIG-06 a 10
  3. Como o Tainacan grava o que você importa (entidades HTML, `wp_kses`, CSV, multivalor, composto): L-MIG-11 a 16
  4. Escrita em massa no banco (nunca `UPDATE`/`REPLACE` de texto em `wp_postmeta`): L-MIG-17 a 28
  5. Identidade e deduplicação: L-MIG-29 a 33
- [`migracao-de-acervos-parte-2.md`](migracao-de-acervos-parte-2.md)
  6. Coleta e importação retomável, anexos: L-MIG-34 a 38
  7. Datas e fusos: L-MIG-39 e 40
  8. Enriquecimento com dados ligados (Wikidata, Getty): L-MIG-41 a 43
  9. IA na catalogação: L-MIG-44 a 46
  10. Sistemas de origem (AtoM/EAD/OAI-PMH, DSpace, OJS, bancos legados): L-MIG-47 a 52
  11. Processo: L-MIG-53 e 54
  12. Checklist de importação (antes, durante, depois)

## Comece por aqui

- L-MIG-01: gerador de CSV alterando valores (caixa, espaços, duplicados).
- L-MIG-06: reconciliar contagens em cada etapa, separadamente.
- L-MIG-11 e L-MIG-12: o que o núcleo faz com `<`, `>` e `&` ao gravar.
- L-MIG-13: `encode = automatic` do importador CSV esvazia valores que não são UTF-8.
- L-MIG-17: `REPLACE` de texto em `wp_postmeta` corrompe PHP serializado.
- L-MIG-29: identidade pela marca no próprio item; duplicata visível é melhor que sobrescrita.
- Parte 2, seção 12: checklist de importação.
