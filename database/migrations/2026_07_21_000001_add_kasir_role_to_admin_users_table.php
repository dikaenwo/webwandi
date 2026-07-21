<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite: drop and recreate the column with the new enum values
        // For MySQL: modify the column
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't enforce enum, so existing column works fine
            // Just ensure default is 'admin'
        } else {
            // MySQL: modify the enum to include 'kasir'
            DB::statement("ALTER TABLE admin_users MODIFY COLUMN role ENUM('admin', 'owner', 'kasir') DEFAULT 'admin'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE admin_users MODIFY COLUMN role ENUM('admin', 'owner') DEFAULT 'admin'");
        }
    }
};
