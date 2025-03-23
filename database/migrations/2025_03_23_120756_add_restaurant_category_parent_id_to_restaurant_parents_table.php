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
        Schema::table('restaurant_parents', function (Blueprint $table) {
            $table->foreignId('restaurant_category_parent_id')->nullable()->constrained('restaurant_category_parents')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_parents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_category_parent_id');
        });
    }
};
