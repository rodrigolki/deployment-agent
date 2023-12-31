<?php

declare(strict_types=1);

use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Controllers\web\SecretsController;
use App\Controllers\web\YamlsController;
use Slim\App;
use Slim\Psr7\Request ;
use Slim\Psr7\Response ;

return function (App $app) {
    
    //Rota padrão para healfcheck da aplicação
    $app->get('/ping', function (Request $request, Response $response) {
        $response->getBody()->write("Pong<br>Serviço: ".$_SERVER['DD_SERVICE'].(isset($_SERVER['TAG']) ? "  <br>Version: ".$_SERVER['TAG'] : "").(isset($_SERVER['DD_ENV']) ? " <br>ENV: ".$_SERVER['DD_ENV'] : ""));
        return $response;
    });

    // CORS Pre-Flight OPTIONS Request Handler
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    //Exemplo de rota com View
    $app->get('/envs', [SecretsController::class, 'secrets'])->setName('secrets');
    $app->get('/yamls', [YamlsController::class, 'yamls'])->setName('yamls');
};
