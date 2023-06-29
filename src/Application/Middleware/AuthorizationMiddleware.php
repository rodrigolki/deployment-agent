<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;

class AuthorizationMiddleware implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $query_params = $request->getQueryParams();

        if (!isset($query_params['authorization'])) throw new HttpUnauthorizedException($request, "Invalid authorization missing authorization");
        
        if(!isset($_SERVER['AUTH_SECRET'])) throw new HttpUnauthorizedException($request, "Invalid authorization missing secret");

        if(  $_SERVER['AUTH_SECRET'] != $query_params['authorization']) throw new HttpUnauthorizedException($request, "Invalid authorization");
            
        return $handler->handle($request);
    }
}
