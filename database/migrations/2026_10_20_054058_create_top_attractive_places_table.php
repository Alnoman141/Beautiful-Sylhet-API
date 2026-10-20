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
        Schema::create('top_attractive_places', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tag_line');
            $table->string('tag');
            $table->unsignedTinyInteger('status')->default(1);

            $table->foreignId('top_attractive_place_parent_id')->constrained('top_attractive_place_parents')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_attractive_places');
    }
};
