<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $countries = File::get(public_path('countries.json'));
            collect(json_decode($countries)->data)->map(function ($item) {;
                $data = [
                    'name' => $item->value,
                    'flag' => $item->emoji,
                ];
                Country::firstOrCreate($data);
            });
            $this->command->info('Countries seeded successfully');
        }catch (\Exception $e) {
            $this->command->error('Error: ' . $e->getMessage());
        }
    }
}
