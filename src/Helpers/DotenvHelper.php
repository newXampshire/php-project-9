<?php

declare(strict_types=1);

namespace App\Helpers;

use Dotenv\Dotenv;

class DotenvHelper
{
    public static function loadEnvFiles(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');

        $dotenv->safeLoad();
    }
}
