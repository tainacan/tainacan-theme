<?php

namespace TainacanStack\Check;

/**
 * Supressão em linha, válida na própria linha e na seguinte:
 *
 *     // tnc-check-ignore TNC-P02: laço sobre no máximo 5 coleções
 *
 * O motivo depois de ':' é obrigatório; sem ele a supressão não vale e o
 * próprio comentário vira achado TNC-B05.
 */
final class Suppressions
{
    const PATTERN = '/tnc-check-ignore\s+((?:TNC-[A-Z]\d{2}[\s,]*)+)(?::(.*))?/i';

    /** @var array<int,string[]> linha => IDs */
    private $map = [];
    /** @var Finding[] */
    public $invalid = [];

    public function __construct(SourceFile $f)
    {
        foreach ($f->lines as $n => $line) {
            if (stripos($line, 'tnc-check-ignore') === false) {
                continue;
            }
            if (!preg_match(self::PATTERN, $line, $m)) {
                continue;
            }
            preg_match_all('/TNC-[A-Z]\d{2}/i', $m[1], $ids);
            $ids = array_map('strtoupper', $ids[0]);
            $reason = isset($m[2]) ? trim(preg_replace('#\s*(\*/|-->|\?>).*$#', '', $m[2])) : '';
            if (strlen($reason) < 8) {
                $finding = new Finding('TNC-B05', Finding::WARN, $f->path, $n, 'Supressão de ' . implode(', ', $ids) . ' sem motivo: ela não foi aplicada.', 'Escreva o motivo depois de dois-pontos: // tnc-check-ignore ' . $ids[0] . ': por que este caso é seguro.');
                $finding->snippet = $line;
                $this->invalid[] = $finding;
                continue;
            }
            foreach ([$n, $n + 1] as $target) {
                if (!isset($this->map[$target])) {
                    $this->map[$target] = [];
                }
                $this->map[$target] = array_merge($this->map[$target], $ids);
            }
        }
    }

    public function covers(Finding $finding)
    {
        for ($l = $finding->line; $l <= max($finding->line, min($finding->endLine, $finding->line + 1)); $l++) {
            if (isset($this->map[$l]) && in_array($finding->rule, $this->map[$l], true)) {
                return true;
            }
        }
        return false;
    }
}
