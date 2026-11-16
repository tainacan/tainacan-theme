<?php
/**
 * Arquivo-fonte carregado uma vez e compartilhado por todas as regras.
 *
 * Oferece tokens de código (sem espaço nem comentário), pares de parênteses,
 * chamadas de função e de método, funções declaradas, callbacks resolvidos e
 * corpos de laço. Compatível com PHP 7.4: nomes qualificados do PHP 8
 * (T_NAME_*) e a sequência T_STRING/T_NS_SEPARATOR do 7.4 viram o mesmo
 * token 'NAME'.
 */

namespace TainacanStack\Check;

final class SourceFile
{
    /** @var string caminho exibido no relatório (relativo à raiz analisada) */
    public $path;
    /** @var string caminho absoluto, ou '' quando o conteúdo veio do índice do git */
    public $absPath;
    /** @var string */
    public $text;
    /** @var string extensão em minúsculas, sem ponto */
    public $ext;
    /** @var string[] linhas, índice começando em 1 */
    public $lines = [];
    /** @var ProjectContext|null */
    public $project;

    /** @var array<int,array{0:int|string,1:string,2:int}> */
    private $code;
    /** @var array<int,array{0:int,1:string}> linha => texto do comentário */
    private $comments;
    /** @var array<int,int> */
    private $pairs;
    /** @var array<int,array<string,mixed>>|null */
    private $functions;
    /** @var array<int,array<string,mixed>>|null */
    private $classes;
    /** @var array<int,array{0:int,1:int}>|null */
    private $loops;

    public function __construct($path, $absPath, $text, $project = null)
    {
        $this->path = str_replace('\\', '/', $path);
        $this->absPath = $absPath;
        $this->text = $text;
        $this->project = $project;
        $this->ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $split = preg_split("/\r\n|\n|\r/", $text);
        foreach ($split as $n => $line) {
            $this->lines[$n + 1] = $line;
        }
    }

    public function isPhp()
    {
        return $this->ext === 'php' || $this->ext === 'inc' || $this->ext === 'phtml';
    }

    // ------------------------------------------------------------------
    // Tokens
    // ------------------------------------------------------------------

    /** @return array<int,array{0:int|string,1:string,2:int}> */
    public function code()
    {
        if ($this->code === null) {
            $this->tokenize();
        }
        return $this->code;
    }

    /** @return array<int,array{0:int,1:string}> */
    public function comments()
    {
        if ($this->code === null) {
            $this->tokenize();
        }
        return $this->comments;
    }

    private function tokenize()
    {
        $this->code = [];
        $this->comments = [];
        if (!$this->isPhp()) {
            $this->pairs = [];
            return;
        }
        $raw = @token_get_all($this->text);
        $line = 1;
        $nameTypes = [T_STRING, T_NS_SEPARATOR, Tok::NAME_QUALIFIED, Tok::NAME_FULLY_QUALIFIED, Tok::NAME_RELATIVE];
        $pending = null; // nome sendo montado
        $count = count($raw);
        for ($k = 0; $k < $count; $k++) {
            $t = $raw[$k];
            if (is_array($t)) {
                $type = $t[0];
                $value = $t[1];
            } else {
                $type = $t;
                $value = $t;
            }
            $tokLine = $line;
            $line += substr_count($value, "\n");

            $isNamePart = in_array($type, $nameTypes, true)
                || ($type === T_NAMESPACE && isset($raw[$k + 1]) && is_array($raw[$k + 1]) && $raw[$k + 1][0] === T_NS_SEPARATOR);
            if ($isNamePart) {
                if ($pending === null) {
                    $pending = ['NAME', $value, $tokLine];
                } else {
                    $pending[1] .= $value;
                }
                continue;
            }
            if ($pending !== null && $type !== T_WHITESPACE && $type !== T_COMMENT && $type !== T_DOC_COMMENT) {
                $this->code[] = $pending;
                $pending = null;
            }
            if ($type === T_WHITESPACE) {
                if ($pending !== null) {
                    $this->code[] = $pending;
                    $pending = null;
                }
                continue;
            }
            if ($type === T_COMMENT || $type === T_DOC_COMMENT) {
                if ($pending !== null) {
                    $this->code[] = $pending;
                    $pending = null;
                }
                $this->comments[] = [$tokLine, $value];
                continue;
            }
            $this->code[] = [$type, $value, $tokLine];
        }
        if ($pending !== null) {
            $this->code[] = $pending;
        }
        $this->computePairs();
    }

    private function computePairs()
    {
        $this->pairs = [];
        $stack = [];
        foreach ($this->code as $i => $t) {
            $type = $t[0];
            if ($type === '(' || $type === '[' || $type === '{' || $type === T_CURLY_OPEN
                || $type === T_DOLLAR_OPEN_CURLY_BRACES || $type === Tok::ATTRIBUTE) {
                $stack[] = $i;
            } elseif ($type === ')' || $type === ']' || $type === '}') {
                if ($stack) {
                    $open = array_pop($stack);
                    $this->pairs[$open] = $i;
                    $this->pairs[$i] = $open;
                }
            }
        }
    }

    public function count()
    {
        return count($this->code());
    }

    public function type($i)
    {
        $c = $this->code();
        return isset($c[$i]) ? $c[$i][0] : null;
    }

    public function val($i)
    {
        $c = $this->code();
        return isset($c[$i]) ? $c[$i][1] : '';
    }

    public function line($i)
    {
        $c = $this->code();
        if (isset($c[$i])) {
            return $c[$i][2];
        }
        return $i < 0 ? 1 : count($this->lines);
    }

    /** Índice do par correspondente de ( [ { ou -1. */
    public function match($i)
    {
        $this->code();
        return isset($this->pairs[$i]) ? $this->pairs[$i] : -1;
    }

    public function is($i, $type)
    {
        return $this->type($i) === $type;
    }

    /** Nome normalizado: sem barra inicial, minúsculo. */
    public static function norm($name)
    {
        return strtolower(ltrim($name, '\\'));
    }

    /** Último segmento de um nome qualificado, minúsculo. */
    public static function short($name)
    {
        $n = self::norm($name);
        $p = strrpos($n, '\\');
        return $p === false ? $n : substr($n, $p + 1);
    }

    public function isName($i, $name = null)
    {
        if ($this->type($i) !== 'NAME') {
            return false;
        }
        return $name === null || self::norm($this->val($i)) === strtolower($name);
    }

    /** Texto aproximado de um intervalo de tokens (inclusive). */
    public function text($s, $e)
    {
        $out = '';
        $prevWord = false;
        for ($i = $s; $i <= $e; $i++) {
            $v = $this->val($i);
            $word = (bool) preg_match('/^[\w$\\\\]/', $v);
            if ($word && $prevWord) {
                $out .= ' ';
            }
            $out .= $v;
            $prevWord = (bool) preg_match('/[\w\\\\]$/', $v);
        }
        return $out;
    }

    /** Valor de um literal de string quando o intervalo é só um literal; senão null. */
    public function literal($s, $e)
    {
        if ($s !== $e || $this->type($s) !== T_CONSTANT_ENCAPSED_STRING) {
            return null;
        }
        return self::unquote($this->val($s));
    }

    public static function unquote($v)
    {
        $q = $v[0];
        $inner = substr($v, 1, -1);
        if ($q === "'") {
            return str_replace(["\\'", '\\\\'], ["'", '\\'], $inner);
        }
        return stripcslashes($inner);
    }

    /** Todos os pedaços literais de texto (strings, heredoc, HTML) num intervalo, concatenados. */
    public function stringContent($s, $e)
    {
        $out = '';
        for ($i = $s; $i <= $e; $i++) {
            $t = $this->type($i);
            if ($t === T_CONSTANT_ENCAPSED_STRING) {
                $out .= self::unquote($this->val($i));
            } elseif ($t === T_ENCAPSED_AND_WHITESPACE) {
                // pedaço de string com aspas duplas: desfaz os escapes mais comuns (\$ \" \\)
                $out .= str_replace(['\\$', '\\"', '\\\\'], ['$', '"', '\\'], $this->val($i));
            } elseif ($t === T_INLINE_HTML) {
                $out .= $this->val($i);
            } elseif ($t === T_VARIABLE || $t === 'NAME') {
                $out .= $this->val($i);
            } elseif ($t === T_OBJECT_OPERATOR) {
                $out .= '->';
            } elseif ($t === T_DOUBLE_COLON) {
                $out .= '::';
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Chamadas
    // ------------------------------------------------------------------

    /**
     * Separa os argumentos de uma chamada a partir do índice do '('.
     * @return array<int,array{0:int,1:int}> intervalos inclusivos
     */
    public function args($open)
    {
        $close = $this->match($open);
        if ($close < 0 || $close === $open + 1) {
            return [];
        }
        $args = [];
        $start = $open + 1;
        for ($i = $open + 1; $i < $close; $i++) {
            $t = $this->type($i);
            if ($t === '(' || $t === '[' || $t === '{' || $t === T_CURLY_OPEN || $t === T_DOLLAR_OPEN_CURLY_BRACES) {
                $m = $this->match($i);
                if ($m > $i) {
                    $i = $m;
                }
                continue;
            }
            if ($t === ',') {
                if ($i > $start) {
                    $args[] = [$start, $i - 1];
                }
                $start = $i + 1;
            }
        }
        if ($close > $start) {
            $args[] = [$start, $close - 1];
        }
        return $args;
    }

    /**
     * Chamadas de função global pelo nome (sem método, sem declaração).
     * @param string[] $names nomes em minúsculas
     * @return array<int,array<string,mixed>>
     */
    public function calls(array $names)
    {
        $want = array_flip(array_map('strtolower', $names));
        $out = [];
        $n = $this->count();
        for ($i = 0; $i < $n; $i++) {
            if ($this->type($i) !== 'NAME' || $this->type($i + 1) !== '(') {
                continue;
            }
            $name = self::norm($this->val($i));
            if (!isset($want[$name]) && !isset($want[self::short($name)])) {
                continue;
            }
            $prev = $this->type($i - 1);
            if (in_array($prev, [T_FUNCTION, T_OBJECT_OPERATOR, Tok::NULLSAFE, T_DOUBLE_COLON, T_NEW, T_CONST], true)) {
                continue;
            }
            $out[] = [
                'name' => self::short($name),
                'i' => $i,
                'open' => $i + 1,
                'close' => $this->match($i + 1),
                'args' => $this->args($i + 1),
                'line' => $this->line($i),
            ];
        }
        return $out;
    }

    /**
     * Chamadas de método (->nome( , ?->nome( ou ::nome( ).
     * @param string[] $names
     * @return array<int,array<string,mixed>> com 'target' = índice do token antes do operador
     */
    public function methodCalls(array $names)
    {
        $want = array_flip(array_map('strtolower', $names));
        $out = [];
        $n = $this->count();
        for ($i = 1; $i < $n; $i++) {
            if ($this->type($i) !== 'NAME' || $this->type($i + 1) !== '(') {
                continue;
            }
            $op = $this->type($i - 1);
            if ($op !== T_OBJECT_OPERATOR && $op !== Tok::NULLSAFE && $op !== T_DOUBLE_COLON) {
                continue;
            }
            $name = strtolower($this->val($i));
            if (!isset($want[$name])) {
                continue;
            }
            $out[] = [
                'name' => $name,
                'i' => $i,
                'op' => $i - 1,
                'target' => $i - 2,
                'static' => $op === T_DOUBLE_COLON,
                'open' => $i + 1,
                'close' => $this->match($i + 1),
                'args' => $this->args($i + 1),
                'line' => $this->line($i),
            ];
        }
        return $out;
    }

    /** Início do enunciado que contém o índice (após ; { } ou abertura de tag). */
    public function statementStart($i)
    {
        for ($k = $i - 1; $k >= 0; $k--) {
            $t = $this->type($k);
            if ($t === ';' || $t === '{' || $t === '}' || $t === T_OPEN_TAG || $t === T_OPEN_TAG_WITH_ECHO) {
                return $k + 1;
            }
            if ($t === ')' || $t === ']') {
                $m = $this->match($k);
                if ($m >= 0 && $m < $k) {
                    $k = $m;
                }
            }
        }
        return 0;
    }

    /**
     * Cadeia de chamadas que envolvem o índice, de dentro para fora, até o
     * fim do enunciado. Elementos: nome da função/método em minúsculas,
     * '(cast)' para conversões, 'isset'/'empty' para essas construções.
     * @return string[]
     */
    public function enclosingCalls($i)
    {
        $chain = [];
        $depth = 0;
        for ($k = $i - 1; $k >= 0; $k--) {
            $t = $this->type($k);
            if ($t === ';' || $t === '{' || $t === '}') {
                break;
            }
            if ($t === ')' || $t === ']') {
                $m = $this->match($k);
                if ($m >= 0 && $m < $k) {
                    $k = $m;
                }
                continue;
            }
            if ($t === '[') {
                continue;
            }
            if ($t === '(') {
                $prev = $this->type($k - 1);
                if ($prev === 'NAME') {
                    $chain[] = self::short($this->val($k - 1));
                } elseif ($prev === T_ISSET) {
                    $chain[] = 'isset';
                } elseif ($prev === T_EMPTY) {
                    $chain[] = 'empty';
                } elseif ($prev === T_ARRAY) {
                    $chain[] = 'array';
                } else {
                    $chain[] = '(';
                }
            }
            if (in_array($t, [T_INT_CAST, T_DOUBLE_CAST, T_BOOL_CAST], true) && $k === $i - 1) {
                $chain[] = '(cast)';
            }
        }
        return $chain;
    }

    // ------------------------------------------------------------------
    // Estruturas
    // ------------------------------------------------------------------

    /** @return array<int,array<string,mixed>> */
    public function classes()
    {
        if ($this->classes === null) {
            $this->parseStructures();
        }
        return $this->classes;
    }

    /** @return array<int,array<string,mixed>> */
    public function functions()
    {
        if ($this->functions === null) {
            $this->parseStructures();
        }
        return $this->functions;
    }

    private function parseStructures()
    {
        $this->classes = [];
        $this->functions = [];
        $n = $this->count();
        for ($i = 0; $i < $n; $i++) {
            $t = $this->type($i);
            if ($t === T_CLASS || $t === T_TRAIT || $t === T_INTERFACE || $t === Tok::ENUM) {
                $prev = $this->type($i - 1);
                if ($prev === T_DOUBLE_COLON) {
                    continue; // Foo::class
                }
                $name = $prev === T_NEW ? '{anon}' : ($this->type($i + 1) === 'NAME' ? $this->val($i + 1) : '{anon}');
                $extends = '';
                $open = -1;
                for ($k = $i + 1; $k < $n; $k++) {
                    if ($this->type($k) === T_EXTENDS && $this->type($k + 1) === 'NAME') {
                        $extends = $this->val($k + 1);
                    }
                    if ($this->type($k) === '{') {
                        $open = $k;
                        break;
                    }
                    if ($this->type($k) === '(') {
                        $m = $this->match($k);
                        $k = $m > $k ? $m : $k;
                    }
                }
                if ($open < 0) {
                    continue;
                }
                $this->classes[] = [
                    'name' => $name,
                    'extends' => $extends,
                    'start' => $i,
                    'open' => $open,
                    'close' => $this->match($open),
                    'line' => $this->line($i),
                ];
                continue;
            }
            if ($t === T_FUNCTION || $t === T_FN) {
                $this->parseFunction($i);
            }
        }
    }

    private function parseFunction($i)
    {
        $n = $this->count();
        $k = $i + 1;
        if ($this->type($k) === '&') {
            $k++;
        }
        $arrow = $this->type($i) === T_FN;
        $name = null;
        if ($this->type($k) === 'NAME') {
            $name = $this->val($k);
            $k++;
        }
        if ($this->type($k) !== '(') {
            return;
        }
        $paramsClose = $this->match($k);
        if ($paramsClose < 0) {
            return;
        }
        $k = $paramsClose + 1;
        if ($arrow) {
            // fn (...) [: tipo] => expressão
            while ($k < $n && $this->type($k) !== T_DOUBLE_ARROW) {
                $k++;
            }
            $start = $k + 1;
            $end = $start;
            for ($j = $start; $j < $n; $j++) {
                $tt = $this->type($j);
                if ($tt === '(' || $tt === '[' || $tt === '{') {
                    $m = $this->match($j);
                    $j = $m > $j ? $m : $j;
                    $end = $j;
                    continue;
                }
                if ($tt === ')' || $tt === ']' || $tt === '}' || $tt === ',' || $tt === ';') {
                    break;
                }
                $end = $j;
            }
            $this->functions[] = $this->fnEntry($i, null, $start - 1, $end + 1, true, true);
            return;
        }
        if ($this->type($k) === T_USE && $this->type($k + 1) === '(') {
            $k = $this->match($k + 1) + 1;
        }
        // tipo de retorno
        while ($k < $n && $this->type($k) !== '{' && $this->type($k) !== ';') {
            $k++;
        }
        if ($this->type($k) !== '{') {
            $this->functions[] = $this->fnEntry($i, $name, -1, -1, $name === null, false);
            return;
        }
        $this->functions[] = $this->fnEntry($i, $name, $k, $this->match($k), $name === null, false);
    }

    private function fnEntry($i, $name, $open, $close, $closure, $arrow)
    {
        $vis = 'public';
        $static = false;
        for ($k = $i - 1; $k >= 0; $k--) {
            $t = $this->type($k);
            if ($t === T_PRIVATE) {
                $vis = 'private';
            } elseif ($t === T_PROTECTED) {
                $vis = 'protected';
            } elseif ($t === T_STATIC) {
                $static = true;
            } elseif (!in_array($t, [T_PUBLIC, T_ABSTRACT, T_FINAL, Tok::READONLY], true)) {
                break;
            }
        }
        $class = null;
        foreach ($this->classes as $c) {
            if ($c['open'] < $i && $i < $c['close']) {
                $class = $c['name'];
            }
        }
        return [
            'name' => $name,
            'class' => $closure ? null : $class,
            'visibility' => $vis,
            'static' => $static,
            'start' => $i,
            'open' => $open,
            'close' => $close,
            'closure' => $closure,
            'arrow' => $arrow,
            'line' => $this->line($i),
        ];
    }

    /** Função mais interna que contém o índice. */
    public function functionAt($i)
    {
        $best = null;
        foreach ($this->functions() as $f) {
            if ($f['open'] >= 0 && $f['open'] < $i && $i < $f['close']) {
                if ($best === null || $f['open'] > $best['open']) {
                    $best = $f;
                }
            }
        }
        return $best;
    }

    public function classAt($i)
    {
        $best = null;
        foreach ($this->classes() as $c) {
            if ($c['open'] < $i && $i < $c['close']) {
                if ($best === null || $c['open'] > $best['open']) {
                    $best = $c;
                }
            }
        }
        return $best;
    }

    public function findMethod($class, $name)
    {
        $name = strtolower($name);
        $candidates = [];
        foreach ($this->functions() as $f) {
            if ($f['name'] !== null && strtolower($f['name']) === $name && $f['class'] !== null) {
                if ($class === null || strtolower($f['class']) === strtolower($class)) {
                    return $f;
                }
                $candidates[] = $f;
            }
        }
        return count($candidates) === 1 ? $candidates[0] : null;
    }

    public function findFunction($name)
    {
        $name = self::short($name);
        foreach ($this->functions() as $f) {
            if ($f['name'] !== null && $f['class'] === null && strtolower($f['name']) === $name) {
                return $f;
            }
        }
        return null;
    }

    /**
     * Resolve um callback passado como argumento para a função/método/closure
     * declarado neste arquivo. Devolve a entrada de functions() ou null.
     */
    public function resolveCallback($s, $e, $callSite)
    {
        $t = $this->type($s);
        if ($t === T_FUNCTION || $t === T_FN || ($t === T_STATIC && in_array($this->type($s + 1), [T_FUNCTION, T_FN], true))) {
            $at = $t === T_STATIC ? $s + 1 : $s;
            foreach ($this->functions() as $f) {
                if ($f['start'] === $at) {
                    return $f;
                }
            }
            return null;
        }
        $lit = $this->literal($s, $e);
        if ($lit !== null) {
            if (strpos($lit, '::') !== false) {
                list($cls, $m) = explode('::', $lit, 2);
                return $this->findMethod(self::short($cls), $m);
            }
            return $this->findFunction($lit);
        }
        // [ $this, 'metodo' ] ou array( $this, 'metodo' )
        $open = null;
        if ($t === '[' && $this->match($s) === $e) {
            $open = $s;
        } elseif ($t === T_ARRAY && $this->type($s + 1) === '(' && $this->match($s + 1) === $e) {
            $open = $s + 1;
        }
        if ($open === null) {
            return null;
        }
        $parts = $this->args($open);
        if (count($parts) !== 2) {
            return null;
        }
        $method = $this->literal($parts[1][0], $parts[1][1]);
        if ($method === null) {
            return null;
        }
        $first = $this->text($parts[0][0], $parts[0][1]);
        $here = $this->classAt($callSite);
        $class = $here ? $here['name'] : null;
        $firstLit = $this->literal($parts[0][0], $parts[0][1]);
        if ($firstLit !== null) {
            $class = self::short($firstLit);
        } elseif (preg_match('/^([\w\\\\]+)::class$/i', $first, $m) && !in_array(strtolower($m[1]), ['self', 'static'], true)) {
            $class = self::short($m[1]);
        }
        return $this->findMethod($class, $method);
    }

    /** Intervalos (abertura, fechamento) dos corpos de laço. */
    public function loops()
    {
        if ($this->loops !== null) {
            return $this->loops;
        }
        $this->loops = [];
        $n = $this->count();
        $ends = [T_FOREACH => T_ENDFOREACH, T_FOR => T_ENDFOR, T_WHILE => T_ENDWHILE];
        for ($i = 0; $i < $n; $i++) {
            $t = $this->type($i);
            if ($t === T_DO && $this->type($i + 1) === '{') {
                $this->loops[] = [$i + 1, $this->match($i + 1)];
                continue;
            }
            if (!isset($ends[$t]) || $this->type($i + 1) !== '(') {
                continue;
            }
            $cond = $this->match($i + 1);
            if ($cond < 0) {
                continue;
            }
            $b = $cond + 1;
            if ($this->type($b) === '{') {
                $this->loops[] = [$b, $this->match($b)];
            } elseif ($this->type($b) === ':') {
                for ($k = $b; $k < $n; $k++) {
                    if ($this->type($k) === $ends[$t]) {
                        $this->loops[] = [$b, $k];
                        break;
                    }
                }
            } elseif ($this->type($b) !== ';') {
                for ($k = $b; $k < $n; $k++) {
                    if ($this->type($k) === ';') {
                        $this->loops[] = [$b, $k];
                        break;
                    }
                }
            }
        }
        return $this->loops;
    }

    public function inLoop($i)
    {
        foreach ($this->loops() as $l) {
            if ($l[0] < $i && $i < $l[1]) {
                return $l;
            }
        }
        return null;
    }

    /** Há chamada de função com algum destes nomes no intervalo? */
    public function hasCallIn($s, $e, array $names)
    {
        $want = array_flip(array_map('strtolower', $names));
        for ($i = $s; $i <= $e; $i++) {
            if ($this->type($i) === 'NAME' && $this->type($i + 1) === '(') {
                $nm = self::short($this->val($i));
                if (isset($want[$nm])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Entradas de um array literal a partir do '[' ou do T_ARRAY.
     * @return array<int,array{key:?string,ks:int,vs:int,ve:int}>|null
     */
    public function arrayEntries($s)
    {
        if ($this->type($s) === T_ARRAY) {
            $s++;
        }
        $t = $this->type($s);
        if ($t !== '[' && $t !== '(') {
            return null;
        }
        $out = [];
        foreach ($this->args($s) as $r) {
            $key = null;
            $vs = $r[0];
            for ($k = $r[0]; $k <= $r[1]; $k++) {
                $tt = $this->type($k);
                if ($tt === '(' || $tt === '[' || $tt === '{') {
                    $m = $this->match($k);
                    $k = $m > $k ? $m : $k;
                    continue;
                }
                if ($tt === T_DOUBLE_ARROW) {
                    $key = $this->literal($r[0], $k - 1);
                    if ($key === null) {
                        $key = '#' . $this->text($r[0], $k - 1);
                    }
                    $vs = $k + 1;
                    break;
                }
            }
            $out[] = ['key' => $key, 'ks' => $r[0], 'vs' => $vs, 've' => $r[1]];
        }
        return $out;
    }

    public function isArrayStart($i)
    {
        $t = $this->type($i);
        return $t === '[' || ($t === T_ARRAY && $this->type($i + 1) === '(');
    }

    public function arrayEnd($i)
    {
        return $this->type($i) === T_ARRAY ? $this->match($i + 1) : $this->match($i);
    }
}
