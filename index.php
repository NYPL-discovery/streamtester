<?php
require __DIR__ . '/vendor/autoload.php';

use Slim\Http\Request;
use Slim\Http\Response;
use NYPL\Starter\Service;
use NYPL\Services\Controller;
use NYPL\Starter\SwaggerGenerator;
use NYPL\Starter\Config;

Config::initialize(__DIR__ . '/config');

$service = new Service();

$service->get("/docs/streamtest", function (Request $request, Response $response) {
    return SwaggerGenerator::generate(
        [__DIR__ . "/src"],
        $response
    );
});

$service->post("/api/v0.1/stream-tests/start", function (Request $request, Response $response) {
    $controller = new Controller\TestController($request, $response);
    return $controller->startTest();
});

$service->post("/api/v0.1/stream-tests/end/{id}", function (Request $request, Response $response, $parameters) {
    $controller = new Controller\TestController($request, $response);
    return $controller->endTest($parameters["id"]);
});

$service->get("/api/v0.1/stream-tests/tests/{id}/results", function (Request $request, Response $response, $parameters) {
    $controller = new Controller\TestController($request, $response);
    return $controller->getResults($parameters["id"]);
});

$service->run();
