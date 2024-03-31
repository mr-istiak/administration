<?php

namespace Administration;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'administration:install';

    protected $description = 'Installs the Administration Panel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fs = new Filesystem();
        $fs->copyDirectory(__DIR__.'/../Admin', resource_path('js/Pages/Admin'));
        $fs->copy(__DIR__.'/administration.php', config_path('administration.php'));

        $routes = $fs->get(base_path('routes/web.php'));
        $pattern = '/Route::get\(\'\/dashboard\',\s*function\s*\(\)\s*{[^}]+}\)->middleware\(\[\s*\'auth\',\s*\'verified\'\]\)->name\(\'dashboard\'\);/';
        file_put_contents(base_path('routes/web.php'), preg_replace($pattern, '', $routes));
        
        Artisan::call('vendor:publish', ['--provider' => 'Administration\ServiceProvider']);
        Artisan::call('migrate:fresh', ['--seed' => true, '--seeder' => 'Administration\RoleSeeder']);
    }
}
