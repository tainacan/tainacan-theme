# TNC-R04 · Text Domain diferente do slug da pasta

**Severidade:** aviso (sempre) · **Categoria:** release · **Arquivos:** cabeçalho do arquivo principal do plugin (só com `tnc-check --release`)

## Sintoma
As traduções do translate.wordpress.org não chegam ao plugin, ou o Plugin Check e a revisão do WordPress.org recusam o pacote. Traduções embarcadas carregam num ambiente e não no outro.

## Causa
O WordPress.org exige que o `Text Domain` seja igual ao slug do plugin, e o translate.wordpress.org distribui os pacotes de idioma pelo slug. O slug é o nome da pasta e não pode ser trocado depois da primeira publicação.

## O que a regra detecta
- `Text Domain` do cabeçalho (ou `"text_domain"` do `.tnc-check.json`, que tem precedência) diferente do nome da pasta raiz do plugin.
- Não roda para o próprio núcleo do Tainacan (domínio `tainacan` ou arquivo principal `tainacan.php`).
- Não roda quando não há `Text Domain` no cabeçalho nem no `.tnc-check.json`.
- Compara com o nome da pasta onde o `tnc-check` encontrou o plugin: um clone com outro nome de pasta (por exemplo, com sufixo de branch) gera achado mesmo com o domínio certo.

## Como corrigir
1. Antes da primeira publicação, alinhe o `Text Domain`, a pasta e o slug.
2. Depois de publicado, mantenha a pasta, o slug e o domínio; troque só o nome de exibição (`Plugin Name`).
3. Ajuste as chamadas de tradução para o domínio certo (ver TNC-W03).

```php
/**
 * Plugin Name: Meu Plugin
 * Text Domain: meu-plugin      (pasta: wp-content/plugins/meu-plugin/)
 */
```

## Quando suprimir
As conferências de release não aceitam supressão em linha. Plugin interno que não vai para o WordPress.org e cujo domínio já está em uso por traduções existentes pode conviver com o aviso; registre a decisão no `AGENTS.md` do projeto.

## Origem
Exigência do WordPress.org. Um plugin de catalogação colaborativa registrou que o slug é imutável depois de publicado e que, ao mudar o nome de exibição, pasta, slug, text domain, prefixos e namespace REST devem ficar como estão.
