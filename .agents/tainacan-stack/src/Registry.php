<?php

namespace TainacanStack\Check;

/** Lista das regras de arquivo. Para criar uma regra, veja skills/tnc-registrar-licao. */
final class Registry
{
    /** @return string[] nomes de classe */
    public static function ruleClasses()
    {
        return [
            Rules\PhpLint::class,
            Rules\ShortOpenTag::class,
            Rules\Encoding::class,
            Rules\DebugLeftovers::class,
            Rules\DirectAccessGuard::class,
            Rules\TemplatePartMissing::class,
            Rules\CallbackMethodMissing::class,
            Rules\MutationObserverLoop::class,
            Rules\WpdbUnprepared::class,
            Rules\SuperglobalUnsanitized::class,
            Rules\HandlerWithoutNonce::class,
            Rules\RestPermission::class,
            Rules\DangerousCalls::class,
            Rules\HardcodedSecret::class,
            Rules\UnboundedQuery::class,
            Rules\QueryInLoop::class,
            Rules\MetaQueryLike::class,
            Rules\DestructiveSql::class,
            Rules\CronWithoutGuard::class,
            Rules\TextDomain::class,
            Rules\JsUnsafeDom::class,
            Rules\SharedHookRemoval::class,
            Rules\CssGlobalSelector::class,
            Rules\WordPressPitfalls::class,
            Rules\PreparePlaceholders::class,
            Rules\LocalizeScriptScalars::class,
            Rules\AdminHookOutput::class,
            Rules\AdminHookInlineScript::class,
            Rules\AdminHookRegistration::class,
            Rules\MetadatumOptionKey::class,
            Rules\TainacanRepositoryUsage::class,
            Rules\FacetedSearchItemHook::class,
            Rules\TainacanQueryParams::class,
            Rules\TainacanPagesLoad::class,
            Rules\TainacanHookUsage::class,
            Rules\OutputEscaping::class,
            Rules\PhpcsSuppression::class,
            Rules\WordPressAlternatives::class,
            Rules\ExternalAssets::class,
            Rules\I18nPlaceholders::class,
        ];
    }

    /**
     * @param string[]|null $only IDs pedidos em --rules
     * @return Rule[]
     */
    public static function rules(?ProjectContext $project = null, ?array $only = null)
    {
        $out = [];
        foreach (self::ruleClasses() as $class) {
            $id = $class::ID;
            if ($only !== null && !self::matches($id, $only)) {
                continue;
            }
            $setting = $project ? $project->ruleSetting($id) : null;
            if ($setting === 'off') {
                continue;
            }
            $override = in_array($setting, [Finding::WARN, Finding::BLOCK], true) ? $setting : null;
            $out[] = new $class($override);
        }
        return $out;
    }

    /** Aceita ID exato ou prefixo (TNC-S, TNC-T). */
    private static function matches($id, array $only)
    {
        foreach ($only as $o) {
            $o = strtoupper(trim($o));
            if ($o === $id || ($o !== '' && strpos($id, $o) === 0)) {
                return true;
            }
        }
        return false;
    }

    /** @return array<int,array<string,string>> */
    public static function describe()
    {
        $out = [];
        foreach (self::ruleClasses() as $class) {
            $out[] = [
                'id' => $class::ID,
                'title' => $class::TITLE,
                'severity' => $class::SEVERITY,
                'category' => $class::CATEGORY,
                'extensions' => implode(',', $class::EXTENSIONS),
            ];
        }
        usort($out, function ($a, $b) {
            return strcmp($a['id'], $b['id']);
        });
        return $out;
    }
}
