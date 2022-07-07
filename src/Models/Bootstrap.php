<?php namespace App\Models;

use App\Application\Settings\SettingsInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
// use Illuminate\Events\Dispatcher;
// use Illuminate\Container\Container;

final class Bootstrap
{
    public static function load($container)
    {        
        $settings = $container->get(SettingsInterface::class);
        $capsule = new Capsule();        
        $capsule->addConnection( $settings->get('db'));
        // $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();                
    }
}