<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W14: script, estilo ou fonte carregado de servidor externo (CDN).
 *
 * O WordPress.org recusa plugins que carregam código de terceiros em tempo
 * de execução (PluginCheck.CodeAnalysis.Offloading): o site passa a depender
 * de um servidor que o administrador não controla, o visitante tem o IP
 * enviado a terceiros (LGPD) e um CDN comprometido executa código no admin.
 * A biblioteca vai para assets/vendor/ do plugin, com a versão anotada.
 */
final class ExternalAssets extends Rule
{
    const ID = 'TNC-W14';
    const TITLE = 'Asset carregado de servidor externo (CDN)';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'wordpress';
    const EXTENSIONS = ['php', 'js', 'jsx', 'ts', 'tsx', 'vue', 'css', 'scss'];

    const ENQUEUE = ['wp_enqueue_script', 'wp_enqueue_style', 'wp_register_script', 'wp_register_style', 'wp_enqueue_script_module', 'wp_register_script_module'];

    const URL = '#^(https?:)?//[a-z0-9.-]+\.[a-z]{2,}#i';

    public function applies(SourceFile $f)
    {
        return parent::applies($f) && $f->project && $f->project->isWordPress();
    }

    protected function check(SourceFile $f)
    {
        if ($f->isPhp()) {
            $this->php($f);
            return;
        }
        if ($f->ext === 'css' || $f->ext === 'scss') {
            $this->css($f);
            return;
        }
        $this->js($f);
    }

    private function php(SourceFile $f)
    {
        foreach ($f->calls(self::ENQUEUE) as $c) {
            if (!isset($c['args'][1])) {
                continue;
            }
            $src = trim($f->stringContent($c['args'][1][0], $c['args'][1][1]));
            if (preg_match(self::URL, $src)) {
                $this->report($f, $c['line'], '`' . $c['name'] . '()` carrega `' . $this->host($src) . '` de servidor externo.', 'Copie a biblioteca para assets/vendor/<lib>/ com a versão anotada e enfileire com plugins_url(). O WordPress.org recusa carregamento externo em tempo de execução.');
            }
        }
        // <script src="https://..."> ou <link href="https://..."> em HTML inline ou em string
        foreach ($f->lines as $n => $line) {
            if (preg_match('/<(script|link)\b[^>]*\b(src|href)\s*=\s*["\']?((https?:)?\/\/[^"\'\s>]+)/i', $line, $m) && $this->isAsset($m[1], $line)) {
                $this->report($f, $n, 'Tag `<' . $m[1] . '>` apontando para `' . $this->host($m[3]) . '`.', 'Sirva o arquivo de assets/vendor/ e use wp_enqueue_script()/wp_enqueue_style().');
            }
        }
    }

    private function isAsset($tag, $line)
    {
        if (strtolower($tag) === 'script') {
            return true;
        }
        return (bool) preg_match('/rel\s*=\s*["\']?(stylesheet|preload|modulepreload)/i', $line) || preg_match('/\.css(\?|["\'])/i', $line) === 1;
    }

    private function js(SourceFile $f)
    {
        foreach ($f->lines as $n => $line) {
            if (preg_match('/^\s*\/\//', $line)) {
                continue;
            }
            $url = null;
            if (preg_match('/\bimport\b[^;]*\bfrom\s*["\']((https?:)?\/\/[^"\']+)["\']/i', $line, $m)) {
                $url = $m[1];
            } elseif (preg_match('/\bimport\s*\(\s*["\']((https?:)?\/\/[^"\']+)["\']/i', $line, $m)) {
                $url = $m[1];
            } elseif (preg_match('/\.src\s*=\s*["\'`]((https?:)?\/\/[^"\'`]+)/i', $line, $m)) {
                $url = $m[1];
            } elseif (preg_match('/<(script|link)\b[^>]*\b(src|href)\s*=\s*["\']((https?:)?\/\/[^"\'\s>]+)/i', $line, $m)) {
                $url = $m[3];
            }
            if ($url !== null && preg_match(self::URL, $url)) {
                $this->report($f, $n, 'Script ou estilo carregado de `' . $this->host($url) . '` em tempo de execução.', 'Inclua a biblioteca no bundle ou em assets/vendor/.');
            }
        }
    }

    private function css(SourceFile $f)
    {
        foreach ($f->lines as $n => $line) {
            if (preg_match('/@import\s+(url\()?\s*["\']?((https?:)?\/\/[^"\')\s]+)/i', $line, $m)) {
                $this->report($f, $n, '`@import` de `' . $this->host($m[2]) . '`.', 'Copie a folha de estilo (e as fontes) para o plugin.');
            } elseif (preg_match('/url\(\s*["\']?((https?:)?\/\/[^"\')\s]+\.(woff2?|ttf|otf|eot|css|js)(\?[^"\')\s]*)?)/i', $line, $m)) {
                $this->report($f, $n, 'Fonte ou estilo carregado de `' . $this->host($m[1]) . '`.', 'Fontes externas enviam o IP do visitante a terceiros; empacote os arquivos em assets/vendor/.');
            }
        }
    }

    private function host($url)
    {
        return preg_match('#^(?:https?:)?//([^/\'"\s]+)#i', $url, $m) ? $m[1] : $url;
    }
}
