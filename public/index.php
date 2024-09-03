<?php

use App\DB\DatabaseConnector;
use App\ExternalServices\GuzzleService;
use App\Models\Url;
use App\Services\UrlCheckService;
use App\Services\UrlService;
use App\Validators\UrlValidator;
use DI\Container;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = new Container();

$container->set(Twig::class, Twig::create(__DIR__ . '/../templates'));
$container->set(Messages::class, new Messages());

$connector = new DatabaseConnector();
$container->set(DatabaseConnector::class, $connector);
$container->set(UrlService::class, new UrlService($connector));
$container->set(UrlCheckService::class, new UrlCheckService($connector, new GuzzleService()));

$app = AppFactory::createFromContainer($container);
$app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response) {
    return $this->get(Twig::class)->render($response, 'index.twig');
})->setName('index');

$app->get('/urls/{id}', function (Request $request, Response $response, array $args) {
    $url = $this->get(UrlService::class)->findById($args['id']);
    if ($url === null) {
        return $this->get(Twig::class)->render($response, '404.twig')->withStatus(404);
    }

    $checks = $this->get(UrlCheckService::class)->findAllBy(
        ['url_id' => $url->getId()],
        ['created_at' => 'DESC']
    );

    $params = [
        'url' => $url,
        'checks' => $checks,
        'messages' => $this->get(Messages::class)->getMessages()
    ];

    return $this->get(Twig::class)->render($response, 'url-one.twig', $params);
})->setName('url-one');

$app->get('/urls', function (Request $request, Response $response) {
    $params = [
        'urls' => $this->get(UrlService::class)->findAll(),
    ];

    return $this->get(Twig::class)->render($response, 'url-list.twig', $params);
})->setName('url-list');

$app->post('/urls', function (Request $request, Response $response) use ($router) {
    $urlName = $request->getParsedBody()['url']['name'];

    /** @var UrlService $urlService */
    $urlService = $this->get(UrlService::class);

    $url = $urlService->findByKey('name', $urlName);
    if ($url !== null) {
        $this->get(Messages::class)->addMessage('success', 'Страница уже существует');
        return $response->withRedirect($router->urlFor('url-one', ['id' => $url->getId()]), 302);
    }

    $validator = new UrlValidator();

    $errors = $validator->validate($urlName);
    if (count($errors) === 0) {
        $urlId = $urlService->create(['name' => $urlName]);
        $this->get(Messages::class)->addMessage('success', 'Страница успешно добавлена');
        return $response->withRedirect($router->urlFor('url-one', ['id' => $urlId]), 302);
    }

    $params = [
        'name' => $urlName,
        'errors' => $errors
    ];

    return $this->get(Twig::class)->render($response, 'index.twig', $params)->withStatus(422);
})->setName('url-create');

$app->post('/urls/{url_id}/checks', function (Request $request, Response $response, array $args) use ($router) {
    /** @var Url $url */
    $url = $this->get(UrlService::class)->findById($args['url_id']);
    if ($url === null) {
        return $this->get(Twig::class)->render($response, '404.twig')->withStatus(404);
    }

    $params = ['id' => $url->getId()];
    $messages = $this->get(Messages::class);

    /** @var UrlCheckService $urlCheckService */
    $urlCheckService = $this->get(UrlCheckService::class);

    try {
        $urlCheckService->create(['url' => $url]);
        $messages->addMessage('success', 'Страница успешно проверена');
    } catch (RequestException) {
        $messages->addMessage('warning', 'Проверка была выполнена успешно, но сервер ответил с ошибкой');
    } catch (ConnectException) {
        $messages->addMessage('danger', 'Произошла ошибка при проверке, не удалось подключиться');
    }

    return $response->withRedirect($router->urlFor('url-one', $params), 302);
})->setName('url-check-create');

$app->run();
