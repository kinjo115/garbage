<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SelectedItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
     * 曜日を日本語で取得
     */
    private function getDayOfWeekJapanese($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek] ?? '';
    }
}
