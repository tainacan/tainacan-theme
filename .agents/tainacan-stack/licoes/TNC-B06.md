# TNC-B06 · Arquivo PHP sem proteção contra acesso direto

**Severidade:** aviso (sempre) · **Categoria:** base · **Arquivos:** .php

## Sintoma
Abrir a URL de um arquivo do plugin no navegador (`/wp-content/plugins/meu-plugin/includes/x.php`) executa código fora do WordPress: aparece um "Fatal error: Call to undefined function", que expõe o caminho do servidor, ou o arquivo roda alguma ação solta. O Plugin Check do WordPress.org reprova o pacote.

## Causa
Todo arquivo `.php` dentro de `wp-content/plugins` é acessível pela web. Sem uma guarda no topo, o PHP executa o arquivo mesmo sem o WordPress carregado. A guarda `defined( 'ABSPATH' ) || exit;` encerra o arquivo quando ele é chamado diretamente.

## O que a regra detecta
- Arquivo PHP de um projeto que é plugin (tem cabeçalho `Plugin Name:` na raiz) sem `ABSPATH`, `WPINC` ou `WP_UNINSTALL_PLUGIN` nos primeiros 4000 bytes.
- O achado vale também para arquivos que só declaram classes ou funções, porque o Plugin Check exige a guarda mesmo assim.
- Não dispara fora de plugins (tema, pasta solta), em `uninstall.php`, nem em arquivos sob `test/`, `tests/`, `bin/`, `developer/`, `scripts/` ou `cli/`.
- Não dispara em arquivo vazio ou sem `<?php`.
- A busca é textual: a palavra `ABSPATH` num comentário nos primeiros 4000 bytes já basta para a regra aceitar o arquivo.

## Como corrigir
Acrescente a guarda logo depois de `<?php` e, se houver, depois da declaração `namespace`.

```php
<?php
// errado
namespace MeuPlugin;

class Servico {}
```

```php
<?php
// certo
namespace MeuPlugin;

defined( 'ABSPATH' ) || exit;

class Servico {}
```

## Quando suprimir
Arquivo que precisa rodar sem o WordPress, como um ponto de entrada próprio que carrega o `wp-load.php` por conta própria: `// tnc-check-ignore TNC-B06: ponto de entrada que carrega wp-load.php`. Ferramentas de desenvolvimento fora das pastas reconhecidas devem ir para `"exclude"` no `.tnc-check.json` e ficar fora do pacote publicado.

## Origem
Exigência do Plugin Check do WordPress.org e boa prática geral do WordPress. Plugins da equipe tiveram achados do Plugin Check em arquivos de ferramentas que iam dentro do pacote.
