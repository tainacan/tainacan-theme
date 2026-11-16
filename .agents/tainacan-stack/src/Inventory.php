<?php

namespace TainacanStack\Check;

/**
 * Inventário do que cada plugin registra no WordPress compartilhado:
 * ganchos, opções, transients, handles de script/estilo, objetos JS
 * globais, shortcodes, rotas REST, tipos de post, taxonomias, eventos de
 * cron, páginas de admin, funções/classes/constantes globais e ganchos do
 * Tainacan. É a matéria-prima de Conflicts.
 */
final class Inventory
{
    /** Constantes do WordPress que plugins e scripts de teste definem por conta própria. */
    const WP_CONSTANT = '/^(ABSPATH|WPINC|WP_[A-Z_]+|DOING_(AJAX|CRON|AUTOSAVE)|DONOTCACHE\w*|SHORTINIT|XMLRPC_REQUEST|REST_REQUEST|IFRAME_REQUEST|DISABLE_WP_CRON|AUTOSAVE_INTERVAL|EMPTY_TRASH_DAYS|SAVEQUERIES|SCRIPT_DEBUG|CONCATENATE_SCRIPTS|COOKIE_DOMAIN|MULTISITE|SUBDOMAIN_INSTALL|TAINACAN_[A-Z_]+)$/';

    /** Subpastas de $dir que são plugins (têm cabeçalho Plugin Name). Se $dir já é um plugin, só ele. */
    public static function pluginDirs($dir)
    {
        $dir = rtrim(str_replace('\\', '/', realpath($dir) ?: $dir), '/');
        $out = [];
        foreach (glob($dir . '/*', GLOB_ONLYDIR) ?: [] as $sub) {
            $sub = str_replace('\\', '/', $sub);
            if (ProjectContext::forRoot($sub)->isPlugin()) {
                $out[] = $sub;
            }
        }
        // pasta de plugins (pode ter um hello.php solto com cabeçalho) ou o próprio plugin
        if (!$out && ProjectContext::forRoot($dir)->isPlugin()) {
            return [$dir];
        }
        sort($out);
        return $out;
    }

    /** @return array<string,array<string,array<int,array<string,mixed>>>> slug => categoria => itens */
    public static function scanWorkspace($dir)
    {
        $inv = [];
        foreach (self::pluginDirs($dir) as $p) {
            $inv[basename($p)] = self::scanPlugin($p);
        }
        return $inv;
    }

    /** @return array<string,array<int,array<string,mixed>>> */
    public static function scanPlugin($dir)
    {
        $data = [
            'hooks' => [], 'removals' => [], 'options' => [], 'transients' => [], 'handles' => [],
            'js_globals' => [], 'shortcodes' => [], 'rest' => [], 'post_types' => [], 'taxonomies' => [],
            'cron' => [], 'admin_pages' => [], 'functions' => [], 'classes' => [], 'constants' => [],
            'tainacan' => [],
        ];
        foreach (Runner::collect($dir) as $entry) {
            $ext = strtolower(pathinfo($entry['rel'], PATHINFO_EXTENSION));
            if (preg_match('#(^|/)(tests?|developer|bin)/#', $entry['rel'])) {
                continue;
            }
            $text = (string) @file_get_contents($entry['abs']);
            if ($ext === 'js' && preg_match_all('/\bwindow\.([A-Za-z_$][\w$]*)\s*=(?!=)/', $text, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[1] as $hit) {
                    $data['js_globals'][] = ['name' => $hit[0], 'where' => $entry['rel'] . ':' . (substr_count(substr($text, 0, $hit[1]), "\n") + 1), 'via' => 'window'];
                }
            }
            if ($ext !== 'php') {
                continue;
            }
            $f = new SourceFile($entry['rel'], $entry['abs'], $text, $entry['project']);
            self::scanFile($f, $data);
        }
        return $data;
    }

    private static function lit(SourceFile $f, array $c, $idx)
    {
        if (!isset($c['args'][$idx])) {
            return null;
        }
        return $f->literal($c['args'][$idx][0], $c['args'][$idx][1]);
    }

    private static function where(SourceFile $f, $line)
    {
        return $f->path . ':' . $line;
    }

    private static function scanFile(SourceFile $f, array &$data)
    {
        $namespace = self::namespaceOf($f);
        foreach ($f->calls(['add_action', 'add_filter']) as $c) {
            $hook = self::lit($f, $c, 0);
            if ($hook === null) {
                continue;
            }
            $prio = isset($c['args'][2]) ? trim($f->text($c['args'][2][0], $c['args'][2][1])) : '10';
            $cb = isset($c['args'][1]) ? $f->text($c['args'][1][0], $c['args'][1][1]) : '';
            $data['hooks'][] = ['name' => $hook, 'priority' => $prio, 'callback' => mb_substr($cb, 0, 80), 'where' => self::where($f, $c['line'])];
        }
        foreach ($f->calls(['remove_action', 'remove_filter', 'remove_all_actions', 'remove_all_filters']) as $c) {
            $hook = self::lit($f, $c, 0);
            if ($hook !== null) {
                $data['removals'][] = ['name' => $hook, 'all' => strpos($c['name'], '_all_') !== false, 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->calls(['get_option', 'update_option', 'add_option', 'delete_option', 'register_setting', 'get_site_option', 'update_site_option']) as $c) {
            $idx = $c['name'] === 'register_setting' ? 1 : 0;
            $key = self::lit($f, $c, $idx);
            if ($key !== null) {
                $data['options'][] = ['name' => $key, 'write' => $c['name'] !== 'get_option' && $c['name'] !== 'get_site_option', 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->calls(['get_transient', 'set_transient', 'delete_transient', 'get_site_transient', 'set_site_transient']) as $c) {
            $key = self::lit($f, $c, 0);
            if ($key !== null) {
                $data['transients'][] = ['name' => $key, 'write' => strpos($c['name'], 'get_') !== 0, 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->calls(['wp_register_script', 'wp_register_style', 'wp_enqueue_script', 'wp_enqueue_style']) as $c) {
            $handle = self::lit($f, $c, 0);
            if ($handle === null || !isset($c['args'][1])) {
                continue; // só enfileirar um handle já registrado não define nada
            }
            $src = $f->text($c['args'][1][0], $c['args'][1][1]);
            if (in_array(strtolower(trim($src)), ["''", '""', 'false', 'null'], true)) {
                continue;
            }
            $data['handles'][] = ['name' => $handle, 'kind' => strpos($c['name'], 'style') !== false ? 'style' : 'script', 'src' => mb_substr($src, 0, 120), 'where' => self::where($f, $c['line'])];
        }
        foreach ($f->calls(['wp_localize_script']) as $c) {
            $obj = self::lit($f, $c, 1);
            if ($obj !== null) {
                $data['js_globals'][] = ['name' => $obj, 'where' => self::where($f, $c['line']), 'via' => 'wp_localize_script'];
            }
        }
        foreach ($f->calls(['add_shortcode']) as $c) {
            $tag = self::lit($f, $c, 0);
            if ($tag !== null) {
                $data['shortcodes'][] = ['name' => $tag, 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->calls(['register_rest_route']) as $c) {
            if (count($c['args']) < 2) {
                continue;
            }
            $ns = $f->literal($c['args'][0][0], $c['args'][0][1]);
            $route = $f->literal($c['args'][1][0], $c['args'][1][1]);
            if ($ns === null || $route === null) {
                continue; // namespace em constante ou variável: não dá para comparar entre plugins
            }
            $data['rest'][] = ['name' => trim($ns, '/') . '/' . ltrim($route, '/'), 'namespace' => trim($ns, '/'), 'where' => self::where($f, $c['line'])];
        }
        foreach ($f->calls(['register_post_type']) as $c) {
            $n = self::lit($f, $c, 0);
            if ($n !== null) {
                $data['post_types'][] = ['name' => $n, 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->calls(['register_taxonomy']) as $c) {
            $n = self::lit($f, $c, 0);
            if ($n !== null) {
                $data['taxonomies'][] = ['name' => $n, 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->calls(['wp_schedule_event', 'wp_schedule_single_event', 'as_schedule_recurring_action', 'as_enqueue_async_action']) as $c) {
            $idx = $c['name'] === 'wp_schedule_event' ? 2 : ($c['name'] === 'wp_schedule_single_event' ? 1 : ($c['name'] === 'as_schedule_recurring_action' ? 2 : 0));
            $n = self::lit($f, $c, $idx);
            if ($n !== null) {
                $data['cron'][] = ['name' => $n, 'where' => self::where($f, $c['line'])];
            }
        }
        $slugPos = ['add_menu_page' => 3, 'add_submenu_page' => 4, 'add_options_page' => 3, 'add_management_page' => 3, 'add_theme_page' => 3, 'add_users_page' => 3, 'add_dashboard_page' => 3];
        foreach ($f->calls(array_keys($slugPos)) as $c) {
            $n = self::lit($f, $c, $slugPos[$c['name']]);
            if ($n !== null) {
                $data['admin_pages'][] = ['name' => $n, 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->calls(['define']) as $c) {
            $n = self::lit($f, $c, 0);
            if ($n !== null && !preg_match(self::WP_CONSTANT, $n)) {
                $data['constants'][] = ['name' => $n, 'guarded' => self::guarded($f, $c['i'], 'defined'), 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->functions() as $fn) {
            if ($fn['closure'] || $fn['class'] !== null || $fn['name'] === null || $namespace !== '') {
                continue;
            }
            // função global declarada dentro de outra (rara) também conta
            $data['functions'][] = ['name' => $fn['name'], 'guarded' => self::guarded($f, $fn['start'], 'function_exists'), 'where' => self::where($f, $fn['line'])];
        }
        foreach ($f->classes() as $c) {
            if ($c['name'] === '{anon}') {
                continue;
            }
            $fqn = ($namespace !== '' ? $namespace . '\\' : '') . $c['name'];
            $data['classes'][] = ['name' => $fqn, 'guarded' => self::guarded($f, $c['start'], 'class_exists'), 'where' => self::where($f, $c['line'])];
        }
        foreach ($f->calls(['tainacan_register_admin_hook']) as $c) {
            $n = self::lit($f, $c, 0);
            if ($n !== null) {
                $data['tainacan'][] = ['name' => 'admin_hook:' . $n, 'where' => self::where($f, $c['line'])];
            }
        }
        foreach ($f->methodCalls(['register_metadata_type', 'register_filter_type', 'register_importer', 'register_exporter', 'register_exposer', 'register_exposer_type', 'register_mapper']) as $c) {
            if (!$c['args']) {
                continue;
            }
            $n = trim($f->text($c['args'][0][0], $c['args'][0][1]));
            $data['tainacan'][] = ['name' => $c['name'] . ':' . trim($n, '\'"'), 'where' => self::where($f, $c['line'])];
        }
    }

    private static function namespaceOf(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) === T_NAMESPACE && $f->type($i + 1) === 'NAME') {
                return ltrim($f->val($i + 1), '\\');
            }
        }
        return '';
    }

    /** A declaração está protegida por if ( ! function_exists/class_exists/defined ( ... ) )? */
    private static function guarded(SourceFile $f, $i, $check)
    {
        $from = max(0, $i - 25);
        for ($k = $i - 1; $k >= $from; $k--) {
            if ($f->type($k) === 'NAME' && strtolower($f->val($k)) === $check) {
                return true;
            }
        }
        // define( 'X', ... ) || ou defined( 'X' ) || define(...)
        if ($check === 'defined') {
            $stmt = $f->text($f->statementStart($i), $i);
            return stripos($stmt, 'defined') !== false;
        }
        return false;
    }

    public static function render(array $inv, $format)
    {
        if ($format === 'json') {
            return json_encode($inv, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        }
        $out = "# Inventário de plugins\n\n| Plugin | Ganchos | Opções | Handles | Rotas REST | Funções globais | Classes | Tainacan |\n|---|---|---|---|---|---|---|---|\n";
        foreach ($inv as $slug => $d) {
            $out .= sprintf(
                "| %s | %d | %d | %d | %d | %d | %d | %d |\n",
                $slug,
                count($d['hooks']),
                count(array_unique(array_column($d['options'], 'name'))),
                count($d['handles']),
                count($d['rest']),
                count($d['functions']),
                count($d['classes']),
                count($d['tainacan'])
            );
        }
        foreach ($inv as $slug => $d) {
            $out .= "\n## $slug\n";
            foreach (['rest' => 'Rotas REST', 'tainacan' => 'Registros no Tainacan', 'shortcodes' => 'Shortcodes', 'post_types' => 'Tipos de post', 'taxonomies' => 'Taxonomias', 'admin_pages' => 'Páginas de admin', 'cron' => 'Cron', 'js_globals' => 'Globais JS'] as $key => $label) {
                if (!$d[$key]) {
                    continue;
                }
                $names = array_unique(array_column($d[$key], 'name'));
                sort($names);
                $out .= '- **' . $label . ':** ' . implode(', ', array_map(function ($n) {
                    return '`' . $n . '`';
                }, $names)) . "\n";
            }
        }
        return $out;
    }
}
