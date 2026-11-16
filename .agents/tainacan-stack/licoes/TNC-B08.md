# TNC-B08 · get_template_part() para arquivo que não existe

**Severidade:** bloqueio (sempre) · **Categoria:** base · **Arquivos:** .php

## Sintoma
Uma seção da página simplesmente não aparece. O site responde 200, o log do PHP fica vazio, o teste de fumaça passa, e a ficha de metadados do item (ou os anexos, ou o rodapé) desapareceu. Quem percebe é a pessoa que conhece a página — às vezes semanas depois.

## Causa
`get_template_part()` não devolve erro nem aviso quando o arquivo não existe: ela chama `locate_template()`, não encontra nada e volta sem imprimir. É um silêncio por projeto, para que um tema filho possa deixar de ter uma parte sem quebrar o pai.

O caso que origina a regra: numa refatoração de tema, `template-parts/single-items-metadata_new.php` e `_old` foram apagados por serem duplicatas, mas `single-items-metadata.php` continuou sendo um despachante que chamava os dois. A página do item passou a sair sem ficha de metadados nenhuma, e nem o `php -l`, nem o PHPCS, nem o próprio verificador acusaram — porque sintaticamente não há nada de errado.

## O que a regra detecta
- `get_template_part()` cujo primeiro argumento é uma **string literal** e cujo arquivo não existe na raiz do projeto.
- Com segundo argumento literal (`get_template_part( 'parte', 'nome' )`), aceita `parte-nome.php` **ou** `parte.php`: é a ordem que o WordPress procura. Só acusa quando nenhum dos dois existe.
- Só roda em projeto WordPress (cabeçalho de plugin ou tema, ou `text_domain` no `.tnc-check.json`).

O que a regra deixa passar, de propósito:

- caminho montado em tempo de execução (`get_template_part( $parte )`, `get_template_part( 'partes/' . $tipo )`): não há como saber o valor sem rodar o código;
- parte que existe no tema **pai** e não no filho: a regra olha a raiz do projeto analisado, e num tema filho a parte do pai é legítima. Em tema filho, declare a parte em `"exclude"` ou use a supressão em linha.

## Como corrigir
Crie o arquivo, corrija o caminho ou tire a chamada. Se o conteúdo foi absorvido por outro arquivo durante uma refatoração, o caminho certo em geral é apagar o despachante e chamar o conteúdo direto.

```php
// errado: aponta para arquivos que não existem mais
if ( function_exists( 'tainacan_the_item_gallery' ) ) {
	get_template_part( 'template-parts/single-items-metadata_new' );
} else {
	get_template_part( 'template-parts/single-items-metadata_old' );
}
```

```php
// certo: um caminho só, no arquivo que existe
get_template_part( 'template-parts/single-items-metadata' );
```

## Quando suprimir
Tema filho que chama uma parte do tema pai, ou parte que um plugin registra em tempo de execução por `get_template_part` com filtro de localização:

```php
// tnc-check-ignore TNC-B08: a parte vem do tema pai (tainacan-acervo), não deste tema filho.
get_template_part( 'template-parts/single-items-header' );
```

## Origem
Erro cometido durante a refatoração do tema Tainacan Interface para o Tainacan Acervo, em outubro de 2026: dois templates duplicados foram removidos e os despachantes que os chamavam ficaram. A regra existe porque nenhuma das outras verificações do projeto — `php -l`, PHPCS com WordPress-Extra e Security, e as demais regras do `tnc-check` — enxerga esse tipo de quebra.
