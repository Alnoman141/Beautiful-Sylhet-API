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
        Schema::create('bank_atm_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_parent_id')->constrained('bank_parents')->cascadeOnDelete();
            $table->foreignId('spot_parent_id')->nullable()->constrained('spot_parents')->cascadeOnDelete();
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
        Schema::dropIfExists('bank_atm_parents');
    }
};
