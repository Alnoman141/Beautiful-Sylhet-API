<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add the column to the parent table.
        Schema::table('tea_estate_parents', function (Blueprint $table) {
            $table->string('map', 255)->nullable()->after('image');
        });

        // 2. Preserve existing data: copy the first non-empty map
        //    from each parent's translations to the parent.
        DB::table('tea_estate_parents')->orderBy('id')->each(function ($parent) {
            $map = DB::table('tea_estates')
                ->where('tea_estate_parent_id', $parent->id)
                ->whereNotNull('map')
                ->where('map', '!=', '')
                ->orderBy('id')
                ->value('map');

            if ($map !== null) {
                DB::table('tea_estate_parents')
                    ->where('id', $parent->id)
                    ->update(['map' => $map]);
            }
        });

        // 3. Drop the column from the translations table.
        Schema::table('tea_estates', function (Blueprint $table) {
            $table->dropColumn('map');
        });
    }

    public function down(): void
    {
        // 1. Re-add the column to the translations table.
        Schema::table('tea_estates', function (Blueprint $table) {
            $table->string('map', 255)->nullable();
        });

        // 2. Copy each parent's map back to all of its translations.
        DB::table('tea_estate_parents')
            ->whereNotNull('map')
            ->orderBy('id')
            ->each(function ($parent) {
                DB::table('tea_estates')
                    ->where('tea_estate_parent_id', $parent->id)
                    ->update(['map' => $parent->map]);
            });

        // 3. Drop the column from the parent table.
        Schema::table('tea_estate_parents', function (Blueprint $table) {
            $table->dropColumn('map');
        });
    }
};