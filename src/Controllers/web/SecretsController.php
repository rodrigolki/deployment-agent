<?php
    
namespace App\Controllers\web;

use App\Models\Environment;
use App\Models\Version;
use DateTime;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SecretsController extends Controller{
    
    public function secrets(Request $request, Response $response, array $args): ResponseInterface
    {
        $envs = Environment::where('deleted_at', null)->get();
        
        $response->getBody()->write(
            $this->view('secrets', [
                "title" => "Environment files",
                "envs" => $envs
            ])
        );
        return $response;
    }

    public function save(Request $request, Response $response): ResponseInterface
    {
        $data = $request->getParsedBody();

        if(! isset($data['name']) || ! isset($data['content']) || ! isset($data['env'])){
            $response->getBody()->write(json_encode([
                "message" => "Invalid parameters"
            ]));
            return $response->withStatus(400);
        }

        $validation = $this->validateEnvContent($data['content']);
        if(! $validation['valid'] ){
            $response->getBody()->write(json_encode([
                "message" => $validation['msg']
            ]));
            return $response->withStatus(400);
        }

        $env = Environment::where('id', $data['id'] ?? 0)->first();
        if($env){
            $v = new Version();
            $v->refer_id = $env->id;
            $v->refer_type = "ENV";
            $v->content = $this->encrypt($env->content);
            $v->version_date = new DateTime($env->last_updated_at);
            $v->save();

            $env->content = $data['content'];
            $env->env = $data['env'];
            $env->slug = $this->createSlug($env->id." ".$data['name']);
            $env->name = $data['name'];
            $env->content_type = $data['content_type'];
            $env->updated_at = new DateTime();
            $env->save();
        }else{
            $env = new Environment();
            $env->env = $data['env'];
            $env->name = $data['name'];
            $env->content = $data['content'];
            $env->content_type = $data['content_type'];
            $env->save();
            Environment::where('id', $env->id)->update(['slug' => $this->createSlug($env->id." ".$data['name'])]);
        }
        return $response->withHeader('Location', '/envs')->withStatus(302);
    }

    public function get(Request $request, Response $response, array $args): ResponseInterface
    {
        $env = Environment::where('id', $args['id'] ?? 0)->first();
        if($env){
            if($env->content_type == 'SECRET' )
                $env->content = "";
            $response->getBody()->write(json_encode($env));
        }else{
            $response->getBody()->write(json_encode([]));
        }
        return $response;
    }

    public function delete(Request $request, Response $response, array $args): ResponseInterface
    {
        $env = Environment::where('id', $args['id'] ?? 0)->update(['deleted_at' => "NOW()"]);
        if($env > 0){
            $response->getBody()->write(json_encode([
                "message" => "Environment deleted"
            ]));
            return $response->withStatus(200);
        }
        $response->getBody()->write(json_encode([
            "message" => "Environment not found"
        ]));
        return $response->withStatus(404);
    }

    public function get_by_identifier(Request $request, Response $response, array $args): ResponseInterface
    {
        $env = Environment::where('slug', $args['identifier'] ?? '')->first();
        if($env){
            $response = $response->withHeader("Content-Type", "text/plain");
            $response->getBody()->write($env->content);
        }else{
            $response = $response->withStatus(404);
        }
        return $response;
    }

    public function get_versions(Request $request, Response $response, array $args): ResponseInterface
    {
        $versions = Version::select('id', 'version_date')
                            ->where('refer_id', $args['id'] ?? 0)
                            ->where('refer_type', 'ENV')
                            ->orderBy('version_date', 'DESC')
                            ->get();
        if($versions){
            $response->getBody()->write(json_encode($versions));
        }else{
            $response->getBody()->write(json_encode([]));
        }
        return $response;
    }

    public function restore_version(Request $request, Response $response, array $args): ResponseInterface
    {
        $version = Version::where('id', $args['id'] ?? 0)->first();
        if($version){
            $env = Environment::where('id', $version->refer_id)->first();
            if($env){
                $v = new Version();
                $v->refer_id = $env->id;
                $v->refer_type = "ENV";
                $v->content = $this->encrypt($env->content);
                $v->version_date = new DateTime($env->last_updated_at);
                $v->save();

                $env->content = $this->decrypt($version->content);
                $env->updated_at = new DateTime();
                $env->save();
                $response->getBody()->write(json_encode([
                    "message" => "Environment restored"
                ]));
                return $response->withStatus(200);
            }
        }
        $response->getBody()->write(json_encode([
            "message" => "Environment not found"
        ]));
        return $response->withStatus(404);
    }

    public function get_version_content(Request $request, Response $response, array $args): ResponseInterface
    {
        $version = Version::where('id', $args['id'] ?? 0)->first();
        if($version){
            $response = $response->withHeader("Content-Type", "text/plain");
            $response->getBody()->write( $this->decrypt($version->content) );
        }else{
            $response->getBody()->write(json_encode([]));
        }
        return $response;
    }

    public static function encrypt($data){
        $cipher = "aes-256-cbc";
        $encrypted = bin2hex(openssl_encrypt($data, $cipher, $_SERVER['CRIPT_PASS_FRASE'], 0, $_SERVER['CRIPT_IV']));
        return $encrypted;
    }

    public static function decrypt($data){
        $cipher = "aes-256-cbc";
        $decrypted = openssl_decrypt(hex2bin($data), $cipher, $_SERVER['CRIPT_PASS_FRASE'], 0, $_SERVER['CRIPT_IV']);
        return $decrypted;
    }

    protected function validateEnvContent($contents){  
        try {
            $env = Dotenv::parse($contents);
            return ["valid" => true, "env" => $env];
        } catch (\Throwable $th) {
            return ["valid" => false, "msg" => $th->getMessage()];
        }
    }
}

?>