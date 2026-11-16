<?php

namespace TainacanStack\Check;

/**
 * Dados do projeto (plugin, tema ou o próprio core) a que um arquivo
 * pertence: raiz, configuração .tnc-check.json, cabeçalho e domínio de texto.
 */
final class ProjectContext
{
    public $root;
    public $slug;
    public $config = [];
    public $mainFile;
    /** @var string|null style.css do tema, quando o projeto e um tema */
    public $themeFile;
    public $header = [];
    public $textDomain;
    public $isTainacanCore = false;

    const DEFAULT_EXCLUDES = [
        '.git/', 'vendor/', 'node_modules/', 'bower_components/', 'build/', 'dist/',
        '.agents/tainacan-stack/', '.claude/', '.codex/', '.github/skills/',
        'tests/fixtures/', 'languages/', 'assets/vendor/', 'lib/vendor/',
    ];

    /** @var array<string,ProjectContext> */
    private static $cache = [];

    public static function forRoot($root)
    {
        $key = rtrim(str_replace('\\', '/', $root), '/');
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = new self($key);
        }
        return self::$cache[$key];
    }

    public function __construct($root)
    {
        $this->root = rtrim(str_replace('\\', '/', $root), '/');
        $this->slug = basename($this->root);
        $cfg = $this->root . '/.tnc-check.json';
        if (is_file($cfg)) {
            $data = json_decode((string) file_get_contents($cfg), true);
            if (is_array($data)) {
                $this->config = $data;
            }
        }
        $this->detectHeader();
        if (!empty($this->config['text_domain'])) {
            $this->textDomain = $this->config['text_domain'];
        }
    }

    private function detectHeader()
    {
        // o plugin pode estar numa subpasta declarada em "plugin_dir" (o núcleo fica em src/)
        $candidates = glob($this->root . '/*.php') ?: [];
        if (!empty($this->config['plugin_dir'])) {
            $candidates = array_merge($candidates, glob($this->root . '/' . trim($this->config['plugin_dir'], '/') . '/*.php') ?: []);
        }
        foreach ($candidates as $file) {
            $head = (string) @file_get_contents($file, false, null, 0, 8192);
            if (!preg_match('/^[ \t\/*#@]*Plugin Name\s*:/mi', $head)) {
                continue;
            }
            $this->mainFile = str_replace('\\', '/', $file);
            foreach (['Plugin Name', 'Version', 'Text Domain', 'Requires PHP', 'Requires at least', 'Requires Plugins', 'Domain Path'] as $key) {
                if (preg_match('/^[ \t\/*#@]*' . preg_quote($key, '/') . '\s*:\s*(.+)$/mi', $head, $m)) {
                    $this->header[$key] = trim($m[1]);
                }
            }
            break;
        }
        // Nao e plugin: pode ser tema, e o cabecalho fica no style.css.
        if ($this->mainFile === null) {
            $style = $this->root . '/style.css';
            if (is_file($style)) {
                $head = (string) @file_get_contents($style, false, null, 0, 8192);
                if (preg_match('/^[ \t\/*#@]*Theme Name\s*:/mi', $head)) {
                    $this->themeFile = str_replace('\\', '/', $style);
                    foreach (['Theme Name', 'Version', 'Text Domain', 'Requires PHP', 'Requires at least', 'Tested up to', 'License', 'Template'] as $key) {
                        if (preg_match('/^[ \t\/*#@]*' . preg_quote($key, '/') . '\s*:\s*(.+)$/mi', $head, $m)) {
                            $this->header[$key] = trim($m[1]);
                        }
                    }
                }
            }
        }

        if (isset($this->header['Text Domain'])) {
            $this->textDomain = $this->header['Text Domain'];
        }
        $this->isTainacanCore = $this->textDomain === 'tainacan'
            || ($this->mainFile !== null && basename($this->mainFile) === 'tainacan.php');
    }

    public function isPlugin()
    {
        return $this->mainFile !== null;
    }

    /**
     * O projeto é código WordPress (plugin, tema ou projeto que se declara
     * assim)? As regras que cobram API do WordPress (escape na saída,
     * wp_json_encode, wp_unslash, CDN, translators) só rodam quando sim; o
     * verificador também analisa ferramentas PHP comuns, como ele mesmo.
     */
    public function isWordPress()
    {
        if ($this->isPlugin() || !empty($this->config['text_domain']) || !empty($this->config['wordpress'])) {
            return true;
        }
        if (is_file($this->root . '/style.css')) {
            $head = (string) @file_get_contents($this->root . '/style.css', false, null, 0, 4096);
            return (bool) preg_match('/^[ \t\/*#@]*Theme Name\s*:/mi', $head);
        }
        return false;
    }

    /**
     * O projeto é um tema?
     *
     * Reconhece pelo que a pessoa declarou (`"tipo": "tema"` no
     * .tnc-check.json) ou pelo cabeçalho "Theme Name:" do style.css na raiz.
     *
     * A distinção importa porque algumas regras cobram de plugin o que em
     * tema é a obrigação: seletor global de CSS (TNC-C02) é vazamento num
     * plugin e é o trabalho de um tema.
     */
    public function isTheme()
    {
        if (isset($this->config['tipo']) && strtolower((string) $this->config['tipo']) === 'tema') {
            return true;
        }
        if (isset($this->config['type']) && strtolower((string) $this->config['type']) === 'theme') {
            return true;
        }
        return $this->themeFile !== null;
    }

    /**
     * Funções do projeto cujo retorno já sai escapado (TNC-S05): nomes exatos
     * ou prefixos com `*` em "escaped_functions" do .tnc-check.json.
     */
    public function isEscapedFunction($name)
    {
        $list = isset($this->config['escaped_functions']) && is_array($this->config['escaped_functions']) ? $this->config['escaped_functions'] : [];
        $name = strtolower($name);
        foreach ($list as $entry) {
            $entry = strtolower((string) $entry);
            if ($entry === $name || (substr($entry, -1) === '*' && strpos($name, substr($entry, 0, -1)) === 0)) {
                return true;
            }
        }
        return false;
    }

    /** @return string[] */
    public function excludes()
    {
        $extra = isset($this->config['exclude']) && is_array($this->config['exclude']) ? $this->config['exclude'] : [];
        return array_merge(self::DEFAULT_EXCLUDES, $extra);
    }

    public function isExcluded($relPath)
    {
        $rel = ltrim(str_replace('\\', '/', $relPath), '/');
        if (preg_match('/\.min\.(js|css)$/', $rel)) {
            return true;
        }
        foreach ($this->excludes() as $ex) {
            $ex = ltrim($ex, '/');
            if ($ex === '') {
                continue;
            }
            if (substr($ex, -1) === '/') {
                if (strpos($rel, $ex) === 0 || strpos($rel, '/' . $ex) !== false) {
                    return true;
                }
            } elseif (fnmatch($ex, $rel)) {
                return true;
            }
        }
        return false;
    }

    /** Severidade configurada para a regra: 'off', 'warn', 'block' ou null. */
    public function ruleSetting($id)
    {
        return isset($this->config['rules'][$id]) ? $this->config['rules'][$id] : null;
    }

    /**
     * Sobe a partir de um caminho até a raiz do projeto.
     *
     * 1. O primeiro diretório com cabeçalho de plugin ou com .tnc-check.json
     *    (o que vier antes) é o candidato.
     * 2. Um .tnc-check.json acima dele só ganha se declarar "plugin_dir"
     *    apontando para o candidato: é o caso do núcleo, com o plugin em src/
     *    e a configuração na raiz do repositório. Sem isso, um plugin dentro
     *    de um monorepo (wp-content versionado, fixtures) continua sendo o
     *    próprio projeto.
     * 3. Sem cabeçalho nem configuração: o primeiro .git, senão o diretório.
     */
    public static function findRoot($path)
    {
        $dir = is_dir($path) ? $path : dirname($path);
        $real = realpath($dir);
        $start = str_replace('\\', '/', $real ?: $dir);
        $cur = $start;
        $candidate = null;
        $git = null;
        while (true) {
            $hasConfig = is_file($cur . '/.tnc-check.json');
            if ($candidate === null && ($hasConfig || self::hasPluginHeader($cur))) {
                $candidate = $cur;
            } elseif ($candidate !== null && $hasConfig) {
                $cfg = json_decode((string) file_get_contents($cur . '/.tnc-check.json'), true);
                if (is_array($cfg) && !empty($cfg['plugin_dir'])) {
                    $declared = rtrim($cur . '/' . trim(str_replace('\\', '/', $cfg['plugin_dir']), '/'), '/');
                    if (stripos($candidate . '/', $declared . '/') === 0) {
                        return $cur;
                    }
                }
            }
            if ($git === null && file_exists($cur . '/.git')) {
                $git = $cur;
            }
            $parent = dirname($cur);
            if ($parent === $cur || $parent === '.' || $parent === '') {
                break;
            }
            $cur = $parent;
        }
        return $candidate ?: ($git ?: $start);
    }

    private static function hasPluginHeader($dir)
    {
        foreach (glob($dir . '/*.php') ?: [] as $file) {
            $head = (string) @file_get_contents($file, false, null, 0, 4096);
            if (preg_match('/^[ \t\/*#@]*Plugin Name\s*:/mi', $head)) {
                return true;
            }
        }
        return false;
    }
}
