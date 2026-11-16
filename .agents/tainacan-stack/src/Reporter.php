<?php

namespace TainacanStack\Check;

final class Reporter
{
    /**
     * @param Finding[] $findings
     * @param array<string,mixed> $meta
     */
    public static function render(array $findings, $format, array $meta = [])
    {
        usort($findings, function (Finding $a, Finding $b) {
            return [$a->file, $a->line, $a->rule] <=> [$b->file, $b->line, $b->rule];
        });
        switch ($format) {
            case 'json':
                return self::json($findings, $meta);
            case 'md':
            case 'markdown':
                return self::markdown($findings, $meta);
            case 'github':
                return self::github($findings);
            case 'sarif':
                return self::sarif($findings, $meta);
            default:
                return self::text($findings, $meta);
        }
    }

    public static function verdict(array $findings)
    {
        $v = 'PASS';
        foreach ($findings as $f) {
            if ($f->severity === Finding::BLOCK) {
                return 'BLOCK';
            }
            $v = 'WARN';
        }
        return $v;
    }

    private static function counts(array $findings)
    {
        $c = ['block' => 0, 'warn' => 0];
        foreach ($findings as $f) {
            $c[$f->severity]++;
        }
        return $c;
    }

    private static function text(array $findings, array $meta)
    {
        $out = '';
        $file = null;
        foreach ($findings as $f) {
            if ($f->file !== $file) {
                $file = $f->file;
                $out .= "\n" . $file . "\n";
            }
            $tag = $f->severity === Finding::BLOCK ? 'BLOQUEIO' : 'aviso   ';
            $out .= sprintf("  %5s  %s  %s  %s\n", $f->line ?: '-', $tag, $f->rule, $f->message);
            if ($f->hint) {
                $out .= '                          → ' . $f->hint . "\n";
            }
        }
        $c = self::counts($findings);
        $files = isset($meta['files']) ? $meta['files'] : 0;
        $out .= sprintf(
            "\n%s: %d bloqueio(s), %d aviso(s) em %d arquivo(s) analisado(s)%s.\n",
            self::verdict($findings),
            $c['block'],
            $c['warn'],
            $files,
            !empty($meta['mode']) ? ' [' . $meta['mode'] . ']' : ''
        );
        if ($findings) {
            $out .= "Detalhe de cada regra: tnc-check --explain <ID>. Supressão justificada: // tnc-check-ignore <ID>: motivo\n";
        }
        return ltrim($out, "\n");
    }

    private static function json(array $findings, array $meta)
    {
        $c = self::counts($findings);
        $data = [
            'tool' => 'tnc-check',
            'version' => isset($meta['version']) ? $meta['version'] : null,
            'verdict' => self::verdict($findings),
            'blocks' => $c['block'],
            'warnings' => $c['warn'],
            'files' => isset($meta['files']) ? $meta['files'] : 0,
            'mode' => isset($meta['mode']) ? $meta['mode'] : null,
            'findings' => array_map(function (Finding $f) {
                return $f->toArray();
            }, $findings),
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    private static function markdown(array $findings, array $meta)
    {
        $c = self::counts($findings);
        $out = '**tnc-check: ' . self::verdict($findings) . '** · ' . $c['block'] . ' bloqueio(s), ' . $c['warn'] . ' aviso(s)'
            . (isset($meta['files']) ? ' em ' . $meta['files'] . ' arquivo(s)' : '') . "\n\n";
        if (!$findings) {
            return $out;
        }
        $out .= "| Arquivo | Linha | Regra | Severidade | Achado |\n|---|---|---|---|---|\n";
        foreach ($findings as $f) {
            $msg = str_replace('|', '\\|', $f->message . ($f->hint ? ' ' . $f->hint : ''));
            $out .= '| `' . $f->file . '` | ' . ($f->line ?: '-') . ' | ' . $f->rule . ' | ' . ($f->severity === Finding::BLOCK ? 'bloqueio' : 'aviso') . ' | ' . $msg . " |\n";
        }
        return $out;
    }

    /**
     * SARIF 2.1.0: o GitHub (aba Security > Code scanning, via
     * github/codeql-action/upload-sarif) e as IDEs mostram os achados na linha.
     */
    private static function sarif(array $findings, array $meta)
    {
        $rules = [];
        $results = [];
        $titles = [];
        foreach (Registry::describe() as $r) {
            $titles[$r['id']] = $r;
        }
        foreach ($findings as $f) {
            if (!isset($rules[$f->rule])) {
                $rules[$f->rule] = [
                    'id' => $f->rule,
                    'name' => isset($titles[$f->rule]) ? preg_replace('/\W+/', '', ucwords($titles[$f->rule]['title'])) : $f->rule,
                    'shortDescription' => ['text' => isset($titles[$f->rule]) ? $titles[$f->rule]['title'] : $f->rule],
                    'helpUri' => 'https://github.com/marcossigismundo/tainacan-stack-ia/blob/main/stack/tainacan-stack/licoes/' . $f->rule . '.md',
                    'properties' => ['category' => isset($titles[$f->rule]) ? $titles[$f->rule]['category'] : ''],
                ];
            }
            $results[] = [
                'ruleId' => $f->rule,
                'level' => $f->severity === Finding::BLOCK ? 'error' : 'warning',
                'message' => ['text' => $f->message . ($f->hint ? ' ' . $f->hint : '')],
                'locations' => [[
                    'physicalLocation' => [
                        'artifactLocation' => ['uri' => $f->file, 'uriBaseId' => '%SRCROOT%'],
                        'region' => ['startLine' => max(1, $f->line), 'endLine' => max(1, $f->endLine)],
                    ],
                ]],
                'partialFingerprints' => ['tncCheck/v1' => $f->fingerprint()],
            ];
        }
        $doc = [
            '$schema' => 'https://raw.githubusercontent.com/oasis-tcs/sarif-spec/main/sarif-2.1/schema/sarif-schema-2.1.0.json',
            'version' => '2.1.0',
            'runs' => [[
                'tool' => ['driver' => [
                    'name' => 'tnc-check',
                    'version' => isset($meta['version']) ? (string) $meta['version'] : 'dev',
                    'informationUri' => 'https://github.com/marcossigismundo/tainacan-stack-ia',
                    'rules' => array_values($rules),
                ]],
                'results' => $results,
            ]],
        ];
        return json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    private static function github(array $findings)
    {
        $out = '';
        foreach ($findings as $f) {
            $level = $f->severity === Finding::BLOCK ? 'error' : 'warning';
            $msg = str_replace(["%", "\r", "\n"], ['%25', '%0D', '%0A'], $f->message . ($f->hint ? ' — ' . $f->hint : ''));
            $out .= '::' . $level . ' file=' . $f->file . ',line=' . max(1, $f->line) . ',title=' . $f->rule . '::' . $msg . "\n";
        }
        return $out;
    }
}
