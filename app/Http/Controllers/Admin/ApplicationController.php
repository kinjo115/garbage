<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectedItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApplicationController extends Controller
{
    /**
     * 申込み一覧を表示
     */
    public function index(Request $request)
    {
        $query = SelectedItem::with(['user', 'tempUser']);

        // 検索・フィルター機能
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reception_number_serial', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tempUser', function($q) use ($search) {
                      $q->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('confirm_status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.applications.index', compact('applications'));
    }

    /**
     * 申込み詳細を表示
     */
    public function show($id)
    {
        $application = SelectedItem::with(['user.userInfo', 'tempUser'])->findOrFail($id);
        return view('admin.applications.show', compact('application'));
    }

    /**
     * 申込みをキャンセル
     */
    public function cancel(Request $request, $id)
    {
        $application = SelectedItem::findOrFail($id);

        DB::beginTransaction();
        try {
            $application->update([
                'confirm_status' => SelectedItem::CONFIRM_STATUS_CANCELLED,
            ]);

            DB::commit();

            return redirect()->route('admin.applications.show', $application->id)
                ->with('success', '申込みをキャンセルしました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'キャンセル処理に失敗しました。');
        }
    }

    /**
     * 申込みステータスを更新
     */
    public function updateStatus(Request $request, $id)
    {
        $application = SelectedItem::findOrFail($id);

        $request->validate([
            'confirm_status' => 'required|in:0,1,2',
            'payment_status' => 'required|in:0,1,2',
        ]);

        DB::beginTransaction();
        try {
            $application->update([
                'confirm_status' => $request->confirm_status,
                'payment_status' => $request->payment_status,
            ]);

            DB::commit();

            return redirect()->route('admin.applications.show', $application->id)
                ->with('success', 'ステータスを更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'ステータスの更新に失敗しました。')
                ->withInput();
        }
    }
}

