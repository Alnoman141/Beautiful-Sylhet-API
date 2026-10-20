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
        Schema::table('police_parents', function (Blueprint $table) {
            $table->unsignedBigInteger('upazila_id')->after('id')->nullable();
            $table->enum('type', ['local_thana', 'tourist_police'])->after('upazila_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('police_parents', function (Blueprint $table) {
            $table->dropColumn('upazila_id');
            $table->dropColumn('type');
        });
    }
};
