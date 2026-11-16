<?php

namespace TainacanStack\Check;

/**
 * Conferências de release (--release): versão igual em todos os lugares,
 * readme.txt completo, changelog com a versão atual, domínio de texto igual
 * ao slug, dependência do Tainacan declarada e, se o plugin agenda cron,
 * alguma chamada de limpeza (wp_clear_scheduled_hook/unschedule) no código.
 */
final class ReleaseChecks
{
    /** @return Finding[] */
    public static function run($dir)
    {
        $root = ProjectContext::findRoot($dir);
        $p = ProjectContext::forRoot($root);
        $out = [];
        if (!$p->isPlugin()) {
            if ($p->isTheme()) {
                return self::theme($root, $p);
            }
            $out[] = new Finding('TNC-R00', Finding::BLOCK, '.', 0, 'Nenhum arquivo com cabeçalho "Plugin Name:" nem "Theme Name:" na raiz de ' . $root . '.');
            return $out;
        }
        $main = Runner::relative($root, $p->mainFile);
        $mainText = (string) file_get_contents($p->mainFile);
        $versions = [];
        if (isset($p->header['Version'])) {
            $versions['cabeçalho ' . $main] = $p->header['Version'];
        }
        if (preg_match_all('/define\s*\(\s*[\'"]([A-Z0-9_]*VERSION[A-Z0-9_]*)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/', $mainText, $m, PREG_SET_ORDER)) {
            foreach ($m as $hit) {
                if (preg_match('/^\d/', $hit[2])) {
                    $versions['constante ' . $hit[1]] = $hit[2];
                }
            }
        }
        if (preg_match_all('/const\s+(VERSION|[A-Z_]*VERSION)\s*=\s*[\'"]([^\'"]+)[\'"]/', $mainText, $m, PREG_SET_ORDER)) {
            foreach ($m as $hit) {
                $versions['const ' . $hit[1]] = $hit[2];
            }
        }
        $readme = is_file($root . '/readme.txt') ? (string) file_get_contents($root . '/readme.txt') : null;
        if ($readme !== null && preg_match('/^Stable tag:\s*(\S+)/mi', $readme, $m) && strtolower($m[1]) !== 'trunk') {
            $versions['readme.txt Stable tag'] = $m[1];
        }
        foreach (['package.json', 'composer.json'] as $json) {
            if (is_file($root . '/' . $json)) {
                $data = json_decode((string) file_get_contents($root . '/' . $json), true);
                if (isset($data['version'])) {
                    $versions[$json] = $data['version'];
                }
            }
        }
        $distinct = array_unique(array_values($versions));
        if (count($distinct) > 1) {
            $list = [];
            foreach ($versions as $where => $v) {
                $list[] = $where . ' = ' . $v;
            }
            $out[] = new Finding('TNC-R01', Finding::BLOCK, $main, 1, 'Versões divergentes: ' . implode('; ', $list) . '.', 'Atualize todas no mesmo commit da release.');
        }
        $version = isset($p->header['Version']) ? $p->header['Version'] : null;
        if ($readme === null) {
            $out[] = new Finding('TNC-R02', Finding::WARN, 'readme.txt', 0, 'Plugin sem readme.txt.', 'Necessário para o WordPress.org e útil para quem instala pelo ZIP.');
        } else {
            foreach (['Requires at least', 'Tested up to', 'Requires PHP', 'License'] as $field) {
                if (!preg_match('/^' . preg_quote($field, '/') . ':/mi', $readme)) {
                    $out[] = new Finding('TNC-R02', Finding::WARN, 'readme.txt', 1, 'readme.txt sem o campo "' . $field . '".');
                }
            }
        }
        if ($version) {
            $changelog = '';
            foreach (['CHANGELOG.md', 'changelog.md', 'CHANGELOG.txt', 'changelog.txt'] as $c) {
                if (is_file($root . '/' . $c)) {
                    $changelog .= (string) file_get_contents($root . '/' . $c);
                }
            }
            if ($readme !== null && preg_match('/== Changelog ==(.*?)(\n== |\z)/s', $readme, $m)) {
                $changelog .= $m[1];
            }
            if ($changelog === '') {
                $out[] = new Finding('TNC-R03', Finding::WARN, $main, 0, 'Nenhum changelog (CHANGELOG.md ou seção == Changelog == do readme.txt).');
            } elseif (strpos($changelog, $version) === false) {
                $out[] = new Finding('TNC-R03', Finding::WARN, $main, 0, 'O changelog não menciona a versão ' . $version . '.', 'Descreva o que mudou antes de publicar.');
            }
        }
        if ($p->textDomain && !$p->isTainacanCore && $p->textDomain !== $p->slug) {
            $out[] = new Finding('TNC-R04', Finding::WARN, $main, 1, 'Text Domain "' . $p->textDomain . '" diferente do slug da pasta "' . $p->slug . '".', 'O WordPress.org exige que sejam iguais; as traduções do translate.wordpress.org usam o slug.');
        }
        if (!$p->isTainacanCore && self::usesTainacan($root)) {
            $req = isset($p->header['Requires Plugins']) ? $p->header['Requires Plugins'] : '';
            if (stripos($req, 'tainacan') === false) {
                $out[] = new Finding('TNC-R05', Finding::WARN, $main, 1, 'O plugin usa classes do Tainacan, mas o cabeçalho não declara "Requires Plugins: tainacan".', 'Desde o WordPress 6.5 o cabeçalho impede ativar sem o Tainacan e evita erro fatal.');
            }
        }
        $all = self::allPhp($root);
        if (preg_match('/wp_schedule_(single_)?event\s*\(/', $all) && !preg_match('/wp_clear_scheduled_hook|wp_unschedule_event|wp_unschedule_hook/', $all)) {
            $out[] = new Finding('TNC-R06', Finding::WARN, $main, 0, 'O plugin agenda eventos de cron, mas nunca os remove.', 'Chame wp_clear_scheduled_hook() no register_deactivation_hook.');
        }
        $js = self::allJs($root);
        if (preg_match('/\bwp\.i18n\b|@wordpress\/i18n/', $js) && strpos($all, 'wp_set_script_translations') === false) {
            $out[] = new Finding('TNC-R07', Finding::WARN, $main, 0, 'O JavaScript usa wp.i18n, mas nenhum PHP chama wp_set_script_translations().', 'Depois do wp_enqueue_script(): wp_set_script_translations( $handle, \'' . ($p->textDomain ?: 'dominio') . '\', <caminho>/languages ). Sem isso o texto do JS não é traduzido.');
        }
        $ruleset = null;
        foreach (['phpcs.xml.dist', 'phpcs.xml', '.phpcs.xml.dist', '.phpcs.xml'] as $file) {
            if (is_file($root . '/' . $file)) {
                $ruleset = $file;
                break;
            }
        }
        if ($ruleset === null) {
            $out[] = new Finding('TNC-R08', Finding::WARN, 'phpcs.xml.dist', 0, 'Plugin sem phpcs.xml.dist.', 'Copie .agents/tainacan-stack/templates/phpcs.xml.dist (WordPress-Extra, WordPress.Security, PluginCheck, PHPCompatibilityWP) e rode vendor/bin/phpcs antes do PR.');
        } else {
            $xml = (string) file_get_contents($root . '/' . $ruleset);
            $missing = [];
            foreach (['WordPress-Extra', 'WordPress.Security', 'PluginCheck'] as $ref) {
                if (stripos($xml, $ref) === false && stripos($xml, '"WordPress"') === false) {
                    $missing[] = $ref;
                }
            }
            if ($missing) {
                $out[] = new Finding('TNC-R08', Finding::WARN, $ruleset, 1, $ruleset . ' sem ' . implode(', ', $missing) . ': o PHPCS não confere escape, nonce, prepare() nem o que o Plugin Check do WordPress.org exige.', 'Acrescente <rule ref="WordPress-Extra"/>, <rule ref="WordPress.Security"/> e <rule ref="PluginCheck"/> (modelo em .agents/tainacan-stack/templates/phpcs.xml.dist).');
            }
        }
        return $out;
    }


    /**
     * Conferências de release de um TEMA.
     *
     * O que muda em relação a plugin: a versão de referência está no
     * cabeçalho do style.css, o readme.txt cobra "Stable tag" (o diretório de
     * temas usa esse campo), o Text Domain tem de ser igual ao nome da pasta
     * e o ruleset do PHPCS não precisa do padrão PluginCheck, que é do
     * diretório de plugins e cobra coisas que não existem em tema.
     *
     * @param string         $root raiz do projeto
     * @param ProjectContext $p    contexto
     * @return Finding[]
     */
    private static function theme($root, ProjectContext $p)
    {
        $out = [];
        $main = 'style.css';
        $version = isset($p->header['Version']) ? $p->header['Version'] : null;

        if ($version === null) {
            $out[] = new Finding('TNC-R00', Finding::BLOCK, $main, 1, 'O cabeçalho do style.css não declara "Version".', 'Sem Version o WordPress não oferece atualização e o cache do navegador serve o CSS antigo.');
        }

        // TNC-R01: a versão tem de ser a mesma em todo lugar.
        $versions = [];
        if ($version !== null) {
            $versions['cabeçalho style.css'] = $version;
        }

        $functions = $root . '/functions.php';
        if (is_file($functions)) {
            $text = (string) file_get_contents($functions);
            if (preg_match_all('/define\s*\(\s*[\'"]([A-Z0-9_]*VERSION[A-Z0-9_]*)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/', $text, $m, PREG_SET_ORDER)) {
                foreach ($m as $hit) {
                    if (preg_match('/^\d/', $hit[2])) {
                        $versions['constante ' . $hit[1]] = $hit[2];
                    }
                }
            }
        }

        $readme = is_file($root . '/readme.txt') ? (string) file_get_contents($root . '/readme.txt') : null;
        if ($readme !== null && preg_match('/^Stable tag:\s*(\S+)/mi', $readme, $m) && strtolower($m[1]) !== 'trunk') {
            $versions['readme.txt Stable tag'] = $m[1];
        }
        foreach (['package.json', 'composer.json'] as $json) {
            if (is_file($root . '/' . $json)) {
                $data = json_decode((string) file_get_contents($root . '/' . $json), true);
                if (isset($data['version'])) {
                    $versions[$json] = $data['version'];
                }
            }
        }

        if (count(array_unique(array_values($versions))) > 1) {
            $list = [];
            foreach ($versions as $where => $v) {
                $list[] = $where . ' = ' . $v;
            }
            $out[] = new Finding('TNC-R01', Finding::BLOCK, $main, 1, 'Versões divergentes: ' . implode('; ', $list) . '.', 'Atualize todas no mesmo commit da release.');
        }

        // TNC-R02: readme.txt do tema.
        if ($readme === null) {
            $out[] = new Finding('TNC-R02', Finding::WARN, 'readme.txt', 0, 'Tema sem readme.txt.', 'Útil para quem instala pelo ZIP e exigido pelo diretório de temas.');
        } else {
            foreach (['Requires at least', 'Tested up to', 'Requires PHP', 'Stable tag', 'License'] as $field) {
                if (!preg_match('/^' . preg_quote($field, '/') . ':/mi', $readme)) {
                    $out[] = new Finding('TNC-R02', Finding::WARN, 'readme.txt', 1, 'readme.txt sem o campo "' . $field . '".');
                }
            }
        }

        // TNC-R03: changelog menciona a versão.
        $changelog = '';
        foreach (['CHANGELOG.md', 'changelog.md', 'CHANGELOG.txt'] as $file) {
            if (is_file($root . '/' . $file)) {
                $changelog .= (string) file_get_contents($root . '/' . $file);
            }
        }
        if ($readme !== null && preg_match('/==\s*Changelog\s*==(.*)$/is', $readme, $m)) {
            $changelog .= $m[1];
        }
        if ($version !== null) {
            if (trim($changelog) === '') {
                $out[] = new Finding('TNC-R03', Finding::WARN, $main, 0, 'Nenhum changelog (CHANGELOG.md ou seção == Changelog == do readme.txt).');
            } elseif (strpos($changelog, $version) === false) {
                $out[] = new Finding('TNC-R03', Finding::WARN, $main, 0, 'O changelog não menciona a versão ' . $version . '.', 'Descreva o que mudou antes de publicar.');
            }
        }

        // TNC-R04: Text Domain igual ao nome da pasta.
        if ($p->textDomain && $p->textDomain !== $p->slug) {
            $out[] = new Finding('TNC-R04', Finding::WARN, $main, 1, 'Text Domain "' . $p->textDomain . '" diferente do nome da pasta "' . $p->slug . '".', 'O WordPress carrega as traduções de um tema pelo nome da pasta; diferentes, o tema fica sem tradução.');
        }

        // TNC-R08: ruleset do PHPCS, sem cobrar PluginCheck.
        $ruleset = null;
        foreach (['phpcs.xml.dist', 'phpcs.xml', '.phpcs.xml.dist'] as $file) {
            if (is_file($root . '/' . $file)) {
                $ruleset = $file;
                break;
            }
        }
        if ($ruleset === null) {
            $out[] = new Finding('TNC-R08', Finding::WARN, 'phpcs.xml.dist', 0, 'Tema sem phpcs.xml.dist.', 'Copie .agents/tainacan-stack/templates/phpcs-tema.xml.dist e rode vendor/bin/phpcs antes da release.');
        } else {
            $text = (string) file_get_contents($root . '/' . $ruleset);
            $missing = [];
            foreach (['WordPress-Extra', 'WordPress.Security'] as $need) {
                if (strpos($text, $need) === false) {
                    $missing[] = $need;
                }
            }
            if ($missing) {
                $out[] = new Finding('TNC-R08', Finding::WARN, $ruleset, 1, $ruleset . ' sem ' . implode(', ', $missing) . ': o PHPCS não confere escape, sanitização nem nonce.', 'Acrescente <rule ref="WordPress-Extra"/> e <rule ref="WordPress.Security"/> (modelo em .agents/tainacan-stack/templates/phpcs-tema.xml.dist).');
            }
        }

        // TNC-R09: o CSS compilado tem de estar em dia com o Sass.
        if (is_dir($root . '/assets/scss') && is_file($root . '/style.css')) {
            $newest = 0;
            foreach (glob($root . '/assets/scss/*.scss') ?: [] as $scss) {
                $newest = max($newest, (int) filemtime($scss));
            }
            if ($newest > (int) filemtime($root . '/style.css')) {
                $out[] = new Finding('TNC-R09', Finding::WARN, $main, 0, 'O Sass em assets/scss/ é mais novo que o style.css compilado.', 'Rode a compilação (npm run build) e inclua o CSS gerado no commit da release.');
            }
        }

        return $out;
    }

    private static function allJs($root)
    {
        $text = '';
        foreach (Runner::collect($root) as $e) {
            if (preg_match('/\.(js|jsx|ts|tsx|vue)$/', $e['rel']) && strpos($e['rel'], '/vendor/') === false && strpos($e['rel'], 'node_modules') === false) {
                $text .= (string) @file_get_contents($e['abs']) . "\n";
            }
        }
        return $text;
    }

    private static function usesTainacan($root)
    {
        return (bool) preg_match('/\\\\?Tainacan\\\\(Repositories|Entities|Metadata_Types|Filter_Types|Importer|Exporter)|tainacan_register_admin_hook|tainacan_get_item/', self::allPhp($root));
    }

    private static $cache = [];

    private static function allPhp($root)
    {
        if (isset(self::$cache[$root])) {
            return self::$cache[$root];
        }
        $text = '';
        foreach (Runner::collect($root) as $e) {
            if (substr($e['rel'], -4) === '.php') {
                $text .= (string) @file_get_contents($e['abs']) . "\n";
            }
        }
        return self::$cache[$root] = $text;
    }
}
