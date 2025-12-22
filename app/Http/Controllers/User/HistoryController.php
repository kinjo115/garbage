<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SelectedItem;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 決済済みの申込み履歴を取得（キャンセルされていないもの、最新順）
        $selectedItems = SelectedItem::where('user_id', $user->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();


        return view('user.history.index', compact('selectedItems'));
    }

    public function show($id)
    {
        $user = Auth::user();

        $selectedItem = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // ユーザー情報を取得（prefectureも一緒に取得）
        $userInfo = $user->userInfo()->with('prefecture')->first();

        if (!$userInfo) {
            return redirect()->route('user.history.index')
                ->with('error', 'ユーザー情報が見つかりませんでした。');
        }

        // 選択済み品目を取得
        $initialSelectedItems = $selectedItem->selected_items ?? [];

        // 品目一覧を取得（編集可能にするため）
        $items = Item::with('itemCategory')->get();

        // 収集日をフォーマット
        $collectionDateFormatted = null;
        $dayOfWeek = '';
        if ($selectedItem->collection_date) {
            $collectionDate = Carbon::parse($selectedItem->collection_date);
            $collectionDateFormatted = $collectionDate->format('n月j日');
            $dayOfWeek = $this->getDayOfWeekJapanese($collectionDate->dayOfWeek);
        }

        // 受付番号を生成
        $receptionNumber = null;
        if ($selectedItem->reception_number_serial && $selectedItem->payment_date) {
            $paymentDate = Carbon::parse($selectedItem->payment_date);
            $yy = $paymentDate->format('y');
            $mm = $paymentDate->format('m');
            $serial = str_pad($selectedItem->reception_number_serial, 5, '0', STR_PAD_LEFT);
            $receptionNumber = $yy . $mm . '-' . $serial;
        }

        // 決済方法の表示名
        $paymentMethodName = '未決済';
        if ($selectedItem->payment_method === 'online') {
            $paymentMethodName = 'オンライン決済';
        } elseif ($selectedItem->payment_method === 'convenience') {
            $paymentMethodName = 'コンビニ決済';
        }

        return view('user.history.show', compact(
            'selectedItem',
            'userInfo',
            'initialSelectedItems',
            'items',
            'collectionDateFormatted',
            'dayOfWeek',
            'receptionNumber',
            'paymentMethodName'
        ));
    }

    /**
     * 申込みをキャンセル
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $itemId = $request->input('item_id');

        if (!$itemId) {
            return response()->json([
                'success' => false,
                'message' => '申込みIDが指定されていません。'
            ], 400);
        }

        $selectedItem = SelectedItem::where('id', $itemId)
            ->where('user_id', $user->id)
            ->first();

        if (!$selectedItem) {
            return response()->json([
                'success' => false,
                'message' => '申込みが見つかりませんでした。'
            ], 404);
        }

        // 既にキャンセル済みかチェック
        if ($selectedItem->confirm_status === SelectedItem::CONFIRM_STATUS_CANCELLED) {
            return response()->json([
                'success' => false,
                'message' => 'この申込みは既にキャンセルされています。'
            ], 400);
        }

        try {
            // confirm_statusをCANCELLEDに設定
            $selectedItem->confirm_status = SelectedItem::CONFIRM_STATUS_CANCELLED;
            $selectedItem->save();

            return response()->json([
                'success' => true,
                'message' => '申込みをキャンセルしました。'
            ]);
        } catch (\Exception $e) {
            Log::error('申込みキャンセルエラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'item_id' => $itemId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'キャンセル処理に失敗しました。もう一度お試しください。'
            ], 500);
        }
    }

    /**
     * 選択された品目を更新
     */
    public function updateItems(Request $request, $id)
    {
        $user = Auth::user();

        $selectedItem = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // キャンセル済みかチェック
        if ($selectedItem->confirm_status === SelectedItem::CONFIRM_STATUS_CANCELLED) {
            return response()->json([
                'success' => false,
                'message' => 'キャンセル済みの申込みは変更できません。'
            ], 400);
        }

        $itemsJson = $request->input('items_json');

        if (empty($itemsJson)) {
            return response()->json([
                'success' => false,
                'message' => '品目が選択されていません。'
            ], 400);
        }

        $items = json_decode($itemsJson, true);

        if (!is_array($items) || count($items) === 0) {
            return response()->json([
                'success' => false,
                'message' => '品目が正しく選択されていません。'
            ], 400);
        }

        // 簡易バリデーション（id, quantity が存在することを確認）
        $idQuantityMap = [];
        foreach ($items as $item) {
            if (!isset($item['id'], $item['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => '品目データが不正です。'
                ], 400);
            }
            if (!is_int($item['quantity']) && !ctype_digit((string) $item['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => '個数が不正です。'
                ], 400);
            }

            $itemId = (int) $item['id'];
            $qty = (int) $item['quantity'];
            if ($itemId <= 0 || $qty <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => '品目データが不正です。'
                ], 400);
            }
            $idQuantityMap[$itemId] = $qty;
        }

        // DBの品目情報から金額を再計算（フロント側の価格は信用しない）
        $dbItems = Item::whereIn('id', array_keys($idQuantityMap))->get();
        if ($dbItems->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => '有効な品目が見つかりませんでした。'
            ], 400);
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
            return response()->json([
                'success' => false,
                'message' => '有効な品目が選択されていません。'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // selected_items テーブルを更新
            $selectedItem->selected_items = $normalizedItems;
            $selectedItem->total_quantity = $totalQuantity;
            $selectedItem->total_amount = $totalAmount;
            $selectedItem->save();

            DB::commit();

            // 確認ページにリダイレクト
            return redirect()
                ->route('user.history.confirmation', ['id' => $id])
                ->with('success', '品目を更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('品目更新エラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'selected_item_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '品目の更新に失敗しました。もう一度お試しください。'
            ], 500);
        }
    }

    /**
     * 確認ページを表示
     */
    public function confirmation($id)
    {
        $user = Auth::user();

        $selectedItem = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // キャンセル済みかチェック
        if ($selectedItem->confirm_status === SelectedItem::CONFIRM_STATUS_CANCELLED) {
            return redirect()->route('user.history.index')
                ->with('error', 'キャンセル済みの申込みは確認できません。');
        }

        // ユーザー情報を取得（prefectureも一緒に取得）
        $userInfo = $user->userInfo()->with('prefecture')->first();

        if (!$userInfo) {
            return redirect()->route('user.history.index')
                ->with('error', 'ユーザー情報が見つかりませんでした。');
        }

        // 選択済み品目を取得
        $initialSelectedItems = $selectedItem->selected_items ?? [];

        // 収集日を取得（既存のcollection_dateがある場合はそれを使用、ない場合は次回の第2水曜日を計算）
        $nextSecondWednesday = null;
        if ($selectedItem->collection_date) {
            $collectionDate = Carbon::parse($selectedItem->collection_date);
            $nextSecondWednesday = [
                'date' => $collectionDate,
                'formatted' => $collectionDate->format('n月j日'),
                'day_of_week' => $collectionDate->format('(D)'),
                'day_of_week_jp' => $this->getDayOfWeekJapanese($collectionDate->dayOfWeek),
            ];
        } else {
            $nextSecondWednesday = $this->getNextSecondWednesday();
        }

        // 受付番号を生成
        $receptionNumber = null;
        if ($selectedItem->reception_number_serial && $selectedItem->payment_date) {
            $paymentDate = Carbon::parse($selectedItem->payment_date);
            $yy = $paymentDate->format('y');
            $mm = $paymentDate->format('m');
            $serial = str_pad($selectedItem->reception_number_serial, 5, '0', STR_PAD_LEFT);
            $receptionNumber = $yy . $mm . '-' . $serial;
        }

        // 決済方法の表示名
        $paymentMethodName = '未決済';
        if ($selectedItem->payment_method === 'online') {
            $paymentMethodName = 'オンライン決済';
        } elseif ($selectedItem->payment_method === 'convenience') {
            $paymentMethodName = 'コンビニ決済';
        }

        return view('user.history.confirmation', compact(
            'selectedItem',
            'userInfo',
            'initialSelectedItems',
            'nextSecondWednesday',
            'receptionNumber',
            'paymentMethodName'
        ));
    }

    /**
     * 確認ページの送信処理（収集日を保存）
     */
    public function confirmationStore(Request $request, $id)
    {
        $user = Auth::user();

        $selectedItem = SelectedItem::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // キャンセル済みかチェック
        if ($selectedItem->confirm_status === SelectedItem::CONFIRM_STATUS_CANCELLED) {
            return redirect()->route('user.history.index')
                ->with('error', 'キャンセル済みの申込みは変更できません。');
        }

        // 同意チェックボックスの確認
        if (!$request->has('agree_terms') || !$request->input('agree_terms')) {
            return back()->withErrors('「以上の内容に同意する」にチェックを入れてください。');
        }

        // 収集日の取得
        $collectionDate = $request->input('collection_date');
        if (empty($collectionDate)) {
            return back()->withErrors('収集日が設定されていません。');
        }

        try {
            // selected_items テーブルを更新（収集日を保存）
            $selectedItem->collection_date = $collectionDate;
            $selectedItem->confirm_status = SelectedItem::CONFIRM_STATUS_CONFIRMED; // 確認済み
            $selectedItem->save();

            return redirect()
                ->route('user.history.show', ['id' => $id])
                ->with('success', '申込内容を確認しました。');
        } catch (\Exception $e) {
            Log::error('確認ページ保存エラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'selected_item_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', '保存に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * 次回の第2水曜日を取得
     */
    private function getNextSecondWednesday()
    {
        $now = now();

        // 今月の第2水曜日を計算
        $firstDayOfMonth = Carbon::create($now->year, $now->month, 1);

        // 第1水曜日を取得
        $firstWednesday = $firstDayOfMonth->copy()->next(Carbon::WEDNESDAY);

        // 第2水曜日 = 第1水曜日 + 7日
        $secondWednesday = $firstWednesday->copy()->addWeek();

        // 今月の第2水曜日が過ぎている場合は、来月の第2水曜日を計算
        if ($secondWednesday->isPast()) {
            $nextMonth = $firstDayOfMonth->copy()->addMonth();
            $firstDayOfNextMonth = Carbon::create($nextMonth->year, $nextMonth->month, 1);

            // 来月の第1水曜日を取得
            $firstWednesdayNextMonth = $firstDayOfNextMonth->copy()->next(Carbon::WEDNESDAY);

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
     * 曜日を日本語で取得
     */
    private function getDayOfWeekJapanese($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek] ?? '';
    }
}
