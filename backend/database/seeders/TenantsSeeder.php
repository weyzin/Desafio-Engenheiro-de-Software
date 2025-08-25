<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantsSeeder extends Seeder
{
    public function run(): void
    {
        // limpa de forma segura (respeitando FKs)
        DB::table('tenant_domains')->delete();
        DB::table('tenants')->delete();

        // IDs fixos para ficarem estáveis em testes
        $acme   = '11111111-1111-1111-1111-111111111111';
        $globex = '22222222-2222-2222-2222-222222222222';

        DB::table('tenants')->insert([
            ['id' => $acme,   'name' => 'ACME Inc.',  'slug' => 'acme',   'created_at'=>now(),'updated_at'=>now()],
            ['id' => $globex, 'name' => 'Globex LLC', 'slug' => 'globex', 'created_at'=>now(),'updated_at'=>now()],
        ]);

        DB::table('tenant_domains')->insert([
            ['tenant_id' => $acme,   'domain' => 'acme.localhost',   'created_at'=>now(),'updated_at'=>now()],
            ['tenant_id' => $globex, 'domain' => 'globex.localhost', 'created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
