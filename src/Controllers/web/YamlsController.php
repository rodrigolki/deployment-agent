<?php

namespace App\Controllers\web;

use App\Models\Version;
use App\Models\Yaml;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class YamlsController extends Controller{

    public function yamls(Request $request, Response $response, array $args): ResponseInterface
    {
        $yamls = Yaml::where('deleted_at', null)->get();
        
        $response->getBody()->write(
            $this->view('yamls', [
                "title" => "Yaml files",
                "yamls" => $yamls
            ])
        );
        return $response;
    }
    
    public function save(Request $request, Response $response): ResponseInterface
    {
        $data = $request->getParsedBody();

        if(! isset($data['name']) || ! isset($data['content']) ){
            $response->getBody()->write(json_encode([
                "message" => "Invalid parameters"
            ]));
            return $response->withStatus(400);
        }

        $yaml = Yaml::where('id', $data['id'] ?? 0)->first();
        if($yaml){
            $v = new Version();
            $v->refer_id = $yaml->id;
            $v->refer_type = "YAML";
            $v->content = $yaml->content;
            $v->version_date = new DateTime($yaml->last_updated_at);
            $v->save();

            $yaml->content = $data['content'];
            $yaml->slug = $this->createSlug($yaml->id." ".$data['name']);
            $yaml->name = $data['name'];
            $yaml->updated_at = new DateTime();
            $yaml->save();
        }else{
            $yaml = new Yaml();
            $yaml->name = $data['name'];
            $yaml->content = $data['content'];
            $yaml->save();
            Yaml::where('id', $yaml->id)->update(['slug' => $this->createSlug($yaml->id." ".$data['name'])]);
        }
        return $response->withHeader('Location', '/yamls')->withStatus(302);
    }

    public function get(Request $request, Response $response, array $args): ResponseInterface
    {
        $yaml = Yaml::where('id', $args['id'] ?? 0)->first();
        if($yaml){
            $response->getBody()->write(json_encode($yaml));
        }else{
            $response->getBody()->write(json_encode([]));
        }
        return $response;
    }

    public function delete(Request $request, Response $response, array $args): ResponseInterface
    {
        $yaml = Yaml::where('id', $args['id'] ?? 0)->update(['deleted_at' => "NOW()"]);
        if($yaml > 0){
            $response->getBody()->write(json_encode([
                "message" => "Yaml deleted"
            ]));
            return $response->withStatus(200);
        }
        $response->getBody()->write(json_encode([
            "message" => "Yaml not found"
        ]));
        return $response->withStatus(404);
    }

    public function get_by_identifier(Request $request, Response $response, array $args): ResponseInterface
    {
        $yaml = Yaml::where('slug', $args['identifier'] ?? '')->first();
        if($yaml){
            $response = $response->withHeader("Content-Type", "text/yaml");
            $response->getBody()->write($yaml->content);
        }else{
            $response = $response->withStatus(404);
        }
        return $response;
    }
 
    public function get_versions(Request $request, Response $response, array $args): ResponseInterface
    {
        $versions = Version::select('id', 'version_date')
                            ->where('refer_id', $args['id'] ?? 0)
                            ->where('refer_type', 'YAML')
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
            $yaml = Yaml::where('id', $version->refer_id)->first();
            if($yaml){
                $v = new Version();
                $v->refer_id = $yaml->id;
                $v->refer_type = "YAML";
                $v->content = $yaml->content;
                $v->version_date = new DateTime($yaml->last_updated_at);
                $v->save();

                $yaml->content = $version->content;
                $yaml->updated_at = new DateTime();
                $yaml->save();


                $response->getBody()->write(json_encode([
                    "message" => "Yaml restored"
                ]));
                return $response->withStatus(200);
            }
        }
        $response->getBody()->write(json_encode([
            "message" => "Yaml not found"
        ]));
        return $response->withStatus(404);
    }

    public function get_version_content(Request $request, Response $response, array $args): ResponseInterface
    {
        $version = Version::where('id', $args['id'] ?? 0)->first();
        if($version){
            $response = $response->withHeader("Content-Type", "text/yaml");
            $response->getBody()->write( $version->content );
        }else{
            $response->getBody()->write(json_encode([]));
        }
        return $response;
    }

    public function apply(Request $request, Response $response, array $args): ResponseInterface
    {
        $yaml = Yaml::where('id', $args['id'] ?? 0)->first();
        if($yaml){
            
            $cmd = "kubectl apply -f {$_SERVER['APP_URL']}/yaml-file/{$yaml->slug}?authorization={$_SERVER['AUTH_SECRET']}";
    
            $output = shell_exec($cmd);
            $response->getBody()->write(json_encode([
                "message" => "Yaml applied",
                "output" => str_replace("\n", "</br>", $output)
            ]));

        }else{
            $response = $response->withStatus(404);
        }
        return $response;
    }
    
    public function detach(Request $request, Response $response, array $args): ResponseInterface
    {
        $yaml = Yaml::where('id', $args['id'] ?? 0)->first();
        if($yaml){
            
            $cmd = "kubectl delete -f {$_SERVER['APP_URL']}/yaml-file/{$yaml->slug}?authorization={$_SERVER['AUTH_SECRET']}";
    
            $output = shell_exec($cmd);
            $response->getBody()->write(json_encode([
                "message" => "Yaml deleted",
                "output" => str_replace("\n", "</br>", $output)
            ]));

        }else{
            $response = $response->withStatus(404);
        }
        return $response;
    }

}

?>