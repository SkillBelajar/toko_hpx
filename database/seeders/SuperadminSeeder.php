<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@saas.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => User::ROLE_SUPERADMIN,
                'tenant_id' => null,
            ],
        );
    }
}
