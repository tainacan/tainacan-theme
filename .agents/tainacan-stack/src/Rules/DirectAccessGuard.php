<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-B06: arquivo PHP de plugin executável diretamente pela URL, sem
 * `defined( 'ABSPATH' ) || exit;`. Exigido pelo Plugin Check do WordPress.org.
 */
final class DirectAccessGuard extends Rule
{
    const ID = 'TNC-B06';
    const TITLE = 'Arquivo PHP sem proteção contra acesso direto';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'base';

    protected function check(SourceFile $f)
    {
        if (!$f->project || !$f->project->isPlugin()) {
            return;
        }
        if (preg_match('#(^|/)(tests?|bin|developer|scripts|cli)/#', $f->path) || basename($f->path) === 'uninstall.php') {
            return;
        }
        $head = substr($f->text, 0, 4000);
        if (preg_match('/\b(ABSPATH|WPINC|WP_UNINSTALL_PLUGIN)\b/', $head)) {
            return;
        }
        // Só classes/interfaces/funções sem efeito no topo não executam nada;
        // ainda assim o Plugin Check exige a guarda. Arquivo só com namespace e
        // declarações recebe aviso; com código solto no topo também.
        if (trim($f->text) === '' || strpos($f->text, '<?php') === false) {
            return;
        }
        $this->report($f, 1, 'Sem `defined( \'ABSPATH\' ) || exit;` no início do arquivo.', 'Acrescente a guarda logo após a abertura `<?php` (e após `namespace`, se houver).');
    }
}
