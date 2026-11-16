# TNC-R01 · Versões divergentes entre cabeçalho, constantes, readme e manifestos

**Severidade:** bloqueio (sempre) · **Categoria:** release · **Arquivos:** arquivo principal do plugin, readme.txt, package.json, composer.json (só com `tnc-check --release`)

## Sintoma
Depois de atualizar o plugin, o navegador continua usando o CSS e o JS antigos, porque o `?ver=` dos assets vem de uma constante que não mudou. O WordPress.org mostra uma versão e o plugin instalado outra. Um build ou CI que lê a versão do `package.json` gera pacote com número errado.

## Causa
A versão do plugin costuma aparecer em vários lugares: o cabeçalho `Version:` do arquivo principal, uma constante usada como cache-buster em `wp_enqueue_script()`/`wp_enqueue_style()`, o `Stable tag` do `readme.txt` (que o WordPress.org usa para escolher o que distribuir) e o `version` do `package.json`/`composer.json`. Atualizar só parte deles deixa o pacote incoerente.

## O que a regra detecta
- Coleta as versões de: cabeçalho `Version:` do arquivo principal; `define( '..VERSION..', '<número>' )` no arquivo principal com valor que começa por dígito; `const VERSION = '...'` ou `const ..._VERSION = '...'` no arquivo principal; `Stable tag:` do `readme.txt` (exceto `trunk`); `version` de `package.json` e `composer.json` na raiz.
- Acusa quando há mais de um valor distinto, listando onde está cada um.
- Não procura constantes de versão fora do arquivo principal (por exemplo, numa classe em `includes/`).
- Não compara com o `CHANGELOG` (isso é TNC-R03) nem com `block.json`.

## Como corrigir
1. Atualize todas as versões no mesmo commit da release.
2. Mantenha uma fonte única e propague com um script (por exemplo, a partir do `package.json`).
3. Use a constante de versão como cache-buster dos assets.

```php
/**
 * Plugin Name: Meu Plugin
 * Version: 1.4.3
 */
// errado: define( 'MEU_PLUGIN_VERSION', '1.4.2' );
// certo:
define( 'MEU_PLUGIN_VERSION', '1.4.3' );
```

```
readme.txt
Stable tag: 1.4.3
```

## Quando suprimir
As conferências de release não aceitam supressão em linha nem baseline. Se um arquivo usa outra numeração de propósito (por exemplo, um `package.json` de ferramenta interna), tire o campo `version` dele ou mova a ferramenta para fora da raiz do plugin.

## Origem
Num painel de indicadores e num plugin de impressão, a constante de versão ficou atrás do cabeçalho e o navegador não renovava JS e CSS após a atualização. Num plugin de processos, cabeçalho, constante, `Stable tag` e `package.json` divergiram, com um bump automático de CI piorando o quadro.
