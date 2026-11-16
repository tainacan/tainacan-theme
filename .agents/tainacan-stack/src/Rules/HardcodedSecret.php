<?php

namespace TainacanStack\Check\Rules;

use TainacanStack\Check\Finding;
use TainacanStack\Check\Rule;
use TainacanStack\Check\SourceFile;

/**
 * TNC-S08: credencial no código: chaves com formato conhecido (Anthropic,
 * OpenAI, GitHub, AWS, Google, Slack, chave privada) e atribuições de
 * senha/token/segredo a literal que não parece exemplo.
 */
final class HardcodedSecret extends Rule
{
    const ID = 'TNC-S08';
    const TITLE = 'Credencial gravada no código';
    const SEVERITY = Finding::BLOCK;
    const CATEGORY = 'seguranca';
    const EXTENSIONS = ['php', 'inc', 'js', 'jsx', 'ts', 'tsx', 'vue', 'json', 'yml', 'yaml', 'env', 'ini', 'sh', 'py', 'md', 'txt', 'xml', 'conf', 'sql'];

    const KNOWN = [
        'chave Anthropic' => '/\bsk-ant-[A-Za-z0-9_\-]{20,}/',
        'chave OpenAI' => '/\bsk-(proj-)?[A-Za-z0-9_\-]{32,}/',
        'token GitHub' => '/\b(ghp|gho|ghu|ghs|ghr)_[A-Za-z0-9]{30,}|\bgithub_pat_[A-Za-z0-9_]{40,}/',
        'chave AWS' => '/\bAKIA[0-9A-Z]{16}\b/',
        'chave Google' => '/\bAIza[0-9A-Za-z_\-]{35}\b/',
        'token Slack' => '/\bxox[baprs]-[A-Za-z0-9\-]{10,}/',
        'chave privada' => '/-----BEGIN (RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----/',
        'senha em URL' => '#[a-z][a-z0-9+.-]*://[^/\s:@\'"]{1,64}:[^/\s@\'"]{4,}@[a-z0-9.-]+#i',
    ];

    const ASSIGN = '/(?:\$|[\'"]|\b)(?:[a-z0-9_]*?)(password|passwd|senha|secret|segredo|api_?key|apikey|access_?token|auth_?token|client_secret|private_key)[a-z0-9_]*[\'"]?\s*(?:=>|=|:)\s*([\'"])([^\'"\s]{8,})\2/i';

    const PLACEHOLDER = '/^(x+|\*+|\.+|changeme|change_me|your|sua|seu|example|exemplo|placeholder|dummy|test|teste|senha|password|secret|null|none|xxx|<|\{|\$|%|__)/i';

    protected function check(SourceFile $f)
    {
        if (preg_match('#(^|/)(tests?|fixtures|examples?|exemplos?)/#', $f->path) || preg_match('/\.(example|sample|dist)(\.|$)/', $f->path)) {
            return;
        }
        foreach ($f->lines as $n => $line) {
            if (strlen($line) > 2000) {
                continue; // linha minificada
            }
            foreach (self::KNOWN as $label => $re) {
                if (preg_match($re, $line, $hit)) {
                    // usuario:senha@host em documentação é exemplo, não credencial
                    if ($label === 'senha em URL' && preg_match('#://([^:]+):([^@]+)@#', $hit[0], $cred)
                        && (preg_match(self::PLACEHOLDER, $cred[1]) || preg_match(self::PLACEHOLDER, $cred[2]) || preg_match('/^(user|usuario|usuário|login|admin|elastic|root|senha|pass|password|secret|token|key|chave)$/i', $cred[2]))) {
                        continue;
                    }
                    $this->report($f, $n, 'Possível ' . $label . ' no código.', 'Remova do código e do histórico do git, revogue a chave e leia de variável de ambiente ou opção protegida.');
                    continue 2;
                }
            }
            if (preg_match(self::ASSIGN, $line, $m)) {
                $value = $m[3];
                if (preg_match(self::PLACEHOLDER, $value) || preg_match('/^[a-z_]+$/', $value) || preg_match('/^[A-Z][A-Z0-9_]+$/', $value) || preg_match('/[&=?\/]/', $value) || preg_match('/^(get_option|getenv|defined|constant)/', $value)) {
                    continue;
                }
                if (preg_match('/__\(|_e\(|esc_html__|label|placeholder|description|nonce|_field|_name\b|_key\'?\s*=>\s*\'[a-z_]+\'/i', $line)) {
                    continue;
                }
                $this->report($f, $n, 'Valor literal atribuído a `' . $m[1] . '`: parece credencial.', 'Leia de variável de ambiente, constante do wp-config.php ou opção; se for exemplo, use um marcador evidente.', null, Finding::WARN);
            }
        }
    }
}
