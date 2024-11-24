<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name'=>'Breaking News',
                'type'=>'breaking',
            ]
            ,
            [
                'name'=>'Common',
                'type'=>'common',
            ]
        ];
        try {
            foreach ($types as $type) {
                \App\Models\NewsType::firstOrCreate($type);
            }
            $this->command->info('News Types created successfully');
        }catch (\Exception $e) {
            $this->command->error($e->getMessage());
        }
    }
}
