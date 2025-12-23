<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    /**
     * 決済履歴一覧を表示
     */
    public function index(Request $request)
    {
        $query = PaymentHistory::with(['selectedItem.user', 'selectedItem.tempUser']);

        // 検索・フィルター機能
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('tran_id', 'like', "%{$search}%")
                  ->orWhere('access_id', 'like', "%{$search}%")
                  ->orWhereHas('selectedItem.user', function($q) use ($search) {
                      $q->where('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('selectedItem.tempUser', function($q) use ($search) {
                      $q->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('err_code')) {
            if ($request->err_code === 'has_error') {
                $query->whereNotNull('err_code');
            } elseif ($request->err_code === 'no_error') {
                $query->whereNull('err_code');
            }
        }

        $paymentHistories = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.payment-histories.index', compact('paymentHistories'));
    }

    /**
     * 決済履歴詳細を表示
     */
    public function show($id)
    {
        $paymentHistory = PaymentHistory::with(['selectedItem.user', 'selectedItem.tempUser'])
            ->findOrFail($id);

        return view('admin.payment-histories.show', compact('paymentHistory'));
    }
}
