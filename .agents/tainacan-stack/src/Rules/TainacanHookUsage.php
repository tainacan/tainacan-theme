<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T09: uso errado de ganchos do Tainacan.
 *  - Callback de tainacan-insert*, tainacan-pre-insert*, tainacan-deleted*,
 *    tainacan-pre-delete* com o primeiro parâmetro tipado como escalar: o
 *    gancho entrega a entidade e o PHP 8 lança TypeError no meio da gravação.
 *  - Filtro genérico tainacan-item-metadata-get-value-as-html sem as
 *    variantes --type-*: ele não roda para os tipos com HTML próprio
 *    (text, textarea, date, url, taxonomy, relationship, compound...).
 *  - 'post_type' => 'any' em código que procura itens: tnc_col_*_item é
 *    exclude_from_search e fica de fora.
 */
final class TainacanHookUsage extends Rule
{
    const ID = 'TNC-T09';
    const TITLE = 'Gancho ou consulta do Tainacan com uso que falha em silêncio';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'tainacan';

    const ENTITY_HOOK = '/^tainacan-(pre-)?(insert|delete|deleted|update)(-|$)/';
    const SCALARS = ['int', 'string', 'float', 'bool', 'array', 'iterable', 'callable', 'null', 'false', 'true'];

    protected function check(SourceFile $f)
    {
        $generic = [];
        $typed = false;
        foreach ($f->calls(['add_action', 'add_filter']) as $c) {
            if (count($c['args']) < 2) {
                continue;
            }
            $hook = $f->literal($c['args'][0][0], $c['args'][0][1]);
            if ($hook === null) {
                continue;
            }
            if ($hook === 'tainacan-item-metadata-get-value-as-html') {
                $generic[] = $c;
            }
            if (strpos($hook, 'tainacan-item-metadata-get-value-as-html--type-') === 0) {
                $typed = true;
            }
            if (!preg_match(self::ENTITY_HOOK, $hook)) {
                continue;
            }
            $fn = $f->resolveCallback($c['args'][1][0], $c['args'][1][1], $c['i']);
            if (!$fn) {
                continue;
            }
            $type = $this->firstParamType($f, $fn);
            if ($type !== null && in_array(strtolower(ltrim($type, '?')), self::SCALARS, true)) {
                $this->report($f, $f->line($fn['start']), 'Callback de `' . $hook . '` com o primeiro parâmetro tipado `' . $type . '`: o gancho entrega a entidade do Tainacan e o PHP 8 lança TypeError no meio da gravação.', 'Tire o tipo ou use a classe da entidade (\\Tainacan\\Entities\\Item etc.) e confira com instanceof.', null, Finding::BLOCK);
            }
        }
        if ($generic && !$typed) {
            foreach ($generic as $c) {
                $this->report($f, $c['line'], 'Só o filtro genérico `tainacan-item-metadata-get-value-as-html`: ele não roda para text, textarea, date, url, taxonomy, relationship, compound, user, control, geocoordinate.', 'Registre também as variantes tainacan-item-metadata-get-value-as-html--type-<tipo> que interessam (o genérico só vale para numeric, selectbox e core-title).');
            }
        }
        $this->postTypeAny($f);
    }

    private function firstParamType(SourceFile $f, array $fn)
    {
        $k = $fn['start'] + 1;
        while ($k < $f->count() && $f->type($k) !== '(') {
            $k++;
        }
        $close = $f->match($k);
        if ($close < 0) {
            return null;
        }
        $type = '';
        for ($i = $k + 1; $i < $close; $i++) {
            $t = $f->type($i);
            if ($t === T_VARIABLE || $t === ',' || $t === T_ELLIPSIS) {
                break;
            }
            if ($t === 'NAME' || $t === T_ARRAY || $t === T_CALLABLE || $t === '?' || $t === '|') {
                $type .= $f->val($i);
            }
        }
        return $type === '' ? null : $type;
    }

    private function postTypeAny(SourceFile $f)
    {
        if (!preg_match('/tnc_col_|Tainacan\\\\|tainacan_items\s*\(|tainacan-item/i', $f->text)) {
            return;
        }
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) === T_CONSTANT_ENCAPSED_STRING && SourceFile::unquote($f->val($i)) === 'post_type'
                && $f->type($i + 1) === T_DOUBLE_ARROW && $f->type($i + 2) === T_CONSTANT_ENCAPSED_STRING
                && SourceFile::unquote($f->val($i + 2)) === 'any') {
                $this->report($f, $f->line($i), '`\'post_type\' => \'any\'` não alcança itens do Tainacan: tnc_col_<id>_item é exclude_from_search.', 'Liste os post types das coleções (Collection::get_db_identifier()) ou use tainacan_items()->fetch().');
            }
        }
    }
}
