<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TenantsSeeder::class,
            UsersSeeder::class,
            VehiclesSeeder::class,
        ]);
    }
}
