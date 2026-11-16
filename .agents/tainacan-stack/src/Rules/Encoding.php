<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-B03: BOM, UTF-8 inválido, caractere de substituição (U+FFFD) e
 * mojibake ("Ã§", "Ã£"). Origem típica: editor ou PowerShell 5.1 regravando
 * o arquivo em outra codificação.
 */
final class Encoding extends Rule
{
    const ID = 'TNC-B03';
    const TITLE = 'Codificação corrompida (BOM, UTF-8 inválido, mojibake)';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'base';
    const EXTENSIONS = ['php', 'inc', 'js', 'jsx', 'ts', 'tsx', 'vue', 'css', 'scss', 'md', 'json', 'po', 'pot', 'txt', 'html', 'xml', 'yml', 'yaml', 'sql'];

    protected function check(SourceFile $f)
    {
        $project = $f->project;
        if ($project && isset($project->config['encoding']) && strtolower($project->config['encoding']) !== 'utf-8') {
            return;
        }
        $text = $f->text;
        if (strncmp($text, "\xEF\xBB\xBF", 3) === 0) {
            $isPhp = $f->isPhp();
            $this->report(
                $f,
                1,
                'Arquivo começa com BOM UTF-8.' . ($isPhp ? ' Em PHP isso envia saída antes dos cabeçalhos ("headers already sent").' : ''),
                'Salve como UTF-8 sem BOM.',
                null,
                $isPhp ? Finding::BLOCK : Finding::WARN
            );
        }
        if (!preg_match('//u', $text)) {
            $line = $this->firstInvalidLine($f);
            $this->report($f, $line, 'Bytes que não são UTF-8 válido (arquivo salvo em Latin-1/CP1252 ou truncado).', 'Converta para UTF-8; se o projeto usa outra codificação, declare "encoding" no .tnc-check.json.');
            return;
        }
        foreach ($f->lines as $n => $line) {
            if (strpos($line, "\xEF\xBF\xBD") !== false) {
                $this->report($f, $n, 'Caractere de substituição U+FFFD: acentos já foram perdidos numa conversão.', 'Recupere o texto original pelo git (git show HEAD:<arquivo>) e edite com ferramenta que preserve UTF-8.');
            } elseif (preg_match('/Ã[\x{0080}-\x{00BF}]|â€[™œ\x{009D}“”]/u', $line)) {
                $this->report($f, $n, 'Mojibake (UTF-8 lido como Latin-1 e regravado): "' . $this->excerpt($line) . '".', 'Desfaça a dupla codificação; no PowerShell 5.1 nunca use Get-Content/Set-Content sem -Encoding UTF8.', null, Finding::WARN);
            }
        }
    }

    private function firstInvalidLine(SourceFile $f)
    {
        foreach ($f->lines as $n => $line) {
            if (!preg_match('//u', $line)) {
                return $n;
            }
        }
        return 1;
    }

    private function excerpt($line)
    {
        if (preg_match('/.{0,15}(Ã[\x{0080}-\x{00BF}]|â€.).{0,15}/u', $line, $m)) {
            return trim($m[0]);
        }
        return mb_substr(trim($line), 0, 40);
    }
}
