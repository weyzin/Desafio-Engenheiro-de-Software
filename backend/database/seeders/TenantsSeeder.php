<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TenantsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        // Tenants
        DB::table('tenants')->insert([
            ['id' => (string) Str::uuid(), 'name' => 'Acme Inc',   'created_at'=>$now,'updated_at'=>$now, 'slug'=>'acme'],
            ['id' => (string) Str::uuid(), 'name' => 'Globex LLC', 'created_at'=>$now,'updated_at'=>$now, 'slug'=>'globex'],
        ]);

        // Domains (opcional)
        $tenants = DB::table('tenants')->get(['id','name','slug']);
        foreach ($tenants as $t) {
            DB::table('tenant_domains')->insert([
                'tenant_id' => $t->id,
                'domain'    => $t->slug.'.local.test',
                'created_at'=> $now,
            ]);
        }
    }
}
