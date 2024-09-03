<?php

declare(strict_types=1);

namespace App\Services;

use App\DB\DatabaseConnector;
use App\Models\Model;
use App\Repositories\Repository;

abstract class Service
{
    protected Repository $repository;

    public function __construct(protected readonly DatabaseConnector $connector)
    {
    }

    abstract public function create(array $data): int;

    public function findById(int $id): ?Model
    {
        return $this->findByKey('id', $id);
    }

    public function findByKey(string $key, mixed $value): ?Model
    {
        return $this->repository->findOneBy($key, $value);
    }

    public function findAll(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        return $this->repository->findAll($orderBy, $direction);
    }

    public function findAllBy(array $criteria, array $orderBy = [], ?int $limit = null): array
    {
        return $this->repository->findAllBy($criteria, $orderBy, $limit);
    }
}
