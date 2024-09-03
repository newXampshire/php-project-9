<?php

declare(strict_types=1);

namespace App\Services;

use App\DB\DatabaseConnector;
use App\ExternalServices\GuzzleService;
use App\Models\Url;
use App\Models\UrlCheck;
use App\Repositories\UrlCheckRepository;
use DateTimeImmutable;
use DOMDocument;
use DOMXPath;

class UrlCheckService extends Service
{
    public function __construct(
        protected readonly DatabaseConnector $connector,
        private readonly GuzzleService $guzzleService,
    ) {
        $this->repository = new UrlCheckRepository($this->connector);
    }

    public function create(array $data): int
    {
        /** @var Url $url */
        $url = $data['url'];

        $check = $this->guzzleService->get($url->getName());
        $body = $this->parseBody($check['body']);

        $model = (new UrlCheck())
            ->setUrlId($url->getId())
            ->setStatusCode((string)$check['statusCode'])
            ->setH1($body['h1'])
            ->setTitle($body['title'])
            ->setDescription($body['description'])
            ->setCreatedAt(new DateTimeImmutable());

        return $this->repository->save($model);
    }

    private function parseBody(string $body): array
    {
        $dom = new DOMDocument();
        $dom->loadHTML($body);

        $domXpath = new DOMXPath($dom);

        return [
            'h1' => $dom->getElementsByTagName('h1')->item(0)->nodeValue,
            'title' => $dom->getElementsByTagName('title')->item(0)->nodeValue,
            'description' => $domXpath
                ->query('//meta[@name="description"]')
                ->item(0)
                ->attributes
                ?->getNamedItem('content')
                ->nodeValue,
        ];
    }
}
