<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W12: wp_localize_script() com booleano ou número no array de dados.
 * O WordPress converte cada valor escalar em string: true vira "1", false
 * vira "" e 0 vira "0", que no JavaScript são todos truthy ou comparam
 * errado com ===. Caso real: `enabled => false` chegou ao JS como "" e um
 * `if (dados.enabled !== false)` ligou um recurso desligado.
 */
final class LocalizeScriptScalars extends Rule
{
    const ID = 'TNC-W12';
    const TITLE = 'wp_localize_script() com booleano ou número (chegam como string)';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'wordpress';

    protected function check(SourceFile $f)
    {
        foreach ($f->calls(['wp_localize_script']) as $c) {
            if (count($c['args']) < 3 || !$f->isArrayStart($c['args'][2][0])) {
                continue;
            }
            foreach ($f->arrayEntries($c['args'][2][0]) ?: [] as $en) {
                $t = $f->type($en['vs']);
                $v = strtolower($f->val($en['vs']));
                $scalar = $en['vs'] === $en['ve']
                    ? ($t === T_LNUMBER || $t === T_DNUMBER || ($t === 'NAME' && in_array($v, ['true', 'false', 'null'], true)))
                    : in_array($t, [T_BOOL_CAST, T_INT_CAST, T_DOUBLE_CAST], true); // (bool) expr, (int) expr: também vira string
                if (!$scalar) {
                    continue;
                }
                $key = $en['key'] !== null ? $en['key'] : $f->val($en['vs']);
                $this->report($f, $f->line($en['vs']), 'wp_localize_script() converte `' . $key . '` em string: true vira "1", false vira "" e números viram texto.', 'Use wp_add_inline_script( $handle, \'var X = \' . wp_json_encode( $dados ) . \';\', \'before\' ), que preserva os tipos, ou converta no JS.');
            }
        }
    }
}
