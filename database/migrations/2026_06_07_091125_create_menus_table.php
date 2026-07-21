<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unsignedBigInteger('price');
            $table->boolean('has_hot')->default(false);
            $table->unsignedBigInteger('price_hot')->nullable();
            $table->text('desc_hot')->nullable();
            $table->boolean('has_ice')->default(false);
            $table->unsignedBigInteger('price_ice')->nullable();
            $table->text('desc_ice')->nullable();
            $table->string('image')->nullable();
            $table->string('tag')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->unsignedInteger('reviews')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
