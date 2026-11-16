<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T01: callback de tainacan_register_admin_hook() que imprime em vez de
 * devolver string, ou contexto/posição inválidos.
 *
 * O core chama o callback na hora do registro (call_user_func) e só aceita
 * o retorno se for string (views/admin/classes/hooks/class-tainacan-admin-hooks.php:48-59).
 * O registro acontece em admin_init, antes dos cabeçalhos: o que for
 * impresso vaza ("headers already sent", redirect que falha em silêncio) e
 * o gancho não é registrado.
 */
final class AdminHookOutput extends Rule
{
    const ID = 'TNC-T01';
    const TITLE = 'Gancho de admin do Tainacan imprime em vez de devolver string';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'tainacan';

    const CONTEXTS = ['collection', 'metadatum', 'item', 'taxonomy', 'term', 'filter', 'role', 'metadataSection'];
    const POSITIONS = ['begin-left', 'begin-right', 'end-left', 'end-right'];
    const PRINTERS = ['printf', 'vprintf', 'readfile', 'print_r', 'var_dump', 'wp_nonce_field', 'submit_button', 'settings_fields', 'do_settings_sections', 'wp_editor', 'the_title', 'the_content', 'esc_html_e', 'esc_attr_e', '_e', '_ex', 'get_template_part', 'load_template'];

    protected function check(SourceFile $f)
    {
        foreach ($f->calls(['tainacan_register_admin_hook']) as $c) {
            $args = $c['args'];
            if (count($args) < 2) {
                continue;
            }
            $ctx = $f->literal($args[0][0], $args[0][1]);
            if ($ctx !== null && !in_array($ctx, self::CONTEXTS, true)) {
                $this->report($f, $c['line'], 'Contexto \'' . $ctx . '\' inválido: tainacan_register_admin_hook() devolve false e o gancho não aparece.', 'Contextos aceitos: ' . implode(', ', self::CONTEXTS) . ' (filtro tainacan-admin-hooks-contexts).');
            }
            if (isset($args[2])) {
                $pos = $f->literal($args[2][0], $args[2][1]);
                if ($pos !== null && !in_array($pos, self::POSITIONS, true)) {
                    $this->report($f, $c['line'], 'Posição \'' . $pos . '\' inválida: o gancho não é registrado.', 'Posições aceitas: ' . implode(', ', self::POSITIONS) . '.');
                }
            }
            $fn = $f->resolveCallback($args[1][0], $args[1][1], $c['i']);
            if (!$fn || $fn['open'] < 0) {
                continue;
            }
            $where = $fn['name'] ? '`' . $fn['name'] . '`' : 'closure';
            if ($fn['arrow']) {
                continue; // fn() => '...' sempre devolve o valor da expressão
            }
            $prints = $this->printsAt($f, $fn['open'], $fn['close']);
            $buffers = $f->hasCallIn($fn['open'], $fn['close'], ['ob_start']);
            if ($prints !== null && !$buffers) {
                $this->report($f, $f->line($prints), 'O callback ' . $where . ' do gancho de admin imprime (' . $this->label($f, $prints) . '): a saída vaza em admin_init e o gancho não é registrado.', 'Use ob_start(); include ...; return (string) ob_get_clean();', $f->line($fn['close']));
                continue;
            }
            if (!$this->returnsValue($f, $fn['open'], $fn['close'])) {
                $this->report($f, $fn['line'], 'O callback ' . $where . ' do gancho de admin não devolve string: o Tainacan descarta o gancho.', 'Termine com return (string) ob_get_clean(); ou return $html;', $f->line($fn['close']));
            }
        }
    }

    /** Índice do primeiro token que imprime, ou null. */
    private function printsAt(SourceFile $f, $s, $e)
    {
        for ($i = $s + 1; $i < $e; $i++) {
            $t = $f->type($i);
            if (in_array($t, [T_ECHO, T_PRINT, T_INLINE_HTML, T_OPEN_TAG_WITH_ECHO, T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE], true)) {
                return $i;
            }
            if ($t === T_CLOSE_TAG) {
                return $i;
            }
            if ($t === 'NAME' && $f->type($i + 1) === '(' && in_array(SourceFile::short($f->val($i)), self::PRINTERS, true)
                && !in_array($f->type($i - 1), [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true)) {
                if (SourceFile::short($f->val($i)) === 'print_r' && count($f->args($i + 1)) >= 2) {
                    continue;
                }
                return $i;
            }
            // closures internas não contam
            if ($t === T_FUNCTION || $t === T_FN) {
                foreach ($f->functions() as $inner) {
                    if ($inner['start'] === $i && $inner['close'] > $i) {
                        $i = $inner['close'];
                        break;
                    }
                }
            }
        }
        return null;
    }

    private function label(SourceFile $f, $i)
    {
        $t = $f->type($i);
        if ($t === T_INLINE_HTML || $t === T_CLOSE_TAG) {
            return 'HTML fora do PHP';
        }
        if (in_array($t, [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE], true)) {
            return strtolower($f->val($i)) . ' de template';
        }
        return trim($f->val($i));
    }

    private function returnsValue(SourceFile $f, $s, $e)
    {
        for ($i = $s + 1; $i < $e; $i++) {
            if ($f->type($i) === T_FUNCTION || $f->type($i) === T_FN) {
                foreach ($f->functions() as $inner) {
                    if ($inner['start'] === $i && $inner['close'] > $i) {
                        $i = $inner['close'];
                        break;
                    }
                }
                continue;
            }
            if ($f->type($i) === T_RETURN && $f->type($i + 1) !== ';') {
                return true;
            }
        }
        return false;
    }
}
