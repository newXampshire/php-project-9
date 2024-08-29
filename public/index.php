<?php

use App\DB\DatabaseConnector;
use App\Services\UrlService;
use App\Validators\UrlValidator;
use DI\Container;
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

    $params = [
        'url' => $url,
        'message' => $this->get(Messages::class)->getFirstMessage('success')
    ];

    return $this->get(Twig::class)->render($response, 'url-one.twig', $params);
})->setName('url-one');

$app->get('/urls', function (Request $request, Response $response) {
    $urls = $this->get(UrlService::class)->findAll();

    return $this->get(Twig::class)->render($response, 'url-list.twig', ['urls' => $urls]);
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
        $urlId = $urlService->create($urlName);
        $this->get(Messages::class)->addMessage('success', 'Страница успешно добавлена');
        return $response->withRedirect($router->urlFor('url-one', ['id' => $urlId]), 302);
    }

    $params = [
        'name' => $urlName,
        'errors' => $errors
    ];

    return $this->get(Twig::class)->render($response, 'index.twig', $params)->withStatus(422);
})->setName('url-create');

$app->run();
