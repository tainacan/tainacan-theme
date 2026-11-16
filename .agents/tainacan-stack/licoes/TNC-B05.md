# TNC-B05 · Supressão sem motivo

**Severidade:** aviso (não muda pelo `.tnc-check.json`; só pode ser desligada) · **Categoria:** base · **Arquivos:** todos os analisados pelo `tnc-check`

## Sintoma
Um achado que deveria estar suprimido continua aparecendo no relatório, e ao lado surge um aviso TNC-B05 na linha do comentário `tnc-check-ignore`.

## Causa
A supressão em linha só vale com um motivo escrito depois de dois-pontos. Sem motivo, ou com motivo de menos de 8 caracteres, o `tnc-check` ignora a supressão e acusa o próprio comentário. O objetivo é que cada exceção fique explicada para quem revisa o código depois (pessoa ou agente).

## O que a regra detecta
- Comentário no formato `tnc-check-ignore <IDs>` sem `:` e motivo, ou com motivo de menos de 8 caracteres depois de retirar `*/`, `-->` ou `?>` do fim.
- Vale para qualquer comentário do arquivo que case com o padrão, inclusive em CSS, HTML, SQL e Vue.
- Não dispara quando o texto não traz um ID no formato `TNC-X00` (por exemplo, `tnc-check-ignore S01`); nesse caso a supressão é simplesmente ignorada, sem aviso.
- Ao rodar com `--rules=...`, o TNC-B05 só aparece se a lista tiver `TNC-B05` ou um prefixo dele (por exemplo, `TNC-B`).
- Pode ser desligada no projeto com `"TNC-B05": "off"` em `"rules"` no `.tnc-check.json`. Trocar para `"block"` não muda nada: o achado continua como aviso.

## Como corrigir
Escreva o motivo depois de dois-pontos. O comentário vale na própria linha e na linha seguinte, e aceita vários IDs separados por vírgula ou espaço.

```php
// errado
// tnc-check-ignore TNC-P02
foreach ( $colecoes as $c ) { $repo->fetch( $c ); }

// certo
// tnc-check-ignore TNC-P02: laço sobre no máximo cinco coleções configuradas
foreach ( $colecoes as $c ) { $repo->fetch( $c ); }
```

Em outros tipos de arquivo: `/* tnc-check-ignore TNC-C02: <motivo do caso> */` (CSS), `-- tnc-check-ignore TNC-P04: <motivo do caso>` (SQL), `<!-- tnc-check-ignore TNC-W09: <motivo do caso> -->` (HTML e templates Vue).

## Quando suprimir
Esta regra não pode ser suprimida em linha: o aviso é gerado depois que as supressões são aplicadas. A correção é escrever o motivo. Para desligá-la no projeto inteiro, use `"TNC-B05": "off"` no `.tnc-check.json`.

## Origem
Regra do próprio `tnc-check`, criada para que exceções não virem um "silenciar tudo" sem justificativa. Segue a mesma ideia do `phpcs:ignore` com comentário explicativo, que o Plugin Check do WordPress.org também espera.
