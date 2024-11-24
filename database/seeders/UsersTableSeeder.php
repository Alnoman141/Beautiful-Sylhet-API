<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $user = User::firstOrCreate([
                'first_name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => bcrypt('superadmin'),
            ]);
            $user->assignRole('superadmin');
            $this->command->info('Super Admin created successfully');
        }catch (\Exception $e) {
            $this->command->error($e->getMessage());
        }
    }
}
