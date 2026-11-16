# TNC-B01 · Erro de sintaxe PHP (php -l)

**Severidade:** bloqueio (sempre) · **Categoria:** base · **Arquivos:** .php

## Sintoma
O site mostra "Parse error" ou tela branca assim que o plugin é ativado ou o arquivo é carregado, e o WordPress pode desativar o plugin sozinho. Quando o erro só aparece numa versão de PHP diferente da sua, o lint local passa e a falha surge apenas no servidor.

## Causa
O arquivo não é PHP válido para o interpretador que vai executá-lo. O caso mais comum é sintaxe de PHP 8 (`match`, `?->`, `enum`, argumentos nomeados) num plugin que declara `Requires PHP: 7.4`. O Tainacan 1.4.1 declara PHP 7.4 como mínimo (`tainacan.php:8-13`), então plugins do ecossistema precisam continuar compatíveis com 7.4.

## O que a regra detecta
- Roda `php -l` sobre o conteúdo do arquivo com o mesmo binário PHP que executa o `tnc-check` (`PHP_BINARY`), com `display_errors=1` e `error_reporting=-1`.
- Qualquer código de saída diferente de zero vira achado, na linha informada pelo PHP (ou na linha 1, se a mensagem não trouxer linha).
- Não dispara em arquivos que não são `.php`. Os arquivos `.inc` e `.phtml` não são analisados por esta regra.
- Não roda com `--no-lint` (opção pensada para varreduras grandes em `--workspace`).
- Não sabe qual versão de PHP o servidor usa: se o verificador roda em PHP 8.x, código que só quebra em 7.4 passa. Para conferir 7.4, rode o `tnc-check` com um PHP 7.4.

## Como corrigir
1. Leia a mensagem do `php -l` no achado e corrija a linha indicada.
2. Se o erro vem de sintaxe nova, reescreva com construções aceitas pela versão mínima declarada no cabeçalho do plugin.
3. Antes de publicar, rode o lint com a mesma versão de PHP do servidor (de preferência dentro do contêiner ou da máquina de produção).

```php
// errado em plugin com Requires PHP: 7.4
$rotulo = match ( $status ) {
    'publish' => 'Público',
    default   => 'Outro',
};

// certo
switch ( $status ) {
    case 'publish':
        $rotulo = 'Público';
        break;
    default:
        $rotulo = 'Outro';
}
```

## Quando suprimir
Não suprima. Arquivo com erro de sintaxe não roda; a correção é sempre no código. Se o arquivo não faz parte do plugin (exemplo, rascunho), tire-o da análise com `"exclude"` no `.tnc-check.json`.

## Origem
Um plugin de impressão publicou várias versões com `match` do PHP 8 enquanto declarava PHP 7.4, e cada folha com QR virava erro de sintaxe em 7.4; o mesmo padrão apareceu num plugin de dados interligados. Em outro caso, o lint rodado fora do contêiner, com outro PHP, deu resultado falso. A regra também segue a boa prática geral de rodar `php -l` antes de qualquer commit.
