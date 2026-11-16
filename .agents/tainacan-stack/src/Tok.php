<?php
/**
 * Constantes de token que só existem em algumas versões do PHP.
 * Valores negativos nunca coincidem com tokens reais.
 */

namespace TainacanStack\Check;

final class Tok
{
    const NAME_QUALIFIED = \PHP_VERSION_ID >= 80000 ? \T_NAME_QUALIFIED : -101;
    const NAME_FULLY_QUALIFIED = \PHP_VERSION_ID >= 80000 ? \T_NAME_FULLY_QUALIFIED : -102;
    const NAME_RELATIVE = \PHP_VERSION_ID >= 80000 ? \T_NAME_RELATIVE : -103;
    const NULLSAFE = \PHP_VERSION_ID >= 80000 ? \T_NULLSAFE_OBJECT_OPERATOR : -104;
    const ATTRIBUTE = \PHP_VERSION_ID >= 80000 ? \T_ATTRIBUTE : -105;
    const ENUM = \PHP_VERSION_ID >= 80100 ? \T_ENUM : -106;
    const READONLY = \PHP_VERSION_ID >= 80100 ? \T_READONLY : -107;
}
