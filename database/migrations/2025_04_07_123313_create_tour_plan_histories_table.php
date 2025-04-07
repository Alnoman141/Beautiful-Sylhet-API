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
        Schema::create('tour_plan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_plan_id')->constrained('tour_plans')->cascadeOnDelete();
            $table->string('type',20);
            $table->longText('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_plan_histories');
    }
};
