<?php

namespace TainacanStack\Check;

/**
 * Modo hook: lê o JSON do evento no stdin (formato do Claude Code, também
 * aceito pelo Codex, Copilot CLI e Cursor) e responde com o código de
 * saída comum a todas as ferramentas: 0 segue, 2 bloqueia, com o motivo no
 * stderr.
 *
 *   post-edit       depois de Edit/Write/MultiEdit/apply_patch: roda as
 *                   regras nas linhas alteradas do arquivo editado.
 *   pre-bash        antes de comando de terminal: barra SQL destrutivo e
 *                   atalhos que pulam as verificações (--no-verify).
 *   pre-powershell  antes de comando PowerShell: barra regravação de
 *                   arquivo de código com Set-Content/Out-File (PS 5.1
 *                   troca a codificação e acrescenta BOM).
 */
final class Hooks
{
    public static function run($kind, ?array $only = null)
    {
        $raw = stream_get_contents(STDIN);
        $event = json_decode((string) $raw, true);
        if (!is_array($event)) {
            return 0; // nunca travar a ferramenta por entrada inesperada
        }
        $input = isset($event['tool_input']) && is_array($event['tool_input']) ? $event['tool_input'] : [];
        switch ($kind) {
            case 'post-edit':
                return self::postEdit($event, $input, $only);
            case 'pre-bash':
                return self::preShell(self::command($input));
            case 'pre-powershell':
                return self::prePowerShell(self::command($input));
        }
        fwrite(STDERR, "tnc-check: hook desconhecido '$kind'.\n");
        return 0;
    }

    private static function command(array $input)
    {
        foreach (['command', 'cmd', 'script'] as $k) {
            if (isset($input[$k]) && is_string($input[$k])) {
                return $input[$k];
            }
        }
        return '';
    }

    /** @return string[] caminhos absolutos dos arquivos tocados pela ferramenta */
    private static function editedFiles(array $event, array $input)
    {
        $files = [];
        foreach (['file_path', 'path', 'filePath', 'notebook_path'] as $k) {
            if (!empty($input[$k]) && is_string($input[$k])) {
                $files[] = $input[$k];
            }
        }
        // apply_patch (Codex): "*** Update File: caminho" / "*** Add File: caminho"
        foreach (['input', 'patch', 'command'] as $k) {
            if (!empty($input[$k]) && is_string($input[$k]) && preg_match_all('/^\*\*\* (?:Update|Add) File: (.+)$/m', $input[$k], $m)) {
                foreach ($m[1] as $p) {
                    $files[] = trim($p);
                }
            }
        }
        $cwd = isset($event['cwd']) && is_string($event['cwd']) ? $event['cwd'] : getcwd();
        $out = [];
        foreach (array_unique($files) as $p) {
            $abs = preg_match('#^([A-Za-z]:[\\\\/]|/)#', $p) ? $p : rtrim($cwd, '/\\') . '/' . $p;
            if (is_file($abs)) {
                $out[] = str_replace('\\', '/', $abs);
            }
        }
        return $out;
    }

    private static function postEdit(array $event, array $input, ?array $only = null)
    {
        $exts = array_flip(Runner::extensions());
        $runner = new Runner($only);
        $findings = [];
        foreach (self::editedFiles($event, $input) as $abs) {
            if (!isset($exts[strtolower(pathinfo($abs, PATHINFO_EXTENSION))])) {
                continue;
            }
            $top = Git::toplevel(dirname($abs));
            $relTop = null;
            if ($top !== null) {
                // o git dá o caminho relativo sem depender de 8.3/maiúsculas/links no Windows
                $relTop = trim(Git::run(dirname($abs), 'rev-parse --show-prefix')) . basename($abs);
                $abs = $top . '/' . $relTop;
            }
            $root = ProjectContext::findRoot($abs);
            $project = ProjectContext::forRoot($root);
            $rel = Runner::relative($project->root, $abs);
            if ($project->isExcluded($rel)) {
                continue;
            }
            $entry = ['rel' => $rel, 'abs' => $abs, 'project' => $project];
            if ($top !== null) {
                $changes = Git::changes($top, 'worktree', [$relTop]);
                if (isset($changes[$relTop])) {
                    $entry['lines'] = $changes[$relTop]['lines'];
                    $entry['new'] = $changes[$relTop]['new'];
                } elseif (trim(Git::run($top, 'ls-files -- ' . escapeshellarg($relTop))) !== '') {
                    continue; // rastreado e sem diferença: nada a dizer
                }
            }
            $findings = array_merge($findings, $runner->checkEntry($entry));
        }
        if (!$findings) {
            return 0;
        }
        $text = Reporter::render($findings, 'text', ['files' => $runner->filesAnalyzed, 'mode' => 'hook']);
        if (Reporter::verdict($findings) === 'BLOCK') {
            fwrite(STDERR, "tnc-check bloqueou esta edição. Corrija antes de seguir:\n" . $text);
            return 2;
        }
        // avisos: entram como contexto para o agente, sem bloquear
        echo json_encode([
            'hookSpecificOutput' => [
                'hookEventName' => 'PostToolUse',
                'additionalContext' => "tnc-check encontrou avisos nas linhas editadas (corrija ou justifique com tnc-check-ignore):\n" . $text,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
        return 0;
    }

    private static function preShell($cmd)
    {
        if ($cmd === '') {
            return 0;
        }
        $reasons = [];
        // Só as opções contam: texto entre aspas e corpo de heredoc (a mensagem
        // do commit) pode citar "-n" ou "--no-verify" sem que o comando os use.
        if (preg_match('/\bgit\b[^|;&]*\b(commit|push|merge|rebase)\b[^|;&]*--no-verify\b|\bgit\b[^|;&]*\bcommit\b[^|;&]*\s-n\b/', self::withoutQuoted($cmd))) {
            $reasons[] = '`--no-verify` pula o gancho pre-commit do tnc-check. Corrija o achado ou justifique com tnc-check-ignore; se for mesmo necessário, peça ao responsável para rodar o comando.';
        }
        if (preg_match('/\bgit\s+push\b[^|;&]*(--force\b|-f\b|--force-with-lease\b)[^|;&]*\b(main|master|develop|trunk)\b/', $cmd)) {
            $reasons[] = 'push forçado em branch compartilhada (main/master/develop) reescreve o histórico da equipe.';
        }
        if (self::looksLikeSql($cmd)) {
            foreach (SqlRisk::analyze(self::extractSql($cmd)) as $risk) {
                if ($risk[0] === Finding::BLOCK) {
                    $reasons[] = $risk[1] . ' Faça backup da tabela e filtre por meta_key; para troca de texto use `wp search-replace --dry-run` primeiro.';
                }
            }
        }
        if (preg_match('/\bwp\s+search-replace\b/', $cmd) && !preg_match('/--dry-run|--export/', $cmd) && preg_match('/\b(--all-tables|wp_posts|wp_postmeta|postmeta)\b/', $cmd)) {
            $reasons[] = '`wp search-replace` sem `--dry-run` em tabelas de conteúdo. Rode primeiro com --dry-run e confira a contagem.';
        }
        if (!$reasons) {
            return 0;
        }
        fwrite(STDERR, "tnc-check bloqueou o comando:\n- " . implode("\n- ", array_unique($reasons)) . "\n");
        return 2;
    }

    /**
     * O comando sem o conteúdo citado: corpos de heredoc e trechos entre aspas
     * simples ou duplas viram vazio, para que regras sobre opções não leiam o
     * texto de uma mensagem (ex.: `git commit -m "trata grep -n"`).
     */
    private static function withoutQuoted($cmd)
    {
        // heredoc: <<EOF, <<'EOF', <<"EOF", <<-EOF ... até a linha só com o delimitador
        $cmd = preg_replace('/<<-?\s*([\'"]?)([A-Za-z_][A-Za-z0-9_]*)\1[^\n]*\n.*?\n[ \t]*\2[ \t]*(?=\n|$)/s', '<<HEREDOC', (string) $cmd);
        // aspas duplas (com escape) e simples (sem escape, como no shell)
        return (string) preg_replace('/"(?:[^"\\\\]|\\\\.)*"|\'[^\']*\'/s', '""', (string) $cmd);
    }

    private static function looksLikeSql($cmd)
    {
        return (bool) preg_match('/\b(wp\s+db\s+(query|cli)|mysql|mariadb|psql|\$wpdb->query|wp\s+eval)\b/i', $cmd)
            && (bool) preg_match('/\b(update|delete|drop|truncate)\b/i', $cmd);
    }

    private static function extractSql($cmd)
    {
        // pega o conteúdo entre aspas, que é onde o SQL costuma estar
        if (preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"|\'((?:[^\'\\\\]|\\\\.)*)\'/s', $cmd, $m, PREG_SET_ORDER)) {
            $parts = [];
            foreach ($m as $hit) {
                $parts[] = isset($hit[2]) && $hit[2] !== '' ? $hit[2] : $hit[1];
            }
            return implode(";\n", $parts);
        }
        return $cmd;
    }

    private static function prePowerShell($cmd)
    {
        if ($cmd === '') {
            return 0;
        }
        $code = '\.(php|inc|js|jsx|ts|tsx|vue|css|scss|json|md|po|pot|txt|html|xml|yml|yaml|sql)\b';
        if (preg_match('/\b(Set-Content|Add-Content|Out-File)\b/i', $cmd) && preg_match('/' . $code . '/i', $cmd)) {
            $msg = 'No Windows PowerShell 5.1, Set-Content/Add-Content/Out-File gravam em ANSI ou em UTF-8 com BOM: acentos viram "Ã§" e PHP com BOM quebra cabeçalhos HTTP.'
                . ' Use a ferramenta de edição do agente, ou [IO.File]::WriteAllText($caminho, $texto, (New-Object Text.UTF8Encoding $false)).';
            fwrite(STDERR, "tnc-check bloqueou o comando:\n- " . $msg . "\n");
            return 2;
        }
        return self::preShell($cmd);
    }
}
