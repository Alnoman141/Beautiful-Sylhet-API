<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Services\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //DB::beginTransaction();
        try {
            collect(Location::maps()['divisions'])->map(function ($a){
                $division = Division::firstOrCreate([
                    'en'=>$a['en'],
                    'bn'=>$a['bn'],
                ]);
                collect($a['districts'])->map(function ($b)use($division){
                    $district = $division->district()->firstOrCreate([
                            'en'=>$b['en'],
                            'bn'=>$b['bn'],
                            ]);
                    collect($b['upazilas'])->map(function ($c)use($district) {
                        $district->upazila()->firstOrCreate([
                            'en' => $c['en'],
                            'bn' => $c['bn'],
                        ]);
                    });
                });
            });
            //DB::commit();
            $this->command->info('Location table seeded successfully');
        }catch (\Exception $e){
            //DB::rollBack();
            $this->command->error($e->getMessage());
        }
    }
}
