<?php

namespace TainacanStack\Check;

/**
 * Baseline: achados já existentes, registrados para que só os novos
 * bloqueiem. Útil para adotar a stack em código legado (inclusive o core)
 * sem precisar corrigir tudo de uma vez. A impressão digital usa regra,
 * arquivo e o texto da linha, então sobrevive a mudanças de numeração.
 */
final class Baseline
{
    const FILE = '.tnc-check-baseline.json';

    /** @var array<string,int> impressão digital => quantidade */
    private $counts = [];

    public static function load($root)
    {
        $b = new self();
        $file = rtrim($root, '/') . '/' . self::FILE;
        if (is_file($file)) {
            $data = json_decode((string) file_get_contents($file), true);
            if (isset($data['findings']) && is_array($data['findings'])) {
                foreach ($data['findings'] as $item) {
                    $fp = $item['fingerprint'];
                    $b->counts[$fp] = (isset($b->counts[$fp]) ? $b->counts[$fp] : 0) + 1;
                }
            }
        }
        return $b;
    }

    /** Consome uma ocorrência; devolve true se o achado já estava no baseline. */
    public function absorbs(Finding $f)
    {
        $fp = $f->fingerprint();
        if (!empty($this->counts[$fp])) {
            $this->counts[$fp]--;
            return true;
        }
        return false;
    }

    /** @param Finding[] $findings */
    public static function write($root, array $findings)
    {
        $items = [];
        foreach ($findings as $f) {
            $items[] = ['rule' => $f->rule, 'file' => $f->file, 'line' => $f->line, 'fingerprint' => $f->fingerprint()];
        }
        usort($items, function ($a, $b) {
            return [$a['file'], $a['line'], $a['rule']] <=> [$b['file'], $b['line'], $b['rule']];
        });
        $data = [
            'generated' => gmdate('Y-m-d\TH:i:s\Z'),
            'note' => 'Achados preexistentes aceitos temporariamente. Corrija e regenere com --baseline-generate; nunca acrescente à mão.',
            'findings' => $items,
        ];
        file_put_contents(rtrim($root, '/') . '/' . self::FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        return count($items);
    }
}
