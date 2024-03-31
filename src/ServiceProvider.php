<?php

namespace Administration;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        //
    }

    public function boot(Router $router)
    {
        $middlewares = collect($router->getMiddlewareGroups()['web']);
        $web = [];
        $middlewares->each(function ($middleware) use (&$web) {
            if (explode('\\', $middleware)[array_key_last(explode('\\', $middleware))] === 'HandleInertiaRequests' ) {
                $web[] = AdministrationMiddleware::class;
            }
            $web[] = $middleware;
        });
        $router->middlewareGroup('web', $web);

        $this->loadRoutesFrom(__DIR__.'/route.php');

        if(! $this->app->runningInConsole()) return;
        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ]);
        $this->commands([
            InstallCommand::class
        ]);
    }
}
