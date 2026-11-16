<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W11: $wpdb->prepare() com número de marcadores diferente do número de
 * argumentos, ou com marcador que o WordPress não conhece. O WordPress emite
 * _doing_it_wrong, devolve a consulta sem os valores e a chamada seguinte
 * falha ou roda com dado errado. Caso real: um rename de variável colou
 * texto no marcador (`%1$sql`) e derrubou a página inteira.
 *
 * Marcadores aceitos: %s %d %f %F %i (identificador, WP 6.2+), posicionais
 * %1$s, e %% para o sinal literal. Argumentos passados como um único array
 * ou por `...$args` não são contados (o WordPress aceita os dois).
 */
final class PreparePlaceholders extends Rule
{
    const ID = 'TNC-W11';
    const TITLE = '$wpdb->prepare() com marcadores e argumentos em número diferente';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'wordpress';

    protected function check(SourceFile $f)
    {
        foreach ($f->methodCalls(['prepare']) as $c) {
            if (strtolower($f->val($c['target'])) !== '$wpdb' || count($c['args']) < 1) {
                continue;
            }
            list($s, $e) = $c['args'][0];
            $sql = $this->literalOnly($f, $s, $e);
            if ($sql === null) {
                continue; // formato montado em variável: fora do alcance estático
            }
            $sql = str_replace('%%', '', $sql);
            // marcador estranho: % seguido de algo que não é s d f F i nem posicional
            // %1$status: o WordPress lê %1$s e deixa "tatus" solto no SQL (rename de variável que colou no marcador)
            if (preg_match_all('/%\d+\$[sdfFi][A-Za-z_]+/', $sql, $glued) && $glued[0]) {
                $this->report($f, $c['line'], 'Marcador posicional colado em texto: `' . implode('`, `', array_unique($glued[0])) . '`. O WordPress lê só o marcador e o resto vira SQL solto.', 'Separe com espaço ou aspas, ou use um marcador sequencial.', $f->line($c['close']), Finding::BLOCK);
                continue;
            }
            if (preg_match_all('/%(?!\d+\$)[^sdfFi\s%\'"`,)]/', $sql, $bad) && $bad[0]) {
                $this->report($f, $c['line'], 'Marcador inválido no $wpdb->prepare(): `' . implode('`, `', array_unique($bad[0])) . '`. Só %s, %d, %f, %F, %i e posicionais (%1$s) são aceitos.', 'Confira se um rename ou um `%` solto (LIKE \'%texto%\') não colou no marcador; para % literal use %%.', $f->line($c['close']), Finding::BLOCK);
                continue;
            }
            preg_match_all('/%(\d+)\$[sdfFi]/', $sql, $pos);
            preg_match_all('/%(?!\d+\$)[sdfFi]/', $sql, $seq);
            $needed = $pos[1] ? max(array_map('intval', $pos[1])) : count($seq[0]);
            if ($pos[1] && $seq[0]) {
                $needed = max($needed, count($seq[0]));
            }
            $rest = array_slice($c['args'], 1);
            if (!$rest) {
                continue; // sem argumentos é a TNC-S01 que avisa
            }
            foreach ($rest as $arg) {
                $t = $f->type($arg[0]);
                if ($t === T_ELLIPSIS || $f->isArrayStart($arg[0])) {
                    continue 2; // ...$args ou array literal
                }
                if ($arg[0] === $arg[1] && $t === T_VARIABLE && count($rest) === 1 && $this->looksLikeArray($f, $f->val($arg[0]), $c['i'])) {
                    continue 2; // um array em variável ($args, $values, $ids ou atribuído como array)
                }
                if ($f->type($arg[1]) === ')' && $f->hasCallIn($arg[0], $arg[1], ['array_merge', 'array_values', 'array_map', 'array_fill'])) {
                    continue 2;
                }
            }
            $given = count($rest);
            if ($given !== $needed) {
                $this->report($f, $c['line'], '$wpdb->prepare() com ' . $needed . ' marcador(es) e ' . $given . ' argumento(s).', 'Alinhe marcadores e argumentos; o WordPress emite _doing_it_wrong e a consulta sai sem os valores.', $f->line($c['close']));
            }
        }
    }

    /** A variável parece um array: pelo nome ou por atribuição a array/array_*() na mesma função. */
    private function looksLikeArray(SourceFile $f, $var, $at)
    {
        if (preg_match('/(args|values|valores|params|ids|list|lista|data|dados|array|arr|placeholders|bind)$/i', $var)) {
            return true;
        }
        $fn = $f->functionAt($at);
        for ($i = $fn ? $fn['open'] : 0; $i < $at; $i++) {
            if ($f->type($i) === T_VARIABLE && $f->val($i) === $var && $f->type($i + 1) === '=') {
                if ($f->isArrayStart($i + 2) || ($f->type($i + 2) === 'NAME' && strpos(SourceFile::short($f->val($i + 2)), 'array_') === 0)) {
                    return true;
                }
            }
            if ($f->type($i) === T_VARIABLE && $f->val($i) === $var && $f->type($i + 1) === '[' && $f->type($i + 2) === ']') {
                return true; // $var[] = ...
            }
        }
        return false;
    }

    /** Texto do formato quando é só literal/concatenação de literais e nomes de tabela; null se tem variável de valor. */
    private function literalOnly(SourceFile $f, $s, $e)
    {
        for ($i = $s; $i <= $e; $i++) {
            $t = $f->type($i);
            if ($t === T_VARIABLE && strtolower($f->val($i)) !== '$wpdb' && !preg_match('/(table|tabela|prefix|tbl)/i', $f->val($i))) {
                return null;
            }
            if ($t === 'NAME' && $f->type($i + 1) === '(' && !in_array(SourceFile::short($f->val($i)), ['implode', 'esc_sql'], true)) {
                return null;
            }
        }
        return $f->stringContent($s, $e);
    }
}
