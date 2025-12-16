<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempUser;

class TempUserItemController extends Controller
{
    public function index($token)
    {
        $tempUser = TempUser::where('token', $token)->first();
        if (!$tempUser) {
            return abort(404);
        }

        return view('user.temp_user.item.index', compact('tempUser'));
    }
}
