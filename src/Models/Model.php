<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\SnakeCamelCaseConverterHelper;
use DateTimeImmutable;

abstract class Model
{
    public function __set(string $name, $value): void
    {
        $method = 'set' . SnakeCamelCaseConverterHelper::convertToCamelCase($name);

        if (is_string($value) && strtotime($value)) {
            $value = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        }

        $this->$method($value);
    }
}
