<?php

use Administration\ModelController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    $list = Collection::make(config('administration.menu'));
    Inertia::share('menu', function() use(&$list) {
        $menu = [];
        $list->each(function($value, $key) use (&$menu) {
            if($key !== 'models') return $menu[$key] = '';
            Collection::make($value)->each(function($model, $name) use (&$menu) {
                if( isset(config('administration.models')[$model]['new']) ? config('administration.models')[$model]['new'] : true ) {
                    $menu[$name] = [
                        "All $name" => 'index'
                    ];
                    $menu[$name]['New '.explode('\\', $model)[array_key_last(explode('\\', $model))]] = 'create';
                    return;
                }
                $menu[$name] = 'index';
            });
        });
        return $menu;
    });

    $list->each(function($value, $key) {
        if($key !== 'models') return Route::get('/'. str_replace(' ', '-', strtolower($key)), function () use ($key) {
            return Inertia::render('Admin/'. str_replace(' ', '', $key));
        })->name(str_replace(' ', '-', strtolower($key)));

        Collection::make($value)->each(function($model, $name) {
            $route = Route::resource(str_replace(' ', '-', strtolower($name)), ModelController::class);
            $exepts = [];
            if(!file_exists(resource_path('js/Pages/'.explode('\\', $model)[array_key_last(explode('\\', $model))].'/Show.vue'))) $exepts[] = 'show';
            if( !(isset(config('administration.models')[$model]['edit']) ? config('administration.models')[$model]['edit'] : true) ) {
                $exepts[] = 'edit';
                $exepts[] = 'update';
            }
            if( !(isset(config('administration.models')[$model]['new']) ? config('administration.models')[$model]['new'] : true) ) {
                $exepts[] = 'create';
                $exepts[] = 'store';
            }
            if( !(isset(config('administration.models')[$model]['delete']) ? config('administration.models')[$model]['delete'] : true) ) $exepts[] = 'destroy';
            $route = $route->except($exepts);
        });
    });
});

