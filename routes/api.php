<?php

declare(strict_types=1);

use App\Application\Middleware\AuthorizationMiddleware;
use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Controllers\StackController;
use App\Models\Bootstrap;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    
    $container = $app->getContainer();    
    Bootstrap::load($container);
    $app->group('', function (RouteCollectorProxy $auth) {

        $auth->get('/stack/{stack_name}', [StackController::class, 'getStackByName'])->setName('get_stack_by_name');
        $auth->post('/stack', [StackController::class, 'implementStack'])->setName('implement_stack');
        $auth->put('/stack/{stack_name}', [StackController::class, 'updateStackByName'])->setName('update_stack_by_name');
        
        $auth->post('/webhook/{stack_name}', [StackController::class, 'webhook'])->setName('webhook');
        
    })->add(new AuthorizationMiddleware())->add(new JsonBodyParserMiddleware());

};
