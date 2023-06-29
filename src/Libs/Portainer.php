<?php
namespace App\Libs;

use Exception;
use GuzzleHttp\Client;

class Portainer{
    private $api = NULL;
    private $jwt = NULL;
    // metodos
    public function __construct(){
        $this->api = new Client([
            'timeout'=> 300,
            "verify" => false,
            'base_uri' => "{$_SERVER['PORTAINER_HOST']}:{$_SERVER['PORTAINER_PORT']}/api/"
        ]);
    }
    
    public function auth(){
        $response = $this->api->post("auth", [
            'json' => [
                'Username' => $_SERVER['PORTAINER_USER'],
                'Password' => $_SERVER['PORTAINER_PASS']
            ]
        ]);

        $data = json_decode($response->getBody()->getContents());
        $this->jwt = $data->jwt;
        return $data->jwt;
    }

    public function getStackByName(string $stackName){
        $this->auth();

        $response = $this->api->get("stacks", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwt
            ]
        ]);
        
        $stacks = json_decode($response->getBody(), true);
        
        $stack = array_filter($stacks, function($stack) use ($stackName){
            return $stack['Name'] === $stackName;
        });
        if(!$stack){
            return NULL;
        }
        return array_pop($stack);
    }

    public function createStack(string $stackFolder){
        

    }
   
    public function updateStack(string $stackName, string $tag = ""){
        $this->auth();
        
        $stack = $this->getStackByName($stackName);
        if(!$stack){
            throw new Exception("Stack not found with name $stackName ", 404);
        }
        $stackId = $stack['Id'];
        $compose = $this->getStackSpecFile($stackId);

        if(!$compose){
            throw new Exception("Error reading spec files", 500);
        }

        if ($tag != "") {
            $stack['Env'] = array_map(function($env) use ($tag){
                if($env['name'] == $_SERVER['VERSION_VARIABLE']) {
                    $env['value'] = $tag;
                    return $env;
                }
                return $env;
            }, $stack['Env']);
        }

        $updatedStack = [
            'stackFileContent' => $compose,
            "prune" => true,
            "pullImage" => true,
            "env" => $stack['Env']
        ];

        
        $response = $this->api->put("stacks/$stackId?endpointId=".$stack['EndpointId'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwt,
                'Content-Type' => 'application/json'
            ],
            'json' => $updatedStack
        ]);

        if ($response->getStatusCode() === 200) {
            return [ 'success' => true, 'message' => 'Stack updated successfully' ];
        }

        return [ 'success' => false, 'message' => 'Error updating stack', 'error' => $response->getBody()  ];
    }

    public function getStackSpecFile(int $stackId){

        $response = $this->api->get("stacks/$stackId/file", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->jwt
            ]
        ]);
        $data = json_decode($response->getBody()->getContents());
        return $data->StackFileContent;
    }
    
}
?>
