<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-W16: MutationObserver no documento inteiro cujo callback escreve no DOM,
 * sem nunca se desconectar.
 *
 * É o laço que não dá erro. O observador escuta `childList` com `subtree` em
 * `document.body`; o callback acrescenta um botão, troca um `textContent` ou
 * remove um nó — e isso é mutação de `childList`, que acorda o observador de
 * novo. A página carrega, desenha e para de responder, sem uma linha no
 * console. Num tema do Tainacan isso acontece com facilidade porque a lista é
 * um aplicativo Vue que muda o DOM o tempo todo, e o tema decora os cartões
 * que ela monta.
 *
 * A regra é de arquivo, não de linha: acusa quando, no mesmo arquivo, há
 * `new MutationObserver`, um `.observe()` no documento ou no corpo com
 * `childList`/`subtree`, escrita de DOM que mexe em filhos, e nenhuma chamada
 * a `disconnect()`. Desconectar antes de escrever (e religar depois) ou juntar
 * as mutações num `requestAnimationFrame` resolve; qualquer `disconnect()` no
 * arquivo já tira a regra do caminho, porque daí o autor sabe do problema.
 */
final class MutationObserverLoop extends Rule
{
    const ID = 'TNC-W16';
    const TITLE = 'MutationObserver no documento que escreve no DOM sem se desconectar';
    const SEVERITY = Finding::WARN;
    const CATEGORY = 'base';
    const EXTENSIONS = ['js', 'jsx', 'ts', 'tsx', 'vue'];

    /** Escritas que alteram filhos — as que o observador de childList vê. */
    const ESCRITA = '/\.(appendChild|insertBefore|replaceChild|removeChild|insertAdjacentElement|insertAdjacentHTML|append|prepend|before|after|replaceWith)\s*\(|\.(innerHTML|outerHTML|textContent|innerText)\s*\+?=[^=]/';

    protected function check(SourceFile $f)
    {
        $linhaObserve = 0;
        $temNovo = false;
        $temEscrita = false;

        foreach ($f->lines as $n => $line) {
            if (strlen($line) > 1000) {
                continue;
            }

            if (strpos($line, 'disconnect') !== false) {
                return; // O autor já trata o religamento: não é caso da regra.
            }

            if (preg_match('/new\s+(window\.)?MutationObserver\s*\(/', $line)) {
                $temNovo = true;
            }

            if (!$linhaObserve && preg_match('/\.observe\s*\(\s*(window\.)?document(\.(body|documentElement))?\s*,/', $line)) {
                // O alvo é o documento; falta saber se escuta filhos.
                $trecho = $line . "\n" . $this->proximas($f, $n, 4);
                if (preg_match('/(childList|subtree)\s*:\s*(true|1)/', $trecho)) {
                    $linhaObserve = $n;
                }
            }

            if (preg_match(self::ESCRITA, $line)) {
                $temEscrita = true;
            }
        }

        if ($temNovo && $linhaObserve && $temEscrita) {
            $this->report(
                $f,
                $linhaObserve,
                'MutationObserver em `document` com `childList`/`subtree`, e o arquivo escreve no DOM sem nunca chamar `disconnect()`.',
                'Escrever acorda o próprio observador: laço infinito que trava a aba sem erro no console. Desconecte antes de escrever e religue depois, junte as mutações num requestAnimationFrame e só escreva o que mudou.'
            );
        }
    }

    /** As próximas linhas, para ler o objeto de opções quebrado em várias. */
    private function proximas(SourceFile $f, $n, $quantas)
    {
        $out = '';
        for ($i = $n + 1; $i <= $n + $quantas; $i++) {
            if (isset($f->lines[$i])) {
                $out .= $f->lines[$i] . "\n";
            }
        }
        return $out;
    }
}
