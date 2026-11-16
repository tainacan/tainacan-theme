<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T06: gancho "depois do item" da busca facetada registrado com um nome
 * só. No core os nomes são assimétricos (view-modes-mixin.js:62-78):
 * hasAfterHook() consulta tainacan_faceted_search_collection_item_after,
 * getAfterHook() aplica tainacan_faceted_search_item_after. Com só um dos
 * dois, o lugar nunca é desenhado ou fica vazio.
 */
final class FacetedSearchItemHook extends Rule
{
    const ID = 'TNC-T06';
    const TITLE = 'Gancho da busca facetada registrado com um nome só';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'tainacan';
    const EXTENSIONS = ['js', 'jsx', 'ts', 'tsx', 'vue', 'php'];

    const ITEM_AFTER = 'tainacan_faceted_search_item_after';
    const COLL_AFTER = 'tainacan_faceted_search_collection_item_after';

    protected function check(SourceFile $f)
    {
        $text = $f->text;
        if (strpos($text, 'tainacan_faceted_search_') === false) {
            return;
        }
        $hasItem = $this->registered($text, self::ITEM_AFTER);
        $hasColl = $this->registered($text, self::COLL_AFTER);
        if ($hasItem && !$hasColl) {
            $this->report($f, $this->lineOf($f, self::ITEM_AFTER), 'Só `' . self::ITEM_AFTER . '` registrado: o core testa `' . self::COLL_AFTER . '` no v-if e o lugar nunca aparece.', 'Registre a mesma função também em \'' . self::COLL_AFTER . '\' (ou use o nome por coleção tainacan_faceted_search_collection_<id>_item_after).');
        } elseif ($hasColl && !$hasItem) {
            $this->report($f, $this->lineOf($f, self::COLL_AFTER), 'Só `' . self::COLL_AFTER . '` registrado: o v-if passa, mas o core aplica `' . self::ITEM_AFTER . '` para gerar o HTML, e o lugar fica vazio.', 'Registre a mesma função também em \'' . self::ITEM_AFTER . '\'.');
        }
    }

    private function registered($text, $name)
    {
        return (bool) preg_match('/addFilter\s*\(\s*[\'"`]' . preg_quote($name, '/') . '[\'"`]/', $text);
    }

    private function lineOf(SourceFile $f, $name)
    {
        foreach ($f->lines as $n => $line) {
            if (preg_match('/addFilter\s*\(\s*[\'"`]' . preg_quote($name, '/') . '[\'"`]/', $line)) {
                return $n;
            }
        }
        return 1;
    }
}
