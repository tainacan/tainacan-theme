<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-S05: saída HTML sem escape no ponto de saída.
 *
 * O Plugin Check do WordPress.org (WordPress.Security.EscapeOutput) só olha o
 * ponto do echo: escape feito antes, em outra variável ou em outro arquivo,
 * não conta. A regra segue o mesmo critério: cada expressão de `echo`,
 * `print`, `<?=` e cada argumento de `printf`/`sprintf` impresso precisa
 * ser literal, número, cast numérico, constante ou chamada a uma função que
 * escapa (esc_*, wp_kses*, absint, wp_json_encode...). `__()`, `_e()`, `_x()`
 * e `_n()` sem escape também acusam: tradução pode trazer HTML.
 *
 * Versão leve do sniff, para quem não roda o PHPCS. Não segue variáveis:
 * `$html` montado com escape em cima ainda acusa, e a correção é escapar na
 * saída (ou usar wp_kses_post() quando o HTML é intencional).
 */
final class OutputEscaping extends Rule
{
    const ID = 'TNC-S05';
    const TITLE = 'Saída HTML sem escape no ponto de saída';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'seguranca';

    /** funções cujo retorno já está escapado ou é inofensivo em HTML */
    const SAFE = [
        // escape
        'esc_html', 'esc_attr', 'esc_url', 'esc_js', 'esc_textarea', 'esc_xml', 'esc_url_raw',
        'esc_html__', 'esc_attr__', 'esc_html_x', 'esc_attr_x',
        'wp_kses', 'wp_kses_post', 'wp_kses_data', 'tag_escape', 'sanitize_html_class', 'sanitize_key',
        'wp_json_encode', 'json_encode', 'rawurlencode', 'urlencode', 'htmlspecialchars', 'highlight_string',
        // numérico ou sem caracteres especiais
        'absint', 'intval', 'floatval', 'boolval', 'count', 'strlen', 'number_format', 'number_format_i18n', 'size_format',
        'md5', 'sha1', 'hash', 'uniqid', 'wp_unique_id', 'wp_generate_uuid4', 'get_the_id', 'get_locale',
        'date_i18n', 'wp_date', 'gmdate', 'date', 'human_time_diff', 'wp_create_nonce',
        // funções do WordPress que devolvem HTML já escapado
        'wp_nonce_field', 'wp_nonce_url', 'wp_referer_field', 'wp_original_referer_field',
        'checked', 'selected', 'disabled', 'wp_readonly', 'get_search_query', 'get_avatar', 'get_the_post_thumbnail',
        'wp_get_attachment_image', 'get_submit_button', 'get_comment_author_link', 'post_class', 'body_class',
        'get_body_class', 'get_post_class', 'the_title_attribute',
    ];

    /** funções que imprimem tradução sem escapar */
    const UNSAFE_PRINT = ['_e' => 'esc_html_e', '_ex' => 'esc_html_x + echo'];

    /** chamadas de impressão com formato */
    const PRINTF = ['printf', 'vprintf'];
    const SPRINTF = ['sprintf', 'vsprintf'];

    /** funções de tradução cujo retorno não é escapado */
    const TRANSLATE = ['__', '_x', '_n', '_nx', 'translate', 'esc_sql'];

    public function applies(SourceFile $f)
    {
        return parent::applies($f) && $f->project && $f->project->isWordPress();
    }

    protected function check(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            $t = $f->type($i);
            if ($t === T_ECHO || $t === T_PRINT || $t === T_OPEN_TAG_WITH_ECHO) {
                $end = $this->statementEnd($f, $i + 1);
                foreach ($this->splitTop($f, $i + 1, $end, ',') as $part) {
                    $this->checkExpr($f, $part[0], $part[1]);
                }
                $i = $end;
            }
        }
        foreach ($f->calls(array_keys(self::UNSAFE_PRINT)) as $c) {
            $this->report($f, $c['line'], '`' . $c['name'] . '()` imprime a tradução sem escapar.', 'Use ' . self::UNSAFE_PRINT[$c['name']] . '(): o texto traduzido pode trazer HTML.');
        }
        foreach ($f->calls(self::PRINTF) as $c) {
            // printf dentro de echo já foi avaliado como expressão
            $this->checkFormatCall($f, $c);
        }
    }

    /** Índice do token que fecha o enunciado (`;` ou `?>`). */
    private function statementEnd(SourceFile $f, $from)
    {
        $n = $f->count();
        for ($k = $from; $k < $n; $k++) {
            $t = $f->type($k);
            if ($t === ';' || $t === T_CLOSE_TAG) {
                return $k;
            }
            if ($t === '(' || $t === '[' || $t === '{' || $t === T_CURLY_OPEN || $t === T_DOLLAR_OPEN_CURLY_BRACES) {
                $m = $f->match($k);
                if ($m > $k) {
                    $k = $m;
                }
            }
        }
        return $n - 1;
    }

    /**
     * Divide o intervalo [s, e) pelo separador no nível de cima.
     * @return array<int,array{0:int,1:int}> intervalos inclusivos (vazios são descartados)
     */
    private function splitTop(SourceFile $f, $s, $e, $sep)
    {
        $parts = [];
        $start = $s;
        for ($k = $s; $k < $e; $k++) {
            $t = $f->type($k);
            if ($t === '(' || $t === '[' || $t === '{' || $t === T_CURLY_OPEN || $t === T_DOLLAR_OPEN_CURLY_BRACES) {
                $m = $f->match($k);
                if ($m > $k) {
                    $k = $m;
                }
                continue;
            }
            if ($t === $sep) {
                if ($k > $start) {
                    $parts[] = [$start, $k - 1];
                }
                $start = $k + 1;
            }
        }
        if ($e > $start) {
            $parts[] = [$start, $e - 1];
        }
        return $parts;
    }

    /** Índice do primeiro separador no nível de cima, ou -1. Aceita string ou tipo de token. */
    private function findTop(SourceFile $f, $s, $e, $sep)
    {
        for ($k = $s; $k <= $e; $k++) {
            $t = $f->type($k);
            if ($t === '(' || $t === '[' || $t === '{' || $t === T_CURLY_OPEN || $t === T_DOLLAR_OPEN_CURLY_BRACES) {
                $m = $f->match($k);
                if ($m > $k) {
                    $k = $m;
                }
                continue;
            }
            if ($t === $sep) {
                return $k;
            }
        }
        return -1;
    }

    private function checkExpr(SourceFile $f, $s, $e)
    {
        $bad = $this->unsafeAt($f, $s, $e);
        if ($bad < 0) {
            return;
        }
        $what = $f->text($bad, min($e, $bad + 6));
        if (strlen($what) > 60) {
            $what = substr($what, 0, 57) . '...';
        }
        $this->report($f, $f->line($bad), 'Saída sem escape no ponto de saída: `' . $what . '`.', 'Escape aqui, no echo: esc_html() em texto, esc_attr() em atributo, esc_url() em href/src, wp_kses_post() quando o HTML é intencional; tradução com esc_html__()/esc_attr__(). Escape feito antes, em outra variável, não conta para o Plugin Check.');
    }

    /**
     * Devolve o índice do primeiro token inseguro da expressão, ou -1 quando
     * toda a expressão é segura.
     */
    private function unsafeAt(SourceFile $f, $s, $e)
    {
        // parênteses envolvendo tudo
        while ($s < $e && $f->type($s) === '(' && $f->match($s) === $e) {
            $s++;
            $e--;
        }
        if ($s > $e) {
            return -1;
        }
        // ternário: avalia os dois ramos
        $q = $this->findTop($f, $s, $e, '?');
        if ($q >= 0) {
            $colon = $this->findTop($f, $q + 1, $e, ':');
            if ($colon < 0) {
                return -1;
            }
            if ($colon === $q + 1) {
                // forma `a ?: b`: a condição é o valor
                $a = $this->unsafeAt($f, $s, $q - 1);
            } else {
                $a = $this->unsafeAt($f, $q + 1, $colon - 1);
            }
            if ($a >= 0) {
                return $a;
            }
            return $this->unsafeAt($f, $colon + 1, $e);
        }
        // `??`: os dois lados
        $c = $this->findTop($f, $s, $e, T_COALESCE);
        if ($c >= 0) {
            $a = $this->unsafeAt($f, $s, $c - 1);
            return $a >= 0 ? $a : $this->unsafeAt($f, $c + 1, $e);
        }
        // concatenação: cada pedaço
        $dot = $this->findTop($f, $s, $e, '.');
        if ($dot >= 0) {
            foreach ($this->splitTop($f, $s, $e + 1, '.') as $p) {
                $bad = $this->unsafeAt($f, $p[0], $p[1]);
                if ($bad >= 0) {
                    return $bad;
                }
            }
            return -1;
        }
        $t = $f->type($s);
        // cast numérico ou lógico
        if (in_array($t, [T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST], true)) {
            return -1;
        }
        if ($t === T_STRING_CAST || $t === T_ARRAY_CAST || $t === T_OBJECT_CAST || $t === T_UNSET_CAST) {
            return $this->unsafeAt($f, $s + 1, $e);
        }
        if ($t === '-' || $t === '+' || $t === '!' || $t === '@') {
            return $this->unsafeAt($f, $s + 1, $e);
        }
        if ($s === $e) {
            if ($t === T_CONSTANT_ENCAPSED_STRING || $t === T_LNUMBER || $t === T_DNUMBER) {
                return -1;
            }
            if ($t === 'NAME') {
                $v = strtolower($f->val($s));
                // constante (PHP_EOL, true, null, MINHA_CONST): não é dado externo
                return -1;
            }
            if ($t === T_VARIABLE) {
                return $s;
            }
            if ($t === T_CLASS_C || $t === T_FUNC_C || $t === T_METHOD_C || $t === T_NS_C || $t === T_LINE || $t === T_FILE || $t === T_DIR) {
                return -1;
            }
            return $s;
        }
        // string com aspas duplas e variáveis dentro
        if ($t === '"') {
            for ($k = $s + 1; $k < $e; $k++) {
                if ($f->type($k) === T_VARIABLE || $f->type($k) === T_CURLY_OPEN || $f->type($k) === T_DOLLAR_OPEN_CURLY_BRACES) {
                    return $k;
                }
            }
            return -1;
        }
        if ($t === T_START_HEREDOC) {
            for ($k = $s + 1; $k < $e; $k++) {
                if ($f->type($k) === T_VARIABLE || $f->type($k) === T_CURLY_OPEN) {
                    return $k;
                }
            }
            return -1;
        }
        // chamada de função: nome ( ... )
        if ($t === 'NAME' && $f->type($s + 1) === '(' && $f->match($s + 1) === $e) {
            $name = SourceFile::short(SourceFile::norm($f->val($s)));
            if (in_array($name, self::SPRINTF, true)) {
                return $this->unsafeInFormat($f, ['args' => $f->args($s + 1), 'i' => $s]);
            }
            if ($name === 'implode' || $name === 'join') {
                $args = $f->args($s + 1);
                $list = count($args) >= 2 ? $args[1] : (isset($args[0]) ? $args[0] : null);
                if ($list && $this->isEscapedArrayMap($f, $list[0], $list[1])) {
                    return -1;
                }
                return $list ? $list[0] : $s;
            }
            if (in_array($name, self::TRANSLATE, true)) {
                return $s;
            }
            if (in_array($name, self::SAFE, true) || strpos($name, 'esc_') === 0) {
                return -1;
            }
            if ($f->project && $f->project->isEscapedFunction($name)) {
                return -1; // declarada em "escaped_functions" do .tnc-check.json
            }
            return $s;
        }
        // constante de classe ou estática (Classe::CONST, Classe::$x), chamada de método: inseguro
        if ($t === 'NAME' && $f->type($s + 1) === T_DOUBLE_COLON) {
            if ($s + 2 === $e && $f->type($e) === 'NAME') {
                return -1; // Classe::CONSTANTE
            }
            return $s;
        }
        if ($t === T_NEW) {
            return $s;
        }
        if ($t === T_VARIABLE) {
            // $this->metodo() ou $obj->prop: só o escape no ponto de saída conta
            return $s;
        }
        // operações aritméticas entre números/variáveis
        $arith = $this->findTop($f, $s, $e, '*');
        if ($arith < 0) {
            $arith = $this->findTop($f, $s, $e, '/');
        }
        if ($arith < 0) {
            $arith = $this->findTop($f, $s, $e, '%');
        }
        if ($arith >= 0) {
            return -1; // resultado numérico
        }
        return $s;
    }

    private function isEscapedArrayMap(SourceFile $f, $s, $e)
    {
        if ($f->type($s) !== 'NAME' || SourceFile::short(SourceFile::norm($f->val($s))) !== 'array_map') {
            return false;
        }
        $args = $f->args($s + 1);
        if (!$args) {
            return false;
        }
        $cb = $f->literal($args[0][0], $args[0][1]);
        if ($cb !== null) {
            $cb = SourceFile::short(strtolower($cb));
            return in_array($cb, self::SAFE, true) || strpos($cb, 'esc_') === 0;
        }
        // closure: aceita se tem chamada esc_*/wp_kses dentro
        return $f->hasCallIn($args[0][0], $args[0][1], ['esc_html', 'esc_attr', 'esc_url', 'wp_kses', 'wp_kses_post', 'esc_js', 'absint', 'intval']);
    }

    private function checkFormatCall(SourceFile $f, array $c)
    {
        $bad = $this->unsafeInFormat($f, $c);
        if ($bad < 0) {
            return;
        }
        $what = $f->text($bad, min($c['close'], $bad + 6));
        if (strlen($what) > 60) {
            $what = substr($what, 0, 57) . '...';
        }
        $this->report($f, $f->line($bad), '`printf()` sem escape: `' . $what . '`.', 'Formato com esc_html__()/esc_attr__() e cada argumento escapado (esc_html(), esc_attr(), esc_url(), (int)): %s substitui mas não escapa.');
    }

    /** Em printf/sprintf: formato e argumentos precisam estar escapados. */
    private function unsafeInFormat(SourceFile $f, array $c)
    {
        $args = $c['args'];
        if (!$args) {
            return -1;
        }
        foreach ($args as $k => $a) {
            $bad = $this->unsafeAt($f, $a[0], $a[1]);
            if ($bad >= 0) {
                return $bad;
            }
        }
        return -1;
    }
}
