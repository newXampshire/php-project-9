<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DB\DatabaseConnector;
use App\Helpers\SnakeCamelCaseConverterHelper;
use App\Models\Model;
use DateTimeImmutable;
use ReflectionClass;

abstract class Repository
{
    protected const TABLE_NAME = '';
    protected const MODEL_NAME = '';

    public function __construct(private readonly DatabaseConnector $connector)
    {
    }

    public function findAll(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        return $this->connector->executeWithConversionToClass(
            static::MODEL_NAME,
            "SELECT * FROM " . static::TABLE_NAME . " ORDER BY $orderBy $direction",
            [],
            true
        );
    }

    public function findBy(string $key, mixed $value): ?Model
    {
        return $this->connector->executeWithConversionToClass(
            static::MODEL_NAME,
            "SELECT * FROM " . static::TABLE_NAME . " WHERE $key = :key",
            [':key' => $value],
        );
    }

    public function save(Model $model): int
    {
        $columns = [];
        $values = [];

        $reflection = new ReflectionClass($model::class);
        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();
            if ($name === 'id') {
                continue;
            }

            $method = 'get' . ucfirst($name);
            $value = $model->$method();
            if (!$value) {
                continue;
            }

            if ($value instanceof DateTimeImmutable) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $columns[] = SnakeCamelCaseConverterHelper::convertToSnakeCase($name);
            $values[':' . $name] = $value;
        }

        $sql =
            'INSERT INTO ' . static::TABLE_NAME . '(' . implode(',', $columns) . ')
            VALUES (' . implode(',', array_keys($values)) . ')';

        return $this->connector->insert($sql, $values);
    }
}
