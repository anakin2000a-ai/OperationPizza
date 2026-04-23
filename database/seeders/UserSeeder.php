<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
            'store_id' => 1,
            'role' => 'SecondShiftStoreManager',
        ]);
         User::create([
            'id' => 2,
            'name' => 'Admin User',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
            'store_id' => 1,
            'role' => 'ThirdShiftStoreManager',
        ]);
         User::create([
            'id' => 3,
            'name' => 'Admin User',
            'email' => 'admin3@example.com',
            'password' => Hash::make('password'),
            'store_id' =>  null,
            'role' => 'SeniorManager',
        ]);

       
    }
}