<?php

namespace AdminPanel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {
    public function register() {
        //
    }

    public function boot() {
        $this->loadRoutesFrom(__DIR__.'./route.php');
    }
}  