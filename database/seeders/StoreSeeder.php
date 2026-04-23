<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
         DB::table('stores')->insert([
            [
                'id' => 1,
                'store' => 'S1',
                'created_at' => '2026-04-21 15:56:40',
                'updated_at' => '2026-04-21 15:56:40',
            ],
            [
                'id' => 2,
                'store' => 'S2',
                'created_at' => '2026-04-21 15:56:40',
                'updated_at' => '2026-04-21 15:56:40',
            ],
            [
                'id' => 3,
                'store' => 'S3',
                'created_at' => '2026-04-21 15:56:40',
                'updated_at' => '2026-04-21 15:56:40',
            ],
        ]);
    }
}