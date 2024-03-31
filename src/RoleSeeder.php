<?php

namespace Administration;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'Owner'
        ]);
        Role::create([
            'name' => 'Member',
            'permissions' => 'include'
        ]);
    }
}
