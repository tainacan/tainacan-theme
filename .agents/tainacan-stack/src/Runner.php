<?php

namespace TainacanStack\Check;

/**
 * Aplica as regras a um conjunto de arquivos e filtra o resultado:
 * supressões em linha, linhas alteradas (modo diff) e baseline.
 */
final class Runner
{
    /** @var string[]|null */
    public $only;
    public $useBaseline = true;
    public $filesAnalyzed = 0;

    /** @var array<string,Baseline> */
    private $baselines = [];

    public function __construct(?array $only = null)
    {
        $this->only = $only;
    }

    /** Extensões que alguma regra atende. */
    public static function extensions()
    {
        $ext = [];
        foreach (Registry::ruleClasses() as $class) {
            foreach ($class::EXTENSIONS as $e) {
                $ext[$e] = true;
            }
        }
        return array_keys($ext);
    }

    /**
     * Arquivos de um diretório (recursivo) ou o próprio arquivo, respeitando exclusões.
     * @return array<int,array{rel:string,abs:string,project:ProjectContext}>
     */
    public static function collect($path)
    {
        $path = str_replace('\\', '/', $path);
        $root = ProjectContext::findRoot($path);
        $project = ProjectContext::forRoot($root);
        $exts = array_flip(self::extensions());
        $out = [];
        if (is_file($path)) {
            $abs = str_replace('\\', '/', realpath($path));
            $out[] = ['rel' => self::relative($root, $abs), 'abs' => $abs, 'project' => $project];
            return $out;
        }
        $base = str_replace('\\', '/', realpath($path) ?: $path);
        $it = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS),
                function ($file) use ($root, $project) {
                    $rel = self::relative($root, str_replace('\\', '/', $file->getPathname()));
                    return !$project->isExcluded($file->isDir() ? $rel . '/' : $rel);
                }
            )
        );
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $abs = str_replace('\\', '/', $file->getPathname());
            $ext = strtolower($file->getExtension());
            if (!isset($exts[$ext]) || $file->getSize() > 1500000) {
                continue;
            }
            $out[] = ['rel' => self::relative($root, $abs), 'abs' => $abs, 'project' => $project];
        }
        return $out;
    }

    public static function relative($root, $abs)
    {
        $root = rtrim($root, '/') . '/';
        if (stripos($abs, $root) === 0) {
            return substr($abs, strlen($root));
        }
        return $abs;
    }

    /**
     * @param array<string,mixed> $entry rel, abs, project, [content], [lines], [new]
     * @return Finding[]
     */
    public function checkEntry(array $entry)
    {
        /** @var ProjectContext $project */
        $project = $entry['project'];
        $content = isset($entry['content']) ? $entry['content'] : @file_get_contents($entry['abs']);
        if ($content === false || $content === null) {
            return [];
        }
        $file = new SourceFile($entry['rel'], $entry['abs'], $content, $project);
        $this->filesAnalyzed++;
        $found = [];
        foreach (Registry::rules($project, $this->only) as $rule) {
            if (!$rule->applies($file)) {
                continue;
            }
            foreach ($rule->run($file) as $finding) {
                $found[] = $finding;
            }
        }
        $supp = new Suppressions($file);
        $kept = [];
        foreach ($found as $finding) {
            if (!$supp->covers($finding)) {
                $kept[] = $finding;
            }
        }
        if ($project->ruleSetting('TNC-B05') !== 'off' && $this->wants('TNC-B05')) {
            $kept = array_merge($kept, $supp->invalid);
        }
        if (isset($entry['lines']) && is_array($entry['lines']) && empty($entry['new'])) {
            $kept = array_values(array_filter($kept, function (Finding $f) use ($entry) {
                for ($l = max(1, $f->line); $l <= max($f->line, $f->endLine); $l++) {
                    if (isset($entry['lines'][$l])) {
                        return true;
                    }
                }
                return false;
            }));
        }
        if ($this->useBaseline) {
            $baseline = $this->baseline($project->root);
            $kept = array_values(array_filter($kept, function (Finding $f) use ($baseline) {
                return !$baseline->absorbs($f);
            }));
        }
        return $kept;
    }

    /** A regra foi pedida em --rules (ID exato ou prefixo)? */
    private function wants($id)
    {
        if ($this->only === null) {
            return true;
        }
        foreach ($this->only as $o) {
            $o = strtoupper(trim($o));
            if ($o !== '' && strpos($id, $o) === 0) {
                return true;
            }
        }
        return false;
    }

    private function baseline($root)
    {
        if (!isset($this->baselines[$root])) {
            $this->baselines[$root] = Baseline::load($root);
        }
        return $this->baselines[$root];
    }

    /** @return Finding[] */
    public function checkPaths(array $paths)
    {
        $all = [];
        foreach ($paths as $p) {
            foreach (self::collect($p) as $entry) {
                $all = array_merge($all, $this->checkEntry($entry));
            }
        }
        return $all;
    }

    /**
     * Arquivos alterados segundo o git.
     * @param string $mode staged | worktree | <ref>
     * @return Finding[]
     */
    public function checkChanges($cwd, $mode, array $paths = [])
    {
        $top = Git::toplevel($cwd);
        if ($top === null) {
            throw new \RuntimeException('Não é um repositório git: ' . $cwd);
        }
        $exts = array_flip(self::extensions());
        $all = [];
        foreach (Git::changes($top, $mode, $paths) as $rel => $info) {
            $abs = $top . '/' . $rel;
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if (!isset($exts[$ext])) {
                continue;
            }
            $root = ProjectContext::findRoot(is_file($abs) ? $abs : dirname($abs));
            $project = ProjectContext::forRoot($root);
            $relProject = self::relative($project->root, $abs);
            if ($project->isExcluded($relProject)) {
                continue;
            }
            $entry = ['rel' => $relProject, 'abs' => $abs, 'project' => $project, 'lines' => $info['lines'], 'new' => $info['new']];
            if ($mode === 'staged') {
                $content = Git::staged($top, $rel);
                if ($content === null) {
                    continue;
                }
                $entry['content'] = $content;
            } elseif (!is_file($abs)) {
                continue;
            }
            $all = array_merge($all, $this->checkEntry($entry));
        }
        return $all;
    }
}
