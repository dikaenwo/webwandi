<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class KasirSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'kasir@skenacoffee.id'],
            [
                'name'     => 'Kasir Skena',
                'email'    => 'kasir@skenacoffee.id',
                'password' => bcrypt('kasir123'),
                'role'     => 'kasir',
            ]
        );

        $this->command->info('✅ Kasir user created: kasir@skenacoffee.id / kasir123');
    }
}
