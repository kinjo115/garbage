<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemCategoryController extends Controller
{
    /**
     * カテゴリー一覧を表示
     */
    public function index(Request $request)
    {
        $query = ItemCategory::with(['parent', 'items', 'children']);

        // 検索機能
        if ($request->filled('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('parent', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }
        }

        $categories = $query->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->get();

        return view('admin.item-categories.index', compact('categories'));
    }

    /**
     * カテゴリー作成画面を表示
     */
    public function create()
    {
        $parentCategories = ItemCategory::whereNull('parent_id')
            ->orderBy('sort', 'asc')
            ->get();
        return view('admin.item-categories.create', compact('parentCategories'));
    }

    /**
     * カテゴリーを保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:item_categories,id',
            'sort' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $category = new ItemCategory();
            $category->name = $request->name;
            $category->parent_id = $request->parent_id;
            $category->sort = $request->sort ?? 0;

            $category->save();

            DB::commit();

            return redirect()->route('admin.item-categories.index')
                ->with('success', 'カテゴリーを作成しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'カテゴリーの作成に失敗しました。')
                ->withInput();
        }
    }

    /**
     * カテゴリー編集画面を表示
     */
    public function edit($id)
    {
        $category = ItemCategory::findOrFail($id);
        $parentCategories = ItemCategory::whereNull('parent_id')
            ->where('id', '!=', $id) // 自分自身を親カテゴリーとして選択できないようにする
            ->orderBy('sort', 'asc')
            ->get();
        return view('admin.item-categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * カテゴリー情報を更新
     */
    public function update(Request $request, $id)
    {
        $category = ItemCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:item_categories,id|not_in:' . $id, // 自分自身を親カテゴリーとして選択できないようにする
            'sort' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $category->name = $request->name;
            $category->parent_id = $request->parent_id;
            $category->sort = $request->sort ?? 0;

            $category->save();

            DB::commit();

            return redirect()->route('admin.item-categories.index')
                ->with('success', 'カテゴリーを更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'カテゴリーの更新に失敗しました。')
                ->withInput();
        }
    }

    /**
     * カテゴリーを削除
     */
    public function destroy(Request $request, $id)
    {
        $category = ItemCategory::findOrFail($id);

        DB::beginTransaction();
        try {
            // 子カテゴリーがある場合は削除できない
            if ($category->children()->count() > 0) {
                return redirect()->back()
                    ->with('error', '子カテゴリーが存在するため、削除できません。先に子カテゴリーを削除してください。');
            }

            // 品目が紐づいている場合は削除できない
            if ($category->items()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'このカテゴリーに紐づいている品目が存在するため、削除できません。');
            }

            // ソフトデリート
            $category->delete();

            DB::commit();

            return redirect()->route('admin.item-categories.index')
                ->with('success', 'カテゴリーを削除しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'カテゴリーの削除に失敗しました。');
        }
    }
}
