<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__. DIRECTORY_SEPARATOR .'..'.DIRECTORY_SEPARATOR);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('<h1>Hello world?</h1>');

    return $response;
});

$app->get('/otp/verify-otp', function (Request $request, Response $response) {
    $input = $request->getQueryParams();

    $rut = $input['rut'] ?? null;

    if (empty($rut)) {
        $response->getBody()->write(json_encode([
            'status' => 500,
            'body' => '"ERROR: El formato del RUT: @!rut es inv\\u00e1lido."',
        ]));

    } else {
        $defaultStatus = $_ENV['DEFAULT_STATUS'] ?? 'verified';

        $otpVerify = match ($defaultStatus) {
            'verified' => true,
            'rejected' => false,
            default => mt_rand() > 0.5 ? true : false
        };

        $response->getBody()->write(json_encode([
            'body' => json_encode([
                ['O' => 0, 'RUT' => $rut, 'OTPVERIFY' => $otpVerify]
            ])
        ]));
    }
    
    $response->withAddedHeader('Content-Type', 'application/json');

    return $response;
});

$app->run();