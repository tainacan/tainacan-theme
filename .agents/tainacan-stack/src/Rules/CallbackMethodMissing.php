<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-B09: callback `array( __CLASS__, 'metodo' )` para um método que a classe
 * não tem.
 *
 * O PHP só reclama quando o callback é chamado, e aí é erro fatal. Numa rota
 * REST o estrago é pior do que parece: o `validate_callback` roda dentro de
 * `has_valid_params()`, antes do callback da rota, então **todo** pedido
 * àquela rota devolve 500 — e o teste que chama a função do callback
 * diretamente, de linha de comando, passa, porque não atravessa a validação
 * de `args`. Foi assim que uma rota de tema ficou quebrada em produção com
 * todos os testes verdes.
 *
 * A regra lê os métodos declarados no arquivo e acusa quem é referenciado por
 * `__CLASS__`, `self::class` ou `static::class` e não existe. Arquivo cuja
 * classe estende outra fica de fora: o método pode vir da mãe.
 */
final class CallbackMethodMissing extends Rule
{
    const ID = 'TNC-B09';
    const TITLE = 'Callback aponta para método que a classe não tem';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'base';
    const EXTENSIONS = ['php'];

    protected function check(SourceFile $f)
    {
        $declarados = [];
        $referencias = [];
        $herda = false;

        foreach ($f->lines as $n => $line) {
            if (strlen($line) > 1000) {
                continue;
            }

            if (preg_match('/\bclass\s+\w+\s+extends\s+/i', $line)) {
                $herda = true;
            }

            if (preg_match('/\bfunction\s+&?(\w+)\s*\(/', $line, $m)) {
                $declarados[strtolower($m[1])] = true;
            }

            if (preg_match_all(
                '/array\s*\(\s*(?:__CLASS__|self::class|static::class)\s*,\s*[\'"](\w+)[\'"]\s*\)/',
                $line,
                $todos,
                PREG_SET_ORDER
            )) {
                foreach ($todos as $hit) {
                    $referencias[] = [$n, $hit[1]];
                }
            }

            if (preg_match_all(
                '/\[\s*(?:__CLASS__|self::class|static::class)\s*,\s*[\'"](\w+)[\'"]\s*\]/',
                $line,
                $todos,
                PREG_SET_ORDER
            )) {
                foreach ($todos as $hit) {
                    $referencias[] = [$n, $hit[1]];
                }
            }
        }

        if ($herda || !$referencias) {
            return;
        }

        foreach ($referencias as $ref) {
            if (isset($declarados[strtolower($ref[1])])) {
                continue;
            }

            $this->report(
                $f,
                $ref[0],
                'O callback aponta para `' . $ref[1] . '()`, que esta classe não declara.',
                'O PHP só falha quando o callback é chamado — numa rota REST, num validate_callback, isso é 500 em todo pedido. Declare o método ou corrija o nome.'
            );
        }
    }
}
