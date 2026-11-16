<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-B02: `<?` sem `php`. Com short_open_tag=Off o trecho vira texto
 * impresso na página e o `php -l` passa sem analisá-lo.
 */
final class ShortOpenTag extends Rule
{
    const ID = 'TNC-B02';
    const TITLE = 'Tag de abertura curta (<?)';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'base';

    protected function check(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            $t = $f->type($i);
            if ($t === T_OPEN_TAG && trim($f->val($i)) === '<?') {
                $this->report($f, $f->line($i), 'Tag curta `<?`: use `<?php`.');
                continue;
            }
            if ($t === T_INLINE_HTML && preg_match_all('/<\?(?=\s)/', $f->val($i), $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $hit) {
                    $line = $f->line($i) + substr_count(substr($f->val($i), 0, $hit[1]), "\n");
                    $this->report($f, $line, 'Tag curta `<?`: com short_open_tag=Off este código é impresso como texto e o `php -l` não o analisa.', 'Troque por `<?php`.');
                }
            }
        }
    }
}
