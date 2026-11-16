<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-S04: rota REST sem permission_callback, rota de escrita aberta
 * (__return_true) ou permission_callback apontando para método
 * protected/private (o WordPress chama de fora da classe e falha).
 */
final class RestPermission extends Rule
{
    const ID = 'TNC-S04';
    const TITLE = 'Rota REST sem permissão adequada';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'seguranca';

    const READ_METHODS = ['GET', 'HEAD', 'OPTIONS', 'READABLE'];

    protected function check(SourceFile $f)
    {
        foreach ($f->calls(['register_rest_route']) as $c) {
            if (count($c['args']) < 3) {
                continue;
            }
            $s = $c['args'][2][0];
            if (!$f->isArrayStart($s)) {
                continue; // array montado em variável: fora do alcance estático
            }
            $route = $f->stringContent($c['args'][1][0], $c['args'][1][1]);
            foreach ($this->endpoints($f, $s) as $ep) {
                $this->checkEndpoint($f, $ep, trim($route), $c);
            }
        }
    }

    /** @return array<int,array<string,array<string,int>>> */
    private function endpoints(SourceFile $f, $s)
    {
        $entries = $f->arrayEntries($s);
        if ($entries === null) {
            return [];
        }
        $keyed = [];
        $list = [];
        foreach ($entries as $en) {
            if ($en['key'] !== null) {
                $keyed[$en['key']] = $en;
            } elseif ($f->isArrayStart($en['vs'])) {
                $sub = $f->arrayEntries($en['vs']);
                $map = [];
                foreach ($sub ?: [] as $se) {
                    if ($se['key'] !== null) {
                        $map[$se['key']] = $se;
                    }
                }
                $map['__line'] = ['vs' => $en['vs']];
                $list[] = $map;
            }
        }
        if (isset($keyed['callback']) || isset($keyed['methods'])) {
            $keyed['__line'] = ['vs' => $s];
            $list[] = $keyed;
        }
        return $list;
    }

    private function checkEndpoint(SourceFile $f, array $ep, $route, array $call)
    {
        $line = $f->line($ep['__line']['vs']);
        $endLine = $f->line($call['close']);
        $isWrite = $this->isWrite($f, isset($ep['methods']) ? $ep['methods'] : null);
        $label = 'Rota `' . $route . '` (' . ($isWrite ? 'escrita' : 'leitura') . ')';
        if (!isset($ep['permission_callback'])) {
            $this->report($f, $line, $label . ' sem `permission_callback`.', 'Declare permission_callback com current_user_can() ou $entidade->can_read()/can_edit(); leitura pública confere que o recurso existe e é público.', $endLine);
            return;
        }
        $pc = $ep['permission_callback'];
        $lit = $f->literal($pc['vs'], $pc['ve']);
        if ($lit === '__return_true' || $this->closureReturnsTrue($f, $pc['vs'], $pc['ve'])) {
            if ($isWrite) {
                $this->report($f, $line, $label . ' aberta a qualquer visitante (permission_callback sempre verdadeiro).', 'Verifique capability (current_user_can) e, para uso no navegador, o nonce wp_rest.', $endLine);
            } else {
                $this->report($f, $line, $label . ' com permission_callback sempre verdadeiro.', 'Mesmo para leitura, confira no callback de permissão que o recurso existe e é público (post_status publish, $item->can_read(), coleção pública); senão, dado privado ou rascunho vaza pela REST.', $endLine, Finding::WARN);
            }
            return;
        }
        $fn = $f->resolveCallback($pc['vs'], $pc['ve'], $call['i']);
        if ($fn && $fn['class'] !== null && $fn['visibility'] !== 'public') {
            $this->report($f, $f->line($pc['vs']), 'permission_callback `' . $fn['name'] . '` é ' . $fn['visibility'] . ': o WordPress o chama de fora da classe e a rota falha.', 'Torne o método public.', $endLine);
        }
    }

    private function isWrite(SourceFile $f, $methods)
    {
        if ($methods === null) {
            return false; // padrão do WordPress é GET
        }
        $txt = strtoupper($f->text($methods['vs'], $methods['ve']));
        $txt = str_replace(['WP_REST_SERVER::', '\\'], '', $txt);
        preg_match_all('/[A-Z_]+/', $txt, $m);
        foreach ($m[0] as $word) {
            if (in_array($word, ['ARRAY', 'TRUE', 'FALSE'], true)) {
                continue;
            }
            if (!in_array($word, self::READ_METHODS, true)) {
                return true;
            }
        }
        return false;
    }

    private function closureReturnsTrue(SourceFile $f, $s, $e)
    {
        $txt = strtolower(preg_replace('/\s+/', '', $f->text($s, $e)));
        return (bool) preg_match('/^(static)?function\(\)\{returntrue;\}$|^(static)?fn\(\)=>true$/', $txt);
    }
}
