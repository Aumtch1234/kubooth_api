<?php
ini_set("display_errors",1);
ini_set("display_startup_errors",1);

// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/web_app/api_booth');

require __DIR__ .'/db.php';
require __DIR__ .'/api/events.php';
require __DIR__ .'/api/zones.php';
require __DIR__ .'/api/booths.php';
require __DIR__ .'/api/users.php';

$app->run();