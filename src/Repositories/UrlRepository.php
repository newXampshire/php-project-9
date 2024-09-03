<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Url;

class UrlRepository extends Repository
{
    protected const TABLE_NAME = 'urls';
    protected const MODEL_NAME = Url::class;

    protected const URL_CHECK_TABLE_NAME = 'url_checks';

    public function findAll(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $sql =
            "SELECT u.id, u.name, uc.status_code as 'uc.status_code', uc.created_at as 'uc.created_at'
             FROM " . static::TABLE_NAME . " u
             LEFT JOIN (
                 SELECT uc2.url_id, uc2.status_code, uc2.created_at
                 FROM " . self::URL_CHECK_TABLE_NAME . " uc2
                 INNER JOIN (
                    SELECT url_id, MAX(created_at) AS created_at
                    FROM " . self::URL_CHECK_TABLE_NAME . "
                    GROUP BY url_id
                 ) last
                 ON last.url_id = uc2.url_id AND last.created_at = uc2.created_at
             ) uc
             ON u.id = uc.url_id
             ORDER BY u.id DESC";

        return $this->connector->executeWithConversionToClass(
            static::MODEL_NAME,
            $sql,
            [],
            true
        );
    }
}
