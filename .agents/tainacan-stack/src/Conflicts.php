<?php

namespace TainacanStack\Check;

/**
 * Colisões entre plugins que convivem no mesmo WordPress. Separa o que
 * quebra (erro fatal, rota ou shortcode sequestrado) do que merece
 * atenção (opção escrita por dois plugins, mesmo filtro do Tainacan na
 * mesma prioridade).
 */
final class Conflicts
{
    /** Ganchos do WordPress/Tainacan em que vários plugins atuando juntos é normal, mas a ordem importa. */
    /** Opções do núcleo do WordPress. */
    const WP_OPTION = '/^(active_plugins|page_on_front|page_for_posts|show_on_front|permalink_structure|blogname|blogdescription|siteurl|home|admin_email|template|stylesheet|rewrite_rules|cron|widget_\w+|sidebars_widgets|theme_mods_\w+|uninstall_plugins|recently_activated|users_can_register|default_role|timezone_string|date_format|time_format|WPLANG|posts_per_page)$/';

    const WATCHED_HOOK = '/^((?!tainacan-register)tainacan[-_](?!register).*|the_content|the_title|template_include|single_template|pre_get_posts|posts_(where|join|clauses|request|results|pre_query)|rest_pre_dispatch|rest_post_dispatch|rest_request_after_callbacks|rest_pre_serve_request|script_loader_tag|style_loader_tag|wp_mail|phpmailer_init|upload_mimes|sanitize_file_name)$/';

    /**
     * @return array<int,array{severity:string,kind:string,name:string,plugins:array<string,string[]>,why:string}>
     */
    public static function find(array $inv)
    {
        $out = [];
        $fatal = [
            'functions' => ['Função global declarada em mais de um plugin: o segundo a carregar causa erro fatal (Cannot redeclare).', true],
            'classes' => ['Classe com o mesmo nome em mais de um plugin: erro fatal ou, com autoload, a versão errada é carregada.', true],
            'constants' => ['Constante definida em mais de um plugin: aviso de redefinição e o valor do primeiro vence em silêncio.', false],
        ];
        foreach ($fatal as $kind => $meta) {
            foreach (self::group($inv, $kind) as $name => $plugins) {
                if (count($plugins) < 2) {
                    continue;
                }
                $allGuarded = self::allGuarded($inv, $kind, $name);
                $out[] = [
                    'severity' => ($meta[1] && !$allGuarded) ? Finding::BLOCK : Finding::WARN,
                    'kind' => $kind,
                    'name' => $name,
                    'plugins' => $plugins,
                    'why' => $meta[0] . ($allGuarded ? ' Todas as declarações estão protegidas por *_exists, então vale a do primeiro plugin carregado: confirme que são idênticas.' : ''),
                ];
            }
        }
        $unique = [
            'shortcodes' => 'Mesmo shortcode em dois plugins: o último registrado substitui o outro.',
            'post_types' => 'Mesmo tipo de post registrado por dois plugins.',
            'taxonomies' => 'Mesma taxonomia registrada por dois plugins.',
            'rest' => 'Mesma rota REST em dois plugins: as respostas se misturam ou uma some.',
            'admin_pages' => 'Mesmo slug de página de admin: um menu esconde o outro.',
            'js_globals' => 'Mesmo objeto JS global: o último script sobrescreve os dados do outro.',
        ];
        foreach ($unique as $kind => $why) {
            foreach (self::group($inv, $kind) as $name => $plugins) {
                if (count($plugins) > 1) {
                    $out[] = ['severity' => Finding::BLOCK, 'kind' => $kind, 'name' => $name, 'plugins' => $plugins, 'why' => $why];
                }
            }
        }
        // handles: mesmo nome com origem diferente
        $handles = [];
        foreach ($inv as $slug => $d) {
            foreach ($d['handles'] as $h) {
                $handles[$h['kind'] . ':' . $h['name']][$slug][] = $h;
            }
        }
        foreach ($handles as $key => $byPlugin) {
            if (count($byPlugin) < 2) {
                continue;
            }
            $plugins = [];
            foreach ($byPlugin as $slug => $list) {
                $plugins[$slug] = array_map(function ($h) {
                    return $h['where'] . ' (' . $h['src'] . ')';
                }, $list);
            }
            $out[] = ['severity' => Finding::BLOCK, 'kind' => 'handles', 'name' => $key, 'plugins' => $plugins, 'why' => 'O mesmo handle aponta para arquivos diferentes: o WordPress carrega só o primeiro registrado, e o outro plugin recebe o script errado.'];
        }
        // opções e transients escritos por mais de um plugin
        foreach (['options' => 'opção', 'transients' => 'transient'] as $kind => $label) {
            $writers = [];
            foreach ($inv as $slug => $d) {
                foreach ($d[$kind] as $o) {
                    if ($o['write']) {
                        $writers[$o['name']][$slug][] = $o['where'];
                    }
                }
            }
            foreach ($writers as $name => $plugins) {
                if ($kind === 'options' && preg_match(self::WP_OPTION, $name)) {
                    continue; // opção do núcleo do WordPress: mexer nela é outra conversa, não colisão entre plugins
                }
                if (count($plugins) > 1) {
                    $out[] = ['severity' => Finding::WARN, 'kind' => $kind, 'name' => $name, 'plugins' => $plugins, 'why' => 'A ' . $label . ' é gravada por mais de um plugin: um sobrescreve o dado do outro.'];
                }
            }
        }
        foreach (self::group($inv, 'cron') as $name => $plugins) {
            if (count($plugins) > 1) {
                $out[] = ['severity' => Finding::WARN, 'kind' => 'cron', 'name' => $name, 'plugins' => $plugins, 'why' => 'Mesmo evento de cron agendado por dois plugins: os callbacks dos dois rodam juntos.'];
            }
        }
        // remove_all_* de um gancho que outro plugin usa
        foreach ($inv as $slug => $d) {
            foreach ($d['removals'] as $r) {
                if (!$r['all']) {
                    continue;
                }
                $victims = [];
                foreach ($inv as $other => $od) {
                    if ($other === $slug) {
                        continue;
                    }
                    foreach ($od['hooks'] as $h) {
                        if ($h['name'] === $r['name']) {
                            $victims[$other][] = $h['where'];
                        }
                    }
                }
                if ($victims) {
                    $notice = (bool) preg_match('/^(all_|network_|user_)?admin_notices$/', $r['name']);
                    $victims[$slug] = [$r['where'] . ' (remove todos)'];
                    $out[] = [
                        'severity' => $notice ? Finding::WARN : Finding::BLOCK,
                        'kind' => 'removals',
                        'name' => $r['name'],
                        'plugins' => $victims,
                        'why' => $slug . ' remove todos os callbacks de um gancho que outros plugins usam'
                            . ($notice ? ' (em admin_notices é comum limpar a tela do próprio plugin; confirme que a remoção só acontece lá).' : ': enquanto a remoção valer, o código dos outros não roda.'),
                    ];
                }
            }
        }
        // mesmo gancho sensível, mesma prioridade, plugins diferentes
        $byHook = [];
        foreach ($inv as $slug => $d) {
            foreach ($d['hooks'] as $h) {
                if (preg_match(self::WATCHED_HOOK, $h['name'])) {
                    $byHook[$h['name'] . ' @' . $h['priority']][$slug][] = $h['where'];
                }
            }
        }
        foreach ($byHook as $name => $plugins) {
            if (count($plugins) > 1) {
                $out[] = ['severity' => 'info', 'kind' => 'hooks', 'name' => $name, 'plugins' => $plugins, 'why' => 'Mais de um plugin no mesmo gancho sensível e na mesma prioridade: a ordem depende de qual carrega primeiro. Teste os plugins juntos.'];
            }
        }
        $order = [Finding::BLOCK => 0, Finding::WARN => 1, 'info' => 2];
        usort($out, function ($a, $b) use ($order) {
            return [$order[$a['severity']], $a['kind'], $a['name']] <=> [$order[$b['severity']], $b['kind'], $b['name']];
        });
        return $out;
    }

    /** @return array<string,array<string,string[]>> nome => plugin => locais */
    private static function group(array $inv, $kind)
    {
        $g = [];
        foreach ($inv as $slug => $d) {
            foreach ($d[$kind] as $item) {
                $key = in_array($kind, ['functions', 'classes'], true) ? strtolower($item['name']) : $item['name'];
                $g[$key][$slug][] = $item['where'];
            }
        }
        return $g;
    }

    private static function allGuarded(array $inv, $kind, $name)
    {
        foreach ($inv as $d) {
            foreach ($d[$kind] as $item) {
                $key = in_array($kind, ['functions', 'classes'], true) ? strtolower($item['name']) : $item['name'];
                if ($key === $name && empty($item['guarded'])) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function exitCode(array $conflicts, $failOn)
    {
        $code = 0;
        foreach ($conflicts as $c) {
            if ($c['severity'] === Finding::BLOCK) {
                return 2;
            }
            if ($c['severity'] === Finding::WARN && $failOn !== 'block') {
                $code = 1;
            }
        }
        return $code;
    }

    public static function render(array $conflicts, $format)
    {
        if ($format === 'json') {
            return json_encode($conflicts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        }
        $count = ['block' => 0, 'warn' => 0, 'info' => 0];
        foreach ($conflicts as $c) {
            $count[$c['severity']]++;
        }
        $out = "# Convivência entre plugins\n\n" . $count['block'] . ' colisão(ões) que quebram, ' . $count['warn'] . ' a revisar, ' . $count['info'] . " ponto(s) de atenção.\n";
        $labels = [Finding::BLOCK => 'Quebra', Finding::WARN => 'Revisar', 'info' => 'Atenção'];
        foreach ($conflicts as $c) {
            $out .= "\n### [" . $labels[$c['severity']] . '] ' . $c['kind'] . ': `' . $c['name'] . "`\n" . $c['why'] . "\n";
            foreach ($c['plugins'] as $slug => $where) {
                $out .= '- **' . $slug . '**: ' . implode(', ', array_slice(array_unique($where), 0, 4)) . (count($where) > 4 ? ' …' : '') . "\n";
            }
        }
        return $out;
    }
}
