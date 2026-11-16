<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-S03: handler de wp_ajax_* ou admin_post_* sem verificação de nonce
 * (CSRF) ou, quando não é nopriv, sem current_user_can().
 * Sem nonce: bloqueia quando o handler altera estado, avisa quando só lê.
 * Sem capability: sempre aviso. A verificação pode estar em métodos da
 * mesma classe chamados pelo handler, até três níveis abaixo. hash_equals
 * conta como verificação (state de OAuth/OIDC, token próprio).
 */
final class HandlerWithoutNonce extends Rule
{
    const ID = 'TNC-S03';
    const TITLE = 'Handler AJAX/admin-post sem nonce ou sem capability';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'seguranca';

    /** hash_equals cobre o anti-CSRF por state de OAuth/OIDC e por token próprio. */
    const NONCE = ['check_ajax_referer', 'check_admin_referer', 'wp_verify_nonce', 'hash_equals'];
    const CAPS = ['current_user_can', 'user_can', 'is_super_admin'];
    const WRITES = [
        'wp_insert_post', 'wp_update_post', 'wp_delete_post', 'wp_trash_post', 'update_option', 'delete_option', 'add_option',
        'update_post_meta', 'delete_post_meta', 'add_post_meta', 'update_user_meta', 'delete_user_meta', 'update_term_meta',
        'wp_set_object_terms', 'wp_insert_term', 'wp_update_term', 'wp_delete_term', 'wp_insert_user', 'wp_update_user',
        'wp_delete_user', 'wp_delete_attachment', 'wp_insert_attachment', 'wp_mail',
        'move_uploaded_file', 'wp_handle_upload', 'media_handle_upload', 'unlink', 'file_put_contents',
    ];
    const WRITE_METHODS = ['insert', 'update', 'delete', 'replace', 'query', 'save', 'trash'];

    protected function check(SourceFile $f)
    {
        foreach ($f->calls(['add_action']) as $c) {
            if (count($c['args']) < 2) {
                continue;
            }
            $hook = $f->literal($c['args'][0][0], $c['args'][0][1]);
            if ($hook === null) {
                // 'wp_ajax_' . $acao
                $txt = $f->stringContent($c['args'][0][0], $c['args'][0][1]);
                if (!preg_match('/^\s*(wp_ajax_(nopriv_)?|admin_post_(nopriv_)?)/', $txt)) {
                    continue;
                }
                $hook = trim($txt);
            }
            if (!preg_match('/^(wp_ajax_|admin_post_)(nopriv_)?/', $hook, $m)) {
                continue;
            }
            $nopriv = !empty($m[2]);
            $fn = $f->resolveCallback($c['args'][1][0], $c['args'][1][1], $c['i']);
            if (!$fn || $fn['open'] < 0) {
                continue;
            }
            $body = $this->bodyWithHelpers($f, $fn);
            $hasNonce = $this->anyCall($f, $body, self::NONCE);
            $writes = $this->writes($f, $body);
            $where = ($fn['class'] ? $fn['class'] . '::' : '') . ($fn['name'] ?: 'closure');
            if (!$hasNonce) {
                $this->report(
                    $f,
                    $fn['line'],
                    'Handler `' . $where . '` de `' . $hook . '` não verifica nonce' . ($writes ? ' e altera dados (CSRF).' : '.'),
                    'Chame check_ajax_referer( \'<acao>\' ) ou check_admin_referer( \'<acao>\' ) no início do handler.',
                    $f->line($fn['close']),
                    $writes ? Finding::BLOCK : Finding::WARN
                );
            }
            if (!$nopriv && !$this->anyCall($f, $body, self::CAPS)) {
                $this->report(
                    $f,
                    $fn['line'],
                    'Handler `' . $where . '` de `' . $hook . '` não verifica capability: qualquer usuário logado (inclusive assinante) pode chamá-lo.',
                    'Acrescente if ( ! current_user_can( \'<capability>\' ) ) { wp_send_json_error( null, 403 ); }',
                    $f->line($fn['close']),
                    Finding::WARN
                );
            }
        }
    }

    /**
     * Intervalos do corpo do handler e dos métodos da mesma classe que ele
     * chama via $this-> ou self:: (até três níveis), onde a verificação costuma morar.
     * @return array<int,array{0:int,1:int}>
     */
    private function bodyWithHelpers(SourceFile $f, array $fn, $depth = 0, array &$seen = [])
    {
        $ranges = [[$fn['open'], $fn['close']]];
        $seen[$fn['open']] = true;
        if ($fn['class'] === null || $depth >= 3) {
            return $ranges;
        }
        for ($i = $fn['open']; $i < $fn['close']; $i++) {
            $isThis = $f->type($i) === T_VARIABLE && $f->val($i) === '$this' && $f->type($i + 1) === T_OBJECT_OPERATOR;
            $isSelf = $f->type($i) === 'NAME' && in_array(strtolower($f->val($i)), ['self', 'static'], true) && $f->type($i + 1) === T_DOUBLE_COLON;
            if (($isThis || $isSelf) && $f->type($i + 2) === 'NAME' && $f->type($i + 3) === '(') {
                $helper = $f->findMethod($fn['class'], $f->val($i + 2));
                if ($helper && $helper['open'] >= 0 && !isset($seen[$helper['open']])) {
                    $ranges = array_merge($ranges, $this->bodyWithHelpers($f, $helper, $depth + 1, $seen));
                }
            }
        }
        return $ranges;
    }

    private function anyCall(SourceFile $f, array $ranges, array $names)
    {
        foreach ($ranges as $r) {
            if ($f->hasCallIn($r[0], $r[1], $names)) {
                return true;
            }
        }
        return false;
    }

    private function writes(SourceFile $f, array $ranges)
    {
        if ($this->anyCall($f, $ranges, self::WRITES)) {
            return true;
        }
        foreach ($ranges as $r) {
            for ($i = $r[0]; $i < $r[1]; $i++) {
                if ($f->type($i) === 'NAME' && $f->type($i + 1) === '(' && $f->type($i - 1) === T_OBJECT_OPERATOR
                    && in_array(strtolower($f->val($i)), self::WRITE_METHODS, true)) {
                    return true;
                }
            }
        }
        return false;
    }
}
