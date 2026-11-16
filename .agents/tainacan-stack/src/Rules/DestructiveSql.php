<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;
use TainacanStack\Check\SqlRisk;

/**
 * TNC-P04: SQL destrutivo em código ou em arquivo .sql: UPDATE em
 * meta_value sem meta_key, REPLACE() em valor que pode ser serializado,
 * UPDATE/DELETE sem WHERE, DROP/TRUNCATE de tabela do WordPress.
 */
final class DestructiveSql extends Rule
{
    const ID = 'TNC-P04';
    const TITLE = 'SQL destrutivo em tabela do WordPress';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'desempenho';
    const EXTENSIONS = ['php', 'inc', 'sql'];

    protected function check(SourceFile $f)
    {
        if ($f->ext === 'sql') {
            $this->checkSqlFile($f);
            return;
        }
        $seen = [];
        $calls = $f->methodCalls(['query', 'prepare']);
        foreach ($calls as $c) {
            if (!$c['args'] || $f->val($c['target']) !== '$wpdb') {
                continue;
            }
            list($s, $e) = $c['args'][0];
            $sql = $f->stringContent($s, $e);
            if ($s === $e && $f->type($s) === T_VARIABLE) {
                $sql = $this->assignedText($f, $f->val($s), $c['i']);
            }
            $this->emit($f, $sql, $c['line'], $f->line($c['close']), $seen);
        }
        // literais de SQL soltos (montados antes, executados em outro lugar)
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            $t = $f->type($i);
            if ($t !== T_CONSTANT_ENCAPSED_STRING && $t !== T_ENCAPSED_AND_WHITESPACE && $t !== T_START_HEREDOC) {
                continue;
            }
            $txt = $t === T_START_HEREDOC ? $this->heredoc($f, $i) : $f->stringContent($i, $i);
            if (preg_match('/^\s*(update\s+\S+.*\bset\b|delete\s+(\w+\s+)?from\b|drop\s+table\b|truncate\b)/is', $this->continueConcat($f, $i, $txt))) {
                $this->emit($f, $this->continueConcat($f, $i, $txt), $f->line($i), $f->line($i), $seen);
            }
        }
    }

    private function emit(SourceFile $f, $sql, $line, $endLine, array &$seen)
    {
        foreach (SqlRisk::analyze($sql) as $risk) {
            $key = $line . '|' . $risk[1];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $this->report($f, $line, $risk[1], 'Filtre por meta_key (e, se possível, por meta_value NOT LIKE \'a:%\'), faça backup da tabela antes e, para troca de texto, prefira `wp search-replace`, que entende PHP serializado.', $endLine, $risk[0]);
        }
    }

    private function checkSqlFile(SourceFile $f)
    {
        $seen = [];
        $buffer = '';
        $start = 1;
        foreach ($f->lines as $n => $line) {
            if ($buffer === '') {
                $start = $n;
            }
            $trim = ltrim($line);
            if (strpos($trim, '--') === 0 || strpos($trim, '#') === 0) {
                continue;
            }
            $buffer .= ' ' . $line;
            if (strpos($line, ';') !== false) {
                $this->emit($f, $buffer, $start, $n, $seen);
                $buffer = '';
            }
        }
        if (trim($buffer) !== '') {
            $this->emit($f, $buffer, $start, count($f->lines), $seen);
        }
    }

    private function heredoc(SourceFile $f, $i)
    {
        $out = '';
        for ($k = $i + 1; $k < $f->count() && $f->type($k) !== T_END_HEREDOC; $k++) {
            $out .= $f->type($k) === T_ENCAPSED_AND_WHITESPACE ? $f->val($k) : ' ' . $f->val($k) . ' ';
        }
        return $out;
    }

    /** Texto do literal até o fim do enunciado, juntando as partes concatenadas. */
    private function continueConcat(SourceFile $f, $i, $txt)
    {
        $k = $i;
        while ($k < $f->count() && $f->type($k) !== ';') {
            $k++;
        }
        return $f->stringContent($i, $k - 1);
    }

    private function assignedText(SourceFile $f, $var, $at)
    {
        $fn = $f->functionAt($at);
        $from = $fn ? $fn['open'] : 0;
        $txt = '';
        for ($i = $from; $i < $at; $i++) {
            if ($f->type($i) === T_VARIABLE && $f->val($i) === $var && in_array($f->type($i + 1), ['=', T_CONCAT_EQUAL], true)) {
                if ($f->type($i + 1) === '=') {
                    $txt = '';
                }
                $k = $i + 2;
                while ($k < $f->count() && $f->type($k) !== ';') {
                    $k++;
                }
                $txt .= $f->stringContent($i + 2, $k - 1);
            }
        }
        return $txt;
    }
}
