# TNC-W11 · $wpdb->prepare() com marcadores e argumentos em número diferente

**Severidade:** aviso (número diferente); bloqueio (marcador inválido ou posicional colado em texto) · **Categoria:** wordpress · **Arquivos:** .php

## Sintoma
A consulta devolve vazio ou falha com erro de SQL, e o `debug.log` traz um `_doing_it_wrong` de `wpdb::prepare` dizendo que o número de marcadores não bate com o de argumentos. Em outro caso, a página inteira cai com erro de SQL depois de um rename de variável que parecia inofensivo.

## Causa
`wpdb::prepare()` conta os marcadores (`%s`, `%d`, `%f`, `%F`, `%i`, posicionais `%1$s`) e os argumentos recebidos; quando divergem, registra `_doing_it_wrong` e devolve a consulta sem substituir os valores (`wp-includes/class-wpdb.php`, método `prepare`). Um marcador posicional colado em texto, como `%1$status`, é lido como `%1$s` seguido de `tatus`, que vira SQL solto. Um `%` literal precisa ser escrito `%%`.

## O que a regra detecta
- Chamadas `$wpdb->prepare( <formato>, ...args )` cujo formato é literal, concatenação de literais ou interpolação só de nomes de tabela (`$wpdb->posts`, `$table`), com `implode()` e `esc_sql()` aceitos no formato.
- Conta os marcadores (ignorando `%%`) e os argumentos depois do formato. Número diferente: aviso.
- Marcador posicional seguido de letra ou sublinhado (`%1$sql`): bloqueio.
- `%` seguido de caractere que não é `s`, `d`, `f`, `F`, `i`, dígito, espaço, aspas ou `%`: bloqueio.
- Não conta quando os argumentos vêm por `...$args`, por um array literal, por `array_merge`/`array_values`/`array_map`/`array_fill`, ou por uma única variável que parece array (nome terminado em `args`, `values`, `params`, `ids`, `list`, `data`, `array`, `placeholders` ou atribuída como array na mesma função).
- Formato montado numa variável ou com valor interpolado fica fora desta regra (a TNC-S01 cuida da interpolação).

## Como corrigir
Alinhe os marcadores com os argumentos e escreva `%%` para o sinal literal.

```php
// errado: dois marcadores, um argumento
$wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_name = %s", $id );

// certo
$wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_name = %s", $id, $slug );

// LIKE: o % literal vai como %% ou dentro do argumento
$wpdb->prepare( "... WHERE post_title LIKE %s", '%' . $wpdb->esc_like( $termo ) . '%' );
```

## Quando suprimir
Quando o array de argumentos vem de uma variável com nome que a regra não reconhece como array: renomeie para `$args`/`$values` ou justifique: `// tnc-check-ignore TNC-W11: $bind é array montado em montar_filtros()`. Não suprima bloqueio de marcador colado.

## Origem
Um indexador de busca teve a listagem derrubada depois de um rename de variável que colou texto num marcador posicional; a consulta passou a sair com SQL inválido e nenhum teste pegou, porque o erro só aparecia com o filtro ativo. A contagem de marcadores segue a documentação de `wpdb::prepare()`.
