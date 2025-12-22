<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SelectedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * 管理画面ダッシュボードを表示
     */
    public function index()
    {
        // 統計情報を取得
        $stats = [
            'total_users' => User::where('role', User::ROLE['USER'])->count(),
            'total_applications' => SelectedItem::count(),
            'pending_applications' => SelectedItem::where('confirm_status', SelectedItem::CONFIRM_STATUS_NOT_CONFIRMED)->count(),
            'confirmed_applications' => SelectedItem::where('confirm_status', SelectedItem::CONFIRM_STATUS_CONFIRMED)->count(),
            'cancelled_applications' => SelectedItem::where('confirm_status', SelectedItem::CONFIRM_STATUS_CANCELLED)->count(),
            'paid_applications' => SelectedItem::where('payment_status', 2)->count(),
            'unpaid_applications' => SelectedItem::where('payment_status', '!=', 2)->where('payment_status', '!=', 0)->count(),
            'total_revenue' => SelectedItem::where('payment_status', 2)->sum('total_amount'),
        ];

        // 最近の申込み（最新10件）
        $recentApplications = SelectedItem::with(['user', 'tempUser'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 今月の申込み数
        $monthlyApplications = SelectedItem::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // 今月の売上
        $monthlyRevenue = SelectedItem::where('payment_status', 2)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('total_amount');

        return view('admin.dashboard.index', compact('stats', 'recentApplications', 'monthlyApplications', 'monthlyRevenue'));
    }
}

