<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T07: parâmetro da REST do Tainacan que o core descarta em silêncio
 * (classes/api/class-tainacan-rest-controller.php, prepare_filters):
 *  - taxquery[..][include_children]: não é copiado; o WP_Tax_Query usa true,
 *    então o filtro por termo sempre inclui os filhos;
 *  - taxquery[..][field]: o nome aceito é metadatum;
 *  - metaquery[..][type]: o nome aceito é metadatumtype.
 */
final class TainacanQueryParams extends Rule
{
    const ID = 'TNC-T07';
    const TITLE = 'Parâmetro de busca que a REST do Tainacan descarta';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'tainacan';
    const EXTENSIONS = ['js', 'jsx', 'ts', 'tsx', 'vue', 'php'];

    protected function check(SourceFile $f)
    {
        $text = $f->text;
        if (stripos($text, 'taxquery') === false && stripos($text, 'metaquery') === false) {
            return;
        }
        $hasTax = stripos($text, 'taxquery') !== false;
        $hasMeta = stripos($text, 'metaquery') !== false;
        foreach ($f->lines as $n => $line) {
            if ($hasTax && preg_match('/taxquery\]?\s*\[\s*[\w\'"]*\s*\]\s*\[\s*[\'"]?include_children|taxquery[^\n]*include_children/i', $line)) {
                $this->report($f, $n, '`include_children` em taxquery é descartado pela REST do Tainacan: o filtro sempre inclui os termos filhos.', 'Para excluir filhos, filtre pelos IDs exatos (operator IN com os termos desejados) ou use o Index Manager.');
            } elseif ($hasTax && preg_match('/taxquery\]?\s*\[\s*[\w\'"]*\s*\]\s*\[\s*[\'"]?field[\'"]?\s*\]/i', $line)) {
                $this->report($f, $n, '`field` em taxquery é descartado: a REST do Tainacan espera `metadatum`.', 'Troque [field] por [metadatum].');
            } elseif ($hasMeta && preg_match('/metaquery\]?\s*\[\s*[\w\'"]*\s*\]\s*\[\s*[\'"]?type[\'"]?\s*\]/i', $line)) {
                $this->report($f, $n, '`type` em metaquery é descartado: a REST do Tainacan espera `metadatumtype` (e ainda o sobrescreve pelo tipo do filtro cadastrado).', 'Troque [type] por [metadatumtype].');
            }
        }
    }
}
