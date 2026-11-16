<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-B08: get_template_part() aponta para um arquivo que não existe.
 *
 * get_template_part() não devolve erro: quando o arquivo não existe, ela
 * simplesmente não imprime nada. A seção desaparece da página e o site
 * continua respondendo 200, de modo que nem o log nem o teste de fumaça
 * acusam. Em tema derivado isso acontece sempre que uma parte é renomeada ou
 * removida e sobra quem a chamava — é como uma refatoração apaga a ficha de
 * metadados de um item sem que nada reclame.
 *
 * A regra só acusa caminho literal, sem variável e sem concatenação, e só
 * quando o arquivo não existe nem com o sufixo de $name (segundo argumento)
 * nem sem ele. Caminho montado em tempo de execução fica de fora.
 */
final class TemplatePartMissing extends Rule
{
    const ID = 'TNC-B08';
    const TITLE = 'get_template_part() para arquivo que não existe';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'base';
    const EXTENSIONS = ['php'];

    const CALLS = ['get_template_part', 'locate_template', 'get_search_form'];

    public function applies(SourceFile $f)
    {
        return parent::applies($f) && $f->project && $f->project->isWordPress();
    }

    protected function check(SourceFile $f)
    {
        $root = $f->project ? $f->project->root : null;
        if (!$root || !is_dir($root)) {
            return;
        }

        foreach ($f->calls(['get_template_part']) as $c) {
            if (empty($c['args'][0])) {
                continue;
            }

            $slug = $f->literal($c['args'][0][0], $c['args'][0][1]);
            if ($slug === null || $slug === '') {
                continue; // Caminho montado em tempo de execução: não é caso da regra.
            }

            $candidates = [];

            // Com $name: slug-name.php tem precedência, mas slug.php também serve.
            if (isset($c['args'][1])) {
                $name = $f->literal($c['args'][1][0], $c['args'][1][1]);
                if ($name !== null && $name !== '') {
                    $candidates[] = $slug . '-' . $name . '.php';
                }
            }
            $candidates[] = $slug . '.php';

            foreach ($candidates as $relative) {
                if (is_file(rtrim($root, '/\\') . '/' . ltrim($relative, '/'))) {
                    continue 2;
                }
            }

            $this->report(
                $f,
                $c['line'],
                'get_template_part( \'' . $slug . '\' ) aponta para `' . $candidates[0] . '`, que não existe no projeto.',
                'get_template_part() não avisa quando o arquivo falta: a seção só desaparece da página. Crie o arquivo, corrija o caminho ou tire a chamada.'
            );
        }
    }
}
