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
        Schema::create('tour_guide_parents', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('phone_number')->unique();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_document')->nullable();
            $table->json('service_locations')->nullable();
            $table->string('rate_per_day')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_guide_parents');
    }
};
