<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::firstOrCreate(
            ['email' => 'admin@skenacoffee.id'],
            [
                'name' => 'Admin Skena',
                'password' => Hash::make('skena123'),
            ]
        );
    }
}
