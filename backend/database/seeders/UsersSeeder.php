<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $tenants = DB::table('tenants')->get(['id','slug']);

        $acmeId   = $tenants->firstWhere('slug','acme')->id ?? null;
        $globexId = $tenants->firstWhere('slug','globex')->id ?? null;

        // Superuser (global)
        DB::table('users')->insert([
            'tenant_id' => null,
            'name' => 'Root Admin',
            'email'=> 'admin@root.com',
            'password' => Hash::make('Password!123'),
            'role' => 'superuser',
            'created_at'=> $now, 'updated_at'=> $now,
        ]);

        // ACME
        DB::table('users')->insert([
            'tenant_id' => $acmeId,
            'name' => 'Acme Owner',
            'email'=> 'owner@acme.com',
            'password' => Hash::make('Password!123'),
            'role' => 'owner',
            'created_at'=> $now, 'updated_at'=> $now,
        ]);
        DB::table('users')->insert([
            'tenant_id' => $acmeId,
            'name' => 'Acme Agent',
            'email'=> 'agent@acme.com',
            'password' => Hash::make('Password!123'),
            'role' => 'agent',
            'created_at'=> $now, 'updated_at'=> $now,
        ]);

        // GLOBEX
        DB::table('users')->insert([
            'tenant_id' => $globexId,
            'name' => 'Globex Owner',
            'email'=> 'owner@globex.com',
            'password' => Hash::make('Password!123'),
            'role' => 'owner',
            'created_at'=> $now, 'updated_at'=> $now,
        ]);
        DB::table('users')->insert([
            'tenant_id' => $globexId,
            'name' => 'Globex Agent',
            'email'=> 'agent@globex.com',
            'password' => Hash::make('Password!123'),
            'role' => 'agent',
            'created_at'=> $now, 'updated_at'=> $now,
        ]);
    }
}
