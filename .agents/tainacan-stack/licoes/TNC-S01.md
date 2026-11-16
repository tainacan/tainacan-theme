# TNC-S01 · SQL com variável sem $wpdb->prepare()

**Severidade:** bloqueio quando o valor vem da requisição ou de parâmetro público sem tipo numérico, em posição de valor, e quando o valor da requisição entra como nome de tabela ou em variável com nome de tabela ou marcador; aviso quando não dá para provar que é seguro · **Categoria:** seguranca · **Arquivos:** .php

## Sintoma
Uma busca, filtro ou ordenação aceita texto montado pelo visitante dentro do SQL. Com uma aspa no parâmetro, a página quebra com erro de banco; com uma carga preparada, a consulta devolve dados de outras tabelas ou altera registros (injeção de SQL). Às vezes o único sinal é um achado de auditoria.

## Causa
`$wpdb->query()`, `get_results()`, `get_var()`, `get_row()` e `get_col()` executam o texto exatamente como recebido. Só `$wpdb->prepare()` escapa e cita os valores, e só os que entram como argumento por marcador (`%s`, `%d`, `%f`, `%i` para identificador a partir do WordPress 6.2). Uma variável interpolada na string antes do `prepare()` já faz parte do SQL e não é protegida. O WordPress emite `_doing_it_wrong` quando `prepare()` recebe consulta sem nenhum `%` (WordPress 6.9.1, `wp-includes/class-wpdb.php:1476-1486`).

## O que a regra detecta
A regra olha chamadas `query`, `get_results`, `get_var`, `get_row`, `get_col` e `prepare` feitas em `$wpdb`, `$db`, `$database` ou `->wpdb`, e classifica cada variável que entra no texto do SQL:
- Bloqueio: valor vindo de `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$request->get_param()` (e similares) ou `$request[...]`, inclusive por atribuição anterior na mesma função; ou parâmetro de função pública sem tipo numérico. Nos dois casos, só em posição de valor (depois de `=`, `<`, `>`, `LIKE`, `IN (`, `LIMIT`, `OFFSET`, `VALUES (`, `BETWEEN`, `THEN`, `AGAINST (`, ou entre aspas), com a exceção descrita mais abaixo.
- Aviso: o mesmo fora de posição de valor (coluna, `ORDER BY`, fragmento); parâmetro de método privado ou protegido; propriedade (`$this->x`) ou retorno de método; variável sem atribuição encontrada; variável vinda de `foreach`; SQL inteiro recebido pronto por parâmetro (`$wpdb->query( $sql )` num helper).
- Aviso: variável interpolada dentro do texto do próprio `prepare()`, mesmo que a consulta também use marcadores (vira bloqueio se a variável vier da requisição).
- Aviso: `prepare()` com um só argumento e sem marcador `%s`, `%d`, `%f` ou `%i`.
- Bloqueio também quando a variável está logo depois de `FROM`, `JOIN`, `INTO`, `UPDATE`, `TABLE`, `EXISTS` ou `ALTER TABLE`, ou tem nome que indica tabela ou marcador (ver abaixo), mas o valor vem da requisição por atribuição na mesma função. Nesse caso a posição no SQL não importa.
- Aviso: `esc_sql( $x )` fora de aspas (`WHERE ID = " . esc_sql( $x )`). O `esc_sql()` só escapa aspas e não protege valor sem aspas.
- Não dispara para: `$wpdb->posts` e outras propriedades de `$wpdb`; variável logo depois de `FROM`, `JOIN`, `INTO`, `UPDATE`, `TABLE`, `EXISTS` ou `ALTER TABLE` que não vem da requisição; conversão `(int)`, `(float)`, `(bool)`; parâmetro tipado `int`, `float` ou `bool`; valor envolvido por `absint`, `intval`, `floatval`, `count`, `sizeof`, `sanitize_key`, `sanitize_sql_orderby` ou `wp_parse_id_list`; `esc_sql( $x )` entre aspas (`'" . esc_sql( $x ) . "'`); `array_map` com `absint`, `intval`, `floatval` ou `esc_sql`; lista de marcadores com `array_fill`.
- Não dispara para variável cujo nome contém `table`, `tabela`, `prefix`, `tbl`, `charset`, `collate`, `placeholder`, `format`, `fmt`, `safe` ou `sql_in` (ou termina em `_tb` ou `_ph`), nem para propriedade com esses nomes, desde que o valor não venha da requisição. `$format = $_GET['x']` interpolado num `WHERE` é bloqueio.
- Limites conhecidos: parâmetro de função com nome de tabela ou marcador (`function f( $table )`) não é acusado, mesmo sem tipo e em função pública, porque a regra não segue os chamadores. Valor que passou por `esc_sql()` numa atribuição anterior (`$x = esc_sql( $_GET['a'] );`) é aceito em qualquer posição, inclusive fora de aspas; só a chamada `esc_sql()` escrita dentro do próprio SQL é conferida.

## Como corrigir
1. Troque cada valor por marcador e passe o valor como argumento do `prepare()`.
2. Para nome de tabela ou coluna, use `%i` (WordPress 6.2+) ou valide contra uma lista fixa.
3. Para `IN (...)`, gere os marcadores com `array_fill` e passe a lista como argumentos.
4. Para `ORDER BY`, aceite só colunas de uma lista permitida, com valor padrão.

```php
// errado
$termo = $request->get_param( 'q' );
$wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE '%$termo%'" );

// certo
$termo = $request->get_param( 'q' );
$wpdb->get_results( $wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s",
    '%' . $wpdb->esc_like( $termo ) . '%'
) );

// ORDER BY por lista permitida
$coluna = in_array( $orderby, [ 'post_title', 'post_date' ], true ) ? $orderby : 'post_date';
```

## Quando suprimir
Quando o valor foi validado de um jeito que a regra não enxerga, como uma lista permitida em outra função: `// tnc-check-ignore TNC-S01: $coluna validada por lista fixa em sanitize_orderby()`. Também em helpers internos que recebem SQL pronto de chamadores que já usam `prepare()`, explicando isso no motivo. Não suprima bloqueio com dado da requisição: corrija.

## Origem
Achados de auditoria em plugins da equipe: um plugin de biblioteca com `ORDER BY` interpolado sem lista permitida e um painel de estatísticas com `WHERE`, `ORDER BY` e `LIMIT` montados por interpolação. A regra também segue a boa prática geral do WordPress (WPCS `WordPress.DB.PreparedSQL`).
