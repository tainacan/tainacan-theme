---
name: tnc-registrar-licao
description: Transforma um problema resolvido em conhecimento reutilizável da equipe: decide se vira regra automática do tnc-check (com fixtures), lição de referência ou nota do projeto, e procura o mesmo problema nos outros plugins. Use depois de corrigir bug que custou investigação, quando alguém diz "lembre disso", "isso já aconteceu antes", "registre a lição", ou em revisão que achou padrão repetível.
---

# tnc-registrar-licao

O objetivo é que o mesmo erro não custe tempo de novo, em nenhum projeto da equipe.

## 1. Vale registrar?

Registre se: a causa foi entendida; pode se repetir (em outro arquivo, plugin ou pessoa); e custou investigação. Não registre erro de digitação, falha pontual de serviço externo ou algo que o próprio código já deixa óbvio.

## 2. Onde registrar

| Situação | Destino |
|---|---|
| Padrão detectável no código (chamada X sem Y, chave Z, gancho W) | **regra** do `tnc-check` + fixtures + lição `licoes/<ID>.md` |
| Vale para qualquer projeto Tainacan, mas não dá para detectar | lição em `referencias/<tema>.md` (formato `### L-<TEMA>-NN`) |
| Só vale para este projeto | seção do projeto no `AGENTS.md` ("Pegadinhas" ou "Não regredir") |

Lição da stack (regra ou referência) se faz **no repositório da stack**, não na cópia em `.agents/tainacan-stack/` (ela é substituída na atualização). Abra PR ou envie o texto para quem mantém a stack.

## 3. Lição de referência

```
### L-<TEMA>-NN · <título curto>
**Sintoma:** ... **Causa:** ... **Correção:** ... **Detecção:** manual: <como verificar> **Vista em:** <tipo de projeto>
```
Sem nomes de pessoas, servidores, domínios internos, IPs ou dados de clientes.

## 4. Regra nova no tnc-check

No repositório da stack:
1. Escolha o ID pela categoria (B base, S segurança, P desempenho, T Tainacan, W WordPress, C convivência) com o próximo número livre (`tnc-check --list-rules`).
2. Crie `stack/tainacan-stack/src/Rules/<Nome>.php` estendendo `Rule`, com `ID`, `TITLE`, `SEVERITY`, `CATEGORY`, `EXTENSIONS` e docblock explicando a causa (cite o núcleo com arquivo:linha). Use os auxiliares de `SourceFile`: `calls()`, `methodCalls()`, `resolveCallback()`, `functionAt()`, `loops()`, `enclosingCalls()`, `arrayEntries()`.
3. Bloqueio só para o que quebra com certeza ou é falha de segurança demonstrável; o resto é aviso. Prefira não acusar a acusar errado.
4. Fixtures em `tests/fixtures/<ID>/`: casos que devem acusar, anotados com `// @expect <ID>` (ou `:block`/`:warn`) na linha exata, e casos parecidos que **não** devem acusar.
5. Registre a classe em `src/Registry.php`. Rode `php tests/run.php` até 0 falha.
6. Meça ruído em código real: `tnc-check --workspace=<pasta-com-vários-plugins> --rules=<ID> --no-lint`. Leia uma amostra dos achados; se houver falso positivo, ajuste a regra e acrescente o caso como fixture que não acusa.
7. Escreva `licoes/<ID>.md` (Sintoma, Causa, O que a regra detecta, Como corrigir, Quando suprimir, Origem) e acrescente ao `licoes/README.md`.
8. Atualize `CHANGELOG.md` e a versão em `stack/tainacan-stack/VERSION`.

## 5. Propagar

Com a regra pronta, rode-a nos plugins do workspace e liste onde o problema existe. Corrija cada um no próprio repositório (cada correção segue `tnc-guardrails`) ou abra issue para o responsável.

Relato: onde a lição ficou, ID da regra (se houver), resultado da varredura nos outros plugins.
