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
        Schema::create('restaurant_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_parent_id')->nullable()->constrained('spot_parents')->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->longText('map_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_parents');
    }
};
