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
        Schema::table('spot_parents', function (Blueprint $table) {
            $table->unsignedBigInteger('spot_type_parent_id')->nullable()->after('id');
            $table->foreign('spot_type_parent_id')->references('id')->on('spot_type_parents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spot_parents', function (Blueprint $table) {
            $table->dropForeign(['spot_type_parent_id']);
            $table->dropColumn('spot_type_parent_id');
        });
    }
};
