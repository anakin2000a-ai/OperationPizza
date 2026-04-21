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
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
                        'id' => 2,

            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}