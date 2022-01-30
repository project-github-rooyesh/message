<?php
namespace Rooyesh\Message;

use Illuminate\Support\ServiceProvider;

class MessageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/message.php'=>config_path('message.php')
        ]);
        $this->loadMigrationsFrom(__DIR__ . "/migrations");

    }

    public function register()
    {
//        $this->app->singleton(MessageInterface::class,function (){
//            return MessageInterface::create(1,'1111','2','09136982135','123',null);
//        }
//        );

    }
}
