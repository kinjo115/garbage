<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemCategory;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // カテゴリーを取得（存在しない場合は作成）
        $largeGarbageCategory = ItemCategory::firstOrCreate(
            ['name' => '大型ゴミ'],
            ['sort' => 1]
        );

        $furnitureCategory = ItemCategory::firstOrCreate(
            ['name' => '家具'],
            ['sort' => 2]
        );

        $applianceCategory = ItemCategory::firstOrCreate(
            ['name' => '家電'],
            ['sort' => 3]
        );

        // ダミーアイテムを作成（10〜20個）
        $items = [
            [
                'item_category_id' => $largeGarbageCategory->id,
                'name' => '犬小屋（大）',
                'description' => '大型の犬小屋です。',
                'price' => '1000',
                'status' => 1,
                'sort' => 1,
            ],
            [
                'item_category_id' => $largeGarbageCategory->id,
                'name' => 'ソファ（3人掛け）',
                'description' => '3人掛けのソファです。',
                'price' => '2000',
                'status' => 1,
                'sort' => 2,
            ],
            [
                'item_category_id' => $largeGarbageCategory->id,
                'name' => 'テーブル（大型）',
                'description' => '大型のテーブルです。',
                'price' => '1500',
                'status' => 1,
                'sort' => 3,
            ],
            [
                'item_category_id' => $largeGarbageCategory->id,
                'name' => 'ベッド（シングル）',
                'description' => 'シングルサイズのベッドです。',
                'price' => '3000',
                'status' => 1,
                'sort' => 4,
            ],
            [
                'item_category_id' => $largeGarbageCategory->id,
                'name' => '本棚（大型）',
                'description' => '大型の本棚です。',
                'price' => '1800',
                'status' => 1,
                'sort' => 5,
            ],
            [
                'item_category_id' => $furnitureCategory->id,
                'name' => 'チェア（オフィス用）',
                'description' => 'オフィス用のチェアです。',
                'price' => '1200',
                'status' => 1,
                'sort' => 6,
            ],
            [
                'item_category_id' => $furnitureCategory->id,
                'name' => 'タンス（5段）',
                'description' => '5段のタンスです。',
                'price' => '2500',
                'status' => 1,
                'sort' => 7,
            ],
            [
                'item_category_id' => $furnitureCategory->id,
                'name' => 'テレビ台',
                'description' => 'テレビを置くための台です。',
                'price' => '1500',
                'status' => 1,
                'sort' => 8,
            ],
            [
                'item_category_id' => $furnitureCategory->id,
                'name' => '食器棚',
                'description' => '食器を収納する棚です。',
                'price' => '2000',
                'status' => 1,
                'sort' => 9,
            ],
            [
                'item_category_id' => $applianceCategory->id,
                'name' => '冷蔵庫（大型）',
                'description' => '大型の冷蔵庫です。',
                'price' => '5000',
                'status' => 1,
                'sort' => 10,
            ],
            [
                'item_category_id' => $applianceCategory->id,
                'name' => '洗濯機',
                'description' => '家庭用の洗濯機です。',
                'price' => '4000',
                'status' => 1,
                'sort' => 11,
            ],
            [
                'item_category_id' => $applianceCategory->id,
                'name' => 'エアコン',
                'description' => 'エアコン本体です。',
                'price' => '3500',
                'status' => 1,
                'sort' => 12,
            ],
            [
                'item_category_id' => $applianceCategory->id,
                'name' => 'テレビ（32インチ）',
                'description' => '32インチのテレビです。',
                'price' => '3000',
                'status' => 1,
                'sort' => 13,
            ],
            [
                'item_category_id' => $applianceCategory->id,
                'name' => '電子レンジ',
                'description' => '電子レンジです。',
                'price' => '1500',
                'status' => 1,
                'sort' => 14,
            ],
            [
                'item_category_id' => $largeGarbageCategory->id,
                'name' => '自転車',
                'description' => '自転車です。',
                'price' => '2000',
                'status' => 1,
                'sort' => 15,
            ],
            [
                'item_category_id' => $largeGarbageCategory->id,
                'name' => 'マットレス（ダブル）',
                'description' => 'ダブルサイズのマットレスです。',
                'price' => '2500',
                'status' => 1,
                'sort' => 16,
            ],
            [
                'item_category_id' => $furnitureCategory->id,
                'name' => 'デスク',
                'description' => '学習用のデスクです。',
                'price' => '1800',
                'status' => 1,
                'sort' => 17,
            ],
            [
                'item_category_id' => $furnitureCategory->id,
                'name' => 'サイドボード',
                'description' => 'リビング用のサイドボードです。',
                'price' => '2200',
                'status' => 1,
                'sort' => 18,
            ],
            [
                'item_category_id' => $applianceCategory->id,
                'name' => '掃除機',
                'description' => '家庭用の掃除機です。',
                'price' => '2000',
                'status' => 1,
                'sort' => 19,
            ],
            [
                'item_category_id' => $applianceCategory->id,
                'name' => '扇風機',
                'description' => '扇風機です。',
                'price' => '800',
                'status' => 1,
                'sort' => 20,
            ],
        ];

        // アイテムを作成
        foreach ($items as $itemData) {
            Item::create([
                'item_category_id' => $itemData['item_category_id'],
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'image' => null,
                'price' => $itemData['price'],
                'status' => $itemData['status'],
                'sort' => $itemData['sort'],
            ]);
        }
    }
}
