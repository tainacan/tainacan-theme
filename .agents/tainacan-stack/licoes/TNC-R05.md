# TNC-R05 · Uso do Tainacan sem Requires Plugins: tainacan

**Severidade:** aviso (sempre) · **Categoria:** release · **Arquivos:** cabeçalho do arquivo principal e todos os .php do plugin (só com `tnc-check --release`)

## Sintoma
Com o Tainacan desativado (ou ainda não instalado), ativar o plugin derruba o site ou o admin com "Class 'Tainacan\Repositories\Items' not found" ou "Call to undefined function tainacan_register_admin_hook()". Ao desativar o Tainacan, o plugin dependente continua ativo e quebra.

## Causa
Desde o WordPress 6.5, o cabeçalho `Requires Plugins:` declara dependências: o WordPress impede ativar o plugin sem elas e impede desativar a dependência enquanto o dependente está ativo (`wp-includes/class-wp-plugin-dependencies.php`, leitura do cabeçalho na linha 554, WordPress 6.9.1). O Tainacan 1.4.1 já exige WordPress 6.5 (`tainacan.php:8-13`), então todo plugin que depende dele pode usar o cabeçalho.

## O que a regra detecta
- Plugin cujos arquivos PHP mencionam `Tainacan\Repositories`, `Tainacan\Entities`, `Tainacan\Metadata_Types`, `Tainacan\Filter_Types`, `Tainacan\Importer`, `Tainacan\Exporter`, `tainacan_register_admin_hook` ou `tainacan_get_item`, e cujo cabeçalho não tem `Requires Plugins:` contendo `tainacan`.
- Não roda para o próprio núcleo do Tainacan.
- Não reconhece outros usos do Tainacan, como as funções `tainacan_items()`/`tainacan_metadata()` sozinhas ou ganchos `tainacan-*`: um plugin que só usa esses não é acusado.

## Como corrigir
Acrescente o cabeçalho e, para versões do WordPress anteriores à 6.5 ou por segurança, verifique a existência do Tainacan antes de usar suas classes.

```php
/**
 * Plugin Name: Meu Plugin
 * Requires at least: 6.5
 * Requires Plugins: tainacan
 */
add_action( 'plugins_loaded', function () {
    if ( ! class_exists( '\Tainacan\Repositories\Items' ) ) {
        return;
    }
    // inicializa o plugin
} );
```

## Quando suprimir
As conferências de release não aceitam supressão em linha. Plugin que usa o Tainacan só de forma opcional (funciona sem ele, com checagem de `class_exists`) pode conviver com o aviso; registre a decisão no `AGENTS.md` do projeto.

## Origem
Boa prática geral do WordPress a partir da versão 6.5. Não há um caso real específico registrado para esta conferência.
