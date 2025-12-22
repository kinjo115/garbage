<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SelectedItem;
use Illuminate\Support\Facades\Auth;

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

}
