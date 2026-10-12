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
        Schema::create('tour_operators', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('name');
            $table->string('address');
            $table->longText('description')->nullable();
            $table->foreignId('tour_operator_parent_id')->constrained('tour_operator_parents')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->unique(
                ['tour_operator_parent_id', 'language_id'],
                'tour_operators_parent_language_unique'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_operators');
    }
};
