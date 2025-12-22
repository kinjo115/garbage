@extends('admin.layouts.app')

@section('meta')
    <title>ユーザー詳細 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', 'ユーザー詳細')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">ユーザー情報</h3>
                    <div style="display: flex; gap: 10px;">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="admin-btn admin-btn-primary">編集</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display: inline;" onsubmit="return confirm('本当に削除しますか？');">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn-danger">削除</button>
                        </form>
                        <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">一覧に戻る</a>
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">基本情報</h4>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">ID:</span>
                            <span class="admin-stat-row-value">{{ $user->id }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">メールアドレス:</span>
                            <span class="admin-stat-row-value">{{ $user->email }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">名前:</span>
                            <span class="admin-stat-row-value">{{ $user->name }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">登録日:</span>
                            <span class="admin-stat-row-value">{{ $user->created_at->format('Y/m/d H:i') }}</span>
                        </div>
                    </div>
                    @if($user->userInfo)
                        <div>
                            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">詳細情報</h4>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">姓:</span>
                                <span class="admin-stat-row-value">{{ $user->userInfo->last_name }}</span>
                            </div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">名:</span>
                                <span class="admin-stat-row-value">{{ $user->userInfo->first_name }}</span>
                            </div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">電話番号:</span>
                                <span class="admin-stat-row-value">{{ $user->userInfo->phone_number }}</span>
                            </div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">郵便番号:</span>
                                <span class="admin-stat-row-value">{{ $user->userInfo->postal_code ?? '-' }}</span>
                            </div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">住所:</span>
                                <span class="admin-stat-row-value">
                                    @if($user->userInfo->prefecture)
                                        {{ $user->userInfo->prefecture->name }}
                                    @endif
                                    {{ $user->userInfo->city }}{{ $user->userInfo->town }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card" style="margin-top: 20px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">申込み履歴</h3>
            </div>
            <div class="admin-card-body">
                @if($applications->count() > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>受付番号</th>
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
                                    <td>{{ $application->reception_number ?? '-' }}</td>
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
                @else
                    <p class="admin-empty">申込み履歴がありません</p>
                @endif
            </div>
        </div>
    </div>
@endsection

