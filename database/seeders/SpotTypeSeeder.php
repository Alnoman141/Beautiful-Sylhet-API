<?php

namespace Database\Seeders;

use App\Models\SpotType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpotTypeSeeder extends Seeder
{
    // Adjust these to match the IDs in your `languages` table
    private const LANG_EN = 1;
    private const LANG_BN = 2;

    public function run(): void
    {
        $types = [
            1 => ['en' => 'General',        'bn' => 'সাধারণ'],
            2 => ['en' => 'Hill',           'bn' => 'পাহাড়'],
            3 => ['en' => 'Waterfall',      'bn' => 'ঝর্ণা'],
            4 => ['en' => 'Amusement Park', 'bn' => 'বিনোদন পার্ক'],
            5 => ['en' => 'Haor',           'bn' => 'হাওর'],
            6 => ['en' => 'Forest',         'bn' => 'বন'],
            7 => ['en' => 'Religious Place', 'bn' => 'ধর্মীয় স্থান'],
            8 => ['en' => 'Museum',         'bn' => 'জাদুঘর'],
            9 => ['en' => 'Tea Estate',     'bn' => 'চা বাগান'],
        ];

        $now = now();

        foreach ($types as $parentId => $names) {
            // spot_type_parents only has id + timestamps
            DB::table('spot_type_parents')->updateOrInsert(
                ['id' => $parentId],
                ['updated_at' => $now, 'created_at' => $now]
            );

            SpotType::updateOrCreate(
                ['spot_type_parent_id' => $parentId, 'language_id' => self::LANG_EN],
                ['name' => $names['en']]
            );

            SpotType::updateOrCreate(
                ['spot_type_parent_id' => $parentId, 'language_id' => self::LANG_BN],
                ['name' => $names['bn']]
            );
        }
    }
}