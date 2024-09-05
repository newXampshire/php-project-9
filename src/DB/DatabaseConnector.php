<?php

declare(strict_types=1);

namespace App\DB;

use App\Helpers\DotenvHelper;
use PDO;

class DatabaseConnector
{
    private ?PDO $connection;

    public function __construct()
    {
        DotenvHelper::loadEnvFiles();

        $databaseUrl = parse_url(getenv('DATABASE_URL')); // @phpstan-ignore-line
        $provider = $databaseUrl['scheme'];
        $username = $databaseUrl['user'];
        $password = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $port = $databaseUrl['port'];
        $dbName = ltrim($databaseUrl['path'], '/');

        $provider = 'pgsql';

        $dsn = "$provider:host=$host;port=$port;dbname=$dbName;user=$username;password=$password";
        $this->connection = new PDO($dsn);
    }

    public function insert(string $query, array $params = []): int
    {
        $this->execute($query, $params);

        return (int)$this->connection?->lastInsertId();
    }

    public function execute(string $query, array $params = [], bool $multiple = false): mixed
    {
        $stmt = $this->connection?->prepare($query);
        if (!$stmt) {
            return null;
        }

        $result = $stmt->execute($params);
        if ($result === false) {
            return null;
        }

        return $multiple ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function executeWithConversionToClass(
        string $className,
        string $query,
        array $params = [],
        bool $multiple = false
    ): mixed {
        $stmt = $this->connection?->prepare($query);
        if (!$stmt) {
            return null;
        }

        $result = $stmt->execute($params);
        if ($result === false) {
            return null;
        }

        $stmt->setFetchMode(PDO::FETCH_CLASS, $className);

        return $multiple ? $stmt->fetchAll() : ($stmt->fetch() ?: null);
    }
}
