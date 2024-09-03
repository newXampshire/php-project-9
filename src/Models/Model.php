<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\SnakeCamelCaseConverterHelper;
use DateTimeImmutable;

abstract class Model
{
    public function __set(string $name, mixed $value): void
    {
        $method = $this->generateMethodName($name);

        $this->$method($this->prepareValue($value));
    }

    protected function generateMethodName(string $name): string
    {
        return 'set' . SnakeCamelCaseConverterHelper::convertToCamelCase($name);
    }

    protected function prepareValue(mixed $value): mixed
    {
        if (is_string($value) && strtotime($value)) {
            $value = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        }

        return $value;
    }
}
