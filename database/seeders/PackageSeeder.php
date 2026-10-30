<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        Package::updateOrCreate(
            ['name' => 'Free'],
            [
                'price' => 0,
                'total_transaction_limit' => 30,
                'total_imei_limit' => 50,
                'is_default' => true,
            ],
        );

        Package::updateOrCreate(
            ['name' => 'Pro'],
            [
                'price' => 90000,
                'total_transaction_limit' => null,
                'total_imei_limit' => null,
                'is_default' => false,
            ],
        );
    }
}
