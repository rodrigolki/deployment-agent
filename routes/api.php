<?php

declare(strict_types=1);

use App\Application\Middleware\AuthorizationMiddleware;
use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Controllers\StackController;
use App\Controllers\web\SecretsController;
use App\Controllers\web\YamlsController;
use App\Models\Bootstrap;
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
        
        $auth->get('/env-file/{identifier}', [SecretsController::class, 'get_by_identifier'])->setName('get_env_by_identifier');
        $auth->get('/yaml-file/{identifier}', [YamlsController::class, 'get_by_identifier'])->setName('get_yaml_by_identifier');

    })->add(new AuthorizationMiddleware())->add(new JsonBodyParserMiddleware());

    $app->group('', function (RouteCollectorProxy $groups) {

        $groups->group('/env', function (RouteCollectorProxy $auth) {
            $auth->get('/versions/{id}', [SecretsController::class, 'get_versions']);
            $auth->get('/version/{id}', [SecretsController::class, 'get_version_content']);
            $auth->put('/restore/{id}', [SecretsController::class, 'restore_version']);
            $auth->post('', [SecretsController::class, 'save']);
            $auth->get('/{id}', [SecretsController::class, 'get']);
            $auth->delete('/{id}', [SecretsController::class, 'delete']);
        });
        $groups->group('/yaml', function (RouteCollectorProxy $auth) {
            $auth->get('/versions/{id}', [YamlsController::class, 'get_versions']);
            $auth->get('/version/{id}', [YamlsController::class, 'get_version_content']);
            $auth->put('/restore/{id}', [YamlsController::class, 'restore_version']);
            $auth->post('', [YamlsController::class, 'save']);
            $auth->get('/{id}', [YamlsController::class, 'get']);
            $auth->put('/{id}', [YamlsController::class, 'detach']);
            $auth->patch('/{id}', [YamlsController::class, 'apply']);
            $auth->delete('/{id}', [YamlsController::class, 'delete']);
        });
        
    })->add(new JsonBodyParserMiddleware());

};
