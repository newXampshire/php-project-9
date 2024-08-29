<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Url;

class UrlRepository extends Repository
{
    protected const TABLE_NAME = 'urls';
    protected const MODEL_NAME = Url::class;
}
