<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-S01: SQL montado com variável sem $wpdb->prepare().
 *
 * Cada variável que entra no texto da consulta é classificada:
 *  - silêncio: identificador de tabela (logo depois de FROM, JOIN, INTO,
 *    UPDATE, TABLE) ou nome de variável que indica tabela/marcador
 *    ($table, $prefix, $placeholders, $format), desde que não venha da
 *    requisição; $wpdb->tabela; parâmetro tipado int/float/bool; conversão
 *    (int); absint/intval; esc_sql entre aspas; lista de marcadores
 *    (array_fill de '%d'); variável montada só com essas peças;
 *  - bloqueio: parâmetro sem tipo de função pública, ou valor vindo da
 *    requisição ($_GET, $_POST, $request->get_param, $request[...]), em
 *    posição de valor (depois de =, <, >, LIKE, IN (, LIMIT, ou entre aspas);
 *  - aviso: o resto, que não dá para provar seguro: propriedade, retorno de
 *    método, fragmento montado em outro lugar, parâmetro de método privado,
 *    variável fora de posição de valor (coluna, ordenação), SQL inteiro
 *    recebido como parâmetro, esc_sql fora de aspas.
 */
final class WpdbUnprepared extends Rule
{
    const ID = 'TNC-S01';
    const TITLE = 'SQL com variável sem $wpdb->prepare()';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'seguranca';

    const SAFE_CALLS = ['absint', 'intval', 'floatval', 'esc_sql', 'count', 'sizeof', 'sanitize_key', 'sanitize_sql_orderby', 'wp_parse_id_list'];
    const TABLE_NAME = '/(table|tabela|prefix|tbl|_tb$|charset|collate|placeholder|format|fmt|_ph$|^\$?safe|_safe|sql_in)/i';
    const REQUEST_METHODS = ['get_param', 'get_params', 'get_json_params', 'get_body_params', 'get_query_params', 'get_url_params'];

    const SAFE = 0;
    const WARN = 1;
    const BLOCK = 2;

    /** @var int profundidade do rastreamento (evita recursão em $ids = implode($ids)) */
    private $depth = 0;

    protected function check(SourceFile $f)
    {
        $calls = $f->methodCalls(['query', 'get_results', 'get_var', 'get_row', 'get_col', 'prepare']);
        foreach ($calls as $c) {
            if (!$this->isWpdb($f, $c['target']) || empty($c['args'])) {
                continue;
            }
            list($s, $e) = $c['args'][0];
            $endLine = $f->line($c['close']);
            if ($c['name'] === 'prepare') {
                $vars = $this->classify($f, $s, $e);
                if ($vars['level'] > self::SAFE) {
                    $this->report($f, $c['line'], 'Variável ' . implode(', ', $vars['names']) . ' interpolada dentro do texto do $wpdb->prepare(); o prepare não protege o que já entrou na string.', 'Troque por marcador (%s, %d, %f; %i para identificador no WP 6.2+) e passe o valor como argumento; para identificador, valide contra lista fixa.', $endLine, $vars['level'] === self::BLOCK ? Finding::BLOCK : Finding::WARN);
                } elseif (count($c['args']) === 1 && $this->withoutPlaceholders($f, $s, $e)) {
                    $this->report($f, $c['line'], '$wpdb->prepare() sem marcadores nem argumentos (o WordPress emite _doing_it_wrong).', 'Se não há valor variável, chame a consulta direto com o literal.', $endLine, Finding::WARN);
                }
                continue;
            }
            if ($this->startsWithPrepare($f, $s)) {
                continue;
            }
            if ($s === $e && $f->type($s) === T_VARIABLE) {
                $ptype = $this->paramType($f, $f->val($s), $c['i']);
                // SQL recebido pronto do chamador (helper genérico): o risco está em quem chama
                $level = $ptype !== null ? self::WARN : $this->trace($f, $f->val($s), $c['i']);
                if ($level > self::SAFE) {
                    $this->report($f, $c['line'], 'A consulta em ' . $f->val($s) . ' é montada com variável e não passa por $wpdb->prepare()' . ($level === self::WARN ? ' (não foi possível provar que os valores são seguros)' : '') . '.', 'Monte com $wpdb->prepare() e marcadores; nomes de tabela podem usar %i (WP 6.2+) ou $wpdb->prefix.', $endLine, $level === self::BLOCK ? Finding::BLOCK : Finding::WARN);
                }
                continue;
            }
            $vars = $this->classify($f, $s, $e);
            if ($vars['level'] > self::SAFE) {
                $this->report($f, $c['line'], 'SQL com ' . implode(', ', $vars['names']) . ' concatenado ou interpolado, sem $wpdb->prepare()' . ($vars['level'] === self::WARN ? ' (não foi possível provar que é seguro)' : '') . '.', 'Use $wpdb->prepare() com %s/%d/%f/%i.', $endLine, $vars['level'] === self::BLOCK ? Finding::BLOCK : Finding::WARN);
            }
        }
    }

    private function isWpdb(SourceFile $f, $target)
    {
        $v = strtolower($f->val($target));
        if ($f->type($target) === T_VARIABLE && in_array($v, ['$wpdb', '$db', '$database'], true)) {
            return true;
        }
        return $f->type($target) === 'NAME' && $v === 'wpdb' && $f->type($target - 1) === T_OBJECT_OPERATOR;
    }

    private function startsWithPrepare(SourceFile $f, $s)
    {
        return $f->type($s) === T_VARIABLE && strtolower($f->val($s)) === '$wpdb' && $f->type($s + 1) === T_OBJECT_OPERATOR && strtolower($f->val($s + 2)) === 'prepare';
    }

    private function withoutPlaceholders(SourceFile $f, $s, $e)
    {
        $lit = $f->stringContent($s, $e);
        return trim($lit) !== '' && !preg_match('/%[sdfi]|%\d+\$[sdfi]/', $lit);
    }

    /**
     * Classifica as variáveis de um intervalo.
     * @return array{level:int,names:string[]}
     */
    private function classify(SourceFile $f, $s, $e)
    {
        $level = self::SAFE;
        $names = [];
        for ($i = $s; $i <= $e; $i++) {
            if ($f->type($i) !== T_VARIABLE) {
                continue;
            }
            $name = $f->val($i);
            $lower = strtolower($name);
            // $wpdb->prepare( ... ) aninhado: verificado na própria chamada
            if ($lower === '$wpdb' && $f->type($i + 1) === T_OBJECT_OPERATOR && strtolower($f->val($i + 2)) === 'prepare' && $f->type($i + 3) === '(') {
                $i = max($i, $f->match($i + 3));
                continue;
            }
            $desc = $name;
            $end = $i;
            if ($f->type($i + 1) === T_OBJECT_OPERATOR && $f->type($i + 2) === 'NAME') {
                $prop = $f->val($i + 2);
                $desc = $name . '->' . $prop . ($f->type($i + 3) === '(' ? '()' : '');
                $end = $f->type($i + 3) === '(' ? $f->match($i + 3) : $i + 2;
                if ($lower === '$wpdb' || preg_match(self::TABLE_NAME, $prop)) {
                    $i = $end;
                    continue;
                }
            } elseif ($lower === '$wpdb') {
                continue;
            }
            $v = $this->variableLevel($f, $i, $name, $desc !== $name);
            $i = max($i, $end);
            if ($v === self::SAFE) {
                continue;
            }
            $level = max($level, $v);
            $names[] = '`' . $desc . '`';
        }
        return ['level' => $level, 'names' => array_values(array_unique($names))];
    }

    private function variableLevel(SourceFile $f, $i, $name, $isMember)
    {
        if (preg_match(self::TABLE_NAME, $name) || $this->inIdentifierPosition($f, $i)) {
            // o nome sugere tabela/marcador, mas vale conferir se não veio da requisição
            return (!$isMember && $this->paramType($f, $name, $i) === null && $this->trace($f, $name, $i) === self::BLOCK) ? self::BLOCK : self::SAFE;
        }
        if (in_array($f->type($i - 1), [T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST], true)) {
            return self::SAFE;
        }
        $chain = $f->enclosingCalls($i);
        if (in_array('esc_sql', $chain, true) && !array_intersect($chain, ['absint', 'intval', 'floatval'])) {
            // esc_sql só escapa aspas: protege dentro de '...', não em WHERE ID = $v
            return $this->quoted($f, $i) ? self::SAFE : self::WARN;
        }
        if (array_intersect($chain, self::SAFE_CALLS)) {
            return self::SAFE;
        }
        if (in_array('array_map', $chain, true) && $this->arrayMapIsSafe($f, $i)) {
            return self::SAFE;
        }
        if ($isMember) {
            return self::WARN; // $this->prop ou $obj->metodo(): estado interno, não provado
        }
        $param = $this->paramType($f, $name, $i);
        if ($param !== null) {
            if (in_array(strtolower(ltrim($param, '?')), ['int', 'float', 'bool'], true)) {
                return self::SAFE;
            }
            // parâmetro de método privado/protegido: a origem está num chamador da própria classe
            $fn = $f->functionAt($i);
            $level = ($fn && $fn['visibility'] !== 'public') ? self::WARN : self::BLOCK;
        } else {
            $level = $this->trace($f, $name, $i);
        }
        // fora de posição de valor (coluna, ordenação, fragmento) não se prova injeção: aviso
        return $level === self::BLOCK && !$this->inValuePosition($f, $i) ? self::WARN : $level;
    }

    /** A variável está onde vai um valor: depois de =, <, >, LIKE, IN (, LIMIT, OFFSET, VALUES ( ou entre aspas. */
    private function inValuePosition(SourceFile $f, $i)
    {
        $prev = $this->adjacentLiteral($f, $i, -1);
        if (preg_match('/(=|<|>|\blike|\bin\s*\(|\blimit|\boffset|\bvalues\s*\(|\bthen|\bbetween|\bagainst\s*\()\s*[\'"%(]*$/i', $prev)) {
            return true;
        }
        $next = $this->adjacentLiteral($f, $i, 1);
        return (bool) preg_match('/[\'"]$/', $prev) && (bool) preg_match('/^%?[\'"]/', $next);
    }

    /** O valor está entre aspas no SQL (literal antes termina em ' e o depois começa com ')? */
    private function quoted(SourceFile $f, $i)
    {
        // olha em volta da chamada esc_sql( ... ) inteira, não da variável (que pode ter [ 'chave' ])
        $start = $i;
        $end = $i;
        for ($k = $i - 1; $k >= 0 && $k > $i - 30; $k--) {
            if ($f->type($k) === 'NAME' && strtolower($f->val($k)) === 'esc_sql' && $f->type($k + 1) === '(') {
                $start = $k;
                $end = $f->match($k + 1);
                break;
            }
        }
        $prev = $this->adjacentLiteral($f, $start, -1);
        $next = $this->adjacentLiteral($f, $end, 1);
        return (bool) preg_match('/[\'"]%?$/', $prev) && (bool) preg_match('/^%?[\'"]/', $next);
    }

    private function adjacentLiteral(SourceFile $f, $i, $dir)
    {
        for ($k = $i + $dir, $steps = 0; $steps < 8 && $k >= 0 && $k < $f->count(); $k += $dir, $steps++) {
            $t = $f->type($k);
            if ($t === T_ENCAPSED_AND_WHITESPACE) {
                return $f->val($k);
            }
            if ($t === T_CONSTANT_ENCAPSED_STRING) {
                return SourceFile::unquote($f->val($k));
            }
            if (!in_array($t, ['.', '"', T_CURLY_OPEN, '{', '}', ']', '[', T_OBJECT_OPERATOR, 'NAME', '(', ')'], true)) {
                break;
            }
        }
        return '';
    }

    /** O texto literal logo antes da variável termina em FROM/JOIN/INTO/UPDATE/TABLE? */
    private function inIdentifierPosition(SourceFile $f, $i)
    {
        $prev = '';
        for ($k = $i - 1; $k >= 0 && $k > $i - 6; $k--) {
            $t = $f->type($k);
            if ($t === T_ENCAPSED_AND_WHITESPACE || $t === T_CONSTANT_ENCAPSED_STRING) {
                $prev = $t === T_CONSTANT_ENCAPSED_STRING ? SourceFile::unquote($f->val($k)) : $f->val($k);
                break;
            }
            if (!in_array($t, ['.', '"', T_CURLY_OPEN, '{'], true)) {
                break;
            }
        }
        return (bool) preg_match('/\b(from|join|into|update|table|exists|alter\s+table)\s*[`"]?$/i', $prev);
    }

    /** Tipo declarado do parâmetro da função que contém $i, '' se sem tipo, null se não é parâmetro. */
    private function paramType(SourceFile $f, $name, $at)
    {
        $fn = $f->functionAt($at);
        if (!$fn) {
            return null;
        }
        $k = $fn['start'];
        while ($k < $f->count() && $f->type($k) !== '(') {
            $k++;
        }
        $close = $f->match($k);
        $type = '';
        for ($i = $k + 1; $i < $close; $i++) {
            $t = $f->type($i);
            if ($t === ',') {
                $type = '';
                continue;
            }
            if ($t === T_VARIABLE) {
                if ($f->val($i) === $name) {
                    return $type;
                }
                // pula valor padrão até a próxima vírgula
                while ($i < $close && $f->type($i) !== ',') {
                    if (in_array($f->type($i), ['(', '['], true)) {
                        $i = $f->match($i);
                    }
                    $i++;
                }
                $type = '';
                continue;
            }
            if ($t === 'NAME' || $t === T_ARRAY || $t === '?') {
                $type .= $f->val($i);
            }
        }
        return null;
    }

    private function arrayMapIsSafe(SourceFile $f, $i)
    {
        for ($k = $i - 1; $k >= 0 && $k > $i - 40; $k--) {
            if ($f->type($k) === 'NAME' && strtolower($f->val($k)) === 'array_map' && $f->type($k + 1) === '(') {
                $args = $f->args($k + 1);
                if (!$args) {
                    return false;
                }
                $cb = $f->literal($args[0][0], $args[0][1]);
                return in_array($cb, ['absint', 'intval', 'esc_sql', 'floatval'], true);
            }
        }
        return false;
    }

    /**
     * Há leitura da requisição sem conversão no intervalo? Superglobais,
     * $request->get_param() e $request['x'] contam; passando por (int),
     * absint, intval, esc_sql, sanitize_key ou wp_parse_id_list, não.
     */
    private function rawRequestIn(SourceFile $f, $s, $e)
    {
        for ($i = $s; $i <= $e; $i++) {
            if ($f->type($i) !== T_VARIABLE) {
                continue;
            }
            $v = strtolower($f->val($i));
            $isSuper = in_array($v, ['$_get', '$_post', '$_request', '$_cookie'], true);
            $isReqObj = in_array($v, ['$request', '$req', '$wp_rest_request'], true)
                && (($f->type($i + 1) === T_OBJECT_OPERATOR && in_array(strtolower($f->val($i + 2)), self::REQUEST_METHODS, true)) || $f->type($i + 1) === '[');
            if (!$isSuper && !$isReqObj) {
                continue;
            }
            if (in_array($f->type($i - 1), [T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST], true)) {
                continue;
            }
            if (array_intersect($f->enclosingCalls($i), self::SAFE_CALLS)) {
                continue;
            }
            return true;
        }
        return false;
    }

    /** Segue as atribuições anteriores da variável na mesma função. */
    private function trace(SourceFile $f, $var, $at)
    {
        if ($this->depth > 3) {
            return self::WARN;
        }
        $this->depth++;
        try {
            return $this->doTrace($f, $var, $at);
        } finally {
            $this->depth--;
        }
    }

    private function doTrace(SourceFile $f, $var, $at)
    {
        $fn = $f->functionAt($at);
        $from = $fn ? $fn['open'] : 0;
        $found = false;
        $level = self::SAFE;
        $n = $f->count();
        for ($i = $from; $i < $at; $i++) {
            if ($f->type($i) !== T_VARIABLE || $f->val($i) !== $var) {
                continue;
            }
            $op = $f->type($i + 1);
            if ($op !== '=' && $op !== T_CONCAT_EQUAL) {
                // foreach ( $lista as $var ) também atribui
                if ($f->type($i - 1) === T_AS || $f->type($i - 1) === T_DOUBLE_ARROW) {
                    $found = true;
                    $level = max($level, self::WARN);
                }
                continue;
            }
            $found = true;
            $s = $i + 2;
            $end = $s;
            while ($end < $n && $f->type($end) !== ';') {
                if (in_array($f->type($end), ['(', '[', '{'], true)) {
                    $m = $f->match($end);
                    $end = $m > $end ? $m : $end;
                }
                $end++;
            }
            $rhs = $f->text($s, $end - 1);
            if ($this->rawRequestIn($f, $s, $end - 1)) {
                $level = self::BLOCK;
                continue;
            }
            if ($this->startsWithPrepare($f, $s) || preg_match('/^(absint|intval|floatval|esc_sql|sanitize_key|wp_parse_id_list|count)\s*\(|^\((int|float|bool)\)/i', $rhs) || preg_match('/array_fill\s*\(/', $rhs)) {
                continue;
            }
            if ($f->type($s) === 'NAME' && strtolower($f->val($s)) === 'array_map' && $this->arrayMapIsSafe($f, $s + 2)) {
                continue;
            }
            $sub = $this->classify($f, $s, $end - 1);
            $level = max($level, $sub['level']);
            if ($level === self::BLOCK) {
                return $level;
            }
        }
        return $found ? $level : self::WARN;
    }
}
