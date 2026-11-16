<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-T05: uso errado dos repositórios do Tainacan.
 *  - new \Tainacan\Repositories\X(): construtor privado (singleton); use get_instance().
 *  - insert()/update() de entidade sem validate() antes: entidade publicada
 *    lança Exception('Entities must be validated before you can save them')
 *    (classes/repositories/class-tainacan-repository.php:164-166) e
 *    Item_Metadata_Entity exige validação sempre.
 *  - catch vazio em volta de insert()/update() de repositório: engole essa
 *    exceção e a importação diz "sucesso" com campos vazios.
 */
final class TainacanRepositoryUsage extends Rule
{
    const ID = 'TNC-T05';
    const TITLE = 'Repositório do Tainacan usado sem singleton ou sem validate()';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'tainacan';

    const REPO_GETTERS = ['tainacan_items', 'tainacan_collections', 'tainacan_metadata', 'tainacan_item_metadata', 'tainacan_taxonomies', 'tainacan_terms', 'tainacan_filters', 'tainacan_metadata_sections'];

    protected function check(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) === T_NEW && $f->type($i + 1) === 'NAME' && $this->isTainacanRepoClass($f, $f->val($i + 1))) {
                $this->report($f, $f->line($i), '`new ' . ltrim($f->val($i + 1), '\\') . '()`: os repositórios do Tainacan são singletons com construtor privado.', 'Use ' . ltrim($f->val($i + 1), '\\') . '::get_instance() ou a função tainacan_items(), tainacan_metadata() etc.');
            }
        }
        $this->swallowedExceptions($f);
        foreach ($f->methodCalls(['insert', 'update']) as $c) {
            if (count($c['args']) < 1 || !$this->isTainacanRepo($f, $c)) {
                continue;
            }
            list($s, $e) = $c['args'][0];
            if ($s !== $e || $f->type($s) !== T_VARIABLE) {
                continue;
            }
            $var = $f->val($s);
            $fn = $f->functionAt($c['i']);
            $from = $fn ? $fn['open'] : 0;
            if (!$this->validatedBefore($f, $var, $from, $c['i'])) {
                $this->report($f, $c['line'], '`->' . $c['name'] . '( ' . $var . ' )` sem `' . $var . '->validate()` antes: entidade publicada (e todo Item_Metadata_Entity) lança exceção.', 'if ( ' . $var . '->validate() ) { $repo->' . $c['name'] . '( ' . $var . ' ); } else { registre ' . $var . '->get_errors(); }', null, Finding::WARN);
            }
        }
    }

    /** \Tainacan\Repositories\X, ou Repositories\X / X dentro do namespace Tainacan ou com use Tainacan\Repositories. */
    private function isTainacanRepoClass(SourceFile $f, $name)
    {
        $n = strtolower(ltrim($name, '\\'));
        if (strpos($n, 'tainacan\\repositories\\') === 0) {
            return true;
        }
        if (strpos($n, 'repositories\\') === 0) {
            return (bool) preg_match('/^\s*namespace\s+Tainacan\s*;|use\s+\\\\?Tainacan\\\\Repositories\s*;/mi', $f->text);
        }
        return false;
    }

    /**
     * O alvo de ->insert()/->update() é um repositório do Tainacan: chamada
     * direta de tainacan_items() etc., X::get_instance() de classe Tainacan,
     * ou variável/propriedade atribuída a partir disso no mesmo arquivo.
     */
    private function isTainacanRepo(SourceFile $f, array $c)
    {
        if ($c['static']) {
            return false;
        }
        $start = $f->statementStart($c['i']);
        $txt = $f->text($start, $c['op']);
        if (preg_match('/\b(' . implode('|', self::REPO_GETTERS) . ')\s*\(\s*\)\s*->\s*$/i', $txt) || preg_match('/Tainacan\\\\Repositories\\\\\w+\s*::\s*get_instance\s*\(\s*\)\s*->\s*$/i', $txt)) {
            return true;
        }
        $t = $c['target'];
        if ($f->type($t) === T_VARIABLE) {
            $holder = preg_quote($f->val($t), '/');
        } elseif ($f->type($t) === 'NAME' && $f->type($t - 1) === T_OBJECT_OPERATOR) {
            $holder = '\$this\s*->\s*' . preg_quote($f->val($t), '/');
        } else {
            return false;
        }
        $source = '(' . implode('|', self::REPO_GETTERS) . ')\s*\(|Tainacan\\\\Repositories\\\\\w+\s*::\s*get_instance|Repositories\\\\\w+\s*::\s*get_instance';
        return (bool) preg_match('/' . $holder . '\s*=\s*\\\\?(' . $source . ')/i', $f->text)
            && (bool) preg_match('/Tainacan|tainacan_/', $f->text);
    }

    /** try { ...->insert()/->update() de repositório... } catch ( ... ) { } com corpo vazio. */
    private function swallowedExceptions(SourceFile $f)
    {
        $n = $f->count();
        for ($i = 0; $i < $n; $i++) {
            if ($f->type($i) !== T_TRY || $f->type($i + 1) !== '{') {
                continue;
            }
            $tryClose = $f->match($i + 1);
            if ($tryClose < 0) {
                continue;
            }
            $saves = false;
            foreach ($f->methodCalls(['insert', 'update']) as $c) {
                if ($c['i'] > $i && $c['i'] < $tryClose && $this->isTainacanRepo($f, $c)) {
                    $saves = true;
                    break;
                }
            }
            if (!$saves) {
                continue;
            }
            $k = $tryClose + 1;
            while ($f->type($k) === T_CATCH && $f->type($k + 1) === '(') {
                $paren = $f->match($k + 1);
                $open = $paren + 1;
                if ($f->type($open) !== '{') {
                    break;
                }
                $close = $f->match($open);
                if ($close === $open + 1) {
                    $this->report($f, $f->line($k), 'catch vazio em volta de insert()/update() de repositório do Tainacan: a falha de validação some e a operação parece ter dado certo.', 'Registre a exceção (error_log, log do importador) ou propague; nunca deixe o catch vazio.', $f->line($close), Finding::WARN);
                }
                $k = $close + 1;
            }
        }
    }

    private function validatedBefore(SourceFile $f, $var, $from, $at)
    {
        for ($i = $from; $i < $at; $i++) {
            if ($f->type($i) === T_VARIABLE && $f->val($i) === $var && $f->type($i + 1) === T_OBJECT_OPERATOR
                && strtolower($f->val($i + 2)) === 'validate' && $f->type($i + 3) === '(') {
                return true;
            }
            if ($f->type($i) === T_VARIABLE && $f->val($i) === $var && $f->type($i + 1) === T_OBJECT_OPERATOR
                && strtolower($f->val($i + 2)) === 'get_validated') {
                return true;
            }
        }
        return false;
    }
}
