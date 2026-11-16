<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-P08: wp_schedule_event sem wp_next_scheduled na mesma função:
 * cada carregamento agenda outra cópia do evento.
 */
final class CronWithoutGuard extends Rule
{
    const ID = 'TNC-P08';
    const TITLE = 'Evento de cron agendado sem verificar se já existe';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'desempenho';

    protected function check(SourceFile $f)
    {
        foreach ($f->calls(['wp_schedule_event', 'wp_schedule_single_event']) as $c) {
            $fn = $f->functionAt($c['i']);
            $from = $fn ? $fn['open'] : 0;
            $to = $fn ? $fn['close'] : $f->count() - 1;
            if ($f->hasCallIn($from, $to, ['wp_next_scheduled', 'as_next_scheduled_action', 'wp_get_scheduled_event'])) {
                continue;
            }
            if ($fn && $this->isActivation($f, $fn)) {
                continue; // no gancho de ativação roda uma vez só
            }
            $this->report($f, $c['line'], '`' . $c['name'] . '()` sem `wp_next_scheduled()` antes: cada carregamento cria outra cópia do evento.', 'if ( ! wp_next_scheduled( \'meu_evento\' ) ) { wp_schedule_event( ... ); } e limpe com wp_clear_scheduled_hook() na desativação.');
        }
    }

    /** A função é a de ativação: nome com activat/ativa(r/cao) ou callback de register_activation_hook. */
    private function isActivation(SourceFile $f, array $fn)
    {
        if ($fn['name'] && preg_match('/activat|ativa(r|cao|ção)|instal/i', $fn['name']) && !preg_match('/deactivat|desativ|uninstal|desinstal/i', $fn['name'])) {
            return true;
        }
        foreach ($f->calls(['register_activation_hook']) as $c) {
            if (count($c['args']) < 2) {
                continue;
            }
            $cb = $f->resolveCallback($c['args'][1][0], $c['args'][1][1], $c['i']);
            if ($cb && $cb['start'] === $fn['start']) {
                return true;
            }
        }
        return false;
    }
}
