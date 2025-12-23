<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * 品目一覧を表示
     */
    public function index(Request $request)
    {
        $query = Item::with('itemCategory');

        // 検索機能
        if ($request->filled('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('itemCategory', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }
        }

        // ステータスフィルター
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // カテゴリーフィルター
        if ($request->filled('category_id')) {
            $query->where('item_category_id', $request->category_id);
        }

        $items = $query->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->get();
        $categories = ItemCategory::orderBy('sort', 'asc')->get();

        return view('admin.items.index', compact('items', 'categories'));
    }

    /**
     * 品目作成画面を表示
     */
    public function create()
    {
        $categories = ItemCategory::orderBy('sort', 'asc')->get();
        return view('admin.items.create', compact('categories'));
    }

    /**
     * 品目を保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_category_id' => 'nullable|exists:item_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|string|max:255',
            'status' => 'required|in:0,1,2',
            'sort' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $item = new Item();
            $item->item_category_id = $request->item_category_id;
            $item->name = $request->name;
            $item->description = $request->description;
            $item->price = $request->price;
            $item->status = $request->status;
            $item->sort = $request->sort ?? 0;

            // 画像アップロード
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('items', 'public');
                $item->image = $imagePath;
            }

            $item->save();

            DB::commit();

            return redirect()->route('admin.items.index')
                ->with('success', '品目を作成しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', '品目の作成に失敗しました。')
                ->withInput();
        }
    }

    /**
     * 品目編集画面を表示
     */
    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $categories = ItemCategory::orderBy('sort', 'asc')->get();
        return view('admin.items.edit', compact('item', 'categories'));
    }

    /**
     * 品目情報を更新
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'item_category_id' => 'nullable|exists:item_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|string|max:255',
            'status' => 'required|in:0,1,2',
            'sort' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $item->item_category_id = $request->item_category_id;
            $item->name = $request->name;
            $item->description = $request->description;
            $item->price = $request->price;
            $item->status = $request->status;
            $item->sort = $request->sort ?? 0;

            // 画像アップロード
            if ($request->hasFile('image')) {
                // 古い画像を削除
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }
                $imagePath = $request->file('image')->store('items', 'public');
                $item->image = $imagePath;
            }

            $item->save();

            DB::commit();

            return redirect()->route('admin.items.index')
                ->with('success', '品目を更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', '品目の更新に失敗しました。')
                ->withInput();
        }
    }

    /**
     * 並び順を更新
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.sort' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                Item::where('id', $itemData['id'])
                    ->update(['sort' => $itemData['sort']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '並び順を更新しました。'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '並び順の更新に失敗しました。'
            ], 500);
        }
    }

    /**
     * 品目を削除
     */
    public function destroy(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        DB::beginTransaction();
        try {
            // 画像を削除
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            // ソフトデリート
            $item->delete();

            DB::commit();

            return redirect()->route('admin.items.index')
                ->with('success', '品目を削除しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', '品目の削除に失敗しました。');
        }
    }
}
