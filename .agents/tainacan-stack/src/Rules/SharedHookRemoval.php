<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-C01: um plugin desfazendo o que outros registraram:
 * remove_all_filters/remove_all_actions, desregistro de scripts e estilos
 * do core, do jQuery ou do Tainacan, e wp_dequeue de handles alheios.
 */
final class SharedHookRemoval extends Rule
{
    const ID = 'TNC-C01';
    const TITLE = 'Remoção de ganchos ou assets de terceiros';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'convivencia';

    const FOREIGN_HANDLE = '/^(jquery|jquery-[\w-]+|wp-[\w-]+|react|react-dom|lodash|moment|backbone|underscore|tainacan[\w-]*|vue|vuejs|elementor[\w-]*|blocksy[\w-]*)$/i';

    protected function check(SourceFile $f)
    {
        foreach ($f->calls(['remove_all_filters', 'remove_all_actions']) as $c) {
            $hook = $c['args'] ? $f->stringContent($c['args'][0][0], $c['args'][0][1]) : '?';
            $this->report($f, $c['line'], '`' . $c['name'] . '( \'' . trim($hook) . '\' )` remove também os ganchos de outros plugins e do tema.', 'Remova só o seu callback com remove_filter( $gancho, $callback, $prioridade ).');
        }
        foreach ($f->calls(['wp_deregister_script', 'wp_deregister_style', 'wp_dequeue_script', 'wp_dequeue_style']) as $c) {
            if (!$c['args']) {
                continue;
            }
            $handle = $f->literal($c['args'][0][0], $c['args'][0][1]);
            if ($handle === null || !preg_match(self::FOREIGN_HANDLE, $handle)) {
                continue;
            }
            if ($f->project && $f->project->textDomain && stripos($handle, $f->project->textDomain) === 0) {
                continue; // handle do próprio plugin
            }
            $this->report($f, $c['line'], '`' . $c['name'] . '( \'' . $handle . '\' )` tira um asset que outros plugins ou o core usam.', 'Declare dependência do asset em vez de removê-lo; se a troca for inevitável, faça só na tela do próprio plugin.');
        }
    }
}
