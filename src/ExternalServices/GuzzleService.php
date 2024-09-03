<?php

declare(strict_types=1);

namespace App\ExternalServices;

use GuzzleHttp\Client;

class GuzzleService
{
    public function get(string $url): array
    {
        $client = new Client();

        $result = $client->request('GET', $url, ['timeout' => 20]);

        return [
            'statusCode' => $result->getStatusCode(),
            'body' => $result->getBody()->getContents()
        ];
    }
}
