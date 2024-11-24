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
        Schema::create('spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_parent_id')->constrained('spot_parents')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->text('tag_line')->nullable();
            $table->string('distance')->nullable();
            $table->longText('travel_info')->nullable();
            $table->longText('foods')->nullable();
            $table->string('visiting_time')->nullable();
            $table->longText('rides')->nullable();
            $table->string('fair')->nullable();
            $table->longText('details')->nullable();
            $table->longText('safety_guideline')->nullable();
            $table->longText('tourist_places')->nullable();
            $table->longText('hazard_info')->nullable();
            $table->longText('advice')->nullable();
            $table->longText('others_info')->nullable();
            $table->longText('risk_factor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spots');
    }
};
