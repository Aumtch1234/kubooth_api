<?php
ini_set("display_errors",1);
ini_set("display_startup_errors",1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\App;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/web_app/api_booth');

// CORS Middleware
$app->add(function (Request $request, RequestHandler $handler): Response {
     // If it's an OPTIONS request, return the headers immediately
     if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withStatus(200);
    }

    // For non-OPTIONS requests, continue handling the request
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Handle OPTIONS requests
require __DIR__ .'/db.php';
require __DIR__ .'/api/events.php';
require __DIR__ .'/api/zones.php';
require __DIR__ .'/api/booths.php';
require __DIR__ .'/api/users.php';
require __DIR__ .'/api/booking.php';
require __DIR__ .'/api/approve.php';
require __DIR__ .'/api/report_data.php';

$app->run();
