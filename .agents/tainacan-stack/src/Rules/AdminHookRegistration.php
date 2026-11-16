<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T03: tainacan_register_admin_hook() chamado fora da ação
 * tainacan-register-admin-hooks. Em init ou no carregamento do plugin a
 * função roda antes do Admin_Hooks existir ou em toda requisição (REST,
 * cron, WP-CLI), executando o callback e gerando HTML à toa.
 */
final class AdminHookRegistration extends Rule
{
    const ID = 'TNC-T03';
    const TITLE = 'Gancho de admin registrado fora de tainacan-register-admin-hooks';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'tainacan';

    protected function check(SourceFile $f)
    {
        $calls = $f->calls(['tainacan_register_admin_hook']);
        if (!$calls) {
            return;
        }
        // só vale o literal no código (um comentário citando a ação não registra nada)
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) === T_CONSTANT_ENCAPSED_STRING && SourceFile::unquote($f->val($i)) === 'tainacan-register-admin-hooks') {
                return;
            }
        }
        foreach ($calls as $c) {
            $this->report($f, $c['line'], 'tainacan_register_admin_hook() fora da ação `tainacan-register-admin-hooks`.', 'Registre dentro de add_action( \'tainacan-register-admin-hooks\', ... ), que o core dispara em admin_init.');
        }
    }
}
