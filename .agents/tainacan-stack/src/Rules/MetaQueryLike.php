<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-P03: meta_query (ou metaquery do Tainacan) com compare LIKE, NOT LIKE
 * ou REGEXP. O MySQL não usa índice e lê wp_postmeta inteira, que num acervo
 * grande tem milhões de linhas.
 */
final class MetaQueryLike extends Rule
{
    const ID = 'TNC-P03';
    const TITLE = 'meta_query com LIKE/REGEXP (varredura completa de wp_postmeta)';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'desempenho';

    protected function check(SourceFile $f)
    {
        if (!preg_match('/meta_?query/i', $f->text)) {
            return;
        }
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) !== T_CONSTANT_ENCAPSED_STRING || strtolower(SourceFile::unquote($f->val($i))) !== 'compare') {
                continue;
            }
            if ($f->type($i + 1) !== T_DOUBLE_ARROW || $f->type($i + 2) !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }
            $op = strtoupper(SourceFile::unquote($f->val($i + 2)));
            if (!in_array($op, ['LIKE', 'NOT LIKE', 'REGEXP', 'NOT REGEXP', 'RLIKE'], true)) {
                continue;
            }
            $ctx = $f->text($f->statementStart($i), $i);
            if (!preg_match('/meta_?query/i', $ctx)) {
                continue;
            }
            $this->report($f, $f->line($i), 'meta_query com `' . $op . '`: sem índice, lê wp_postmeta inteira.', 'Com o Tainacan Index Manager/Elasticsearch, busque pelo índice; sem ele, prefira comparação exata (=, IN) ou um termo de taxonomia.');
        }
    }
}
