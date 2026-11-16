<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W09: JavaScript inseguro: eval/new Function, document.write e
 * innerHTML/outerHTML/insertAdjacentHTML com valor variável (XSS).
 * Em Vue, v-html com dado que não seja HTML já sanitizado no servidor.
 */
final class JsUnsafeDom extends Rule
{
    const ID = 'TNC-W09';
    const TITLE = 'JavaScript inseguro (eval, innerHTML variável, v-html)';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'seguranca';
    const EXTENSIONS = ['js', 'jsx', 'ts', 'tsx', 'vue'];

    /**
     * Fontes de HTML que o Tainacan já entrega sanitizado no servidor
     * (wp_kses) ou que são do próprio código: ganchos de formulário e da busca
     * facetada, $i18n, renderMetadata, campos *_as_html, ícones e formulários
     * de opções gerados em PHP.
     */
    const TRUSTED_HTML = '/(sanitiz|safe|purif|kses|_as_html|AsHtml|\$i18n|i18n\.|Hook|hooks\[|Form\b|_form\b|\.icon\b|renderMetadata|render\w*Label)/i';

    protected function check(SourceFile $f)
    {
        $inBlock = false;
        foreach ($f->lines as $n => $line) {
            if (strlen($line) > 1000) {
                continue;
            }
            $code = $this->strip($line, $inBlock);
            if (preg_match('/(^|[^\w.$])eval\s*\(|\bnew\s+Function\s*\(/', $code)) {
                $this->report($f, $n, '`eval`/`new Function` executa texto como código.', null, null, Finding::BLOCK);
            }
            if (preg_match('/\bdocument\.write(ln)?\s*\(/', $code)) {
                $this->report($f, $n, '`document.write` apaga ou corrompe a página quando roda depois do carregamento.', 'Crie elementos com createElement/append.');
            }
            if (preg_match('/\.(innerHTML|outerHTML)\s*\+?=\s*(.+)$/', $code, $m) && !$this->isStaticValue($m[2])) {
                $this->report($f, $n, '`' . $m[1] . '` recebe valor variável (XSS se vier do usuário ou da API).', 'Use textContent, ou monte com createElement; para HTML do servidor, sanitize lá (wp_kses).');
            }
            if (preg_match('/\.insertAdjacentHTML\s*\(\s*[\'"][^\'"]+[\'"]\s*,\s*(.+)$/', $code, $m) && !$this->isStaticValue($m[1])) {
                $this->report($f, $n, '`insertAdjacentHTML` com valor variável (XSS se vier do usuário ou da API).', 'Use insertAdjacentText ou createElement.');
            }
            if ($f->ext === 'vue' && preg_match('/\bv-html\s*=\s*"([^"]+)"/', $code, $m) && !preg_match(self::TRUSTED_HTML, $m[1])) {
                $this->report($f, $n, '`v-html="' . $m[1] . '"` insere HTML sem escape; scripts dentro dele não executam e dado do usuário vira XSS.', 'Use {{ }} para texto; para HTML, garanta wp_kses no servidor e indique no nome (ex.: descricaoSanitizada).');
            }
        }
    }

    private function isStaticValue($expr)
    {
        $e = trim(preg_replace('/\)?\s*;?\s*$/', '', trim($expr)));
        return $e === '' || $e === "''" || $e === '""' || $e === '``'
            || (bool) preg_match('/^([\'"])[^\'"$]*\1$/', $e)
            || (bool) preg_match('/^`[^`$]*`$/', $e);
    }

    private function strip($line, &$inBlock)
    {
        $out = '';
        $i = 0;
        $len = strlen($line);
        while ($i < $len) {
            if ($inBlock) {
                $end = strpos($line, '*/', $i);
                if ($end === false) {
                    return $out;
                }
                $inBlock = false;
                $i = $end + 2;
                continue;
            }
            $two = substr($line, $i, 2);
            if ($two === '/*') {
                $inBlock = true;
                $i += 2;
                continue;
            }
            if ($two === '//' && ($i === 0 || $line[$i - 1] !== ':')) {
                return $out;
            }
            $out .= $line[$i];
            $i++;
        }
        return $out;
    }
}
