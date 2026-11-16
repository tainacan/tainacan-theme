<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W15: string traduzível com mais de um marcador sem numeração
 * (%1$s, %2$s) ou com marcador e sem comentário "translators:" na linha
 * anterior. O Plugin Check acusa os dois
 * (WordPress.WP.I18n.UnorderedPlaceholdersText e MissingTranslatorsComment).
 *
 * Sem numeração, o tradutor não pode inverter a ordem das palavras; sem o
 * comentário, não sabe o que cada marcador representa e traduz errado.
 */
final class I18nPlaceholders extends Rule
{
    const ID = 'TNC-W15';
    const TITLE = 'Marcador em string traduzível sem numeração ou sem comentário translators';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'wordpress';

    const FUNCS = ['__', '_e', '_x', '_ex', '_n', '_nx', 'esc_html__', 'esc_html_e', 'esc_html_x', 'esc_attr__', 'esc_attr_e', 'esc_attr_x', '_n_noop', '_nx_noop'];

    const PLACEHOLDER = '/%(?:(\d+)\$)?[+-]?\'?.?\d*(?:\.\d+)?[bcdeEfFgGosuxX]/';

    public function applies(SourceFile $f)
    {
        return parent::applies($f) && $f->project && $f->project->isWordPress();
    }

    protected function check(SourceFile $f)
    {
        $comments = [];
        foreach ($f->comments() as $c) {
            if (stripos($c[1], 'translators:') !== false) {
                $end = $c[0] + substr_count($c[1], "\n");
                $comments[$end] = true;
            }
        }
        foreach ($f->calls(self::FUNCS) as $c) {
            $texts = [];
            $plural = in_array($c['name'], ['_n', '_nx', '_n_noop', '_nx_noop'], true);
            foreach (array_slice($c['args'], 0, $plural ? 2 : 1) as $a) {
                $lit = $f->literal($a[0], $a[1]);
                if ($lit === null && $f->type($a[0]) === T_CONSTANT_ENCAPSED_STRING) {
                    $lit = $f->stringContent($a[0], $a[1]); // literal concatenado
                }
                if ($lit !== null) {
                    $texts[] = $lit;
                }
            }
            if (!$texts) {
                continue;
            }
            $count = 0;
            $unordered = false;
            foreach ($texts as $text) {
                $text = str_replace('%%', '', $text);
                if (!preg_match_all(self::PLACEHOLDER, $text, $m, PREG_SET_ORDER)) {
                    continue;
                }
                $count = max($count, count($m));
                if (count($m) > 1) {
                    foreach ($m as $hit) {
                        if (!isset($hit[1]) || $hit[1] === '') {
                            $unordered = true;
                        }
                    }
                }
            }
            if ($count === 0) {
                continue;
            }
            if ($unordered) {
                $this->report($f, $c['line'], 'String traduzível com ' . $count . ' marcadores sem numeração.', 'Numere todos: %1$s, %2$s. Sem isso o tradutor não pode mudar a ordem das palavras.');
            }
            $stmt = $f->line($f->statementStart($c['i']));
            $has = false;
            // dentro do enunciado (ex.: printf( /* translators */ __( ... ) )) qualquer linha serve
            for ($l = $c['line'] - 1; $l >= $stmt; $l--) {
                if (isset($comments[$l])) {
                    $has = true;
                    break;
                }
            }
            // antes do enunciado: até 3 linhas, sem código no meio
            for ($l = $stmt - 1; !$has && $l >= max(1, $stmt - 3); $l--) {
                if (isset($comments[$l])) {
                    $has = true;
                    break;
                }
                if (trim($f->lines[$l]) !== '' && !preg_match('#^\s*(//|/\*|\*)#', $f->lines[$l])) {
                    break; // outra linha de código no meio
                }
            }
            if (!$has) {
                $this->report($f, $c['line'], 'String com marcador sem comentário `translators:` na linha anterior.', 'Acrescente /* translators: %s = nome da coleção */ imediatamente antes da chamada, dizendo o que cada marcador representa.');
            }
        }
    }
}
