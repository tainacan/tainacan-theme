<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-S02: leitura de $_GET/$_POST/$_REQUEST/$_COOKIE sem sanitização na
 * mesma expressão. Comparação direta, isset/empty e verificação de nonce
 * não contam como uso. Versão leve da regra do WPCS
 * (WordPress.Security.ValidatedSanitizedInput), para quem não roda o PHPCS.
 */
final class SuperglobalUnsanitized extends Rule
{
    const ID = 'TNC-S02';
    const TITLE = 'Entrada HTTP usada sem sanitização';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'seguranca';

    const GLOBALS = ['$_GET', '$_POST', '$_REQUEST', '$_COOKIE', '$_SERVER'];

    /**
     * Chaves de $_SERVER que quem visita controla.
     *
     * O resto de $_SERVER vem do servidor web e não é entrada do visitante,
     * de modo que acusar $_SERVER inteiro seria ruído. Estas cinco não: a
     * query string, o caminho pedido, o referenciador, o agente e o Host
     * chegam no pedido e já apareceram concatenados dentro de href em tema
     * publicado — $_SERVER['QUERY_STRING'] montando o link de cada item da
     * lista, impresso sem esc_url().
     */
    const SERVER_KEYS = ['QUERY_STRING', 'REQUEST_URI', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'HTTP_HOST', 'PHP_SELF', 'PATH_INFO'];

    const SANITIZERS = [
        'sanitize_text_field', 'sanitize_textarea_field', 'sanitize_key', 'sanitize_email', 'sanitize_title',
        'sanitize_title_with_dashes', 'sanitize_file_name', 'sanitize_user', 'sanitize_url', 'sanitize_hex_color',
        'sanitize_html_class', 'sanitize_mime_type', 'sanitize_option', 'sanitize_meta', 'sanitize_term_field',
        'esc_url_raw', 'esc_url', 'esc_html', 'esc_attr', 'esc_textarea', 'esc_sql', 'esc_js',
        'absint', 'intval', 'floatval', 'boolval', 'rest_sanitize_boolean', 'wp_validate_boolean',
        'wp_kses', 'wp_kses_post', 'wp_kses_data', 'filter_var', 'filter_input', 'map_deep', 'array_map',
        'isset', 'empty', 'array_key_exists', 'in_array', 'is_numeric', 'ctype_digit', 'preg_match',
        'wp_verify_nonce', 'check_admin_referer', 'check_ajax_referer', 'wp_safe_redirect', 'is_array',
        'count', 'json_encode', 'wp_json_encode', 'hash_equals', 'sanitize_textarea', 'tainacan_sanitize',
    ];

    protected function check(SourceFile $f)
    {
        $n = $f->count();
        $safe = array_flip(self::SANITIZERS);
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) !== T_VARIABLE || !in_array($f->val($i), self::GLOBALS, true)) {
                continue;
            }
            $name = $f->val($i);

            // De $_SERVER, só as chaves que quem visita controla.
            if ($name === '$_SERVER') {
                $keyTok = $f->type($i + 1) === '[' ? $f->literal($i + 2, $i + 2) : null;
                if ($keyTok === null || !in_array(strtoupper($keyTok), self::SERVER_KEYS, true)) {
                    continue;
                }
            }

            // fim do acesso: $_POST['a']['b']
            $end = $i;
            while ($f->type($end + 1) === '[') {
                $m = $f->match($end + 1);
                if ($m < 0) {
                    break;
                }
                $end = $m;
            }
            $after = $f->type($end + 1);
            $before = $f->type($i - 1);
            if ($after === '=' || $after === T_COALESCE_EQUAL) {
                continue; // escrita
            }
            if ($this->isComparison($after) || $this->isComparison($before)) {
                continue;
            }
            if (in_array($before, [T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST], true)) {
                continue;
            }
            $chain = $f->enclosingCalls($i);
            $ok = false;
            $stringSanitizer = null;
            foreach ($chain as $fn) {
                if (isset($safe[$fn]) || strpos($fn, 'sanitize') !== false || strpos($fn, 'validate') !== false) {
                    $ok = true;
                    if ($stringSanitizer === null && !isset(self::UNSLASHING[$fn]) && (strpos($fn, 'sanitize') === 0 || strpos($fn, 'esc_') === 0 || strpos($fn, 'wp_kses') === 0)) {
                        $stringSanitizer = $fn;
                    }
                    break;
                }
            }
            if ($ok) {
                // sanitizador de texto sem wp_unslash(): as barras do magic quotes do WordPress ficam no valor
                if ($stringSanitizer !== null && $f->project && $f->project->isWordPress() && !$this->hasUnslash($chain) && !$this->isKeyOnly($f, $i, $end)) {
                    $this->report($f, $f->line($i), '`' . ($end > $i ? $f->text($i, $end) : $name) . '` passa por ' . $stringSanitizer . '() sem wp_unslash().', 'O WordPress adiciona barras a todo $_POST/$_GET; sanitize_text_field( wp_unslash( ... ) ) remove-as antes de sanitizar. O Plugin Check exige (ValidatedSanitizedInput.MissingUnslash).');
                }
                continue;
            }
            // $x = isset( $_GET['a'] ) ? $_GET['a'] : '' ainda é uso; ternário com sanitização na mesma linha é aceito
            $stmt = $f->text($f->statementStart($i), $end);
            if (preg_match('/sanitize_|absint|intval|esc_|wp_kses/i', substr($stmt, strrpos($stmt, '=') ?: 0)) && $f->type($end + 1) === ':' ) {
                continue;
            }
            $key = $end > $i ? $f->text($i, $end) : $name;
            $msg = '`' . $key . '` usado sem sanitização.';
            if ($name === '$_REQUEST') {
                $msg .= ' Prefira $_GET ou $_POST: $_REQUEST mistura cookies.';
            }
            if ($name === '$_SERVER') {
                $msg .= ' Esta chave vem no pedido de quem visita, não do servidor.';
            }
            $this->report($f, $f->line($i), $msg, 'Envolva com sanitize_text_field( wp_unslash( ... ) ), absint(), sanitize_key() ou a função adequada ao tipo.');
            $i = $end;
        }
    }

    /** sanitizadores que já dispensam wp_unslash() (o resultado não guarda barras) */
    const UNSLASHING = [
        'absint' => 1, 'intval' => 1, 'floatval' => 1, 'boolval' => 1, 'count' => 1, 'is_array' => 1, 'is_numeric' => 1,
        'sanitize_key' => 1, 'sanitize_html_class' => 1, 'wp_parse_id_list' => 1, 'wp_parse_slug_list' => 1,
        'wp_validate_boolean' => 1, 'rest_sanitize_boolean' => 1, 'filter_var' => 1, 'filter_input' => 1,
        'wp_verify_nonce' => 1, 'check_admin_referer' => 1, 'check_ajax_referer' => 1, 'hash_equals' => 1,
    ];

    private function hasUnslash(array $chain)
    {
        foreach ($chain as $fn) {
            if (in_array($fn, ['wp_unslash', 'stripslashes', 'stripslashes_deep', 'stripslashes_from_strings_only', 'map_deep'], true)) {
                return true;
            }
        }
        return false;
    }

    /** $_GET['x'] usado só como chave de in_array/isset numa lista fixa: as barras não importam */
    private function isKeyOnly(SourceFile $f, $i, $end)
    {
        $after = $f->type($end + 1);
        return $after === ',' && in_array('in_array', $f->enclosingCalls($i), true);
    }

    private function isComparison($t)
    {
        return in_array($t, [T_IS_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL, '<', '>', T_IS_SMALLER_OR_EQUAL, T_IS_GREATER_OR_EQUAL, T_SPACESHIP], true);
    }
}
