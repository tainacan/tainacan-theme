<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T08: subclasse de \Tainacan\Pages que nunca liga load_page().
 * O pai não chama load_page() sozinho (views/class-tainacan-pages.php:63-70);
 * sem add_action( 'load-' . $page_suffix, [ $this, 'load_page' ] ) depois
 * do add_submenu_page(), CSS e JS da página não são enfileirados.
 */
final class TainacanPagesLoad extends Rule
{
    const ID = 'TNC-T08';
    const TITLE = 'Página de admin do Tainacan sem load_page()';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'tainacan';

    protected function check(SourceFile $f)
    {
        foreach ($f->classes() as $c) {
            $parent = strtolower(ltrim($c['extends'], '\\'));
            if ($parent !== 'tainacan\\pages' && !($parent === 'pages' && preg_match('/use\s+\\\\?Tainacan\\\\Pages\s*;|namespace\s+Tainacan\s*;/i', $f->text))) {
                continue;
            }
            $body = $f->text($c['open'], $c['close']);
            // add_action( 'load-' . $suffix, [ $this, 'load_page' ] )
            // também aceita "load-{$suffix}" / "load-$suffix" interpolado
            if (preg_match('/[\'"]load-[\'"]\s*\.|"load-\{?\$/', $body) && preg_match('/[\'"]load_page[\'"]/', $body)) {
                continue;
            }
            $this->report($f, $c['line'], 'Classe `' . $c['name'] . '` estende \\Tainacan\\Pages sem ligar load_page(): CSS e JS da página não carregam.', 'Depois de add_submenu_page(): add_action( \'load-\' . $page_suffix, [ $this, \'load_page\' ] ); e use o trait Singleton_Instance (construtor protegido).');
        }
    }
}
