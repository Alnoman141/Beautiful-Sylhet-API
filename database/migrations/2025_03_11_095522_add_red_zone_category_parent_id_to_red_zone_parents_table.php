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
        Schema::table('red_zone_parents', function (Blueprint $table) {
            $table->foreignId('red_zone_category_parent_id')
                ->nullable()
                ->after('spot_parent_id')
                ->constrained('red_zone_category_parents')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('red_zone_parents', function (Blueprint $table) {
            $table->dropForeign(['red_zone_category_parent_id']);
            $table->dropColumn('red_zone_category_parent_id');
        });
    }
};
