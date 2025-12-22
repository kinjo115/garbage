@extends('admin.layouts.app')

@section('meta')
    <title>申込み管理 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', '申込み管理')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <h3 class="admin-card-title">申込み一覧</h3>
                    <form method="GET" action="{{ route('admin.applications.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="検索..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <select name="status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">すべてのステータス</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>未確認</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>確認済み</option>
                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>キャンセル済み</option>
                        </select>
                        <select name="payment_status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">すべての支払い状況</option>
                            <option value="0" {{ request('payment_status') == '0' ? 'selected' : '' }}>未支払い</option>
                            <option value="1" {{ request('payment_status') == '1' ? 'selected' : '' }}>支払い待ち</option>
                            <option value="2" {{ request('payment_status') == '2' ? 'selected' : '' }}>支払済み</option>
                        </select>
                        <button type="submit" class="admin-btn admin-btn-primary">検索</button>
                        @if(request('search') || request('status') || request('payment_status'))
                            <a href="{{ route('admin.applications.index') }}" class="admin-btn admin-btn-secondary">クリア</a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="admin-card-body">
                @if($applications->count() > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>受付番号</th>
                                <th>ユーザー</th>
                                <th>金額</th>
                                <th>ステータス</th>
                                <th>支払い状況</th>
                                <th>作成日</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                                <tr>
                                    <td>{{ $application->id }}</td>
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
                                    <td>
                                        @if($application->payment_status == 2)
                                            <span class="admin-badge admin-badge-confirmed">支払済み</span>
                                        @elseif($application->payment_status == 1)
                                            <span class="admin-badge admin-badge-pending">支払い待ち</span>
                                        @else
                                            <span class="admin-badge">未支払い</span>
                                        @endif
                                    </td>
                                    <td>{{ $application->created_at->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.applications.show', $application->id) }}" class="admin-btn admin-btn-sm admin-btn-primary">詳細</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="margin-top: 20px;">
                        {{ $applications->links() }}
                    </div>
                @else
                    <p class="admin-empty">申込みが見つかりませんでした</p>
                @endif
            </div>
        </div>
    </div>
@endsection

