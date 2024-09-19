<?php

use App\Controller\ApiController;
use App\Controller\HomeController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';


// Instantiate Slim App:
$app = AppFactory::create();

// Define routes:
$app->get('/', [ HomeController::class, 'index' ]);
$app->get('/weatherdataHtml', [ HomeController::class, 'getWeatherDataHtml' ]);

$app->get('/api/getWeather', [ ApiController::class, 'getWeather' ]);

/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (RequestInterface $request, ResponseInterface $response) {
    $response = $response->withStatus(404);
    $response->getBody()->write('404 not found');
    return $response;
});

// Run application:
$app->run();
