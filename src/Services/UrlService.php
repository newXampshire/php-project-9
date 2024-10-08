<?php

declare(strict_types=1);

namespace App\Services;

use App\DB\DatabaseConnector;
use App\Models\Url;
use App\Repositories\UrlRepository;
use DateTimeImmutable;

class UrlService extends Service
{
    public function __construct(protected readonly DatabaseConnector $connector)
    {
        $this->repository = new UrlRepository($this->connector);
    }

    public function create(array $data): int
    {
        $model = (new Url())
            ->setName($data['name'])
            ->setCreatedAt(new DateTimeImmutable());

        return $this->repository->save($model);
    }
}
