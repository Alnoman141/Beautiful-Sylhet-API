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
        Schema::create('tour_operator_parents', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_document')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('tin_document')->nullable();
            // service locations will be stored as a JSON array of strings
            $table->json('service_locations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_operator_parents');
    }
};
