<?php

namespace TainacanStack\Check;

/**
 * Base das regras. Cada regra declara as constantes abaixo e implementa
 * check(), registrando achados com report(). A explicação longa (sintoma,
 * causa, correção, origem) fica em licoes/<ID>.md.
 */
abstract class Rule
{
    const ID = '';
    const TITLE = '';
    const SEVERITY = Finding::WARN;
    /** base, seguranca, tainacan, desempenho, wordpress, convivencia */
    const CATEGORY = '';
    /** extensões atendidas */
    const EXTENSIONS = ['php'];

    /** @var string|null severidade definida pela configuração do projeto */
    private $override;

    /** @var Finding[] */
    private $found = [];

    public function __construct($override = null)
    {
        $this->override = $override;
    }

    public function applies(SourceFile $f)
    {
        return in_array($f->ext, static::EXTENSIONS, true);
    }

    /** @return Finding[] */
    public function run(SourceFile $f)
    {
        $this->found = [];
        $this->check($f);
        $out = $this->found;
        $this->found = [];
        return $out;
    }

    abstract protected function check(SourceFile $f);

    /**
     * @param string|null $severity severidade deste achado quando difere do padrão da regra
     */
    protected function report(SourceFile $f, $line, $message, $hint = null, $endLine = null, $severity = null)
    {
        $sev = $this->override ?: ($severity ?: static::SEVERITY);
        $finding = new Finding(static::ID, $sev, $f->path, $line, $message, $hint, $endLine);
        $finding->snippet = isset($f->lines[$line]) ? $f->lines[$line] : '';
        $this->found[] = $finding;
    }
}
