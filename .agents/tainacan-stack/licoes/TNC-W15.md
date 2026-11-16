# TNC-W15 · Marcador em string traduzível sem numeração ou sem comentário translators

**Severidade:** aviso (sempre) · **Categoria:** wordpress · **Arquivos:** .php (só em projeto WordPress)

## Sintoma
O Plugin Check acusa `WordPress.WP.I18n.UnorderedPlaceholdersText` e `WordPress.WP.I18n.MissingTranslatorsComment`. A tradução para inglês de "%s em %s" sai como "Collection in Item" porque a ordem das palavras muda e o tradutor não pôde inverter os argumentos. No translate.wordpress.org, a string "%d em %s" aparece sem contexto e recebe uma tradução errada.

## Causa
`sprintf()` substitui os marcadores na ordem dos argumentos. Com `%s ... %s`, o tradutor não tem como trocar a ordem; com `%1$s ... %2$s`, pode escrever `%2$s contém %1$s`. E, como o tradutor vê a string isolada, só o comentário `translators:` imediatamente antes da chamada (que o `wp i18n make-pot` copia para o `.pot`) explica o que cada marcador representa.

## O que a regra detecta
Nas funções de tradução (`__`, `_e`, `_x`, `_n`, `esc_html__`, `esc_attr__`, etc.) com texto literal:
- mais de um marcador `%s`/`%d`/`%1$s`... em que algum não tem numeração;
- pelo menos um marcador e nenhum comentário contendo `translators:` nas 3 linhas anteriores ao enunciado (sem código no meio) ou dentro do enunciado (`printf( /* translators: ... */ __( ... ) )`).

`%%` não conta como marcador. Texto em variável fica fora (TNC-W03 cuida).

## Como corrigir

```php
/* translators: %1$s = título do item, %2$s = nome da coleção */
$texto = sprintf( __( '%1$s em %2$s', 'meu-plugin' ), $item_title, $collection_name );

printf(
    /* translators: %d = quantidade de itens importados */
    esc_html( _n( '%d item importado', '%d itens importados', $count, 'meu-plugin' ) ),
    (int) $count
);
```

O comentário diz **o que cada marcador é** ("%s = nome do usuário"), não "translators: placeholder". Um marcador só também precisa do comentário.

## Quando suprimir
Marcador que não é de `sprintf` (ex.: texto literal "100%s" por engano de regex): corrija o texto. Não há caso legítimo para suprimir a numeração.

## Origem
Limpeza de Plugin Check em plugin de biblioteca: 200 strings sem comentário translators e dezenas sem numeração, corrigidas uma a uma. Os blocos Gutenberg do núcleo seguem o padrão certo e servem de modelo.
