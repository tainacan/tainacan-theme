<?php

namespace TainacanStack\Check;

/** Leitura de diff e do índice do git, sem dependências. */
final class Git
{
    public static function run($cwd, $args, &$code = null)
    {
        $cmd = 'git -C ' . escapeshellarg($cwd) . ' ' . $args . ' 2>' . (DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null');
        $out = [];
        exec($cmd, $out, $code);
        return implode("\n", $out);
    }

    public static function toplevel($cwd)
    {
        $code = 0;
        $top = trim(self::run($cwd, 'rev-parse --show-toplevel', $code));
        return $code === 0 && $top !== '' ? str_replace('\\', '/', $top) : null;
    }

    /**
     * Arquivos alterados e as linhas novas de cada um.
     * @param string $mode 'staged' | 'worktree' | ref (ex.: origin/develop)
     * @return array<string,array{new:bool,lines:array<int,bool>}> caminho relativo => dados
     */
    public static function changes($top, $mode, array $paths = [])
    {
        $limit = '';
        if ($paths) {
            $limit = ' -- ' . implode(' ', array_map('escapeshellarg', $paths));
        }
        if ($mode === 'staged') {
            $diff = self::run($top, 'diff --cached --unified=0 --no-color --no-ext-diff --diff-filter=ACMR' . $limit);
        } elseif ($mode === 'worktree') {
            $diff = self::run($top, 'diff HEAD --unified=0 --no-color --no-ext-diff --diff-filter=ACMR' . $limit);
        } else {
            $base = trim(self::run($top, 'merge-base ' . escapeshellarg($mode) . ' HEAD'));
            if ($base === '') {
                $base = $mode;
            }
            $diff = self::run($top, 'diff ' . escapeshellarg($base) . ' --unified=0 --no-color --no-ext-diff --diff-filter=ACMR' . $limit);
        }
        $files = self::parse($diff);
        if ($mode === 'worktree') {
            // arquivos novos ainda não rastreados contam inteiros
            $untracked = self::run($top, 'ls-files --others --exclude-standard' . $limit);
            foreach (array_filter(explode("\n", $untracked)) as $rel) {
                $files[str_replace('\\', '/', $rel)] = ['new' => true, 'lines' => []];
            }
        }
        return $files;
    }

    /** @return array<string,array{new:bool,lines:array<int,bool>}> */
    public static function parse($diff)
    {
        $files = [];
        $current = null;
        $isNew = false;
        foreach (explode("\n", $diff) as $line) {
            if (strpos($line, 'diff --git ') === 0) {
                $current = null;
                $isNew = false;
                continue;
            }
            if (strpos($line, 'new file mode') === 0) {
                $isNew = true;
                continue;
            }
            if (strpos($line, '+++ ') === 0) {
                $path = substr($line, 4);
                if ($path === '/dev/null') {
                    $current = null;
                    continue;
                }
                $path = preg_replace('#^b/#', '', $path);
                $path = trim($path, '"');
                $current = $path;
                $files[$current] = ['new' => $isNew, 'lines' => []];
                continue;
            }
            if ($current !== null && preg_match('/^@@ -\d+(?:,\d+)? \+(\d+)(?:,(\d+))? @@/', $line, $m)) {
                $start = (int) $m[1];
                $count = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 1;
                for ($k = 0; $k < $count; $k++) {
                    $files[$current]['lines'][$start + $k] = true;
                }
            }
        }
        return $files;
    }

    /** Conteúdo do arquivo no índice (o que vai para o commit). */
    public static function staged($top, $rel)
    {
        $code = 0;
        $cmd = 'git -C ' . escapeshellarg($top) . ' show ' . escapeshellarg(':' . $rel);
        $out = shell_exec($cmd);
        return $out === null ? null : $out;
    }
}
