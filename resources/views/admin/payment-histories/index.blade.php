@extends('admin.layouts.app')

@section('meta')
    <title>決済履歴 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', '決済履歴')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <h3 class="admin-card-title">決済履歴一覧</h3>
                    <form method="GET" action="{{ route('admin.payment-histories.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="検索（OrderID、TranID、メールアドレス）..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <select name="status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">すべてのステータス</option>
                            <option value="CAPTURE" {{ request('status') == 'CAPTURE' ? 'selected' : '' }}>CAPTURE</option>
                            <option value="AUTH" {{ request('status') == 'AUTH' ? 'selected' : '' }}>AUTH</option>
                        </select>
                        <select name="err_code" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">すべて</option>
                            <option value="no_error" {{ request('err_code') == 'no_error' ? 'selected' : '' }}>エラーなし</option>
                            <option value="has_error" {{ request('err_code') == 'has_error' ? 'selected' : '' }}>エラーあり</option>
                        </select>
                        <button type="submit" class="admin-btn admin-btn-primary">検索</button>
                        @if(request('search') || request('status') || request('err_code'))
                            <a href="{{ route('admin.payment-histories.index') }}" class="admin-btn admin-btn-secondary">クリア</a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="admin-card-body">
                @if($paymentHistories->count() > 0)
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>OrderID</th>
                                <th>TranID</th>
                                <th>申込みID</th>
                                <th>ユーザー</th>
                                <th>金額</th>
                                <th>ステータス</th>
                                <th>エラー</th>
                                <th>決済日時</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentHistories as $history)
                                <tr>
                                    <td>{{ $history->id }}</td>
                                    <td>{{ $history->order_id ?? '-' }}</td>
                                    <td>{{ $history->tran_id ?? '-' }}</td>
                                    <td>
                                        @if($history->selectedItem)
                                            <a href="{{ route('admin.applications.show', $history->selectedItem->id) }}">{{ $history->selected_item_id }}</a>
                                        @else
                                            {{ $history->selected_item_id }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($history->selectedItem)
                                            @if($history->selectedItem->user)
                                                {{ $history->selectedItem->user->email }}
                                            @elseif($history->selectedItem->tempUser)
                                                {{ $history->selectedItem->tempUser->email }} (ゲスト)
                                            @else
                                                -
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>¥{{ number_format($history->amount ?? 0) }}</td>
                                    <td>
                                        @if($history->status === 'CAPTURE')
                                            <span class="admin-badge admin-badge-confirmed">CAPTURE</span>
                                        @elseif($history->status === 'AUTH')
                                            <span class="admin-badge admin-badge-pending">AUTH</span>
                                        @else
                                            <span class="admin-badge">{{ $history->status ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($history->err_code)
                                            <span class="admin-badge admin-badge-cancelled" title="{{ $history->err_info }}">{{ $history->err_code }}</span>
                                        @else
                                            <span class="admin-badge admin-badge-confirmed">正常</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($history->tran_date)
                                            {{ \Carbon\Carbon::createFromFormat('YmdHis', $history->tran_date)->format('Y/m/d H:i') }}
                                        @else
                                            {{ $history->created_at->format('Y/m/d H:i') }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.payment-histories.show', $history->id) }}" class="admin-btn admin-btn-sm admin-btn-primary">詳細</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="margin-top: 20px;">
                        {{ $paymentHistories->links() }}
                    </div>
                @else
                    <p class="admin-empty">決済履歴が見つかりませんでした</p>
                @endif
            </div>
        </div>
    </div>
@endsection

