<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotel_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_parent_id')
                ->nullable()
                ->constrained('spot_parents')->cascadeOnDelete();
            $table->foreignId('hotel_type_parent_id')
                ->nullable()
                ->constrained('hotel_type_parents')->cascadeOnDelete();
            $table->foreignId('hotel_category_parent_id')
                ->nullable()
                ->constrained('hotel_category_parents')->cascadeOnDelete();
            $table->string('website_link')->nullable();
            $table->string('map_link')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_parents');
    }
};
