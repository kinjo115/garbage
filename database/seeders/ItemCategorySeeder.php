<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemCategory;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ダミーカテゴリーを作成
        ItemCategory::create([
            'name' => '大型ゴミ',
            'parent_id' => null,
            'sort' => 1,
        ]);

        ItemCategory::create([
            'name' => '家具',
            'parent_id' => null,
            'sort' => 2,
        ]);

        ItemCategory::create([
            'name' => '家電',
            'parent_id' => null,
            'sort' => 3,
        ]);
    }
}
