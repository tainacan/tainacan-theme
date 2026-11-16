# TNC-W03 · Tradução sem domínio de texto ou com domínio errado

**Severidade:** aviso (sempre) · **Categoria:** wordpress · **Arquivos:** .php

## Sintoma
Textos do plugin continuam em inglês (ou no idioma do código) mesmo com a tradução instalada, ou aparecem traduzidos por acaso com o texto de outro plugin. Frases montadas com variável nunca aparecem no arquivo `.pot` para tradução.

## Causa
As funções de tradução procuram a frase no catálogo do domínio informado. Sem domínio, o WordPress usa `default` (o catálogo do núcleo). Com um domínio diferente do `Text Domain` do plugin, a frase vai para o catálogo errado. As ferramentas de extração (`wp i18n make-pot`) só leem literais: `__( $texto )` não entra no `.pot`. O WordPress.org e o translate.wordpress.org usam o slug do plugin como domínio.

## O que a regra detecta
- Chamadas a `__`, `_e`, `esc_html__`, `esc_html_e`, `esc_attr__`, `esc_attr_e`, `_x`, `_ex`, `esc_html_x`, `esc_attr_x`, `_n`, `_nx`, `_n_noop` e `_nx_noop`.
- Primeiro argumento que é uma variável: "texto variável".
- Chamada sem o argumento de domínio na posição esperada.
- Domínio literal diferente do `Text Domain` do cabeçalho do plugin (ou do `"text_domain"` do `.tnc-check.json`): um achado por arquivo e por domínio, com a contagem de chamadas.
- Não dispara quando o domínio é constante ou variável.
- Não dispara para o domínio `tainacan` quando o `.tnc-check.json` declara `"allow_tainacan_domain": true`.
- Não analisa chamadas cujo primeiro argumento é concatenação, constante ou chamada de função (só a variável simples é acusada).
- Não compara o `textdomain` de `block.json` com o do plugin.

## Como corrigir
1. Passe sempre o domínio do plugin como último argumento.
2. Use literal no texto; para frases com valor, use `sprintf()` com marcador e comentário `translators:`.
3. Se o domínio do código é o certo e o cabeçalho está errado, corrija o cabeçalho (ver TNC-R04).

```php
// errado
$a = __( 'Salvar' );
$b = esc_html__( 'Coleção', 'outro-plugin' );
$c = __( $rotulo, 'meu-plugin' );

// certo
$a = __( 'Salvar', 'meu-plugin' );
$b = esc_html__( 'Coleção', 'meu-plugin' );
/* translators: %s: nome da coleção */
$c = sprintf( __( 'Itens da coleção %s', 'meu-plugin' ), $nome );
```

## Quando suprimir
Reaproveitamento intencional de frase do núcleo do Tainacan: declare `"allow_tainacan_domain": true` no `.tnc-check.json` (lembrando que a frase pode mudar ou sumir no núcleo sem aviso). Texto variável que já vem traduzido de outra fonte (por exemplo, rótulo devolvido por uma API que aplica `__()` lá dentro): `// tnc-check-ignore TNC-W03: rótulo já traduzido pela API do Tainacan`.

## Origem
Boa prática geral de internacionalização no WordPress. Um plugin de memorial teve rótulos de blocos que nunca ficavam traduzíveis porque o domínio do `block.json` divergia do plugin; esse caso específico não é coberto pela regra.
