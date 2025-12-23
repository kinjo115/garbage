<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GmoPaymentController extends Controller
{
    public function callback(Request $request)
    {
        Log::info('GMO Callback Received', [
            'request' => $request->all(),
        ]);
    }
}
