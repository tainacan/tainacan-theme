<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-B07: supressão do PHPCS (phpcs:ignore / phpcs:disable) sem
 * justificativa técnica, com justificativa vazia ("is safe", "false
 * positive"), sem código de sniff (suprime tudo), com a sintaxe antiga
 * (@codingStandardsIgnore*) ou phpcs:disable sem phpcs:enable.
 *
 * A hierarquia da equipe é refatorar > API canônica > suprimir. A supressão
 * é o último recurso e precisa responder, em uma frase, por que a API
 * alternativa não serve. O texto fica no código para quem revisa depois.
 */
final class PhpcsSuppression extends Rule
{
    const ID = 'TNC-B07';
    const TITLE = 'Supressão do PHPCS sem justificativa técnica';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'base';

    const EMPTY_REASONS = '/^(is safe|safe|its? safe|this is safe|safe (here|to ignore)|ok|ok here|fine|false positive|not an issue|trust me|verified( manually)?|needed|necessary|required|intentional|by design|legacy|performance|para passar( no ci)?|necess[aá]rio|seguro|intencional|falso positivo|n[aã]o [eé] problema)\.?$/iu';

    protected function check(SourceFile $f)
    {
        $disables = 0;
        $enables = 0;
        foreach ($f->comments() as $c) {
            $line = $c[0];
            $text = $c[1];
            // o PHPCS só reconhece a anotação no início do comentário; texto no meio de docblock é prosa
            $body = preg_replace('#^\s*(//|\#|/\*+)\s*#', '', $text);
            if (preg_match('/^@codingStandardsIgnore(Line|Start|End|File)/', $body, $m)) {
                $this->report($f, $line, 'Sintaxe antiga `@codingStandardsIgnore' . $m[1] . '` (removida no PHPCS 4).', 'Use // phpcs:ignore <Sniff.Code> -- motivo, ou phpcs:disable/enable.');
                continue;
            }
            if (!preg_match_all('/^phpcs:(ignore|disable|enable)([^\r\n]*)/i', $body, $all, PREG_SET_ORDER)) {
                continue;
            }
            foreach ($all as $m) {
                $kind = strtolower($m[1]);
                $rest = trim(preg_replace('#\s*\*/\s*$#', '', $m[2]));
                if ($kind === 'enable') {
                    $enables++;
                    continue;
                }
                if ($kind === 'disable') {
                    $disables++;
                }
                $codes = '';
                $reason = '';
                if (preg_match('/^(.*?)\s--\s*(.*)$/s', $rest, $parts)) {
                    $codes = trim($parts[1]);
                    $reason = trim(preg_replace('#\s//.*$#', '', $parts[2])); // comentário depois do motivo
                } else {
                    $codes = preg_replace('#\s//.*$#', '', $rest);
                }
                $codes = trim($codes, " \t:");
                if ($codes === '' || !preg_match('/[A-Za-z]+\.[A-Za-z]+/', $codes)) {
                    $this->report($f, $line, '`phpcs:' . $kind . '` sem código de sniff: suprime todas as regras' . ($kind === 'disable' ? ' até o phpcs:enable' : ' na linha') . '.', 'Nomeie o sniff (ex.: WordPress.DB.PreparedSQL.NotPrepared) e justifique depois de " -- ".');
                    continue;
                }
                if ($reason === '') {
                    $this->report($f, $line, '`phpcs:' . $kind . ' ' . $this->shortCodes($codes) . '` sem justificativa.', 'Depois de " -- ", diga em uma frase por que a API alternativa não serve aqui. Antes de suprimir, tente refatorar ou usar a função canônica do WordPress.');
                    continue;
                }
                if (strlen($reason) < 12 || preg_match(self::EMPTY_REASONS, $reason)) {
                    $this->report($f, $line, 'Justificativa vazia em `phpcs:' . $kind . '`: "' . $reason . '".', '"É seguro" ou "falso positivo" não explicam nada a quem revisa. Diga qual risco a regra aponta e por que ele não existe aqui (ex.: "tabela própria do plugin; entrada via %d no prepare()").');
                }
            }
        }
        if ($disables > $enables) {
            $this->report($f, $this->lastLineOf($f, 'phpcs:disable'), '`phpcs:disable` sem `phpcs:enable` correspondente: o resto do arquivo fica sem verificação.', 'Feche o bloco com // phpcs:enable <mesmos códigos> logo depois do trecho.');
        }
    }

    private function shortCodes($codes)
    {
        return strlen($codes) > 50 ? substr($codes, 0, 47) . '...' : $codes;
    }

    private function lastLineOf(SourceFile $f, $needle)
    {
        $last = 1;
        foreach ($f->comments() as $c) {
            if (stripos($c[1], $needle) !== false) {
                $last = $c[0];
            }
        }
        return $last;
    }
}
