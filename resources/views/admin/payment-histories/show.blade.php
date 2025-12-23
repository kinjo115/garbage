@extends('admin.layouts.app')

@section('meta')
    <title>決済履歴詳細 | 名古屋市ゴミ収集サイト</title>
@endsection

@section('page-title', '決済履歴詳細')

@section('content')
    <div class="admin-page">
        <div class="admin-card">
            <div class="admin-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="admin-card-title">決済履歴詳細</h3>
                    <a href="{{ route('admin.payment-histories.index') }}" class="admin-btn admin-btn-secondary">一覧に戻る</a>
                </div>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">基本情報</h4>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">ID:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->id }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">OrderID:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->order_id ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">TranID:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->tran_id ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">AccessID:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->access_id ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">申込みID:</span>
                            <span class="admin-stat-row-value">
                                @if($paymentHistory->selectedItem)
                                    <a href="{{ route('admin.applications.show', $paymentHistory->selectedItem->id) }}">{{ $paymentHistory->selected_item_id }}</a>
                                @else
                                    {{ $paymentHistory->selected_item_id }}
                                @endif
                            </span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">ステータス:</span>
                            <span class="admin-stat-row-value">
                                @if($paymentHistory->status === 'CAPTURE')
                                    <span class="admin-badge admin-badge-confirmed">CAPTURE</span>
                                @elseif($paymentHistory->status === 'AUTH')
                                    <span class="admin-badge admin-badge-pending">AUTH</span>
                                @else
                                    <span class="admin-badge">{{ $paymentHistory->status ?? '-' }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">JobCd:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->job_cd ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">決済情報</h4>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">金額:</span>
                            <span class="admin-stat-row-value">¥{{ number_format($paymentHistory->amount ?? 0) }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">税額:</span>
                            <span class="admin-stat-row-value">¥{{ number_format($paymentHistory->tax ?? 0) }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">通貨:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->currency ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">Forward:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->forward ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">Method:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->method ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">PayTimes:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->pay_times ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">Approve:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->approve ?? '-' }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">PayType:</span>
                            <span class="admin-stat-row-value">{{ $paymentHistory->pay_type ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">エラー情報</h4>
                    <div class="admin-stat-row">
                        <span class="admin-stat-row-label">ErrCode:</span>
                        <span class="admin-stat-row-value">
                            @if($paymentHistory->err_code)
                                <span class="admin-badge admin-badge-cancelled">{{ $paymentHistory->err_code }}</span>
                            @else
                                <span class="admin-badge admin-badge-confirmed">エラーなし</span>
                            @endif
                        </span>
                    </div>
                    @if($paymentHistory->err_info)
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">ErrInfo:</span>
                            <span class="admin-stat-row-value" style="color: #dc3545;">{{ $paymentHistory->err_info }}</span>
                        </div>
                    @endif
                </div>

                <div style="margin-top: 30px;">
                    <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">日時情報</h4>
                    <div class="admin-stat-row">
                        <span class="admin-stat-row-label">決済日時:</span>
                        <span class="admin-stat-row-value">
                            @if($paymentHistory->tran_date)
                                {{ \Carbon\Carbon::createFromFormat('YmdHis', $paymentHistory->tran_date)->format('Y年n月j日 H:i:s') }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="admin-stat-row">
                        <span class="admin-stat-row-label">作成日時:</span>
                        <span class="admin-stat-row-value">{{ $paymentHistory->created_at->format('Y年n月j日 H:i:s') }}</span>
                    </div>
                    <div class="admin-stat-row">
                        <span class="admin-stat-row-label">更新日時:</span>
                        <span class="admin-stat-row-value">{{ $paymentHistory->updated_at->format('Y年n月j日 H:i:s') }}</span>
                    </div>
                </div>

                @if($paymentHistory->selectedItem)
                    <div style="margin-top: 30px;">
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">関連情報</h4>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">ユーザー:</span>
                            <span class="admin-stat-row-value">
                                @if($paymentHistory->selectedItem->user)
                                    <a href="{{ route('admin.users.show', $paymentHistory->selectedItem->user->id) }}">{{ $paymentHistory->selectedItem->user->email }}</a>
                                @elseif($paymentHistory->selectedItem->tempUser)
                                    {{ $paymentHistory->selectedItem->tempUser->email }} (ゲスト)
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">申込み金額:</span>
                            <span class="admin-stat-row-value">¥{{ number_format($paymentHistory->selectedItem->total_amount ?? 0) }}</span>
                        </div>
                        <div class="admin-stat-row">
                            <span class="admin-stat-row-label">支払い状況:</span>
                            <span class="admin-stat-row-value">
                                @if($paymentHistory->selectedItem->payment_status == 2)
                                    <span class="admin-badge admin-badge-confirmed">支払済み</span>
                                @elseif($paymentHistory->selectedItem->payment_status == 1)
                                    <span class="admin-badge admin-badge-pending">支払い待ち</span>
                                @else
                                    <span class="admin-badge admin-badge-cancelled">未支払い</span>
                                @endif
                            </span>
                        </div>
                    </div>
                @endif

                @if($paymentHistory->raw_response)
                    <div style="margin-top: 30px;">
                        <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; font-family: 'Noto Sans JP', sans-serif;">生レスポンス</h4>
                        <div style="background-color: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
                            <pre style="margin: 0; font-size: 12px; white-space: pre-wrap; word-wrap: break-word;">{{ json_encode($paymentHistory->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

