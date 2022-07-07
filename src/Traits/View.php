<?php
    
namespace App\Traits;

/**
 * 
 */
trait View
{
    protected $twig;
    
    protected function twig()
    {
        
        $loader = new \Twig\Loader\FilesystemLoader('../src/Views');
        $this->twig = new \Twig\Environment($loader, [
            // 'cache' => '/path/to/compilation_cache',
            'debug' => true
        ]);
    }

    protected function functions()
    {
        # code...
    }
    
    protected function load()
    {
        $this->twig();
        
        $this->functions();
    }
    
    protected function view( $view, $data)
    {
        $this->load();

        $template = $this->twig->load(str_replace(".", "/", $view).".twig");
        
        return $template->render($data);
    }
}


?>