@extends('admin.layouts.app')

@section('meta')
    <title>管理画面 - ダッシュボード | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', 'ダッシュボード')

@section('content')
    <div class="admin-page">
        <div class="admin-stats">
            <div class="admin-stat-card">
                <div class="admin-stat-icon">👥</div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">総ユーザー数</div>
                    <div class="admin-stat-value">{{ number_format($stats['total_users']) }}</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">📋</div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">総申込み数</div>
                    <div class="admin-stat-value">{{ number_format($stats['total_applications']) }}</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">⏳</div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">未確認申込み</div>
                    <div class="admin-stat-value">{{ number_format($stats['pending_applications']) }}</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">✅</div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">確認済み申込み</div>
                    <div class="admin-stat-value">{{ number_format($stats['confirmed_applications']) }}</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">❌</div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">キャンセル済み</div>
                    <div class="admin-stat-value">{{ number_format($stats['cancelled_applications']) }}</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">💰</div>
                <div class="admin-stat-content">
                    <div class="admin-stat-label">総売上</div>
                    <div class="admin-stat-value">¥{{ number_format($stats['total_revenue']) }}</div>
                </div>
            </div>
        </div>

        <div class="admin-dashboard-grid">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">今月の統計</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-stat-row">
                        <span class="admin-stat-row-label">今月の申込み数:</span>
                        <span class="admin-stat-row-value">{{ number_format($monthlyApplications) }}</span>
                    </div>
                    <div class="admin-stat-row">
                        <span class="admin-stat-row-label">今月の売上:</span>
                        <span class="admin-stat-row-value">¥{{ number_format($monthlyRevenue) }}</span>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">最近の申込み</h3>
                </div>
                <div class="admin-card-body">
                    @if($recentApplications->count() > 0)
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>受付番号</th>
                                    <th>ユーザー</th>
                                    <th>金額</th>
                                    <th>ステータス</th>
                                    <th>作成日</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentApplications as $application)
                                    <tr>
                                        <td>{{ $application->reception_number ?? '-' }}</td>
                                        <td>
                                            @if($application->user)
                                                {{ $application->user->email }}
                                            @elseif($application->tempUser)
                                                {{ $application->tempUser->email }} (ゲスト)
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>¥{{ number_format($application->total_amount ?? 0) }}</td>
                                        <td>
                                            @if($application->confirm_status == \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED)
                                                <span class="admin-badge admin-badge-cancelled">キャンセル済み</span>
                                            @elseif($application->confirm_status == \App\Models\SelectedItem::CONFIRM_STATUS_CONFIRMED)
                                                <span class="admin-badge admin-badge-confirmed">確認済み</span>
                                            @else
                                                <span class="admin-badge admin-badge-pending">未確認</span>
                                            @endif
                                        </td>
                                        <td>{{ $application->created_at->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.applications.show', $application->id) }}" class="admin-btn admin-btn-sm">詳細</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="admin-empty">申込みがありません</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

