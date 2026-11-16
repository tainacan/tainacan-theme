<?php

namespace TainacanStack\Check;

/**
 * Linha de comando do tnc-check. Códigos de saída: 0 PASS, 1 WARN, 2 BLOCK
 * (com --fail-on=block, avisos saem com 0). Erro de uso também sai com 2.
 */
final class Cli
{
    const USAGE = <<<'TXT'
tnc-check — verificador da Stack de IA do Tainacan

Uso:
  tnc-check [opções] [caminho ...]        analisa arquivos ou pastas (padrão: .)
  tnc-check --staged                      só o que está no índice do git (gancho pre-commit)
  tnc-check --changed                     alterações ainda não commitadas (contra HEAD)
  tnc-check --diff=<ref>                  linhas alteradas desde o ponto de divergência com <ref> (CI)
  tnc-check --workspace=<pasta>           cada plugin da pasta, com matriz regra × plugin
  tnc-check --release[=<pasta>]           conferências de release (versões, readme, changelog)
  tnc-check --inventory=<pasta>           inventário de ganchos, opções, handles, rotas, globais
  tnc-check --conflicts=<pasta>           colisões entre os plugins de uma pasta
  tnc-check --hook=<tipo>                 modo hook (lê JSON do stdin): post-edit, pre-bash, pre-powershell
  tnc-check --list-rules | --explain=<ID> | --version

Opções:
  --rules=TNC-S01,TNC-T      só estas regras (ID ou prefixo)
  --format=text|json|md|github|sarif   (sarif: aba Code scanning do GitHub e IDEs)
  --fail-on=warn|block       a partir de qual severidade o código de saída indica falha (padrão: warn)
  --no-baseline              ignora .tnc-check-baseline.json
  --baseline-generate        grava os achados atuais como baseline do projeto
  --no-lint                  pula o php -l (mais rápido em varreduras grandes)
  --output=<arquivo>         grava o relatório em arquivo
TXT;

    /** @var array<string,string|bool> */
    private $opts = [];
    /** @var string[] */
    private $paths = [];

    public static function main(array $argv)
    {
        $cli = new self();
        try {
            return $cli->run(array_slice($argv, 1));
        } catch (\Throwable $e) {
            fwrite(STDERR, 'tnc-check: ' . $e->getMessage() . "\n");
            return 2;
        }
    }

    public function run(array $args)
    {
        foreach ($args as $a) {
            if (strpos($a, '--') === 0) {
                $eq = strpos($a, '=');
                if ($eq === false) {
                    $this->opts[substr($a, 2)] = true;
                } else {
                    $this->opts[substr($a, 2, $eq - 2)] = substr($a, $eq + 1);
                }
            } else {
                $this->paths[] = $a;
            }
        }
        if ($this->opt('help') || $this->opt('h')) {
            echo self::USAGE, "\n";
            return 0;
        }
        if ($this->opt('version')) {
            echo 'tnc-check ', self::version(), "\n";
            return 0;
        }
        if ($this->opt('list-rules')) {
            return $this->listRules();
        }
        if ($this->opt('explain')) {
            return $this->explain((string) $this->opt('explain'));
        }
        if ($this->opt('no-lint')) {
            Rules\PhpLint::$enabled = false;
        }
        if ($this->opt('hook')) {
            return Hooks::run((string) $this->opt('hook'), $this->only());
        }
        if ($this->opt('inventory')) {
            return $this->emit(Inventory::render(Inventory::scanWorkspace((string) $this->opt('inventory')), $this->format()), 0);
        }
        if ($this->opt('conflicts')) {
            $inv = Inventory::scanWorkspace((string) $this->opt('conflicts'));
            $conf = Conflicts::find($inv);
            return $this->emit(Conflicts::render($conf, $this->format()), Conflicts::exitCode($conf, $this->failOn()));
        }
        if ($this->opt('release') !== null && $this->opt('release') !== false) {
            $dir = $this->opt('release') === true ? ($this->paths ? $this->paths[0] : '.') : (string) $this->opt('release');
            $findings = ReleaseChecks::run($dir);
            return $this->finish($findings, ['mode' => 'release', 'files' => 1]);
        }
        if ($this->opt('workspace')) {
            return $this->workspace((string) $this->opt('workspace'));
        }

        $runner = new Runner($this->only());
        $runner->useBaseline = !$this->opt('no-baseline') && !$this->opt('baseline-generate');
        $cwd = getcwd();
        if ($this->opt('staged')) {
            $findings = $runner->checkChanges($cwd, 'staged', $this->paths);
            $mode = 'staged';
        } elseif ($this->opt('changed')) {
            $findings = $runner->checkChanges($cwd, 'worktree', $this->paths);
            $mode = 'changed';
        } elseif ($this->opt('diff')) {
            $ref = $this->opt('diff') === true ? 'HEAD' : (string) $this->opt('diff');
            $findings = $runner->checkChanges($cwd, $ref, $this->paths);
            $mode = 'diff ' . $ref;
        } else {
            $findings = $runner->checkPaths($this->paths ?: ['.']);
            $mode = 'completo';
        }
        if ($this->opt('baseline-generate')) {
            $root = ProjectContext::findRoot($this->paths ? $this->paths[0] : $cwd);
            $n = Baseline::write($root, $findings);
            fwrite(STDERR, "Baseline gravado em $root/" . Baseline::FILE . " com $n achado(s).\n");
            return 0;
        }
        return $this->finish($findings, ['mode' => $mode, 'files' => $runner->filesAnalyzed]);
    }

    private function workspace($dir)
    {
        $plugins = Inventory::pluginDirs($dir);
        $runner = new Runner($this->only());
        $runner->useBaseline = !$this->opt('no-baseline');
        $all = [];
        $matrix = [];
        foreach ($plugins as $p) {
            $findings = $runner->checkPaths([$p]);
            $slug = basename($p);
            foreach ($findings as $f) {
                $f->file = $slug . '/' . $f->file;
                $matrix[$f->rule][$slug] = (isset($matrix[$f->rule][$slug]) ? $matrix[$f->rule][$slug] : 0) + 1;
            }
            $all = array_merge($all, $findings);
        }
        $format = $this->format();
        if ($format === 'json') {
            $data = json_decode(Reporter::render($all, 'json', ['files' => $runner->filesAnalyzed, 'mode' => 'workspace', 'version' => self::version()]), true);
            $data['plugins'] = array_map('basename', $plugins);
            $data['matrix'] = $matrix;
            return $this->emit(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", $this->exitCode($all));
        }
        $out = self::matrixMarkdown($matrix, array_map('basename', $plugins));
        $out .= "\n" . Reporter::render($all, $format === 'text' ? 'md' : $format, ['files' => $runner->filesAnalyzed, 'mode' => 'workspace']);
        return $this->emit($out, $this->exitCode($all));
    }

    private static function matrixMarkdown(array $matrix, array $plugins)
    {
        ksort($matrix);
        $titles = [];
        foreach (Registry::describe() as $r) {
            $titles[$r['id']] = $r['title'];
        }
        $titles['TNC-B05'] = 'Supressão sem motivo';
        $out = "## Matriz regra × plugin\n\n| Regra | " . implode(' | ', $plugins) . " |\n|---|" . str_repeat('---|', count($plugins)) . "\n";
        foreach ($matrix as $rule => $row) {
            $cells = [];
            foreach ($plugins as $p) {
                $cells[] = isset($row[$p]) ? (string) $row[$p] : '·';
            }
            $out .= '| ' . $rule . ' ' . (isset($titles[$rule]) ? $titles[$rule] : '') . ' | ' . implode(' | ', $cells) . " |\n";
        }
        return $out;
    }

    private function finish(array $findings, array $meta)
    {
        $meta['version'] = self::version();
        return $this->emit(Reporter::render($findings, $this->format(), $meta), $this->exitCode($findings));
    }

    private function exitCode(array $findings)
    {
        $v = Reporter::verdict($findings);
        if ($v === 'BLOCK') {
            return 2;
        }
        if ($v === 'WARN') {
            return $this->failOn() === 'block' ? 0 : 1;
        }
        return 0;
    }

    private function emit($text, $code)
    {
        $out = $this->opt('output');
        if ($out) {
            file_put_contents((string) $out, $text);
        } else {
            echo $text;
        }
        return $code;
    }

    private function listRules()
    {
        $rows = Registry::describe();
        if ($this->format() === 'json') {
            echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
            return 0;
        }
        foreach ($rows as $r) {
            printf("%-8s %-8s %-12s %s\n", $r['id'], $r['severity'], $r['category'], $r['title']);
        }
        echo "\nTambém: TNC-B05 (supressão sem motivo) e TNC-R00..R06 (--release).\n";
        return 0;
    }

    private function explain($id)
    {
        $id = strtoupper($id);
        $file = self::stackRoot() . '/licoes/' . $id . '.md';
        if (!is_file($file)) {
            fwrite(STDERR, "Sem lição registrada para $id em licoes/.\n");
            return 2;
        }
        echo file_get_contents($file);
        return 0;
    }

    private function only()
    {
        $r = $this->opt('rules');
        if (!$r || $r === true) {
            return null;
        }
        return array_values(array_filter(array_map('trim', explode(',', (string) $r))));
    }

    private function format()
    {
        $f = $this->opt('format');
        return is_string($f) ? strtolower($f) : 'text';
    }

    private function failOn()
    {
        $f = $this->opt('fail-on');
        return $f === 'block' ? 'block' : 'warn';
    }

    private function opt($name)
    {
        return array_key_exists($name, $this->opts) ? $this->opts[$name] : null;
    }

    public static function stackRoot()
    {
        return str_replace('\\', '/', dirname(__DIR__));
    }

    public static function version()
    {
        $v = @file_get_contents(self::stackRoot() . '/VERSION');
        return $v ? trim($v) : 'dev';
    }
}
