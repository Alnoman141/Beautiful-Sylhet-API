<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name'=>'Bangla',
                'code'=>'bn',
            ],
            [
                'name' => 'English',
                'code' => 'en',
            ]
        ];
        try {
            foreach ($languages as $language) {
                \App\Models\Language::firstOrCreate($language);
            }
            $this->command->info('Languages created successfully');
        } catch (\Exception $e) {
            $this->command->error($e->getMessage());
        }
    }
}
