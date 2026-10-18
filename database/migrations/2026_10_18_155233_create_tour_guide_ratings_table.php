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
        Schema::create('tour_guide_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_guide_parent_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('review')->nullable();
            $table->unsignedTinyInteger('status')->default(0); // 0 = pending, 1 = approved, 2 = rejected

            $table->foreign('tour_guide_parent_id')->references('id')->on('tour_guides');
            $table->foreign('user_id')->references('id')->on('users');

            // user can only rate a tour_guide once
            $table->unique(['tour_guide_parent_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_guide_ratings');
    }
};
