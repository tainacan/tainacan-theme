<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-C02: CSS de plugin com seletor global (elemento solto, *, html/body
 * sem classe). Vaza para o tema, para o admin do Tainacan e para os outros
 * plugins carregados na mesma página.
 *
 * Não vale para tema: num tema, estilizar `body`, `a` e `h1` é exatamente a
 * função do arquivo. A regra nasceu de plugin que pintava `a { color: ... }`
 * e trocava a cor dos links do site inteiro; aplicá-la a tema produziria um
 * achado por folha de estilo e nenhuma informação.
 */
final class CssGlobalSelector extends Rule
{
    const ID = 'TNC-C02';
    const TITLE = 'Seletor CSS global vazando para tema e outros plugins';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'convivencia';
    const EXTENSIONS = ['css', 'scss'];

    const ELEMENTS = 'a|abbr|article|aside|b|blockquote|body|button|caption|code|dd|details|div|dl|dt|em|fieldset|figcaption|figure|footer|form|h[1-6]|header|hr|html|i|iframe|img|input|label|legend|li|main|nav|ol|option|p|pre|section|select|small|span|strong|summary|svg|table|tbody|td|textarea|tfoot|th|thead|tr|ul|video|audio|\*';

    public function applies(SourceFile $f)
    {
        if (!parent::applies($f)) {
            return false;
        }
        // Em tema, seletor global é a obrigação do arquivo, não um vazamento.
        return !($f->project && $f->project->isTheme());
    }

    protected function check(SourceFile $f)
    {
        if (preg_match('#(^|/)(admin|editor|print|impressao)[\w-]*\.s?css$#i', $f->path) && $f->project && !empty($f->project->config['css_admin_isolated'])) {
            return;
        }
        $css = preg_replace_callback('#/\*.*?\*/#s', function ($m) {
            return preg_replace('/[^\n]/', ' ', $m[0]);
        }, $f->text);
        $len = strlen($css);
        $depth = 0;
        $buf = '';
        $bufStart = 0;
        $atStack = [];
        for ($i = 0; $i < $len; $i++) {
            $ch = $css[$i];
            if ($ch === '{') {
                $sel = trim($buf);
                $isAt = strpos($sel, '@') === 0;
                $atStack[] = $isAt;
                $parentIsRule = false;
                foreach ($atStack as $k => $a) {
                    if ($k < count($atStack) - 1 && !$a) {
                        $parentIsRule = true;
                    }
                }
                if (!$isAt && !$parentIsRule && $sel !== '' && !preg_match('/^(from|to|\d+%)/', $sel)) {
                    $this->checkSelector($f, $sel, $bufStart, $css);
                }
                $depth++;
                $buf = '';
                continue;
            }
            if ($ch === '}') {
                array_pop($atStack);
                $depth = max(0, $depth - 1);
                $buf = '';
                continue;
            }
            if ($ch === ';' ) {
                $buf = '';
                continue;
            }
            if ($buf === '' && trim($ch) === '') {
                continue;
            }
            if ($buf === '') {
                $bufStart = $i;
            }
            $buf .= $ch;
        }
    }

    private function checkSelector(SourceFile $f, $selectorList, $offset, $css)
    {
        $line = substr_count(substr($css, 0, $offset), "\n") + 1;
        foreach (explode(',', $selectorList) as $sel) {
            $sel = trim($sel);
            if ($sel === '' || $sel === ':root') {
                continue;
            }
            // primeiro composto do seletor
            $first = preg_split('/[\s>+~]+/', $sel)[0];
            if (preg_match('/^(html|body)$/i', $first) && strpos($sel, ' ') === false && strpos($sel, '>') === false) {
                $this->report($f, $line, 'Seletor `' . $sel . '` altera a página inteira.', 'Limite à tela do plugin com uma classe no body (ex.: body.meu-plugin) ou num contêiner próprio.');
                return;
            }
            if (preg_match('/^(html|body)$/i', $first)) {
                $first = preg_split('/[\s>+~]+/', $sel)[1];
            }
            if (preg_match('/^(' . self::ELEMENTS . ')(:[\w-]+(\([^)]*\))?)*$/i', $first)) {
                $this->report($f, $line, 'Seletor global `' . $sel . '`: atinge o tema e todos os plugins da página.', 'Prefixe com a classe raiz do plugin (ex.: .meu-plugin ' . $sel . ').');
                return;
            }
        }
    }
}
