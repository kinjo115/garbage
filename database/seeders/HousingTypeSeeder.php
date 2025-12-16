<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HousingType;

class HousingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            '戸建住宅',
            '集合住宅'
        ];

        foreach ($types as $type) {
            HousingType::create([
                'name' => $type
            ]);
        }
    }
}