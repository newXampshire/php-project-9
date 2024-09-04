<?php

declare(strict_types=1);

namespace App\Services;

use App\DB\DatabaseConnector;
use App\Models\Url;
use App\Repositories\UrlRepository;
use DateTimeImmutable;

class UrlService
{
    private UrlRepository $repository;

    public function __construct(private readonly DatabaseConnector $connector)
    {
        $this->repository = new UrlRepository($this->connector);
    }

    public function findById(int $id): ?Url
    {
        return $this->findByKey('id', $id);
    }

    public function findByKey(string $key, mixed $value): ?Url
    {
        return $this->repository->findBy($key, $value);
    }

    public function findAll(): array
    {
        return $this->repository->findAll('id', 'DESC');
    }

    public function create(string $name): int
    {
        $model = new Url();
        $model->setName($name);
        $model->setCreatedAt(new DateTimeImmutable());

        return $this->repository->save($model);
    }
}
