<?php
namespace App\Controllers;

use App\Libs\Portainer;
use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class StackController {

    private $portainer = NULL;

    public function __construct(){
        $this->portainer = new Portainer();
    }

    public function getStackByName(Request $request, Response $response, array $args) : ResponseInterface
    {
        $stack = $this->portainer->getStackByName($args['stack_name']);
        $stack = $stack ? $stack : [];

        return HelpersClass::response_json($response, $stack);
    }
    
    public function implementStack(Request $request, Response $response) : ResponseInterface
    {
        return HelpersClass::response_json($response, ["msg" => "Method not implemented"]);
    }
    
    public function updateStackByName(Request $request, Response $response, array $args) : ResponseInterface
    {
        $stack = $this->portainer->updateStack($args['stack_name']);
        
        return HelpersClass::response_json($response, $stack);
    }
    
    public function webhook(Request $request, Response $response, array $args) : ResponseInterface
    {
        $webhookData = $request->getParsedBody();

        if (!isset($webhookData['push_data']['tag'])) {
            throw new Exception("Tag not found", 400);
        }

        $regex = $_SERVER['REGEX_TAG'];
        if(!$regex){
            throw new Exception("Regex tag not found", 400);
        }

        if (!preg_match($regex, $webhookData['push_data']['tag'])) {
            throw new Exception("Agent not listen for tag ".$webhookData['push_data']['tag'], 400);
        }

        $stack = $this->portainer->updateStack($args['stack_name'], $webhookData['push_data']['tag']);

        try {
            $hub = new Client([
                'timeout'=> 30,
                "verify" => false,
            ]);

            $hub->post($webhookData['callback_url'], [
                'json' => [
                    "state" => "success",
                    "description" => "Deployed stack {$args['stack_name']} with tag {$webhookData['push_data']['tag']} ",
                    "context" => "",
                    "target_url" => ""
                ]]
            );

            if (isset($_SERVER['DISCORD_WEBHOOK'])) {
                $hub->post(
                    $_SERVER['DISCORD_WEBHOOK'],
                [
                    'headers' => ['Content-type' => 'application/json'],
                    'http_errors' => false,
                    'body'=> json_encode([
                        "content" =>  "🤖🤖 Deployed stack {$args['stack_name']} with tag {$webhookData['push_data']['tag']}"
                    ])
                ]
            );
            }

        } catch (\Throwable $th) {
            error_log("Unable to send callback to {$webhookData['callback_url']}");
        }



        return HelpersClass::response_json($response, $stack);
    }


}

?>