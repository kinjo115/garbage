<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempUser;
use App\Models\Item;
use App\Models\SelectedItem;

class TempUserItemController extends Controller
{
    public function index($token)
    {
        $tempUser = TempUser::where('token', $token)->first();
        if (!$tempUser) {
            return abort(404);
        }

        $items = Item::all();

        $selected = SelectedItem::where('temp_user_id', $tempUser->id)
            ->whereNull('user_id')
            ->first();

        $initialSelectedItems = $selected?->selected_items ?? [];

        return view('user.temp_user.item.index', compact('tempUser', 'items', 'initialSelectedItems'));
    }

    /**
     * 選択された品目を保存
     */
    public function store(Request $request, $token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();

        $itemsJson = $request->input('items_json');

        if (empty($itemsJson)) {
            return back()->withErrors('品目が選択されていません。');
        }

        $items = json_decode($itemsJson, true);

        if (!is_array($items) || count($items) === 0) {
            return back()->withErrors('品目が正しく選択されていません。');
        }

        // 簡易バリデーション（id, quantity が存在することを確認）
        $idQuantityMap = [];
        foreach ($items as $item) {
            if (!isset($item['id'], $item['quantity'])) {
                return back()->withErrors('品目データが不正です。');
            }
            if (!is_int($item['quantity']) && !ctype_digit((string) $item['quantity'])) {
                return back()->withErrors('個数が不正です。');
            }

            $itemId = (int) $item['id'];
            $qty = (int) $item['quantity'];
            if ($itemId <= 0 || $qty <= 0) {
                return back()->withErrors('品目データが不正です。');
            }
            $idQuantityMap[$itemId] = $qty;
        }

        // DBの品目情報から金額を再計算（フロント側の価格は信用しない）
        $dbItems = Item::whereIn('id', array_keys($idQuantityMap))->get();
        if ($dbItems->count() === 0) {
            return back()->withErrors('有効な品目が見つかりませんでした。');
        }

        $normalizedItems = [];
        $totalQuantity = 0;
        $totalAmount = 0;

        foreach ($dbItems as $dbItem) {
            $qty = $idQuantityMap[$dbItem->id] ?? 0;
            if ($qty <= 0) {
                continue;
            }

            $lineAmount = $dbItem->price * $qty;
            $totalQuantity += $qty;
            $totalAmount += $lineAmount;

            $normalizedItems[] = [
                'id' => $dbItem->id,
                'name' => $dbItem->name,
                'price' => $dbItem->price,
                'quantity' => $qty,
                'line_amount' => $lineAmount,
            ];
        }

        if (empty($normalizedItems)) {
            return back()->withErrors('有効な品目が選択されていません。');
        }

        // selected_items テーブルに保存（temp_user_id と紐付け）
        SelectedItem::updateOrCreate(
            [
                'temp_user_id' => $tempUser->id,
                'user_id' => null,
            ],
            [
                'selected_items' => $normalizedItems,
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
            ]
        );

        return redirect()
            ->route('user.confirmation.index', ['token' => $token])
            ->with('success', '品目を保存しました。');
    }

    public function confirmationIndex($token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();

        $selected = SelectedItem::where('temp_user_id', $tempUser->id)
            ->whereNull('user_id')
            ->first();

        $initialSelectedItems = $selected?->selected_items ?? [];

        return view('user.temp_user.item.confirmation', compact('tempUser', 'selected', 'initialSelectedItems'));
    }
}