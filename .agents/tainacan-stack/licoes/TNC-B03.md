# TNC-B03 · Codificação corrompida (BOM, UTF-8 inválido, mojibake)

**Severidade:** bloqueio, exceto BOM em arquivo que não é PHP e mojibake, que são aviso · **Categoria:** base · **Arquivos:** .php, .inc, .js, .jsx, .ts, .tsx, .vue, .css, .scss, .md, .json, .po, .pot, .txt, .html, .xml, .yml, .yaml, .sql

## Sintoma
Textos da interface aparecem com pares de caracteres no lugar dos acentos ("Opções" vira "Op" + "Ã" + "§" + "Ã" + "µes") ou com o losango de interrogação (U+FFFD). Em PHP, um BOM no início do arquivo gera "Cannot modify header information - headers already sent", quebra redirecionamentos e respostas REST/JSON. Um CSV ou JSON lido pelo plugin falha na primeira coluna ou no primeiro campo.

## Causa
O arquivo foi gravado em outra codificação ou passou por uma conversão dupla. O caso típico é o PowerShell 5.1: `Get-Content`/`Set-Content` sem `-Encoding` lê UTF-8 como ANSI e regrava, e `-Encoding UTF8` acrescenta BOM. O BOM (`EF BB BF`) fica antes do `<?php`, o PHP o envia como saída e os cabeçalhos HTTP não podem mais ser alterados. O caractere U+FFFD indica que os bytes originais já foram perdidos numa conversão.

## O que a regra detecta
- BOM UTF-8 no início do arquivo: bloqueio em PHP, aviso nos demais tipos.
- Bytes que não formam UTF-8 válido (arquivo em Latin-1/CP1252 ou truncado): bloqueio, na primeira linha inválida. Quando isso acontece, as verificações seguintes do arquivo não rodam.
- Caractere de substituição U+FFFD em qualquer linha: bloqueio.
- Mojibake, isto é, UTF-8 lido como Latin-1 e regravado (`Ã` seguido de caractere entre U+0080 e U+00BF, ou `â€` seguido de aspas e símbolos): aviso.
- Não dispara quando o `.tnc-check.json` declara `"encoding"` diferente de `utf-8`.
- Não dispara em arquivos minificados (`*.min.js`, `*.min.css`) nem nas pastas excluídas por padrão (`vendor/`, `node_modules/`, `languages/` e outras).

## Como corrigir
1. BOM: salve o arquivo como UTF-8 sem BOM.
2. Latin-1/CP1252: converta para UTF-8 (por exemplo, `iconv -f cp1252 -t utf-8`).
3. U+FFFD ou mojibake: recupere a versão boa pelo git (`git show HEAD:<arquivo>`) e refaça a edição com uma ferramenta que preserve UTF-8. A reversão automática de mojibake acerta só parte dos caracteres; prefira o histórico.
4. No Windows, não edite arquivos com `Get-Content -Raw | Set-Content`. Use as ferramentas de edição ou .NET com `UTF8Encoding($false)`.

```powershell
# errado (PowerShell 5.1)
(Get-Content arquivo.php -Raw) -replace 'a','b' | Set-Content arquivo.php

# certo
$t = [IO.File]::ReadAllText('arquivo.php')
[IO.File]::WriteAllText('arquivo.php', $t.Replace('a','b'), (New-Object Text.UTF8Encoding($false)))
```

## Quando suprimir
Só quando o texto estranho é intencional, como um teste que documenta mojibake: `// tnc-check-ignore TNC-B03: exemplo de mojibake usado no teste de conversão`. Para um projeto que usa outra codificação de propósito, declare `"encoding"` no `.tnc-check.json` em vez de suprimir. Um BOM ou bytes inválidos em PHP não devem ser suprimidos.

## Origem
Edições feitas no Windows com PowerShell 5.1 corromperam acentos e gravaram BOM em arquivos de vários plugins da equipe, e arquivos CSV com BOM quebraram a leitura da primeira coluna. A regra também cobre a boa prática geral de manter fontes em UTF-8 sem BOM.
