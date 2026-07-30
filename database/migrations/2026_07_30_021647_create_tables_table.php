<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->string('name');
            $table->unsignedInteger('capacity')->default(4);
            $table->timestamps();
        });

        // Seed default 8 tables
        DB::table('tables')->insert([
            ['number' => 1, 'name' => 'Meja 1', 'capacity' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['number' => 2, 'name' => 'Meja 2', 'capacity' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['number' => 3, 'name' => 'Meja 3', 'capacity' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['number' => 4, 'name' => 'Meja 4', 'capacity' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['number' => 5, 'name' => 'Meja 5', 'capacity' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['number' => 6, 'name' => 'Meja 6', 'capacity' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['number' => 7, 'name' => 'Meja 7', 'capacity' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['number' => 8, 'name' => 'Meja 8', 'capacity' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
