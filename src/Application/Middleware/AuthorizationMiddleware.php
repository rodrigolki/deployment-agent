<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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

        $params = $request->getServerParams();
        $query_params = $request->getQueryParams();

        $authorization = $params['HTTP_AUTHORIZATION'] ?? null;

        if (isset($query_params['authorization'])) {
            foreach ($query_params as $key => $value) {
                $query_params[$key] = preg_replace("/[^0-9a-zA-Z_.@]/", "", $value);
            }
            
            if(!isset($_SERVER['SECRET_EXTERNAL'])) throw new HttpUnauthorizedException($request, "Secret ausente para domínio.");
    
            if(  $_SERVER['SECRET_EXTERNAL'] != $_GET['authorization']) throw new HttpUnauthorizedException($request, "Sem permissão para acesso ao arquivo.");
            
        }else{

            if (! $authorization ) throw new HttpUnauthorizedException($request, "Token para autenticação usente.");

            $partes = explode(" ", $authorization);
            $authorization = sizeof($partes) > 1 ? $partes[1] : $authorization;
            if ($authorization == base64_encode($_SERVER['GATEWAY_API_KEY'].":".$_SERVER['GATEWAY_API_SECRET']))  return $handler->handle($request);
            
            if (!isset($_SERVER['GESTAO_SECRET']) || !isset($_SERVER['GESTAO_KEY']) || !isset($_SERVER['GESTAO_INITVEC'])) throw new HttpUnauthorizedException($request, "Inconsistência com variáveis de ambiente."); 

            $decoded = JWT::decode($authorization, new Key($_SERVER['GESTAO_SECRET'], 'HS256'));
        
            // Access is granted.
            $_SERVER['jwt'] = json_decode(openssl_decrypt(hex2bin($decoded->data), "AES-128-CBC", $_SERVER['GESTAO_KEY'], OPENSSL_RAW_DATA, $_SERVER['GESTAO_INITVEC']), true);
        }
        return $handler->handle($request);
    }
}
