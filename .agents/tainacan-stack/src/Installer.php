<?php

namespace TainacanStack\Check;

/**
 * Instala, atualiza e confere a stack num repositório.
 *
 * Layout no alvo (a mesma pasta serve de plugin do Claude Code e de pasta
 * de skills/agentes do padrão .agents usado por Codex, Copilot, Cursor,
 * Gemini/Antigravity e opencode):
 *
 *   .agents/tainacan-stack/   verificador, lições, referências, papéis, modelos
 *   .agents/skills/tnc-*      skills
 *   .agents/agents/tnc-*      subagentes (Claude, Antigravity)
 *   .agents/hooks/hooks.json  hooks do plugin do Claude
 *   .agents/.claude-plugin/   manifesto do plugin
 *   .claude-plugin/marketplace.json + .claude/settings.json   habilitam o plugin no Claude
 *   AGENTS.md (bloco gerenciado), CLAUDE.md (@AGENTS.md)
 *   opcionais: .codex/agents/*.toml, .codex/hooks.json, GEMINI.md,
 *              .github/agents/*.agent.md, .cursor/agents/*.md,
 *              .github/workflows/tnc-check.yml, .tnc-check.json, gancho pre-commit
 */
final class Installer
{
    const BEGIN = '<!-- tainacan-stack:inicio -->';
    const END = '<!-- tainacan-stack:fim -->';
    const MARKETPLACE = 'tainacan-stack-local';
    const PLUGIN = 'tainacan-stack';
    const TOOLS = ['claude', 'codex', 'copilot', 'cursor', 'gemini', 'opencode'];
    const STATE = '.agents/tainacan-stack.json';

    /** @var string pasta stack/ da origem */
    private $source;
    /** @var string */
    private $target;
    /** @var string[] */
    private $log = [];
    /** @var bool */
    private $dryRun = false;

    public function __construct($source, $target, $dryRun = false)
    {
        $this->source = rtrim(str_replace('\\', '/', $source), '/');
        $this->target = rtrim(str_replace('\\', '/', realpath($target) ?: $target), '/');
        $this->dryRun = $dryRun;
    }

    public static function main(array $argv)
    {
        $cmd = isset($argv[1]) ? $argv[1] : 'ajuda';
        $args = [];
        $opts = [];
        foreach (array_slice($argv, 2) as $a) {
            if (strpos($a, '--') === 0) {
                $eq = strpos($a, '=');
                $opts[$eq === false ? substr($a, 2) : substr($a, 2, $eq - 2)] = $eq === false ? true : substr($a, $eq + 1);
            } else {
                $args[] = $a;
            }
        }
        $source = dirname(dirname(__DIR__)); // .../stack (ou .agents no alvo)
        $target = isset($args[0]) ? $args[0] : '.';
        $inst = new self($source, $target, !empty($opts['simular']));
        try {
            switch ($cmd) {
                case 'instalar':
                case 'atualizar':
                    $inst->install($opts, $cmd === 'atualizar');
                    break;
                case 'gancho':
                    $inst->gitHook();
                    break;
                case 'status':
                    $inst->status();
                    return 0;
                default:
                    echo self::usage();
                    return $cmd === 'ajuda' || $cmd === '--help' ? 0 : 2;
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'tnc-stack: ' . $e->getMessage() . "\n");
            return 2;
        }
        echo implode("\n", $inst->log), "\n";
        return 0;
    }

    public static function usage()
    {
        return <<<'TXT'
tnc-stack — instalador da Stack de IA do Tainacan

  tnc-stack instalar <repositório> [opções]   instala ou atualiza a stack no repositório
  tnc-stack atualizar <repositório>           reinstala com as mesmas opções da última vez
  tnc-stack gancho <repositório>              só o gancho pre-commit (cada pessoa, depois do clone)
  tnc-stack status <repositório>              versão instalada × versão desta stack

Opções de instalar:
  --ferramentas=claude,codex,copilot,cursor,gemini,opencode|todas   (padrão: claude,codex)
  --ci              cria .github/workflows/tnc-check.yml
  --baseline        grava os achados atuais em .tnc-check-baseline.json (adoção em código legado)
  --sem-gancho      não instala o gancho pre-commit
  --simular         mostra o que faria, sem gravar
TXT;
    }

    public function status()
    {
        $installed = @file_get_contents($this->target . '/.agents/tainacan-stack/VERSION');
        $here = @file_get_contents($this->source . '/tainacan-stack/VERSION');
        $state = $this->readJson($this->target . '/' . self::STATE);
        echo 'Repositório: ' . $this->target . "\n";
        echo 'Instalada:   ' . ($installed ? trim($installed) : 'não instalada') . "\n";
        echo 'Disponível:  ' . trim((string) $here) . "\n";
        if ($state) {
            echo 'Ferramentas: ' . implode(', ', $state['ferramentas']) . "\n";
            echo 'Instalada em: ' . $state['data'] . "\n";
        }
    }

    public function install(array $opts, $reuse)
    {
        if (!is_dir($this->target)) {
            throw new \RuntimeException('repositório não encontrado: ' . $this->target);
        }
        if ($this->target === $this->source || $this->target . '/stack' === $this->source) {
            throw new \RuntimeException('o alvo é a própria stack');
        }
        if ($this->source === $this->target . '/.agents') {
            throw new \RuntimeException('esta é a cópia instalada; para instalar ou atualizar, rode o tnc-stack do repositório da stack (a cópia instalada serve para "gancho" e "status")');
        }
        $state = $this->readJson($this->target . '/' . self::STATE);
        $tools = ['claude', 'codex'];
        if ($reuse && $state && !empty($state['ferramentas'])) {
            $tools = $state['ferramentas'];
        }
        if (!empty($opts['ferramentas'])) {
            $tools = $opts['ferramentas'] === 'todas' ? self::TOOLS : array_values(array_intersect(self::TOOLS, array_map('trim', explode(',', $opts['ferramentas']))));
        }
        $ci = !empty($opts['ci']) || ($reuse && !empty($state['ci']));

        $this->copyStack();
        $this->agentsMd();
        if (in_array('claude', $tools, true)) {
            $this->claude();
        }
        if (in_array('codex', $tools, true)) {
            $this->codex();
        }
        if (in_array('gemini', $tools, true)) {
            $this->importFile('GEMINI.md');
        }
        if (in_array('copilot', $tools, true)) {
            $this->copilot();
        }
        if (in_array('cursor', $tools, true)) {
            $this->cursor();
        }
        $this->tncConfig();
        $this->phpcsRuleset();
        $this->exportIgnore();
        if ($ci) {
            $this->ciWorkflow();
        }
        if (empty($opts['sem-gancho'])) {
            $this->gitHook();
        }
        if (!empty($opts['baseline'])) {
            $this->baseline();
        }
        $this->write($this->target . '/' . self::STATE, json_encode([
            'versao' => trim((string) file_get_contents($this->source . '/tainacan-stack/VERSION')),
            'ferramentas' => $tools,
            'ci' => $ci,
            'data' => gmdate('Y-m-d\TH:i:s\Z'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        $this->log[] = '';
        $this->log[] = 'Pronto. Próximos passos:';
        $this->log[] = '- Preencha a seção do projeto no AGENTS.md (fora do bloco da stack).';
        $this->log[] = '- Rode: php .agents/tainacan-stack/bin/tnc-check --changed';
        $this->log[] = '- Faça commit de .agents/, AGENTS.md, CLAUDE.md e das configurações geradas.';
        $this->log[] = '- Cada pessoa da equipe, depois do clone: php .agents/tainacan-stack/bin/tnc-stack gancho .';
    }

    // ------------------------------------------------------------------

    private function copyStack()
    {
        $dst = $this->target . '/.agents';
        // tainacan-stack/ é substituída inteira (é gerenciada); o resto só nos itens tnc-*
        $this->replaceDir($this->source . '/tainacan-stack', $dst . '/tainacan-stack', ['/tests/', '/.git/']);
        foreach (glob($dst . '/skills/tnc-*', GLOB_ONLYDIR) ?: [] as $old) {
            $this->removeDir($old);
        }
        foreach (glob($this->source . '/skills/tnc-*', GLOB_ONLYDIR) ?: [] as $skill) {
            $this->replaceDir($skill, $dst . '/skills/' . basename($skill));
        }
        foreach (glob($dst . '/agents/tnc-*.md') ?: [] as $old) {
            $this->unlink($old);
        }
        foreach (glob($this->source . '/agents/tnc-*.md') ?: [] as $agent) {
            $this->copy($agent, $dst . '/agents/' . basename($agent));
        }
        $hooks = $dst . '/hooks/hooks.json';
        if (!is_file($hooks) || strpos((string) file_get_contents($hooks), 'tnc-check') !== false) {
            $this->copy($this->source . '/hooks/hooks.json', $hooks);
        } else {
            $this->log[] = 'AVISO: ' . $hooks . ' já existe e não é da stack; os hooks do tnc-check não foram instalados.';
        }
        $manifest = $dst . '/.claude-plugin/plugin.json';
        $current = $this->readJson($manifest);
        if (!$current || (isset($current['name']) && $current['name'] === self::PLUGIN)) {
            $this->copy($this->source . '/.claude-plugin/plugin.json', $manifest);
        } else {
            $this->log[] = 'AVISO: ' . $manifest . ' pertence a outro plugin (' . $current['name'] . '); o plugin da stack não foi registrado.';
        }
        $this->log[] = 'stack copiada para .agents/ (versão ' . trim((string) file_get_contents($this->source . '/tainacan-stack/VERSION')) . ')';
    }

    private function agentsMd()
    {
        $file = $this->target . '/AGENTS.md';
        $block = self::BEGIN . "\n" . trim((string) file_get_contents($this->source . '/tainacan-stack/AGENTS.base.md')) . "\n" . self::END;
        if (!is_file($file)) {
            $tpl = (string) file_get_contents($this->source . '/tainacan-stack/templates/AGENTS.projeto.md');
            $ctx = ProjectContext::forRoot($this->target);
            $tpl = str_replace(
                ['{{NOME}}', '{{DESCRICAO}}', '{{VERSAO}}'],
                [isset($ctx->header['Plugin Name']) ? $ctx->header['Plugin Name'] : basename($this->target), 'Descreva em uma frase o que este projeto faz.', isset($ctx->header['Version']) ? $ctx->header['Version'] : '0.1.0'],
                $tpl
            );
            $this->write($file, rtrim($tpl) . "\n\n" . $block . "\n");
            $this->log[] = 'AGENTS.md criado (seção do projeto + bloco da stack)';
            return;
        }
        $text = (string) file_get_contents($file);
        $start = strpos($text, self::BEGIN);
        $end = strpos($text, self::END);
        if ($start !== false && $end !== false && $end > $start) {
            $text = substr($text, 0, $start) . $block . substr($text, $end + strlen(self::END));
            $this->log[] = 'AGENTS.md: bloco da stack atualizado';
        } else {
            $text = rtrim($text) . "\n\n" . $block . "\n";
            $this->log[] = 'AGENTS.md: bloco da stack acrescentado no fim (o conteúdo do projeto foi preservado)';
        }
        $this->write($file, $text);
        $size = strlen($text);
        if ($size > 30000) {
            $this->log[] = 'AVISO: AGENTS.md tem ' . round($size / 1024) . ' KiB; o Codex lê no máximo 32 KiB. Mova detalhes do projeto para docs/ e deixe referências.';
        }
    }

    private function importFile($name)
    {
        $file = $this->target . '/' . $name;
        $text = is_file($file) ? (string) file_get_contents($file) : '';
        if (strpos($text, '@AGENTS.md') !== false) {
            return;
        }
        $this->write($file, $text === '' ? "@AGENTS.md\n" : rtrim($text) . "\n\n@AGENTS.md\n");
        $this->log[] = $name . ': importa @AGENTS.md';
    }

    private function claude()
    {
        $this->importFile('CLAUDE.md');
        $mp = $this->target . '/.claude-plugin/marketplace.json';
        $data = $this->readJson($mp) ?: ['name' => self::MARKETPLACE, 'owner' => ['name' => 'Stack de IA do Tainacan'], 'plugins' => []];
        $name = isset($data['name']) ? $data['name'] : self::MARKETPLACE;
        $found = false;
        foreach ($data['plugins'] as &$p) {
            if (isset($p['name']) && $p['name'] === self::PLUGIN) {
                $p['source'] = './.agents';
                $found = true;
            }
        }
        unset($p);
        if (!$found) {
            $data['plugins'][] = ['name' => self::PLUGIN, 'source' => './.agents', 'description' => 'Stack de IA do Tainacan (skills, revisores, tnc-check)'];
        }
        $this->writeJson($mp, $data);
        $settings = $this->target . '/.claude/settings.json';
        $s = $this->readJson($settings) ?: [];
        if (!isset($s['extraKnownMarketplaces']) || !is_array($s['extraKnownMarketplaces'])) {
            $s['extraKnownMarketplaces'] = [];
        }
        $s['extraKnownMarketplaces'][$name] = ['source' => ['source' => 'directory', 'path' => '.']];
        if (!isset($s['enabledPlugins']) || !is_array($s['enabledPlugins'])) {
            $s['enabledPlugins'] = [];
        }
        $s['enabledPlugins'][self::PLUGIN . '@' . $name] = true;
        $this->writeJson($settings, $s);
        $this->log[] = 'Claude Code: plugin ' . self::PLUGIN . '@' . $name . ' habilitado em .claude/settings.json (cada pessoa confirma ao confiar na pasta)';
    }

    private function codex()
    {
        foreach (glob($this->target . '/.codex/agents/tnc-*.toml') ?: [] as $old) {
            $this->unlink($old);
        }
        foreach (glob($this->source . '/agents/tnc-*.md') ?: [] as $agent) {
            list($meta, $body) = $this->frontmatter((string) file_get_contents($agent));
            $toml = 'name = ' . $this->tomlString($meta['name']) . "\n"
                . 'description = ' . $this->tomlString($meta['description']) . "\n"
                . 'developer_instructions = ' . $this->tomlMultiline(trim($body)) . "\n";
            $this->write($this->target . '/.codex/agents/' . $meta['name'] . '.toml', $toml);
        }
        $hooksFile = $this->target . '/.codex/hooks.json';
        $existing = $this->readJson($hooksFile);
        if (!$existing || strpos((string) json_encode($existing), 'tnc-check') !== false) {
            $cmd = function ($kind) {
                return '"${TNC_PHP:-php}" .agents/tainacan-stack/bin/tnc-check --hook=' . $kind;
            };
            $this->writeJson($hooksFile, ['hooks' => [
                'PostToolUse' => [['matcher' => 'Edit|Write|apply_patch', 'hooks' => [['type' => 'command', 'command' => $cmd('post-edit'), 'timeout' => 60]]]],
                'PreToolUse' => [['matcher' => 'Bash', 'hooks' => [['type' => 'command', 'command' => $cmd('pre-bash'), 'timeout' => 20]]]],
            ]]);
            $this->log[] = 'Codex: subagentes em .codex/agents/ e hooks em .codex/hooks.json (o Codex só roda hooks de projeto confiável)';
        } else {
            $this->log[] = 'AVISO: .codex/hooks.json já existe e não é da stack; hooks do tnc-check não instalados para o Codex.';
        }
    }

    private function copilot()
    {
        foreach (glob($this->source . '/agents/tnc-*.md') ?: [] as $agent) {
            list($meta, $body) = $this->frontmatter((string) file_get_contents($agent));
            $md = "---\nname: " . $meta['name'] . "\ndescription: " . $this->yamlString($meta['description']) . "\n---\n\n" . trim($body) . "\n";
            $this->write($this->target . '/.github/agents/' . $meta['name'] . '.agent.md', $md);
        }
        $this->log[] = 'Copilot: agentes em .github/agents/ (instruções e skills vêm de AGENTS.md e .agents/skills)';
    }

    private function cursor()
    {
        foreach (glob($this->source . '/agents/tnc-*.md') ?: [] as $agent) {
            $this->copy($agent, $this->target . '/.cursor/agents/' . basename($agent));
        }
        $this->log[] = 'Cursor: agentes em .cursor/agents/ (instruções e skills vêm de AGENTS.md e .agents/skills)';
    }

    private function tncConfig()
    {
        $file = $this->target . '/.tnc-check.json';
        if (is_file($file)) {
            return;
        }
        $ctx = ProjectContext::forRoot($this->target);
        $data = json_decode((string) file_get_contents($this->source . '/tainacan-stack/templates/tnc-check.json'), true);
        // plugin numa subpasta (o núcleo fica em src/): declara plugin_dir quando só uma subpasta tem cabeçalho
        if (!$ctx->isPlugin()) {
            $withHeader = [];
            foreach (glob($this->target . '/*', GLOB_ONLYDIR) ?: [] as $sub) {
                if (!preg_match('#/(vendor|node_modules|tests?|docs|build|dist|\.\w+)$#', $sub) && ProjectContext::forRoot(str_replace('\\', '/', $sub))->isPlugin()) {
                    $withHeader[] = basename($sub);
                }
            }
            if (count($withHeader) === 1) {
                $data['plugin_dir'] = $withHeader[0];
                $data['exclude'][] = $withHeader[0] . '/vendor/';
                $this->log[] = 'plugin encontrado em ' . $withHeader[0] . '/: declarado como plugin_dir';
                $ctx = ProjectContext::forRoot($this->target . '/' . $withHeader[0]);
            }
        }
        $data['text_domain'] = $ctx->textDomain ?: basename($this->target);

        // Tema: declara o tipo, para o verificador trocar as regras que
        // valem só para plugin (seletor global de CSS) e as conferências de
        // release, e para o Sass e o CSS compilado ficarem fora da análise.
        if ($ctx->isTheme()) {
            $data['tipo'] = 'tema';
            foreach (['assets/vendor/', 'style.css', 'style.min.css'] as $extra) {
                if (!in_array($extra, $data['exclude'], true)) {
                    $data['exclude'][] = $extra;
                }
            }
            $this->log[] = 'projeto reconhecido como tema: "tipo": "tema" no .tnc-check.json';
        }

        $this->write($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        $this->log[] = '.tnc-check.json criado (domínio de texto: ' . $data['text_domain'] . ')';
    }

    /**
     * Ruleset estrito do PHPCS quando o projeto ainda não tem nenhum: o
     * modelo de plugin (com PluginCheck) ou o de tema (sem PluginCheck, que
     * é padrão do diretório de plugins). Um ruleset existente é mantido:
     * TNC-R08 avisa se faltam as regras de segurança.
     */
    private function phpcsRuleset()
    {
        foreach (['phpcs.xml.dist', 'phpcs.xml', '.phpcs.xml.dist', '.phpcs.xml'] as $file) {
            if (is_file($this->target . '/' . $file)) {
                return;
            }
        }
        $ctx = ProjectContext::forRoot($this->target);
        $isTheme = $ctx->isTheme();
        if (!$isTheme && !$ctx->isPlugin() && empty($ctx->config['plugin_dir'])) {
            return; // nem plugin nem tema: sem ruleset
        }
        $domain = $ctx->textDomain ?: basename($this->target);
        $prefix = str_replace('-', '_', $domain);
        $model = $isTheme ? 'phpcs-tema.xml.dist' : 'phpcs.xml.dist';
        $tpl = (string) file_get_contents($this->source . '/tainacan-stack/templates/' . $model);
        $this->write($this->target . '/phpcs.xml.dist', str_replace(['{{TEXT_DOMAIN}}', '{{PREFIX}}'], [$domain, $prefix], $tpl));
        $this->log[] = 'phpcs.xml.dist criado a partir de ' . $model . ' (instale as dependências de desenvolvimento indicadas no cabeçalho do arquivo)';
    }

    /**
     * A stack é ferramenta de desenvolvimento: fica fora do pacote do plugin.
     * export-ignore tira as pastas do `git archive` (ZIP de release) e o
     * .distignore cobre o `wp dist-archive` e o deploy para o WordPress.org.
     */
    private function exportIgnore()
    {
        $paths = ['/.agents', '/.claude', '/.claude-plugin', '/.codex', '/.cursor', '/.github/agents', '/.tnc-check.json', '/.tnc-check-baseline.json', '/AGENTS.md', '/CLAUDE.md', '/GEMINI.md'];
        $file = $this->target . '/.gitattributes';
        $text = is_file($file) ? (string) file_get_contents($file) : '';
        $add = [];
        foreach ($paths as $p) {
            if (!preg_match('#^' . preg_quote($p, '#') . '\s+export-ignore#m', $text)) {
                $add[] = $p . ' export-ignore';
            }
        }
        if ($add) {
            $this->write($file, rtrim($text) . ($text === '' ? '' : "\n\n") . "# Stack de IA do Tainacan: fora do pacote do plugin\n" . implode("\n", $add) . "\n");
            $this->log[] = '.gitattributes: pastas da stack marcadas como export-ignore (fora do ZIP de release)';
        }
        $dist = $this->target . '/.distignore';
        if (is_file($dist)) {
            $d = (string) file_get_contents($dist);
            $miss = [];
            foreach ($paths as $p) {
                if (strpos($d, ltrim($p, '/')) === false) {
                    $miss[] = ltrim($p, '/');
                }
            }
            if ($miss) {
                $this->write($dist, rtrim($d) . "\n" . implode("\n", $miss) . "\n");
                $this->log[] = '.distignore: pastas da stack acrescentadas';
            }
        }
        $this->log[] = 'Lembrete: se o CI roda o Plugin Check do WordPress.org, exclua .agents,.claude,.claude-plugin,.codex,.cursor (exclude-directories).';
    }

    private function ciWorkflow()
    {
        $file = $this->target . '/.github/workflows/tnc-check.yml';
        if (is_file($file)) {
            $this->log[] = '.github/workflows/tnc-check.yml já existe: mantido';
            return;
        }
        $branches = is_dir($this->target . '/.git') && trim(Git::run($this->target, 'branch --list develop')) !== '' ? 'main, master, develop' : 'main, master';
        $tpl = str_replace('{{BRANCHES}}', $branches, (string) file_get_contents($this->source . '/tainacan-stack/templates/ci-tnc-check.yml'));
        $this->write($file, $tpl);
        $this->log[] = 'CI: .github/workflows/tnc-check.yml criado';
    }

    public function gitHook()
    {
        $top = Git::toplevel($this->target);
        if ($top === null) {
            $this->log[] = 'sem repositório git: gancho pre-commit não instalado';
            return;
        }
        $hooksDir = trim(Git::run($top, 'rev-parse --git-path hooks'));
        if ($hooksDir === '') {
            $hooksDir = '.git/hooks';
        }
        if (!preg_match('#^([A-Za-z]:)?/#', $hooksDir)) {
            $hooksDir = $top . '/' . $hooksDir;
        }
        $hook = $hooksDir . '/pre-commit';
        $ours = (string) file_get_contents($this->source . '/tainacan-stack/templates/pre-commit.sh');
        if (is_file($hook) && strpos((string) file_get_contents($hook), 'tainacan-stack') === false) {
            $this->rename($hook, $hooksDir . '/pre-commit.local');
            $this->log[] = 'gancho pre-commit anterior preservado como pre-commit.local (é chamado depois do tnc-check)';
        }
        $this->write($hook, $ours);
        if (!$this->dryRun) {
            @chmod($hook, 0755);
        }
        $this->log[] = 'gancho pre-commit instalado (bloqueio do tnc-check impede o commit)';

        // commit-msg: Conventional Commits (feat, fix, docs...), padrão do núcleo e dos plugins
        $msgHook = $hooksDir . '/commit-msg';
        $oursMsg = (string) file_get_contents($this->source . '/tainacan-stack/templates/commit-msg.sh');
        if (is_file($msgHook) && strpos((string) file_get_contents($msgHook), 'tainacan-stack') === false) {
            $this->rename($msgHook, $hooksDir . '/commit-msg.local');
            $this->log[] = 'gancho commit-msg anterior preservado como commit-msg.local';
        }
        $this->write($msgHook, $oursMsg);
        if (!$this->dryRun) {
            @chmod($msgHook, 0755);
        }
        $this->log[] = 'gancho commit-msg instalado (mensagem em Conventional Commits)';
    }

    private function baseline()
    {
        if ($this->dryRun) {
            $this->log[] = '[simular] baseline seria gerado';
            return;
        }
        $runner = new Runner();
        $runner->useBaseline = false;
        Rules\PhpLint::$enabled = false;
        $findings = $runner->checkPaths([$this->target]);
        $n = Baseline::write($this->target, $findings);
        $this->log[] = 'baseline: ' . $n . ' achado(s) preexistente(s) registrados em ' . Baseline::FILE . ' (só achados novos vão bloquear)';
    }

    // ------------------------------------------------------------------
    // utilitários de arquivo

    private function frontmatter($text)
    {
        $meta = [];
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $text, $m)) {
            foreach (explode("\n", $m[1]) as $line) {
                if (preg_match('/^(\w+):\s*(.*)$/', $line, $kv)) {
                    $meta[$kv[1]] = trim($kv[2]);
                }
            }
            return [$meta, $m[2]];
        }
        return [$meta, $text];
    }

    private function tomlString($s)
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $s) . '"';
    }

    private function tomlMultiline($s)
    {
        return "'''\n" . str_replace("'''", "' ' '", $s) . "\n'''";
    }

    private function yamlString($s)
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $s) . '"';
    }

    private function readJson($file)
    {
        if (!is_file($file)) {
            return null;
        }
        $d = json_decode((string) file_get_contents($file), true);
        return is_array($d) ? $d : null;
    }

    private function writeJson($file, array $data)
    {
        $this->write($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
    }

    private function write($file, $content)
    {
        if ($this->dryRun) {
            $this->log[] = '[simular] gravaria ' . $this->rel($file);
            return;
        }
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }
        file_put_contents($file, $content);
    }

    private function copy($from, $to)
    {
        $this->write($to, (string) file_get_contents($from));
    }

    private function unlink($file)
    {
        if (!$this->dryRun) {
            @unlink($file);
        }
    }

    private function rename($from, $to)
    {
        if (!$this->dryRun) {
            rename($from, $to);
        }
    }

    private function replaceDir($from, $to, array $skip = [])
    {
        $this->removeDir($to);
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($it as $item) {
            $rel = substr(str_replace('\\', '/', $item->getPathname()), strlen($from));
            foreach ($skip as $s) {
                if (strpos($rel . '/', $s) !== false) {
                    continue 2;
                }
            }
            if ($item->isFile()) {
                $this->copy($item->getPathname(), $to . $rel);
            }
        }
    }

    private function removeDir($dir)
    {
        if (!is_dir($dir) || $this->dryRun) {
            return;
        }
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }

    private function rel($file)
    {
        return Runner::relative($this->target, str_replace('\\', '/', $file));
    }
}
