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

        if ($tempUser->status < 2) {
            return redirect()
                ->route('user.register.confirm.store.map', ['token' => $token])
                ->with('error', '申込みが完了していません。基本情報を入力してください。');
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

        if (!$selected) {
            return redirect()
                ->route('user.item.index', ['token' => $token])
                ->with('error', '品目が選択されていません。');
        }

        $initialSelectedItems = $selected?->selected_items ?? [];

        // 次回の第2水曜日を計算
        $nextSecondWednesday = $this->getNextSecondWednesday();

        return view('user.temp_user.item.confirmation', compact('tempUser', 'selected', 'initialSelectedItems', 'nextSecondWednesday'));
    }

    /**
     * 次回の第2水曜日を取得
     */
    private function getNextSecondWednesday()
    {
        $now = now();

        // 今月の第2水曜日を計算
        $firstDayOfMonth = \Carbon\Carbon::create($now->year, $now->month, 1);

        // 第1水曜日を取得
        $firstWednesday = $firstDayOfMonth->copy()->next(\Carbon\Carbon::WEDNESDAY);

        // 第2水曜日 = 第1水曜日 + 7日
        $secondWednesday = $firstWednesday->copy()->addWeek();

        // 今月の第2水曜日が過ぎている場合は、来月の第2水曜日を計算
        if ($secondWednesday->isPast()) {
            $nextMonth = $firstDayOfMonth->copy()->addMonth();
            $firstDayOfNextMonth = \Carbon\Carbon::create($nextMonth->year, $nextMonth->month, 1);

            // 来月の第1水曜日を取得
            $firstWednesdayNextMonth = $firstDayOfNextMonth->copy()->next(\Carbon\Carbon::WEDNESDAY);

            // 来月の第2水曜日
            $secondWednesday = $firstWednesdayNextMonth->copy()->addWeek();
        }

        return [
            'date' => $secondWednesday,
            'formatted' => $secondWednesday->format('n月j日'),
            'day_of_week' => $secondWednesday->format('(D)'),
            'day_of_week_jp' => $this->getDayOfWeekJapanese($secondWednesday->dayOfWeek),
        ];
    }

    /**
     * 確認ページの送信処理
     */
    public function confirmationStore(Request $request, $token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();

        // 同意チェックボックスの確認
        if (!$request->has('agree_terms') || !$request->input('agree_terms')) {
            return back()->withErrors('「以上の内容に同意する」にチェックを入れてください。');
        }

        // 収集日の取得
        $collectionDate = $request->input('collection_date');
        if (empty($collectionDate)) {
            return back()->withErrors('収集日が設定されていません。');
        }

        // selected_items テーブルを更新（収集日を保存）
        $selected = SelectedItem::where('temp_user_id', $tempUser->id)
            ->whereNull('user_id')
            ->first();

        if (!$selected) {
            return redirect()
                ->route('user.item.index', ['token' => $token])
                ->with('error', '品目が選択されていません。');
        }

        $selected->collection_date = $collectionDate;
        $selected->confirm_status = SelectedItem::CONFIRM_STATUS_CONFIRMED; // 確認済み
        $selected->save();

        return redirect()
            ->route('user.payment.index', ['token' => $token])
            ->with('success', '申込内容を確認しました。支払い方法を選択してください。');
    }

    /**
     * 曜日を日本語で取得
     */
    private function getDayOfWeekJapanese($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek] ?? '';
    }
}