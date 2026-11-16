<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W03: chamada de tradução sem domínio de texto, com domínio diferente
 * do declarado no cabeçalho do plugin, ou com texto variável (que as
 * ferramentas de extração não enxergam).
 */
final class TextDomain extends Rule
{
    const ID = 'TNC-W03';
    const TITLE = 'Tradução sem domínio de texto ou com domínio errado';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'wordpress';

    /** função => posição (base 0) do argumento de domínio */
    const DOMAIN_ARG = [
        '__' => 1, '_e' => 1, 'esc_html__' => 1, 'esc_html_e' => 1, 'esc_attr__' => 1, 'esc_attr_e' => 1,
        '_x' => 2, '_ex' => 2, 'esc_html_x' => 2, 'esc_attr_x' => 2,
        '_n' => 3, '_nx' => 4, '_n_noop' => 2, '_nx_noop' => 3,
    ];

    protected function check(SourceFile $f)
    {
        $expected = $f->project ? $f->project->textDomain : null;
        $wrong = []; // domínio => [primeira linha, quantidade]
        foreach ($f->calls(array_keys(self::DOMAIN_ARG)) as $c) {
            $pos = self::DOMAIN_ARG[$c['name']];
            $args = $c['args'];
            if (!$args) {
                continue;
            }
            if ($f->type($args[0][0]) !== T_CONSTANT_ENCAPSED_STRING || $args[0][0] !== $args[0][1]) {
                // texto variável não é extraído para o .pot
                if ($f->type($args[0][0]) === T_VARIABLE) {
                    $this->report($f, $c['line'], '`' . $c['name'] . '()` com texto variável: o texto não entra no arquivo de tradução.', 'Passe o literal; para frases montadas use sprintf( __( \'... %s ...\', \'dominio\' ), $valor ).');
                }
                continue;
            }
            if (!isset($args[$pos])) {
                $this->report($f, $c['line'], '`' . $c['name'] . '()` sem domínio de texto' . ($expected ? ' (esperado \'' . $expected . '\')' : '') . '.', 'Acrescente o domínio do plugin como último argumento.');
                continue;
            }
            $domain = $f->literal($args[$pos][0], $args[$pos][1]);
            if ($domain === null) {
                continue; // constante ou variável: aceito
            }
            if ($expected && $domain !== $expected && !($domain === 'tainacan' && $this->reusesCoreString($f))) {
                if (!isset($wrong[$domain])) {
                    $wrong[$domain] = [$c['line'], 0];
                }
                $wrong[$domain][1]++;
            }
        }
        // domínio divergente: um achado por arquivo, com a contagem
        foreach ($wrong as $domain => $info) {
            $this->report($f, $info[0], 'Domínio \'' . $domain . '\' diferente do declarado no plugin (\'' . $expected . '\')' . ($info[1] > 1 ? ', em ' . $info[1] . ' chamadas neste arquivo' : '') . '.', $domain === 'tainacan' ? 'Reaproveitar o domínio do core só funciona se a frase existir lá e pode mudar sem aviso; declare "allow_tainacan_domain": true no .tnc-check.json se for intencional.' : 'Use o domínio do cabeçalho "Text Domain" (ou corrija o cabeçalho, se o domínio do código é o certo).');
        }
    }

    private function reusesCoreString(SourceFile $f)
    {
        return $f->project && !empty($f->project->config['allow_tainacan_domain']);
    }
}
