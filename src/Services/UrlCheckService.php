<?php

declare(strict_types=1);

namespace App\Services;

use App\DB\DatabaseConnector;
use App\Models\UrlCheck;
use App\Repositories\UrlCheckRepository;
use DateTimeImmutable;

class UrlCheckService extends Service
{
    public function __construct(protected readonly DatabaseConnector $connector)
    {
        $this->repository = new UrlCheckRepository($this->connector);
    }

    public function create(array $data): int
    {
        $model = (new UrlCheck())
            ->setUrlId($data['urlId'])
            ->setCreatedAt(new DateTimeImmutable());

        return $this->repository->save($model);
    }
}
