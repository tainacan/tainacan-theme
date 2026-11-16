---
name: tnc-migracao-acervo
description: Planeja e executa migração ou importação de acervo para o Tainacan (de bancos legados, planilhas, DSpace, OJS, AtoM, CDS/ISIS, sistemas próprios) com integridade verificável: extração fiel, mapeamento de metadados, CSV de importação, anexos, reconciliação de contagens. Use para "migrar", "importar", "gerar CSV para o Tainacan", "mapear campos", ou quando uma importação diz sucesso mas os dados chegam vazios ou alterados.
---

# tnc-migracao-acervo

Referência: `.agents/tainacan-stack/referencias/migracao-de-acervos.md`.

Roteiro completo com critério de passagem por fase, modelos (inventário, mapa de campos, decisões, dicionário de correções, reconciliação, plano de reimportação, checklist da virada) e casos reais: pasta `migracao/` do repositório `tainacan-stack-ia` (não é instalada nos projetos).

## Princípio inegociável

O valor da origem chega intacto. Nenhuma normalização silenciosa: caixa, espaços, pontuação, duplicados, datas e acentos ficam como na origem, a menos que a regra de transformação tenha sido **decidida e registrada** com o responsável pelo acervo. Toda transformação tem uma coluna de origem conservada para conferência.

## Fluxo

1. **Inventário da origem**: tabelas ou campos, contagem de registros por entidade, tipos, campos multivalorados, hierarquias, anexos (quantidade, tamanho, formatos). Somente leitura na origem.
2. **Mapa de metadados**: origem → coleção/metadado do Tainacan, tipo do metadado, obrigatório, multivalor, taxonomia (nova ou existente), vocabulário controlado. Decisões em aberto ficam listadas para o responsável; não invente.
3. **Extração**: script reproduzível (versionado), lendo da origem e gravando arquivos intermediários sem tocar nos valores. Codificação conferida (origem Latin-1/CP1252 → UTF-8 sem perda; teste com amostra acentuada).
4. **CSV de importação**: separador de multivalor igual ao configurado no importador; campo composto e relacionamento no formato que o importador aceita; IDs especiais (`special_document`, `special_attachments`, `special_item_status`) conferidos na versão do Tainacan em uso.
5. **Ensaio**: importe uma amostra estratificada (registros com todos os casos: vazio, multivalor, acento, hierarquia, anexo grande) num ambiente de testes. Compare campo a campo com a origem.
6. **Importação completa** em ambiente de testes, depois em produção, com logs desligados durante a carga (`disable_logs()`), em lotes.
7. **Reconciliação**: contagem por coleção, por metadado preenchido e por termo, de cada lado: origem × CSV × banco do Tainacan × índice de busca (se houver). Diferença tem que ter explicação registrada.

## Armadilhas

- Importação que diz "sucesso" com campos vazios: entidade sem `validate()` (exceção engolida), string enviada a metadado múltiplo sem tratar, `explode` ignorando o separador real de `value_as_string`.
- Taxonomia: valor "7" vira termo chamado "7" se o importador recebeu o ID como nome.
- Duas metadata na mesma taxonomia se apagam: `wp_set_object_terms` de uma remove os termos da outra.
- `UPDATE` em massa em `wp_postmeta` para "corrigir" depois corrompe serializado (TNC-P04). Corrija na origem e reimporte, ou use a API.
- Anexos: confira tamanho máximo de upload, tempo de execução e espaço em disco antes.

## Entrega

Planilha ou relatório de reconciliação, lista de decisões de transformação com quem decidiu, scripts versionados, e o que ficou pendente.
