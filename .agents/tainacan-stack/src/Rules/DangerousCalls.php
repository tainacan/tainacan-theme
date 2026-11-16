<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-S06: construções perigosas: eval (exceto em pastas de teste),
 * create_function, assert com string, unserialize sem allowed_classes
 * (bloqueio com dado da requisição, aviso nos demais casos), extract
 * (idem), include/require de caminho vindo da requisição, echo direto de
 * entrada HTTP, preg_replace com /e.
 */
final class DangerousCalls extends Rule
{
    const ID = 'TNC-S06';
    const TITLE = 'Construção perigosa (eval, unserialize, extract, include dinâmico)';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'seguranca';

    const INPUT = '/\$_(GET|POST|REQUEST|COOKIE|FILES|SERVER)\b/';

    protected function check(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            $t = $f->type($i);
            if ($t === T_EVAL && preg_match('#(^|/)(tests?|spec)/#', $f->path)) {
                continue; // stubs de teste
            }
            if ($t === T_EVAL) {
                $this->report($f, $f->line($i), '`eval()` executa texto como código.', 'Reescreva sem eval.');
            } elseif (in_array($t, [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE], true)) {
                $end = $i + 1;
                while ($end < $n && $f->type($end) !== ';') {
                    $end++;
                }
                if (preg_match(self::INPUT, $f->text($i + 1, $end - 1))) {
                    $this->report($f, $f->line($i), 'include/require com caminho vindo da requisição (inclusão de arquivo arbitrário).', 'Mapeie a entrada para uma lista fixa de arquivos permitidos.');
                }
            } elseif ($t === T_ECHO || $t === T_PRINT || $t === T_OPEN_TAG_WITH_ECHO) {
                $end = $i + 1;
                while ($end < $n && $f->type($end) !== ';' && $f->type($end) !== T_CLOSE_TAG) {
                    $end++;
                }
                for ($k = $i + 1; $k < $end; $k++) {
                    if ($f->type($k) === T_VARIABLE && preg_match(self::INPUT, $f->val($k))) {
                        $chain = $f->enclosingCalls($k);
                        $escaped = (bool) array_intersect($chain, ['isset', 'empty']);
                        foreach ($chain as $fn) {
                            if (preg_match('/^(esc_|wp_kses|absint|intval|sanitize_|number_format|wp_json_encode|json_encode)/', $fn)) {
                                $escaped = true;
                            }
                        }
                        if (!$escaped && !in_array($f->type($k - 1), [T_INT_CAST, T_DOUBLE_CAST], true)) {
                            $this->report($f, $f->line($k), 'Entrada HTTP impressa sem escape (XSS refletido).', 'Use esc_html(), esc_attr() ou esc_url() conforme o contexto.');
                            break;
                        }
                    }
                }
            }
        }
        foreach ($f->calls(['create_function']) as $c) {
            $this->report($f, $c['line'], '`create_function()` é eval disfarçado e foi removido no PHP 8.', 'Use closure.');
        }
        foreach ($f->calls(['assert']) as $c) {
            if ($c['args'] && $f->type($c['args'][0][0]) === T_CONSTANT_ENCAPSED_STRING) {
                $this->report($f, $c['line'], '`assert()` com string avalia código.', 'Passe uma expressão, não texto.');
            }
        }
        foreach ($f->calls(['unserialize']) as $c) {
            if (!$c['args']) {
                continue;
            }
            $hasOptions = count($c['args']) >= 2 && stripos($f->text($c['args'][1][0], $c['args'][1][1]), 'allowed_classes') !== false;
            if ($hasOptions) {
                continue;
            }
            $arg = $f->text($c['args'][0][0], $c['args'][0][1]);
            $sev = preg_match(self::INPUT, $arg) ? Finding::BLOCK : Finding::WARN;
            $this->report($f, $c['line'], '`unserialize()` sem `allowed_classes` permite injeção de objeto.', 'Use json_decode(), maybe_unserialize() em dado do próprio banco, ou unserialize( $x, [ \'allowed_classes\' => false ] ).', null, $sev);
        }
        foreach ($f->calls(['extract']) as $c) {
            $arg = $c['args'] ? $f->text($c['args'][0][0], $c['args'][0][1]) : '';
            $sev = preg_match(self::INPUT, $arg) ? Finding::BLOCK : Finding::WARN;
            $this->report($f, $c['line'], '`extract()` cria variáveis a partir de chaves' . ($sev === Finding::BLOCK ? ' vindas da requisição' : '') . ' e pode sobrescrever as existentes.', 'Leia as chaves explicitamente.', null, $sev);
        }
        foreach ($f->calls(['preg_replace']) as $c) {
            if ($c['args'] && preg_match('/^([\'"])(.).*\2[a-zA-Z]*e[a-zA-Z]*\1$/s', $f->val($c['args'][0][0]))) {
                $this->report($f, $c['line'], '`preg_replace()` com modificador /e (eval).', 'Use preg_replace_callback().');
            }
        }
    }
}
