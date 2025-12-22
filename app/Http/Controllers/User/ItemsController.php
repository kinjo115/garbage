<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\SelectedItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ItemsController extends Controller
{
    /**
     * 品目選択画面を表示
     */
    public function index()
    {
        $user = Auth::user();

        // ユーザー情報を確認
        $userInfo = $user->userInfo;
        if (!$userInfo) {
            return redirect()->route('user.info.edit')
                ->with('error', '基本情報を入力してください。');
        }

        // 地図情報を確認
        if (!$userInfo->home_latitude || !$userInfo->home_longitude) {
            return redirect()->route('user.info.map')
                ->with('error', '地図登録を完了してください。');
        }

        $items = Item::with('itemCategory')->get();

        // 認証済みユーザーの未確定のSelectedItemを取得（新規申込み用）
        $selected = SelectedItem::where('user_id', $user->id)
            ->whereNull('temp_user_id')
            ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CONFIRMED)
            ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CANCELLED)
            ->first();

        $initialSelectedItems = $selected?->selected_items ?? [];

        // TempUser形式で渡す（ブレードの互換性のため）
        $tempUser = (object)[
            'token' => null, // 認証済みユーザーなのでトークンは不要
            'email' => $user->email,
            'userInfo' => $userInfo,
        ];

        return view('user.item.index', compact('tempUser', 'items', 'initialSelectedItems'));
    }

    /**
     * 選択された品目を保存
     */
    public function store(Request $request)
    {
        $user = Auth::user();

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

        try {
            DB::beginTransaction();

            // selected_items テーブルに保存（user_id と紐付け、新規申込み用）
            $selected = SelectedItem::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'temp_user_id' => null,
                ],
                [
                    'selected_items' => $normalizedItems,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'confirm_status' => SelectedItem::CONFIRM_STATUS_NOT_CONFIRMED,
                ]
            );

            DB::commit();

            // 確認ページにリダイレクト
            return redirect()
                ->route('user.items.confirmation')
                ->with('success', '品目を保存しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('品目保存エラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', '品目の保存に失敗しました。もう一度お試しください。');
        }
    }

    /**
     * 確認ページを表示（新規申込み用と既存申込み変更用の両方に対応）
     */
    public function confirmation($id = null)
    {
        $user = Auth::user();

        // IDが指定されている場合は既存の申込み、ない場合は新規申込み
        if ($id) {
            // 既存の申込みを変更する場合
            $selected = SelectedItem::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // キャンセル済みかチェック
            if ($selected->confirm_status === SelectedItem::CONFIRM_STATUS_CANCELLED) {
                return redirect()->route('user.history.index')
                    ->with('error', 'キャンセル済みの申込みは確認できません。');
            }
        } else {
            // 新規申込みの場合
            $selected = SelectedItem::where('user_id', $user->id)
                ->whereNull('temp_user_id')
                ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CONFIRMED)
                ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CANCELLED)
                ->first();

            if (!$selected) {
                return redirect()
                    ->route('user.items.index')
                    ->with('error', '品目が選択されていません。');
            }
        }

        // ユーザー情報を取得（prefectureも一緒に取得）
        $userInfo = $user->userInfo()->with('prefecture')->first();

        if (!$userInfo) {
            return redirect()->route($id ? 'user.history.index' : 'user.info.edit')
                ->with('error', 'ユーザー情報が見つかりませんでした。');
        }

        // 選択済み品目を取得
        $initialSelectedItems = $selected->selected_items ?? [];

        // 収集日を取得（既存のcollection_dateがある場合はそれを使用、ない場合は次回の第2水曜日を計算）
        $nextSecondWednesday = null;
        if ($selected->collection_date) {
            $collectionDate = Carbon::parse($selected->collection_date);
            $nextSecondWednesday = [
                'date' => $collectionDate,
                'formatted' => $collectionDate->format('n月j日'),
                'day_of_week' => $collectionDate->format('(D)'),
                'day_of_week_jp' => $this->getDayOfWeekJapanese($collectionDate->dayOfWeek),
            ];
        } else {
            $nextSecondWednesday = $this->getNextSecondWednesday();
        }

        // 受付番号を生成（既存の申込みの場合のみ）
        $receptionNumber = null;
        if ($id && $selected->reception_number_serial && $selected->payment_date) {
            $paymentDate = Carbon::parse($selected->payment_date);
            $yy = $paymentDate->format('y');
            $mm = $paymentDate->format('m');
            $serial = str_pad($selected->reception_number_serial, 5, '0', STR_PAD_LEFT);
            $receptionNumber = $yy . $mm . '-' . $serial;
        }

        // 決済方法の表示名（既存の申込みの場合のみ）
        $paymentMethodName = '未決済';
        if ($id && $selected->payment_method === 'online') {
            $paymentMethodName = 'オンライン決済';
        } elseif ($id && $selected->payment_method === 'convenience') {
            $paymentMethodName = 'コンビニ決済';
        }

        // TempUser形式で渡す（ブレードの互換性のため）
        $tempUser = (object)[
            'token' => null, // 認証済みユーザーなのでトークンは不要
            'email' => $user->email,
            'userInfo' => $userInfo,
        ];

        return view('user.item.confirmation', compact(
            'tempUser',
            'selected',
            'initialSelectedItems',
            'nextSecondWednesday',
            'id',
            'receptionNumber',
            'paymentMethodName'
        ));
    }

    /**
     * 確認ページの送信処理（新規申込み用と既存申込み変更用の両方に対応）
     */
    public function confirmationStore(Request $request, $id = null)
    {
        $user = Auth::user();

        // 同意チェックボックスの確認
        if (!$request->has('agree_terms') || !$request->input('agree_terms')) {
            return back()->withErrors('「以上の内容に同意する」にチェックを入れてください。');
        }

        // 収集日の取得
        $collectionDate = $request->input('collection_date');
        if (empty($collectionDate)) {
            return back()->withErrors('収集日が設定されていません。');
        }

        // IDが指定されている場合は既存の申込み、ない場合は新規申込み
        if ($id) {
            // 既存の申込みを変更する場合
            $selected = SelectedItem::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // キャンセル済みかチェック
            if ($selected->confirm_status === SelectedItem::CONFIRM_STATUS_CANCELLED) {
                return redirect()->route('user.history.index')
                    ->with('error', 'キャンセル済みの申込みは変更できません。');
            }
        } else {
            // 新規申込みの場合
            $selected = SelectedItem::where('user_id', $user->id)
                ->whereNull('temp_user_id')
                ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CONFIRMED)
                ->where('confirm_status', '!=', SelectedItem::CONFIRM_STATUS_CANCELLED)
                ->first();

            if (!$selected) {
                return redirect()
                    ->route('user.items.index')
                    ->with('error', '品目が選択されていません。');
            }
        }

        try {
            // selected_items テーブルを更新（収集日を保存）
            $selected->collection_date = $collectionDate;
            $selected->confirm_status = SelectedItem::CONFIRM_STATUS_CONFIRMED; // 確認済み
            $selected->save();

            // 既存の申込みの場合は詳細ページ、新規申込みの場合は詳細ページにリダイレクト
            return redirect()
                ->route('user.payment.index', ['id' => $selected->id])
                ->with('success', '申込内容を確認しました。支払い方法を選択してください。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('品目保存エラー: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', '品目の保存に失敗しました。もう一度お試しください。');
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
     * 申込み詳細を表示
     */
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

        // TempUser形式で渡す（ブレードの互換性のため）
        $tempUser = (object)[
            'token' => null, // 認証済みユーザーなのでトークンは不要
            'email' => $user->email,
            'userInfo' => $userInfo,
        ];

        return view('user.item.index', compact(
            'tempUser',
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
                ->route('user.items.confirmation', ['id' => $id])
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
     * 曜日を日本語で取得
     */
    private function getDayOfWeekJapanese($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek] ?? '';
    }
}
