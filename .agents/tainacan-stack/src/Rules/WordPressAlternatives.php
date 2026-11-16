<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W13: função PHP com equivalente do WordPress que o Plugin Check exige
 * (WordPress.WP.AlternativeFunctions, WordPress.DateTime.RestrictedFunctions,
 * WordPress.PHP.DiscouragedPHPFunctions, WordPress.PHP.IniSet).
 *
 * O equivalente resolve um problema real em cada caso: wp_json_encode()
 * trata UTF-8 inválido e devolve string sempre; wp_remote_get() passa por
 * proxy, timeout e filtros; WP_Filesystem respeita permissões de hospedagem;
 * wp_rand() usa CSPRNG; gmdate()/wp_date() não dependem do fuso do PHP, que
 * o WordPress fixa em UTC; wp_mail() respeita SMTP configurado por plugin.
 * Heredoc com HTML sai para um arquivo de template com ob_start(), onde o
 * escape no ponto de saída é visível.
 */
final class WordPressAlternatives extends Rule
{
    const ID = 'TNC-W13';
    const TITLE = 'Função PHP com equivalente do WordPress';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'wordpress';

    /** função => [substituto, motivo] */
    const MAP = [
        'json_encode' => ['wp_json_encode()', 'trata UTF-8 inválido e nunca devolve false sem aviso'],
        'unlink' => ['wp_delete_file()', 'passa pelo filtro wp_delete_file e respeita a hospedagem'],
        'rand' => ['wp_rand()', 'gerador criptográfico'],
        'mt_rand' => ['wp_rand()', 'gerador criptográfico'],
        'srand' => ['wp_rand()', 'sem semente manual'],
        'mt_srand' => ['wp_rand()', 'sem semente manual'],
        'date' => ['gmdate() para banco e cálculo, wp_date()/date_i18n() para exibição', 'o WordPress fixa o fuso do PHP em UTC; date() ignora o fuso do site'],
        'date_default_timezone_set' => ['wp_timezone() e DateTimeZone', 'o WordPress depende do fuso do PHP em UTC para gravar datas'],
        'parse_url' => ['wp_parse_url()', 'resultado consistente entre versões do PHP'],
        'strip_tags' => ['wp_strip_all_tags()', 'remove também o conteúdo de script e style'],
        'mail' => ['wp_mail()', 'respeita SMTP e filtros configurados no site'],
        'move_uploaded_file' => ['wp_handle_upload() ou media_handle_upload()', 'valida tipo, nome e destino'],
        'curl_init' => ['wp_remote_get()/wp_remote_post()', 'proxy, timeout, SSL e filtros do WordPress'],
        'curl_exec' => ['wp_remote_get()/wp_remote_post()', 'proxy, timeout, SSL e filtros do WordPress'],
        'fsockopen' => ['wp_remote_get()/wp_remote_post()', 'proxy, timeout, SSL e filtros do WordPress'],
        'fopen' => ['WP_Filesystem (ou wp_remote_get() para URL)', 'permissões de arquivo da hospedagem'],
        'fwrite' => ['WP_Filesystem->put_contents()', 'permissões de arquivo da hospedagem'],
        'fputs' => ['WP_Filesystem->put_contents()', 'permissões de arquivo da hospedagem'],
        'file_put_contents' => ['WP_Filesystem->put_contents()', 'permissões de arquivo da hospedagem'],
        'mkdir' => ['wp_mkdir_p()', 'cria o caminho inteiro com as permissões certas'],
        'rmdir' => ['WP_Filesystem->rmdir()', 'permissões de arquivo da hospedagem'],
        'rename' => ['WP_Filesystem->move()', 'permissões de arquivo da hospedagem'],
        'chmod' => ['WP_Filesystem->chmod()', 'permissões de arquivo da hospedagem'],
        'touch' => ['WP_Filesystem->touch()', 'permissões de arquivo da hospedagem'],
        'copy' => ['WP_Filesystem->copy() ou copy_dir()', 'permissões de arquivo da hospedagem'],
        'set_time_limit' => ['lotes menores ou processo em segundo plano', 'em hospedagem compartilhada a chamada é ignorada ou derruba o worker'],
        'ini_set' => ['constantes do wp-config.php ou configuração da hospedagem', 'muda o comportamento do PHP para o site inteiro'],
        'error_reporting' => ['WP_DEBUG no wp-config.php', 'esconde erros de outros plugins'],
    ];

    /** fopen/fclose/fread com fluxo php:// é aceito (ex.: php://output para CSV) */
    const STREAM_OK = ['fopen', 'fwrite', 'fputs', 'file_put_contents'];

    public function applies(SourceFile $f)
    {
        return parent::applies($f) && $f->project && $f->project->isWordPress();
    }

    protected function check(SourceFile $f)
    {
        $calls = $f->calls(array_keys(self::MAP));
        // arquivo que abre fluxo php:// (CSV para php://output, php://temp): as escritas nele são aceitas
        $hasPhpStream = false;
        foreach ($calls as $c) {
            if ($c['name'] === 'fopen' && isset($c['args'][0]) && preg_match('#^php://#i', $f->stringContent($c['args'][0][0], $c['args'][0][1]))) {
                $hasPhpStream = true;
            }
        }
        foreach ($calls as $c) {
            $name = $c['name'];
            $first = isset($c['args'][0]) ? $f->stringContent($c['args'][0][0], $c['args'][0][1]) : '';
            if (in_array($name, self::STREAM_OK, true) && preg_match('#^php://#i', $first)) {
                continue;
            }
            if (($name === 'fwrite' || $name === 'fputs') && $hasPhpStream) {
                continue;
            }
            if ($name === 'fopen' && $this->isRemote($first)) {
                $this->report($f, $c['line'], '`fopen()` com URL: use wp_remote_get().', 'A API HTTP do WordPress passa por proxy, timeout, SSL e filtros do site.');
                continue;
            }
            if ($name === 'ini_set' && preg_match('/^(display_errors|error_log|log_errors|memory_limit|max_execution_time|upload_max_filesize|post_max_size|default_charset|zlib\.output_compression|session\.)/i', $first) === 0 && $first !== '' && strpos($first, '$') === false) {
                continue; // chaves inofensivas (ex.: precision) ficam de fora
            }
            list($alt, $why) = self::MAP[$name];
            $this->report($f, $c['line'], '`' . $name . '()`: use ' . $alt . '.', ucfirst($why) . '. Se a função do WordPress não serve aqui, diga por quê numa supressão justificada.');
        }
        foreach ($f->calls(['file_get_contents']) as $c) {
            $first = isset($c['args'][0]) ? $f->stringContent($c['args'][0][0], $c['args'][0][1]) : '';
            if ($this->isRemote($first)) {
                $this->report($f, $c['line'], '`file_get_contents()` com URL: use wp_remote_get() e wp_remote_retrieve_body().', 'allow_url_fopen costuma estar desligado na hospedagem, e a API HTTP do WordPress trata proxy, timeout e erros.');
            }
        }
        $this->heredocWithHtml($f);
    }

    private function isRemote($s)
    {
        return (bool) preg_match('#^(https?:)?//|^(ftp|ftps|ssh2)://#i', trim($s)) || preg_match('#["\']https?://#i', $s) === 1;
    }

    private function heredocWithHtml(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) !== T_START_HEREDOC) {
                continue;
            }
            $body = '';
            for ($k = $i + 1; $k < $n && $f->type($k) !== T_END_HEREDOC; $k++) {
                if ($f->type($k) === T_ENCAPSED_AND_WHITESPACE) {
                    $body .= $f->val($k);
                }
            }
            if (preg_match('/<[a-z][a-z0-9-]*[\s>\/]/i', $body)) {
                $this->report($f, $f->line($i), 'Heredoc com HTML.', 'Mova o HTML para um arquivo de template incluído com ob_start()/include/ob_get_clean(): o escape de cada valor fica visível no ponto de saída, e o editor e o Plugin Check enxergam o HTML.');
            }
            $i = $k;
        }
    }
}
