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
        Artisan::call('vendor:publish', ['--provider' => 'Administration\ServiceProvider']);
        Artisan::call('migrate:fresh', ['--seed' => true, '--seeder' => 'Administration\RoleSeeder']);
    }
}
