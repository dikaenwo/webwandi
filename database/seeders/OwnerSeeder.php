<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'owner@skenacoffee.id'],
            [
                'name'     => 'Owner Skena',
                'email'    => 'owner@skenacoffee.id',
                'password' => bcrypt('owner123'),
                'role'     => 'owner',
            ]
        );

        $this->command->info('✅ Owner user created: owner@skenacoffee.id / owner123');
    }
}
