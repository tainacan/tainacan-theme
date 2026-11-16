<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-P02: consulta (ou requisição HTTP) dentro de laço: o padrão N+1.
 * Funções que o WordPress já cacheia depois de um WP_Query (get_post_meta,
 * get_the_terms) ficam de fora para não gerar ruído.
 */
final class QueryInLoop extends Rule
{
    const ID = 'TNC-P02';
    const TITLE = 'Consulta ou requisição HTTP dentro de laço (N+1)';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'desempenho';

    const FUNCS = [
        'get_posts', 'get_children', 'get_term_by', 'get_terms', 'wp_get_object_terms', 'get_page_by_path',
        'get_page_by_title', 'url_to_postid', 'attachment_url_to_postid', 'count_user_posts',
        'wp_remote_get', 'wp_remote_post', 'wp_remote_request', 'wp_remote_head', 'wp_safe_remote_get',
        'wp_safe_remote_post', 'file_get_contents', 'curl_exec',
    ];
    const WPDB = ['get_results', 'get_var', 'get_row', 'get_col', 'query'];
    const TAINACAN = ['fetch', 'fetch_one', 'fetch_by_collection', 'fetch_ids', 'fetch_items', 'fetch_by_db_identifier'];

    protected function check(SourceFile $f)
    {
        if (!$f->loops()) {
            return;
        }
        $reported = [];
        foreach ($f->calls(self::FUNCS) as $c) {
            if ($c['name'] === 'file_get_contents' && !$this->looksRemote($f, $c)) {
                continue;
            }
            $this->flag($f, $c['i'], $c['line'], '`' . $c['name'] . '()`', $reported);
        }
        foreach ($f->methodCalls(array_merge(self::WPDB, self::TAINACAN)) as $c) {
            $isWpdb = $f->val($c['target']) === '$wpdb';
            if (in_array($c['name'], self::WPDB, true) && !$isWpdb) {
                continue;
            }
            if (in_array($c['name'], self::TAINACAN, true) && $isWpdb) {
                continue;
            }
            $label = $isWpdb ? '`$wpdb->' . $c['name'] . '()`' : '`->' . $c['name'] . '()` (repositório)';
            $this->flag($f, $c['i'], $c['line'], $label, $reported);
        }
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) === T_NEW && $f->type($i + 1) === 'NAME' && in_array(SourceFile::short($f->val($i + 1)), ['wp_query', 'wp_term_query', 'wp_user_query'], true)) {
                $this->flag($f, $i, $f->line($i), '`new ' . $f->val($i + 1) . '`', $reported);
            }
        }
    }

    private function flag(SourceFile $f, $i, $line, $label, array &$reported)
    {
        $loop = $f->inLoop($i);
        if (!$loop) {
            return;
        }
        // laço sobre literal pequeno: foreach ( [ 'a', 'b' ] as ... )
        if ($this->literalLoop($f, $loop[0])) {
            return;
        }
        $key = $line . $label;
        if (isset($reported[$key])) {
            return;
        }
        $reported[$key] = true;
        $this->report($f, $line, $label . ' dentro de laço: uma consulta por volta.', 'Busque tudo antes do laço numa consulta só (post__in, IN (...), \'fields\' => \'ids\', update_meta_cache) ou justifique com tnc-check-ignore se o laço é curto e limitado.');
    }

    private function literalLoop(SourceFile $f, $bodyOpen)
    {
        // procura o '(' do cabeçalho do laço antes do corpo
        $close = $bodyOpen - 1;
        if ($f->type($close) !== ')') {
            return false;
        }
        $open = $f->match($close);
        return $open >= 0 && ($f->type($open + 1) === '[' || $f->type($open + 1) === T_ARRAY);
    }

    private function looksRemote(SourceFile $f, array $c)
    {
        return $c['args'] && preg_match('/https?:|url|uri|endpoint/i', $f->text($c['args'][0][0], $c['args'][0][1]));
    }
}
