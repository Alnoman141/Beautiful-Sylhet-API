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
        Schema::create('associations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('association_parent_id')
                ->constrained('association_parents')
                ->onDelete('cascade');
            $table->unsignedBigInteger('language_id')
                ->constrained('languages')
                ->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('president_name')->nullable();
            $table->string('secretary_name')->nullable();
            $table->string('address')->nullable();

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associations');
    }
};
