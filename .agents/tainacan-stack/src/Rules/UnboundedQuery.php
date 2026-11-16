<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-P01: consulta sem limite (posts_per_page/perpage = -1, nopaging).
 * Em acervo com dezenas de milhares de itens isso esgota memória e tempo.
 * (meta_query com LIKE é a TNC-P03, em MetaQueryLike.)
 */
final class UnboundedQuery extends Rule
{
    const ID = 'TNC-P01';
    const TITLE = 'Consulta sem limite de resultados';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'desempenho';

    const LIMIT_KEYS = ['posts_per_page', 'numberposts', 'perpage', 'per_page', 'posts_per_archive_page', 'number'];

    protected function check(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }
            $key = strtolower(SourceFile::unquote($f->val($i)));
            $next = $f->type($i + 1);
            $isPair = $next === T_DOUBLE_ARROW;
            // $args['posts_per_page'] = -1
            $isAssign = $next === ']' && $f->type($i + 2) === '=';
            if (!$isPair && !$isAssign) {
                continue;
            }
            $v = $isPair ? $i + 2 : $i + 3;
            if (in_array($key, self::LIMIT_KEYS, true) && $this->isMinusOne($f, $v)) {
                if ($key === 'number' && !$this->nearTermsQuery($f, $i)) {
                    continue;
                }
                $this->report($f, $f->line($i), '`' . $key . '` = -1 traz todos os registros de uma vez.', 'Pagine (lotes de 100 a 500), use \'fields\' => \'ids\' quando só precisa dos IDs, ou conte com found_posts/COUNT.');
            } elseif ($key === 'nopaging' && strtolower($f->val($v)) === 'true') {
                $this->report($f, $f->line($i), '`nopaging` => true traz todos os registros de uma vez.', 'Pagine a consulta.');
            }
        }
    }

    private function isMinusOne(SourceFile $f, $v)
    {
        if ($f->type($v) === '-' && $f->val($v + 1) === '1') {
            return true;
        }
        return $f->type($v) === T_CONSTANT_ENCAPSED_STRING && SourceFile::unquote($f->val($v)) === '-1';
    }

    private function nearTermsQuery(SourceFile $f, $i)
    {
        $chain = $f->enclosingCalls($i);
        if (array_intersect($chain, ['get_terms', 'get_users', 'get_comments', 'wp_get_object_terms'])) {
            return true;
        }
        return (bool) preg_match('/WP_Term_Query|WP_User_Query|get_terms|get_users/i', $f->text($f->statementStart($i), $i));
    }
}
