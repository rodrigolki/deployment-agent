<?php

declare(strict_types=1);

use App\Application\Middleware\AuthorizationMiddleware;
use App\Controllers\DocumentController;
use App\Models\Bootstrap;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    
    $container = $app->getContainer();    
    Bootstrap::load($container);

    //Grupo de rotas autenticadas
    $app->group('', function (RouteCollectorProxy $group) {

        header("Content-type: application/json");

        $group->get('/auth', function (Request $request, Response $response, array $args) {
            $response->getBody()->write('Rota autenticada');
            return $response;
        })->setName('authorized');

        $group->get('/documents', [DocumentController::class, 'index'])->setName('documents');

    })->add(new AuthorizationMiddleware());

};
