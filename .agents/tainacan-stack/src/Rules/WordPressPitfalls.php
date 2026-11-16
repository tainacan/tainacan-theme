<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W10: armadilhas de WordPress que já derrubaram sites inteiros.
 *  - '@' na rota de register_rest_route: o WP compila a rota como @^...$@i
 *    e a regex inválida derruba o REST de todos os plugins.
 *  - escrita direta em $wp_filter / ->callbacks: dessincroniza o WP_Hook
 *    (WP 6.4+); use add_filter/remove_filter.
 *  - efeito colateral (gravação, contador, cache) dentro de
 *    permission_callback: ele roda mais de uma vez por requisição e antes de
 *    validar argumentos.
 *  - phpmailer_init / wp_mail_from sem escopo: muda todos os e-mails do site.
 *  - resposta de wp_remote_* guardada em transient sem conferir erro: a
 *    falha fica em cache por horas.
 */
final class WordPressPitfalls extends Rule
{
    const ID = 'TNC-W10';
    const TITLE = 'Armadilha de WordPress que afeta o site inteiro';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'wordpress';

    const MAIL_HOOKS = ['phpmailer_init', 'wp_mail_from', 'wp_mail_from_name', 'wp_mail_content_type', 'wp_mail_charset'];
    const SIDE_EFFECTS = ['update_option', 'add_option', 'delete_option', 'set_transient', 'delete_transient', 'wp_cache_set', 'wp_cache_incr', 'wp_cache_add', 'update_post_meta', 'update_user_meta', 'wp_insert_post', 'wp_update_post'];

    protected function check(SourceFile $f)
    {
        $this->restRouteAt($f);
        $this->wpFilterWrites($f);
        $this->permissionSideEffects($f);
        $this->mailHijack($f);
        $this->remoteCachedWithoutCheck($f);
    }

    private function restRouteAt(SourceFile $f)
    {
        foreach ($f->calls(['register_rest_route']) as $c) {
            if (count($c['args']) < 2) {
                continue;
            }
            $route = $f->stringContent($c['args'][1][0], $c['args'][1][1]);
            if (strpos($route, '@') !== false) {
                $this->report($f, $c['line'], 'Rota REST com `@` (`' . trim($route) . '`): o WordPress compila a rota como @^…$@i e a regex quebrada derruba o REST do site inteiro.', 'Tire o @ do padrão (use [^/]+ ou codifique o valor).', null, Finding::BLOCK);
            }
        }
    }

    private function wpFilterWrites(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) !== T_VARIABLE || $f->val($i) !== '$wp_filter') {
                continue;
            }
            // $wp_filter[...] = / unset( $wp_filter[...] ) / $wp_filter[...]->callbacks[...] =
            $end = $i;
            while (in_array($f->type($end + 1), ['[', T_OBJECT_OPERATOR], true)) {
                if ($f->type($end + 1) === '[') {
                    $end = $f->match($end + 1);
                } else {
                    $end += 2;
                }
                if ($end < 0) {
                    break;
                }
            }
            $isUnset = $f->type($i - 1) === '(' && $f->type($i - 2) === T_UNSET;
            $isWrite = in_array($f->type($end + 1), ['=', T_CONCAT_EQUAL], true);
            if (($isUnset || $isWrite) && $end > $i) {
                $callbacks = stripos($f->text($i, $end), 'callbacks') !== false;
                $this->report(
                    $f,
                    $f->line($i),
                    $callbacks
                        ? 'Escrita direta em `$wp_filter[...]->callbacks`: dessincroniza o WP_Hook (desde o WP 6.4) e afeta ganchos de outros plugins.'
                        : 'Troca de uma entrada inteira de `$wp_filter`: remove os callbacks de todos os plugins nesse gancho até a restauração.',
                    'Use remove_filter()/add_filter(); para suspender um callback de terceiro, guarde e restaure pela mesma API, sempre em try/finally.',
                    null,
                    $callbacks ? Finding::BLOCK : Finding::WARN
                );
            }
        }
    }

    private function permissionSideEffects(SourceFile $f)
    {
        foreach ($f->calls(['register_rest_route']) as $c) {
            if (count($c['args']) < 3 || !$f->isArrayStart($c['args'][2][0])) {
                continue;
            }
            $close = $f->arrayEnd($c['args'][2][0]);
            for ($i = $c['args'][2][0]; $i < $close; $i++) {
                if ($f->type($i) !== T_CONSTANT_ENCAPSED_STRING || SourceFile::unquote($f->val($i)) !== 'permission_callback' || $f->type($i + 1) !== T_DOUBLE_ARROW) {
                    continue;
                }
                $vs = $i + 2;
                $ve = $vs;
                if ($f->isArrayStart($vs)) {
                    $ve = $f->arrayEnd($vs);
                } elseif (in_array($f->type($vs), [T_FUNCTION, T_FN, T_STATIC], true)) {
                    foreach ($f->functions() as $fnc) {
                        if ($fnc['start'] === $vs || $fnc['start'] === $vs + 1) {
                            $ve = max($fnc['close'], $vs);
                        }
                    }
                }
                $fn = $f->resolveCallback($vs, $ve, $c['i']);
                if (!$fn || $fn['open'] < 0) {
                    continue;
                }
                if ($f->hasCallIn($fn['open'], $fn['close'], self::SIDE_EFFECTS)) {
                    $this->report($f, $f->line($fn['start']), 'permission_callback com efeito colateral (gravação, cache ou contador): o WordPress pode chamá-lo mais de uma vez e antes de validar os argumentos.', 'Deixe o permission_callback só decidir; mova contadores e caches para o callback da rota.');
                }
            }
        }
    }

    private function mailHijack(SourceFile $f)
    {
        foreach ($f->calls(['add_action', 'add_filter']) as $c) {
            $hook = $c['args'] ? $f->literal($c['args'][0][0], $c['args'][0][1]) : null;
            if ($hook === null || !in_array($hook, self::MAIL_HOOKS, true)) {
                continue;
            }
            $fn = $f->functionAt($c['i']);
            if ($fn && $f->hasCallIn($fn['open'], $fn['close'], ['wp_mail', 'remove_action', 'remove_filter'])) {
                continue; // escopo local: liga, envia, desliga
            }
            $this->report($f, $c['line'], '`' . $hook . '` registrado para o site inteiro: muda todos os e-mails do WordPress e dos outros plugins.', 'Ligue o filtro só em volta do seu wp_mail() e remova logo depois, ou confira no callback se o e-mail é do seu plugin.');
        }
    }

    private function remoteCachedWithoutCheck(SourceFile $f)
    {
        foreach ($f->functions() as $fn) {
            if ($fn['open'] < 0) {
                continue;
            }
            $s = $fn['open'];
            $e = $fn['close'];
            if (!$f->hasCallIn($s, $e, ['set_transient', 'set_site_transient', 'wp_cache_set'])) {
                continue;
            }
            if (!$f->hasCallIn($s, $e, ['wp_remote_get', 'wp_remote_post', 'wp_remote_request', 'wp_safe_remote_get', 'wp_safe_remote_post'])) {
                continue;
            }
            if ($f->hasCallIn($s, $e, ['is_wp_error', 'wp_remote_retrieve_response_code'])) {
                continue;
            }
            foreach ($f->calls(['set_transient', 'set_site_transient', 'wp_cache_set']) as $c) {
                if ($c['i'] > $s && $c['i'] < $e) {
                    $this->report($f, $c['line'], 'Resposta remota guardada em cache sem conferir is_wp_error()/código HTTP: uma falha fica em cache pelo tempo todo do transient.', 'Só grave depois de confirmar sucesso; em falha, grave por pouco tempo (ex.: 60 s) ou não grave.');
                    break;
                }
            }
        }
    }
}
