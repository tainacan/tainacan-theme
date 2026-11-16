<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-B01: erro de sintaxe PHP. Usa o próprio analisador do PHP em processo
 * (token_get_all com TOKEN_PARSE), que é o mesmo do `php -l`, sem abrir um
 * processo por arquivo: uma varredura de milhares de arquivos cai de minutos
 * para segundos. Com --no-lint a regra é desligada.
 */
final class PhpLint extends Rule
{
    const ID = 'TNC-B01';
    const TITLE = 'Erro de sintaxe PHP (php -l)';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'base';

    /** @var bool desligado com --no-lint */
    public static $enabled = true;

    protected function check(SourceFile $f)
    {
        if (!self::$enabled || !$f->isPhp()) {
            return;
        }
        $level = error_reporting(0); // deprecações de compilação não interessam aqui
        try {
            token_get_all($f->text, TOKEN_PARSE);
        } catch (\ParseError $e) {
            $this->report($f, $e->getLine() ?: 1, 'Erro de sintaxe: ' . $e->getMessage() . '.', 'Corrija a sintaxe antes de qualquer outra verificação.');
        } catch (\Throwable $e) {
            $this->report($f, 1, 'O PHP não conseguiu analisar o arquivo: ' . $e->getMessage() . '.', 'Confira com php -l.');
        } finally {
            error_reporting($level);
        }
    }
}
