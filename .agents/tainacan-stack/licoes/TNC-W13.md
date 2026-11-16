# TNC-W13 · Função PHP com equivalente do WordPress

**Severidade:** aviso (sempre) · **Categoria:** wordpress · **Arquivos:** .php (só em projeto WordPress)

## Sintoma
O Plugin Check recusa o plugin por `WordPress.WP.AlternativeFunctions`, `WordPress.DateTime.RestrictedFunctions.date_date` ou `WordPress.PHP.DiscouragedPHPFunctions`. Datas gravadas com três horas de diferença entre sites. Arquivo que o plugin cria não abre em hospedagem com usuário de FTP diferente do PHP. `json_encode()` devolve `false` para um metadado com byte inválido e o JavaScript recebe `false` em vez de um objeto. E-mail que ignora o SMTP configurado pelo administrador.

## Causa
Cada função nativa tem um equivalente do WordPress que resolve um problema de ambiente:

| Nativa | Use | Por quê |
|---|---|---|
| `json_encode()` | `wp_json_encode()` | trata UTF-8 inválido e tem fallback por profundidade |
| `date()` | `gmdate()` para banco e cálculo; `wp_date()`/`date_i18n()` para exibição | o WordPress fixa o fuso do PHP em UTC; `date()` ignora o fuso do site |
| `date_default_timezone_set()` | `wp_timezone()` | o WordPress depende do fuso UTC para gravar e comparar datas |
| `rand()`, `mt_rand()` | `wp_rand()` | gerador criptográfico |
| `unlink()` | `wp_delete_file()` | filtro `wp_delete_file` e compatibilidade com a hospedagem |
| `fopen()`/`fwrite()`/`file_put_contents()`/`mkdir()`/`rename()`/`chmod()`/`copy()`/`touch()` | `WP_Filesystem` (`wp_mkdir_p()` para pastas) | permissões de arquivo do servidor; `fopen('php://output')` e `php://temp` são aceitos |
| `file_get_contents( URL )`, `curl_*`, `fsockopen()` | `wp_remote_get()`/`wp_remote_post()` + `wp_remote_retrieve_body()` | proxy, timeout, SSL, filtros e `allow_url_fopen` desligado |
| `parse_url()` | `wp_parse_url()` | resultado consistente entre versões do PHP |
| `strip_tags()` | `wp_strip_all_tags()` | remove também o conteúdo de `<script>` e `<style>` |
| `mail()` | `wp_mail()` | SMTP e filtros do site |
| `move_uploaded_file()` | `wp_handle_upload()` / `media_handle_upload()` | valida tipo, nome e destino |
| `set_time_limit()`, `ini_set()`, `error_reporting()` | lotes menores, processo em segundo plano, `wp-config.php` | em hospedagem compartilhada são ignorados ou afetam o site inteiro |
| heredoc com HTML | template em arquivo com `ob_start()`/`include`/`ob_get_clean()` | o escape de cada valor fica visível no ponto de saída (TNC-S05) |

## O que a regra detecta
As chamadas da tabela, por nome, em projeto WordPress. Não acusa: método com o mesmo nome (`$obj->date()`), `fopen`/`fwrite` com fluxo `php://`, `file_get_contents()` de caminho local, `ini_set()` de chave inofensiva (ex.: `precision`), heredoc sem tag HTML (SQL, texto). Em arquivo que abre um fluxo `php://`, as escritas `fwrite()`/`fputs()` são aceitas, porque a regra não segue a variável do recurso.

## Como corrigir
Troque pela função da tabela. Para arquivos:

```php
global $wp_filesystem;
if ( ! function_exists( 'WP_Filesystem' ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}
WP_Filesystem();
$wp_filesystem->put_contents( $path, $content, FS_CHMOD_FILE );
```

Para HTTP:

```php
$response = wp_remote_get( $url, [ 'timeout' => 15 ] );
if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
    return null;
}
$body = wp_remote_retrieve_body( $response );
```

## Quando suprimir
Quando a função do WordPress realmente não serve, com a razão: `// tnc-check-ignore TNC-W13: fluxo de 2 GB lido em pedaços com fread(); WP_Filesystem carrega tudo em memória`. Ou: `// tnc-check-ignore TNC-W13: gmdate() aqui; o formato é fixo e não depende de fuso` não vale, porque aí basta usar `gmdate()`.

## Origem
Plugin de certificados com `date()` em todo lugar gravou validade com fuso errado em sites fora de Brasília. Plugin de preservação digital, por natureza cheio de operações de arquivo, acumulou 287 achados e precisou escolher caso a caso entre `WP_Filesystem` e supressão justificada. O Plugin Check acusa todos.
