---
name: tnc-diagnostico
description: Investiga bug sem causa conhecida em site ou plugin Tainacan/WordPress: tela branca, erro 500, REST quebrada, contagem que não bate, campo vazio, gancho que não aparece, lentidão súbita, comportamento diferente entre ambientes. Reproduz, isola, acha a causa raiz e verifica se o mesmo problema existe em outros plugins.
---

# tnc-diagnostico

## Princípio

Causa raiz antes de correção. Uma hipótese por vez, cada uma com um teste que a confirma ou derruba. Registre o que foi descartado: evita repetir caminho.

## 1. Reproduzir

- Escreva a reprodução mínima: URL, usuário/papel, dado de entrada, resultado esperado × obtido.
- Capture o erro real: `WP_DEBUG` + `WP_DEBUG_LOG` (`wp-content/debug.log`), console do navegador, aba Rede (resposta da REST, cabeçalhos `X-WP-Total`, `X-Tainacan-Query-Time`), `wp eval`.
- Compare ambientes: versão do WordPress, do Tainacan (1.0.x × 1.4.x mudam logs, `perpage` e `tainacan_get_the_document`), do PHP e lista de plugins ativos.

## 2. Consultar o que já se sabe

Antes de investigar do zero, procure o sintoma:
```
grep -rni "<palavra do sintoma>" .agents/tainacan-stack/licoes .agents/tainacan-stack/referencias
```
Sintomas clássicos e onde olhar:
- "headers already sent", redirect que não acontece, HTML no início da resposta: saída antes do cabeçalho (BOM, TNC-B03; gancho de admin que imprime, TNC-T01; espaço depois de `?>`).
- Campo de opção do metadado sempre vazio: TNC-T04.
- Gancho de admin ou da busca facetada que não aparece: TNC-T01, T02, T03, T06.
- Exceção "Entities must be validated": TNC-T05.
- REST inteira fora do ar depois de ativar um plugin: rota com `@` (TNC-W10), fatal em `rest_api_init`.
- Contagem diferente entre lista, filtro e índice: `referencias/busca-e-indice.md` (origem × banco × índice separadamente).
- Itens do Tainacan com 404 depois de ativar algo: regras de reescrita (`referencias/convivencia-entre-plugins.md`).
- Acentos quebrados: TNC-B03 e `referencias/ambiente-de-desenvolvimento.md`.

## 3. Isolar

- Conflito de plugins: desative todos menos o Tainacan e o suspeito; reative um a um. Rode `tnc-check --conflicts=wp-content/plugins` para colisões de nome, handle, rota, opção e ganchos sensíveis.
- Regressão: `git bisect` entre a última versão boa e a ruim, com a reprodução como teste.
- Dado: o problema está no dado gravado ou no código que lê? Leia o valor cru (`get_post_meta`, `wp db query` só leitura) antes de mexer no código.

## 4. Corrigir e provar

Siga `tnc-guardrails`: a reprodução do passo 1 vira o critério de aceite. Repita-a depois da correção e mostre o resultado.

## 5. Procurar o mesmo defeito nos outros plugins

Se a causa é um padrão de código, verifique os vizinhos:
```
php .agents/tainacan-stack/bin/tnc-check --workspace=<pasta-dos-plugins> --rules=<ID> --format=md
```
Sem regra para o padrão, use `grep` e proponha a regra em `tnc-registrar-licao`.

## Relato

Sintoma, causa raiz (arquivo:linha), por que só aparecia em certas condições, correção, prova, outros lugares afetados, lição registrada (sim/não).
