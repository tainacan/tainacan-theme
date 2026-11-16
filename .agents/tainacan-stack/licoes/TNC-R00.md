# TNC-R00 · Pasta sem cabeçalho Plugin Name: na raiz

**Severidade:** bloqueio (sempre) · **Categoria:** release · **Arquivos:** arquivos .php da raiz do projeto (só com `tnc-check --release`)

## Sintoma
O `tnc-check --release` termina com um único bloqueio e nenhuma outra conferência de release aparece. Ao instalar o ZIP gerado a partir da pasta, o WordPress recusa o pacote dizendo que nenhum plugin válido foi encontrado, ou o plugin não aparece na lista de plugins depois de copiado para o servidor.

## Causa
O WordPress só reconhece como plugin um arquivo `.php` que esteja direto em `wp-content/plugins/` ou no primeiro nível da pasta do plugin e tenha o cabeçalho `Plugin Name:` nos primeiros 8 KiB (WordPress 6.9.4, `wp-admin/includes/plugin.php:297-325` e `:340-344` em `get_plugins()`, `:74-94` em `get_plugin_data()`, e `wp-includes/functions.php:6910` em `get_file_data()`). Um pacote sem esse arquivo é recusado na instalação com "No valid plugins were found." (`wp-admin/includes/class-plugin-upgrader.php:490`). As conferências de release dependem desse arquivo principal: é dele que saem a versão (TNC-R01), o `Text Domain` (TNC-R04) e o `Requires Plugins` (TNC-R05). Sem ele, não há o que comparar.

## O que a regra detecta
- A raiz do projeto não tem nenhum arquivo `.php` com uma linha `Plugin Name:` nos primeiros 8 KiB. A linha pode começar com espaços, `*`, `/`, `#` ou `@`, como num docblock.
- Cabeçalho na mesma linha do `<?php` (`<?php /* Plugin Name: Meu Plugin */`) não é reconhecido, embora o WordPress o aceite (`wp-includes/functions.php:6938`). É um falso positivo conhecido; ponha o cabeçalho numa linha própria.
- A raiz é procurada a partir da pasta passada em `--release=<pasta>` (sem valor, o primeiro caminho informado ou a pasta atual): é a própria pasta ou a primeira acima dela que tenha `.tnc-check.json`, `.git` ou um `.php` com o cabeçalho (nessa busca, lido nos primeiros 4 KiB). Por isso, rodar numa subpasta sem cabeçalho, ou na raiz de um repositório que guarda o plugin numa subpasta, gera o achado.
- Só arquivos diretamente na raiz contam: um cabeçalho em `src/plugin.php` ou em outra subpasta não evita o achado.
- Quando o achado aparece, as demais conferências (TNC-R01 a TNC-R06) não rodam.
- Não roda fora do `--release`: a análise normal de arquivos não exige cabeçalho.

## Como corrigir
1. Rode o `--release` apontando para a pasta do plugin: `tnc-check --release=<pasta do plugin>`.
2. Se o arquivo principal não tem cabeçalho, acrescente-o no início do arquivo, na raiz da pasta do plugin.
3. Se o repositório guarda o plugin numa subpasta, rode o `--release` apontando para ela, e não para a raiz do repositório.

```php
<?php
/**
 * Plugin Name: Meu Plugin
 * Version: 1.0.0
 * Text Domain: meu-plugin
 * Requires Plugins: tainacan
 */
```

## Quando suprimir
As conferências de release não aceitam supressão em linha nem baseline. Se a pasta não é um plugin (tema, biblioteca, repositório de documentação), não rode o `--release` nela.

## Origem
Regra do próprio `tnc-check`: as conferências de release precisam do arquivo principal para ler versão e cabeçalhos, e um pacote sem esse arquivo não é instalável no WordPress.
