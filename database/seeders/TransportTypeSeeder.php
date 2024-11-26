<?php

namespace Database\Seeders;

use App\Models\TransportTypeParent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id' => 1,
                'names'=>[
                    [
                        'name' => 'ফ্লাইট',
                        'language_id' => 1,
                    ],
                    [
                        'name' => 'Flight',
                        'language_id' => 2,
                    ],
                ]
            ],
            [
                'id' => 2,
                'names'=>[
                    [
                        'name' => 'ট্রেন',
                        'language_id' => 1,
                    ],
                    [
                        'name' => 'Train',
                        'language_id' => 2,
                    ],
                ]
            ],
            [
                'id'=>3,
                'names'=>[
                    [
                        'name' => 'জাহাজ',
                        'language_id' => 1,
                    ],
                    [
                        'name' => 'Ship',
                        'language_id' => 2,
                    ],
                ]
            ],
            [
                'id'=>4,
                'names'=>[
                    [
                        'name' => 'বাইক',
                        'language_id' => 1,
                    ],
                    [
                        'name' => 'Bike',
                        'language_id' => 2,
                    ],
                ]
            ],
            [
                'id'=>5,
                'names'=>[
                    [
                        'name' => 'চাঁদের গাড়ি',
                        'language_id' => 1,
                    ],
                    [
                        'name' => 'Chander Gari',
                        'language_id' => 2,
                    ],
                ]
            ]
        ];
        DB::beginTransaction();
        try {
            foreach ($types as $type) {
                $parent = TransportTypeParent::firstOrCreate(['id' => $type['id']]);
                foreach ($type['names'] as $name) {
                    $parent->types()->updateOrCreate(
                        ['language_id' => $name['language_id']],
                        ['name' => $name['name']]
                    );
                }
            }
            DB::commit();
            $this->command->info('Transport types are seeded successfully');
        }catch (\Exception $e) {
            DB::rollBack();
            $this->command->error($e->getMessage());
        }
    }
}
