<?php

namespace TainacanStack\Check;

final class Finding
{
    const BLOCK = 'block';
    const WARN = 'warn';

    public $rule;
    public $severity;
    public $file;
    /** @var int linha principal; 0 = achado do arquivo inteiro */
    public $line;
    /** @var int última linha do trecho envolvido (para o modo diff) */
    public $endLine;
    public $message;
    public $hint;
    /** @var string conteúdo da linha, usado na impressão digital do baseline */
    public $snippet = '';

    public function __construct($rule, $severity, $file, $line, $message, $hint = null, $endLine = null)
    {
        $this->rule = $rule;
        $this->severity = $severity;
        $this->file = $file;
        $this->line = (int) $line;
        $this->endLine = $endLine === null ? (int) $line : max((int) $line, (int) $endLine);
        $this->message = $message;
        $this->hint = $hint;
    }

    public function fingerprint()
    {
        return sha1($this->rule . '|' . $this->file . '|' . preg_replace('/\s+/', ' ', trim($this->snippet)));
    }

    public function toArray()
    {
        return [
            'rule' => $this->rule,
            'severity' => $this->severity,
            'file' => $this->file,
            'line' => $this->line,
            'message' => $this->message,
            'hint' => $this->hint,
        ];
    }
}
