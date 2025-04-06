<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['superadmin', 'admin', 'editor','tourist'];
        try {
            foreach ($roles as $role) {
                Role::firstOrCreate(['name' => $role,'guard_name' => 'web']);
            }
        }catch (\Exception $e) {
            $this->command->error($e->getMessage());
        }
    }
}
