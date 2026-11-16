# TNC-R08 · phpcs.xml.dist ausente ou sem regras de segurança

**Severidade:** aviso · **Categoria:** release · **Quando roda:** `tnc-check --release`

## Sintoma
O PHPCS passa verde no CI e o Plugin Check do WordPress.org devolve centenas de erros de escape, sanitização, nonce e `prepare()`. Ou: não há PHPCS nenhum e cada revisor cobra um padrão diferente.

## Causa
`WordPress-Core` e `WordPress-Docs` só cobrem formatação e documentação. Escape de saída, sanitização de entrada, nonce, SQL preparado e as regras do diretório de plugins estão em `WordPress-Extra`, `WordPress.Security` e no padrão `PluginCheck` (pacote `plugin-check/phpcs-sniffs`). Sem eles, o CI não vê o que o Plugin Check vai acusar. O núcleo do Tainacan usa só `WordPress-Core` + `WordPress-Docs`: plugin de extensão que copia esse `phpcs.xml.dist` herda a lacuna.

## O que a conferência detecta
- Nenhum `phpcs.xml.dist`, `phpcs.xml`, `.phpcs.xml.dist` ou `.phpcs.xml` na raiz do plugin.
- Arquivo presente sem referência a `WordPress-Extra`, `WordPress.Security` ou `PluginCheck` (o padrão `WordPress` completo também satisfaz).

## Como corrigir
Copie `.agents/tainacan-stack/templates/phpcs.xml.dist` para a raiz e instale as dependências de desenvolvimento:

```json
"require-dev": {
    "wp-coding-standards/wpcs": "^3.0",
    "phpcompatibility/phpcompatibility-wp": "^2.1",
    "phpcsstandards/phpcsextra": "^1.2",
    "dealerdirect/phpcodesniffer-composer-installer": "^1.0"
}
```

O padrão `PluginCheck` vem com o plugin Plugin Check (`wp plugin install plugin-check --activate` e `wp plugin check <slug>`), ou pelo pacote Composer `wordpress/plugin-check` quando disponível no projeto. Rode `vendor/bin/phpcs --standard=phpcs.xml.dist` antes de cada PR, e no CI.

A primeira rodada num plugin existente traz muitos achados: siga a hierarquia refatorar > API canônica > suprimir com justificativa (TNC-B07), por arquivo, começando pelos de segurança.

## Quando suprimir
Conferência de release não aceita supressão. Projeto que não é plugin do WordPress.org pode desligar a regra no `.tnc-check.json` (`"rules": { "TNC-R08": "off" }`).

## Origem
Decisão da equipe depois de um plugin passar meses "limpo" no PHPCS e chegar a 1.707 achados no Plugin Check. O ruleset estrito passou a ser padrão de todo plugin novo.
