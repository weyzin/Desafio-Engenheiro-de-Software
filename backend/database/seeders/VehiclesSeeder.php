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
            ['brand'=>'Toyota','model'=>'Corolla','year'=>2021,'price'=>105000,'status'=>'available'],
            ['brand'=>'Honda','model'=>'Civic','year'=>2020,'price'=>98000,'status'=>'reserved'],
            ['brand'=>'Ford','model'=>'Focus','year'=>2019,'price'=>72000,'status'=>'sold'],
            ['brand'=>'Volkswagen','model'=>'Golf','year'=>2018,'price'=>65000,'status'=>'available'],
            ['brand'=>'Chevrolet','model'=>'Onix','year'=>2022,'price'=>78000,'status'=>'available'],
            ['brand'=>'Hyundai','model'=>'HB20','year'=>2023,'price'=>82000,'status'=>'available'],
            ['brand'=>'Renault','model'=>'Kwid','year'=>2022,'price'=>52000,'status'=>'available'],
            ['brand'=>'Fiat','model'=>'Argo','year'=>2021,'price'=>61000,'status'=>'reserved'],
            ['brand'=>'Nissan','model'=>'Kicks','year'=>2020,'price'=>90000,'status'=>'available'],
            ['brand'=>'Jeep','model'=>'Compass','year'=>2019,'price'=>115000,'status'=>'sold'],
            ['brand'=>'Peugeot','model'=>'208','year'=>2022,'price'=>74000,'status'=>'available'],
            ['brand'=>'Citroen','model'=>'C3','year'=>2023,'price'=>70000,'status'=>'available'],
            ['brand'=>'Toyota','model'=>'Yaris','year'=>2021,'price'=>88000,'status'=>'available'],
            ['brand'=>'Honda','model'=>'Fit','year'=>2018,'price'=>60000,'status'=>'sold'],
            // 15º — com placa ABC-1234 (apenas exemplo de negócio; campo plate opcional no seu DDL)
            ['brand'=>'Ford','model'=>'Ka','year'=>2019,'price'=>50000,'status'=>'available'],
        ];

        $vehiclesGlobex = [
            ['brand'=>'Toyota','model'=>'Corolla','year'=>2019,'price'=>90000,'status'=>'available'],
            ['brand'=>'Honda','model'=>'Civic','year'=>2018,'price'=>82000,'status'=>'sold'],
            ['brand'=>'Ford','model'=>'Focus','year'=>2017,'price'=>60000,'status'=>'reserved'],
            ['brand'=>'Volkswagen','model'=>'Polo','year'=>2020,'price'=>78000,'status'=>'available'],
            ['brand'=>'Chevrolet','model'=>'Cruze','year'=>2021,'price'=>110000,'status'=>'available'],
            ['brand'=>'Hyundai','model'=>'Creta','year'=>2022,'price'=>140000,'status'=>'available'],
            ['brand'=>'Renault','model'=>'Duster','year'=>2019,'price'=>75000,'status'=>'available'],
            ['brand'=>'Fiat','model'=>'Pulse','year'=>2023,'price'=>102000,'status'=>'available'],
            ['brand'=>'Nissan','model'=>'Versa','year'=>2021,'price'=>83000,'status'=>'reserved'],
            ['brand'=>'Jeep','model'=>'Renegade','year'=>2018,'price'=>88000,'status'=>'available'],
        ];

        $creatorAcme   = DB::table('users')->where('email','owner@acme.com')->value('id');
        $creatorGlobex = DB::table('users')->where('email','owner@globex.com')->value('id');

        foreach ($vehiclesAcme as $v) {
            DB::table('vehicles')->insert([
                'tenant_id'  => $acmeId,
                'brand'      => $v['brand'],
                'model'      => $v['model'],
                'year'       => $v['year'],
                'price'      => $v['price'],
                'status'     => $v['status'],
                'images_json'=> json_encode([]),
                'created_by' => $creatorAcme,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($vehiclesGlobex as $v) {
            DB::table('vehicles')->insert([
                'tenant_id'  => $globexId,
                'brand'      => $v['brand'],
                'model'      => $v['model'],
                'year'       => $v['year'],
                'price'      => $v['price'],
                'status'     => $v['status'],
                'images_json'=> json_encode([]),
                'created_by' => $creatorGlobex,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
