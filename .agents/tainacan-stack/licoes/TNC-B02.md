# TNC-B02 · Tag de abertura curta (<?)

**Severidade:** bloqueio (sempre) · **Categoria:** base · **Arquivos:** .php

## Sintoma
Código PHP aparece impresso como texto na página, ou um trecho simplesmente não executa, sem erro nenhum. O `php -l` passa, porque com `short_open_tag=Off` o PHP trata o trecho como HTML e não o analisa.

## Causa
A tag `<?` (sem `php`) só abre um bloco de PHP quando a diretiva `short_open_tag` está ligada no `php.ini`. Ela vem desligada em muitas instalações, então o mesmo arquivo funciona num servidor e vaza código no outro. O WordPress Coding Standards proíbe a tag curta por esse motivo. A tag de eco `<?=` não depende da diretiva e é válida desde o PHP 5.4.

## O que a regra detecta
- Token de abertura `<?` sem `php` (quando o PHP que roda o verificador tem `short_open_tag` ligado).
- `<?` seguido de espaço, tabulação ou quebra de linha dentro de HTML solto do arquivo (quando o PHP do verificador tem `short_open_tag` desligado e o trecho virou texto).
- Não dispara em `<?php` nem em `<?=`.
- Não dispara em `<?xml` e outras sequências em que `<?` vem colado a uma letra.

## Como corrigir
Troque cada `<?` por `<?php`.

```php
<!-- errado -->
<div><? echo esc_html( $titulo ); ?></div>

<!-- certo -->
<div><?php echo esc_html( $titulo ); ?></div>
```

## Quando suprimir
Não deve ser suprimida em código PHP. Se o arquivo é um modelo de outra linguagem que usa `<?` de propósito, exclua o caminho com `"exclude"` no `.tnc-check.json` em vez de suprimir linha a linha.

## Origem
Boa prática geral de PHP e do WordPress (a tag curta é proibida pelo WPCS e quebra conforme a configuração do servidor). Um plugin de biblioteca também registrou problema com `<?` literal dentro de comentário PHP; esse caso específico (`<?xml` em comentário) não é coberto pela regra.
