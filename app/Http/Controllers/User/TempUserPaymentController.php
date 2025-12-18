<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempUser;
use App\Models\SelectedItem;

class TempUserPaymentController extends Controller
{
    //
    public function index($token)
    {
        $tempUser = TempUser::where('token', $token)->firstOrFail();
        if ($tempUser->selectedItem && $tempUser->selectedItem->confirm_status !== SelectedItem::CONFIRM_STATUS_CONFIRMED) {
            return redirect()
                ->route('user.confirmation.index', ['token' => $token])
                ->with('error', '申込内容が確認されていません。申込内容の確認ページに戻ってください。');
        }
        return view('user.temp_user.payment.index', compact('tempUser'));
    }
}
