@extends('admin.layouts.app')

@section('meta')
    <title>申込み詳細 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', '申込み詳細')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">申込み情報</h3>
                    <a href="{{ route('admin.applications.index') }}" class="admin-btn admin-btn-secondary">一覧に戻る</a>
                </div>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">基本情報</h4>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">ID:</span>
                            <span class="admin-stat-row-value">{{ $application->id }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">受付番号:</span>
                            <span class="admin-stat-row-value">{{ $application->reception_number ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">ユーザー:</span>
                            <span class="admin-stat-row-value">
                                @if($application->user)
                                    <a href="{{ route('admin.users.show', $application->user->id) }}">{{ $application->user->email }}</a>
                                @elseif($application->tempUser)
                                    {{ $application->tempUser->email }} (ゲスト)
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">収集日:</span>
                            <span class="admin-stat-row-value">{{ $application->collection_date ? $application->collection_date->format('Y/m/d') : '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">総数量:</span>
                            <span class="admin-stat-row-value">{{ number_format($application->total_quantity ?? 0) }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">総金額:</span>
                            <span class="admin-stat-row-value">¥{{ number_format($application->total_amount ?? 0) }}</span>
                        </div>
                    </div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">ステータス</h4>
                        <form method="POST" action="{{ route('admin.applications.update-status', $application->id) }}">
                            @csrf
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">確認ステータス</label>
                                <select name="confirm_status" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <option value="0" {{ $application->confirm_status == 0 ? 'selected' : '' }}>未確認</option>
                                    <option value="1" {{ $application->confirm_status == 1 ? 'selected' : '' }}>確認済み</option>
                                    <option value="2" {{ $application->confirm_status == 2 ? 'selected' : '' }}>キャンセル済み</option>
                                </select>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-family: 'Noto Sans JP', sans-serif;">支払いステータス</label>
                                <select name="payment_status" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <option value="0" {{ $application->payment_status == 0 ? 'selected' : '' }}>未支払い</option>
                                    <option value="1" {{ $application->payment_status == 1 ? 'selected' : '' }}>支払い待ち</option>
                                    <option value="2" {{ $application->payment_status == 2 ? 'selected' : '' }}>支払済み</option>
                                </select>
                            </div>
                            <button type="submit" class="admin-btn admin-btn-primary">ステータスを更新</button>
                        </form>
                        <form method="POST" action="{{ route('admin.applications.cancel', $application->id) }}" style="margin-top: 15px;" onsubmit="return confirm('本当にキャンセルしますか？');">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn-danger" {{ $application->confirm_status == \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED ? 'disabled' : '' }}>キャンセル</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($application->selected_items)
            <div class="admin-card" style="margin-top: 20px;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">選択された品目</h3>
                </div>
                <div class="admin-card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>品目名</th>
                                <th>数量</th>
                                <th>単価</th>
                                <th>小計</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($application->selected_items as $item)
                                <tr>
                                    <td>{{ $item['name'] ?? '-' }}</td>
                                    <td>{{ number_format($item['quantity'] ?? 0) }}</td>
                                    <td>¥{{ number_format($item['price'] ?? 0) }}</td>
                                    <td>¥{{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($application->user && $application->user->userInfo)
            <div class="admin-card" style="margin-top: 20px;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">ユーザー情報</h3>
                </div>
                <div class="admin-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">名前:</span>
                                <span class="admin-stat-row-value">{{ $application->user->userInfo->last_name }} {{ $application->user->userInfo->first_name }}</span>
                            </div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">電話番号:</span>
                                <span class="admin-stat-row-value">{{ $application->user->userInfo->phone_number }}</span>
                            </div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">郵便番号:</span>
                                <span class="admin-stat-row-value">{{ $application->user->userInfo->postal_code ?? '-' }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="admin-stat-row">
                                <span class="admin-stat-row-label">住所:</span>
                                <span class="admin-stat-row-value">
                                    @if($application->user->userInfo->prefecture)
                                        {{ $application->user->userInfo->prefecture->name }}
                                    @endif
                                    {{ $application->user->userInfo->city }}{{ $application->user->userInfo->town }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

