<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class HelpersClass {
    
    public static function response_json(Response $response, Array $data) : ResponseInterface
    {
        header("Content-type: application/json");
        $response->getBody()->write(json_encode($data));
        return $response;
    }
}

?>