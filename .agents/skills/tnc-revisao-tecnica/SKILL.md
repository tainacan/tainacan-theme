---
name: tnc-revisao-tecnica
description: Revisão técnica de código Tainacan/WordPress sobre diff, PR, branch, commit ou módulo inteiro, com o tnc-check como triagem e revisores por papel (segurança, desempenho, API do Tainacan, convivência). Use para "revise", "code review", "analise este PR", "auditoria", "security review", ou antes de aprovar merge.
---

# tnc-revisao-tecnica

## Entrada

Defina o alvo e escreva-o no topo do relatório: PR (`gh pr diff <n>`), branch contra a base (`git diff origin/develop...HEAD`), commits, arquivos ou um módulo inteiro. Sem alvo claro, pergunte.

## 1. Triagem automática

```
php .agents/tainacan-stack/bin/tnc-check --diff=origin/develop --format=md
```
Para módulo inteiro: `tnc-check <pasta> --format=md --no-baseline`. Saída de ferramenta é **triagem**, não veredito: cada achado será confirmado ou descartado na leitura.

## 2. Leitura por papel

Leia o diff completo e o contexto de cada trecho alterado (a função inteira e quem a chama). Aplique os papéis em `.agents/tainacan-stack/papeis/`:

| Papel | Arquivo | Quando |
|---|---|---|
| Segurança | `seguranca.md` | sempre que houver entrada, saída, SQL, REST, AJAX, upload, permissão |
| Desempenho | `desempenho.md` | consultas, laços, listagens, importação, índice |
| API do Tainacan | `tainacan.md` | uso de entidades, repositórios, ganchos, REST do Tainacan |
| Convivência | `convivencia.md` | ganchos globais, CSS/JS, assets, opções, nomes globais |
| Release | `release.md` | mudança de versão, migração, desinstalação |

| WordPress.org | `wordpress-org.md` | plugin que vai ao diretório, ou sempre que houver echo, entrada, SQL, i18n, enqueue, readme |
| Correção | `correcao.md` | sempre: lógica, casos-limite, regressão, concorrência |

No Claude Code, os papéis existem como subagentes (`tnc-revisor-seguranca`, `tnc-revisor-desempenho`, `tnc-revisor-tainacan`, `tnc-revisor-convivencia`, `tnc-revisor-release`, `tnc-revisor-wordpress-org`, `tnc-revisor-correcao`): dispare os aplicáveis **em paralelo** com o mesmo alvo e consolide. Nas outras ferramentas, aplique os papéis em sequência. Os revisores rodam no modelo principal; subagente auxiliar que só localiza arquivos ou junta trechos para eles usa modelo menor (constituição, "Como trabalhar", item 5).

## 2b. Segunda camada: revisão de correção com verificação

Toda revisão tem **duas camadas juntas**, sem que precisem pedir:

1. **Checklist dos padrões** (passos 1 e 2 acima): segurança WordPress, Plugin Check, i18n, API do Tainacan, convivência, carga, `php -l`, `phpcs` com o ruleset estrito.
2. **Caça a bugs de correção** com verificação de cada achado. No Claude Code: invoque a skill `/code-review` sobre o mesmo alvo: `/code-review medium <alvo>` uma vez por entrega (o diff que vai para deploy ou o PR), não a cada commit intermediário; `/code-review high` quando a mudança mexe em segurança, migração de dados, transação ou é release, e na análise completa de um plugin; `/code-review xhigh` só quando pedirem; `/code-review ultra` só quem usa dispara, porque roda na nuvem e é cobrado: sugira quando a mudança for crítica. Nas outras ferramentas, o papel `correcao.md` (subagente `tnc-revisor-correcao`).

Junte os achados das duas camadas num relatório só, por gravidade, dizendo de qual camada veio cada um. Em conflito (a segunda camada sugere suprimir onde há API canônica, CDN, `$wpdb` onde há `WP_Query`), valem os padrões da stack.

## 3. Regra de evidência

- Achado **confirmado**: arquivo:linha, o cenário concreto que falha (entrada → resultado errado) e, para segurança, o caminho do dado da entrada ao uso com arquivo:linha de cada passo.
- Sem isso, o achado é **hipótese** e aparece separado.
- Dívida preexistente fora do diff não bloqueia, a menos que a mudança a amplie.

## 4. Relatório

```
## Revisão técnica: <alvo>
**Veredito:** aprovar | aprovar com ressalvas | pedir mudanças
**tnc-check:** <resumo da triagem>

### Bloqueios (corrigir antes do merge)
1. `arquivo.php:42`: <defeito em uma frase>. Cenário: <entrada → resultado>. Correção: <o que fazer>.

### Ressalvas (corrigir ou justificar)
### Hipóteses (verificar)
### Observações fora do escopo
```

Ordene por gravidade. Seja específico; nada de "considere melhorar a legibilidade". Se o PR não tem testes para comportamento novo, isso é ressalva.

Achado novo e reutilizável: proponha `tnc-registrar-licao`.
