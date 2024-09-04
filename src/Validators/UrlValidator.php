<?php

declare(strict_types=1);

namespace App\Validators;

class UrlValidator implements ValidatorInterface
{
    public function validate(mixed $url): array
    {
        if (empty($url)) {
            return ['URL не должен быть пустым'];
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false || strlen($url) > 255) {
            return ['Некорректный URL'];
        }

        return [];
    }
}
