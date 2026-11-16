# TNC-R07 · JavaScript usa wp.i18n sem wp_set_script_translations()

**Severidade:** aviso · **Categoria:** release · **Quando roda:** `tnc-check --release`

## Sintoma
O PHP do plugin está todo traduzido, o `.po` tem as strings do JavaScript (extraídas pelo `wp i18n make-pot`), mas o painel, o bloco ou o drawer seguem em inglês. Nenhum erro no console.

## Causa
`wp.i18n.__()` procura as traduções no objeto que o WordPress injeta para aquele *handle* de script. Esse objeto só existe se o PHP chamou `wp_set_script_translations( $handle, $domain, $path )` depois do `wp_enqueue_script()`/`wp_register_script()`. Sem a chamada, `__()` devolve o texto original. O arquivo de tradução do JS é um `.json` por script (`<dominio>-<locale>-<hash>.json`), gerado por `wp i18n make-json`.

## O que a conferência detecta
Algum `.js`/`.vue` do plugin (fora de `vendor/` e `node_modules/`) usa `wp.i18n` ou importa `@wordpress/i18n`, e nenhum `.php` chama `wp_set_script_translations()`.

## Como corrigir

```php
wp_enqueue_script( 'meu-plugin-painel', plugins_url( 'build/painel.js', MEU_PLUGIN_FILE ), [ 'wp-i18n', 'wp-api-fetch' ], MEU_PLUGIN_VERSION, true );
wp_set_script_translations( 'meu-plugin-painel', 'meu-plugin', plugin_dir_path( MEU_PLUGIN_FILE ) . 'languages' );
```

No build: `wp i18n make-pot . languages/meu-plugin.pot` e, depois de traduzir, `wp i18n make-json languages --no-purge`. Bloco Gutenberg com `block.json`: a dependência `wp-i18n` entra sozinha, mas a chamada de `wp_set_script_translations()` continua necessária fora do `register_block_type()` com `textdomain` no `block.json`.

## Quando suprimir
Conferência de release não aceita supressão. Se o JS usa `wp.i18n` só para `sprintf()` sem traduzir, troque por função própria.

## Origem
Plugin com painel Vue traduzido no PHP e em inglês na tela por duas versões, até alguém notar que faltava uma linha.
