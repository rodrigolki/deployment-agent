<?php
    
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends Controller{
    
    public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $response->getBody()->write(
            $this->view('home', [
                "title" => "Homepage"
            ])
        );
        return $response;
    }
}

?>