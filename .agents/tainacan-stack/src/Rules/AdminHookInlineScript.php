<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T02: <script> dentro do HTML de um gancho de admin do Tainacan.
 * O HTML entra no formulário por v-html (innerHTML), e o navegador não
 * executa <script> inserido assim (views/admin/js/mixins.js:85-96,
 * item-edition-form.vue:648-652). A regra procura <script no HTML do
 * callback e nos templates incluídos por caminho literal. Atributos on*
 * que chamam funções definidas nesse script também falham, mas não são
 * detectados.
 */
final class AdminHookInlineScript extends Rule
{
    const ID = 'TNC-T02';
    const TITLE = '<script> no HTML de gancho de admin do Tainacan (não executa)';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'tainacan';

    protected function check(SourceFile $f)
    {
        foreach ($f->calls(['tainacan_register_admin_hook']) as $c) {
            if (count($c['args']) < 2) {
                continue;
            }
            $fn = $f->resolveCallback($c['args'][1][0], $c['args'][1][1], $c['i']);
            if (!$fn || $fn['open'] < 0) {
                continue;
            }
            $hit = $this->scriptIn($f, $fn['open'], $fn['close']);
            if ($hit !== null) {
                $this->report($f, $hit, '`<script>` no HTML do gancho de admin: entra por v-html e nunca executa.', 'Mova o JS para um arquivo enfileirado em admin_enqueue_scripts e escute o evento tainacan-<entidade>-hook-reload no document.', $f->line($fn['close']));
                continue;
            }
            foreach ($this->includedTemplates($f, $fn['open'], $fn['close']) as $tpl) {
                $text = (string) @file_get_contents($tpl['path']);
                if (preg_match('/<script\b/i', $text, $m, PREG_OFFSET_CAPTURE)) {
                    $line = substr_count(substr($text, 0, $m[0][1]), "\n") + 1;
                    $this->report($f, $tpl['line'], 'O template ' . basename($tpl['path']) . ' (linha ' . $line . ') tem `<script>`, mas é usado num gancho de admin: entra por v-html e nunca executa.', 'Mova o JS para um arquivo enfileirado em admin_enqueue_scripts.', $f->line($fn['close']));
                }
            }
        }
    }

    private function scriptIn(SourceFile $f, $s, $e)
    {
        for ($i = $s; $i <= $e; $i++) {
            $t = $f->type($i);
            if (($t === T_CONSTANT_ENCAPSED_STRING || $t === T_ENCAPSED_AND_WHITESPACE || $t === T_INLINE_HTML) && preg_match('/<script\b/i', $f->val($i), $m, PREG_OFFSET_CAPTURE)) {
                return $f->line($i) + substr_count(substr($f->val($i), 0, $m[0][1]), "\n");
            }
        }
        return null;
    }

    /** Templates incluídos por caminho resolvível (__DIR__ . '/x.php', plugin_dir_path( __FILE__ ) . 'x.php'). */
    private function includedTemplates(SourceFile $f, $s, $e)
    {
        $out = [];
        if ($f->absPath === '') {
            return $out;
        }
        $dir = dirname($f->absPath);
        for ($i = $s; $i <= $e; $i++) {
            if (!in_array($f->type($i), [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE], true)) {
                continue;
            }
            $k = $i + 1;
            $expr = '';
            while ($k <= $e && $f->type($k) !== ';') {
                $expr .= $f->type($k) === T_CONSTANT_ENCAPSED_STRING ? '"' . SourceFile::unquote($f->val($k)) . '"' : $f->val($k);
                $k++;
            }
            if (preg_match('/^\(?\s*(?:__DIR__|dirname\(__FILE__\)|plugin_dir_path\(__FILE__\))\s*\.\s*"([^"]+)"\s*\)?$/', str_replace(' ', '', $expr), $m)) {
                $path = $dir . '/' . ltrim($m[1], '/');
                if (is_file($path)) {
                    $out[] = ['path' => $path, 'line' => $f->line($i)];
                }
            }
        }
        return $out;
    }
}
