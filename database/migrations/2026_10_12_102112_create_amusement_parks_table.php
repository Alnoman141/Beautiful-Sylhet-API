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
        Schema::create('amusement_parks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->text('description')->nullable();
            $table->string('distance')->nullable();
            $table->string('ticket_price')->nullable();
            $table->string('opening_hours')->nullable();
            $table->string('map')->nullable();

            $table->foreignId('amusement_park_parent_id')
                ->constrained('amusement_park_parents')
                ->cascadeOnDelete();

            $table->foreignId('language_id')
                ->constrained('languages')
                ->cascadeOnDelete();
            $table->unique(
                ['amusement_park_parent_id', 'language_id'],
                'amusement_parks_parent_language_unique'
            );

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amusement_parks');
    }
};
