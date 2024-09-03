<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\UrlCheck;

class UrlCheckRepository extends Repository
{
    protected const TABLE_NAME = 'url_checks';
    protected const MODEL_NAME = UrlCheck::class;
}
