<?php

declare(strict_types=1);

namespace App\Helpers;

class SnakeCamelCaseConverterHelper
{
    public static function convertToSnakeCase(string $camelCaseString): string
    {
        // @phpstan-ignore-next-line
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseString));
    }

    public static function convertToCamelCase(string $snakeCaseString): string
    {
        return str_replace("_", "", ucwords($snakeCaseString, " /_"));
    }
}
