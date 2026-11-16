<?php

namespace TainacanStack\Check;

/**
 * Análise de SQL destrutivo sobre tabelas do WordPress. Usada pela regra
 * TNC-P04 (código e arquivos .sql) e pelo hook que intercepta comandos de
 * terminal antes de rodarem (wp db query, mysql -e).
 */
final class SqlRisk
{
    /**
     * @return array<int,array{0:string,1:string}> lista de [severidade, mensagem]
     */
    public static function analyze($sql)
    {
        $out = [];
        $flat = preg_replace('/\s+/', ' ', $sql);
        // ponto e vírgula dentro de literal é raro em SQL de plugin; a divisão simples não estoura em texto longo
        $parts = preg_split('/;\s*/', (string) $flat);
        foreach ($parts === false ? [(string) $flat] : $parts as $stmt) {
            $s = trim($stmt);
            if ($s === '') {
                continue;
            }
            $lower = strtolower($s);
            if (!preg_match('/^\s*(update\s+[`\w{}$>\-\.]+\s+(as\s+\w+\s+)?(\w+\s+)?(inner\s+|left\s+)?(join\b.*?)?set\b|delete\s+(\w+\s+)?from\b|drop\s+table\b|truncate\b)/s', $lower)) {
                continue;
            }
            $core = self::coreTable($lower);
            $noWhere = $core ? Finding::BLOCK : Finding::WARN;
            $isMeta = (bool) preg_match('/(postmeta|termmeta|usermeta|commentmeta|\bmeta\b)/', $lower);
            $isOptions = strpos($lower, 'options') !== false;

            if (preg_match('/^\s*update\b/', $lower)) {
                $hasWhere = strpos($lower, ' where ') !== false;
                $replacesValue = (bool) preg_match('/(meta_value|option_value|post_content)\s*=\s*replace\s*\(/', $lower);
                if (!$hasWhere) {
                    $out[] = [$noWhere, 'UPDATE sem WHERE altera a tabela inteira' . ($core ? ' (tabela do WordPress).' : '; confirme que é intencional.')];
                    continue;
                }
                if ($isMeta && strpos($lower, 'meta_value') !== false && strpos($lower, 'meta_key') === false) {
                    $out[] = [Finding::BLOCK, 'UPDATE em meta_value sem filtro de meta_key: atinge valores serializados de outros plugins e do core (PHP serializado guarda o tamanho de cada string; trocar o texto corrompe a linha inteira).'];
                    continue;
                }
                if ($replacesValue) {
                    $out[] = [Finding::WARN, 'REPLACE() direto em ' . ($isOptions ? 'option_value' : 'meta_value/post_content') . ': se alguma linha for PHP serializado, o tamanho registrado deixa de bater e o valor é perdido.', ];
                }
            } elseif (preg_match('/^\s*delete\b/', $lower)) {
                if (strpos($lower, ' where ') === false) {
                    $out[] = [$noWhere, 'DELETE sem WHERE apaga a tabela inteira' . ($core ? ' (tabela do WordPress).' : '; confirme que é intencional.')];
                } elseif ($isMeta && strpos($lower, 'meta_key') === false && strpos($lower, 'meta_id') === false && strpos($lower, 'post_id') === false) {
                    $out[] = [Finding::WARN, 'DELETE em tabela de meta sem meta_key, meta_id nem post_id no filtro.'];
                }
            } elseif (preg_match('/^\s*(drop|truncate)\b/', $lower) && $core) {
                $out[] = [Finding::BLOCK, strtoupper(strtok(trim($s), ' ')) . ' em tabela do WordPress.'];
            } elseif (preg_match('/^\s*(drop|truncate)\b/', $lower) && preg_match('/(tainacan_logs|tnc_bg_process)/', $lower)) {
                $out[] = [Finding::BLOCK, strtoupper(strtok(trim($s), ' ')) . ' em tabela do núcleo do Tainacan.'];
            }
        }
        return $out;
    }

    /** A tabela alvo (logo após UPDATE / DELETE FROM / TABLE) é do WordPress? */
    private static function coreTable($lower)
    {
        if (!preg_match('/^\s*(?:update|delete\s+(?:\w+\s+)?from|drop\s+table(?:\s+if\s+exists)?|truncate(?:\s+table)?)\s+([^\s(]+)/', $lower, $m)) {
            return false;
        }
        $t = trim($m[1], '`{}');
        return (bool) preg_match('/(^|_|->)(posts|postmeta|options|users|usermeta|terms|term_taxonomy|term_relationships|termmeta|comments|commentmeta|links|sitemeta|blogs)$/', $t);
    }
}
