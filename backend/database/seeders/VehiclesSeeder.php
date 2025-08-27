<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiclesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $tenants = DB::table('tenants')->get(['id','slug']);

        $acmeId   = $tenants->firstWhere('slug','acme')->id ?? null;
        $globexId = $tenants->firstWhere('slug','globex')->id ?? null;

        $vehiclesAcme = [
            ['brand'=>'Toyota','model'=>'Corolla','version'=>'XEi 2.0','year'=>2023,'km'=>12000,'price'=>125000,'status'=>'available','notes'=>'Único dono, revisões em dia. Potencial comprador telefone 8888-8888'],
            ['brand'=>'Toyota','model'=>'Corolla','version'=>'GLi','year'=>2022,'km'=>23000,'price'=>118000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Corolla','version'=>null,'year'=>2021,'km'=>41000,'price'=>105000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Corolla','version'=>'Altis','year'=>2020,'km'=>52000,'price'=>98000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Yaris','version'=>'XS','year'=>2023,'km'=>8000,'price'=>99000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Yaris','version'=>null,'year'=>2022,'km'=>15000,'price'=>94000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Yaris','version'=>null,'year'=>2021,'km'=>28000,'price'=>88000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Etios','version'=>null,'year'=>2019,'km'=>60000,'price'=>52000,'status'=>'available','notes'=>'Bom para app. Potencial comprador telefone 7777-8888'],
            ['brand'=>'Toyota','model'=>'Etios','version'=>null,'year'=>2018,'km'=>72000,'price'=>48000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Hilux','version'=>'SRV','year'=>2020,'km'=>65000,'price'=>185000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Hilux','version'=>'SR','year'=>2019,'km'=>78000,'price'=>175000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'SW4','version'=>'SRX','year'=>2018,'km'=>82000,'price'=>195000,'status'=>'available'],

            ['brand'=>'Honda','model'=>'Civic','version'=>'EXL','year'=>2020,'km'=>46000,'price'=>98000,'status'=>'reserved'],
            ['brand'=>'Ford','model'=>'Focus','version'=>null,'year'=>2019,'km'=>70000,'price'=>72000,'status'=>'sold'],
            ['brand'=>'Volkswagen','model'=>'Golf','version'=>'Highline','year'=>2018,'km'=>90000,'price'=>65000,'status'=>'available'],
            ['brand'=>'Chevrolet','model'=>'Onix','version'=>'LTZ','year'=>2022,'km'=>20000,'price'=>78000,'status'=>'available'],
            ['brand'=>'Hyundai','model'=>'HB20','version'=>'Comfort Plus','year'=>2023,'km'=>6000,'price'=>82000,'status'=>'available'],
            ['brand'=>'Renault','model'=>'Kwid','version'=>null,'year'=>2022,'km'=>10000,'price'=>52000,'status'=>'available'],
            ['brand'=>'Fiat','model'=>'Argo','version'=>'Drive','year'=>2021,'km'=>31000,'price'=>61000,'status'=>'reserved'],
            ['brand'=>'Nissan','model'=>'Kicks','version'=>'SV','year'=>2020,'km'=>42000,'price'=>90000,'status'=>'available'],
            ['brand'=>'Jeep','model'=>'Compass','version'=>'Longitude','year'=>2019,'km'=>68000,'price'=>115000,'status'=>'sold'],
            ['brand'=>'Peugeot','model'=>'208','version'=>'Griffe','year'=>2022,'km'=>18000,'price'=>74000,'status'=>'available'],
            ['brand'=>'Citroen','model'=>'C3','version'=>null,'year'=>2023,'km'=>5000,'price'=>70000,'status'=>'available'],
            ['brand'=>'Ford','model'=>'Ka','version'=>null,'year'=>2019,'km'=>65000,'price'=>50000,'status'=>'available'],
        ];

        $vehiclesGlobex = [
            ['brand'=>'Toyota','model'=>'Corolla','version'=>'GLi','year'=>2019,'km'=>72000,'price'=>90000,'status'=>'available'],
            ['brand'=>'Honda','model'=>'Civic','version'=>'EX','year'=>2018,'km'=>95000,'price'=>82000,'status'=>'sold'],
            ['brand'=>'Ford','model'=>'Focus','version'=>null,'year'=>2017,'km'=>110000,'price'=>60000,'status'=>'reserved'],
            ['brand'=>'Volkswagen','model'=>'Polo','version'=>'TSI','year'=>2020,'km'=>40000,'price'=>78000,'status'=>'available'],
            ['brand'=>'Chevrolet','model'=>'Cruze','version'=>'LT','year'=>2021,'km'=>30000,'price'=>110000,'status'=>'available'],
            ['brand'=>'Hyundai','model'=>'Creta','version'=>'Prestige','year'=>2022,'km'=>12000,'price'=>140000,'status'=>'available'],
            ['brand'=>'Renault','model'=>'Duster','version'=>null,'year'=>2019,'km'=>69000,'price'=>75000,'status'=>'available'],
            ['brand'=>'Fiat','model'=>'Pulse','version'=>'Audace','year'=>2023,'km'=>7000,'price'=>102000,'status'=>'available'],
            ['brand'=>'Nissan','model'=>'Versa','version'=>'V-Drive','year'=>2021,'km'=>26000,'price'=>83000,'status'=>'reserved'],
            ['brand'=>'Jeep','model'=>'Renegade','version'=>'Sport','year'=>2018,'km'=>88000,'price'=>88000,'status'=>'available'],
        ];

        $creatorAcme   = DB::table('users')->where('email','owner@acme.com')->value('id');
        $creatorGlobex = DB::table('users')->where('email','owner@globex.com')->value('id');

        foreach ($vehiclesAcme as $v) {
            DB::table('vehicles')->insert([
                'tenant_id'   => $acmeId,
                'brand'       => $v['brand'],
                'model'       => $v['model'],
                'version'     => $v['version'] ?? null,
                'year'        => $v['year'],
                'km'          => $v['km'] ?? 0,
                'price'       => $v['price'],
                'status'      => $v['status'],
                'notes'       => $v['notes'] ?? null,
                'images_json' => json_encode([]),
                'created_by'  => $creatorAcme,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        foreach ($vehiclesGlobex as $v) {
            DB::table('vehicles')->insert([
                'tenant_id'   => $globexId,
                'brand'       => $v['brand'],
                'model'       => $v['model'],
                'version'     => $v['version'] ?? null,
                'year'        => $v['year'],
                'km'          => $v['km'] ?? 0,
                'price'       => $v['price'],
                'status'      => $v['status'],
                'notes'       => $v['notes'] ?? null,
                'images_json' => json_encode([]),
                'created_by'  => $creatorGlobex,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
