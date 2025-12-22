@php
    use Carbon\Carbon;
    $days = ['日', '月', '火', '水', '木', '金', '土'];
@endphp

@extends('layouts.app')

@section('meta')
    <title>申込み履歴 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="申込み履歴 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="申込み履歴,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="申込み履歴 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="申込み履歴 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.history.index') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection

@section('content')
    <div class="c-page">
        <div class="c-container">
            <div class="breadcrumbs">
                <div class="breadcrumbs-item">
                    <a href="{{ route('home') }}">ホーム</a>
                </div>
                <div class="breadcrumbs-item">
                    <a href="{{ route('user.mypage') }}">マイページ</a>
                </div>
                <div class="breadcrumbs-item">
                    <span>申込み履歴</span>
                </div>
            </div>
            <div class="page-content history-content">
                <div class="page-header">
                    <h1 class="page-title">申込み履歴</h1>
                </div>

                @if($selectedItems->count() > 0)
                    <div class="history-list">
                        @foreach($selectedItems as $item)
                            @php
                                // 受付番号を生成
                                $receptionNumber = null;
                                if ($item->reception_number_serial && $item->payment_date) {
                                    $paymentDate = Carbon::parse($item->payment_date);
                                    $yy = $paymentDate->format('y');
                                    $mm = $paymentDate->format('m');
                                    $serial = str_pad($item->reception_number_serial, 5, '0', STR_PAD_LEFT);
                                    $receptionNumber = $yy . $mm . '-' . $serial;
                                }

                                // 収集日をフォーマット
                                $collectionDateFormatted = null;
                                $dayOfWeek = '';
                                if ($item->collection_date) {
                                    $collectionDate = Carbon::parse($item->collection_date);
                                    $collectionDateFormatted = $collectionDate->format('Y/m/d');
                                    $dayOfWeek = $days[$collectionDate->dayOfWeek];
                                }

                                // 決済方法の表示名
                                $paymentMethodName = '未決済';
                                if ($item->payment_method === 'online') {
                                    $paymentMethodName = 'オンライン決済済み';
                                } elseif ($item->payment_method === 'convenience') {
                                    $paymentMethodName = 'コンビニ決済済み';
                                }
                                // キャンセル済みかチェック
                                $isCancelled = $item->confirm_status === \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED;
                            @endphp

                            <div class="history-card {{ $isCancelled ? 'history-card-cancelled' : '' }}">
                                <div class="history-card-header">
                                    <div class="history-card-left">
                                        @if($receptionNumber)
                                            <p class="history-reception-number">受付番号: {{ $receptionNumber }}</p>
                                        @endif
                                        @if($collectionDateFormatted)
                                            <p class="history-collection-date">収集日: {{ $collectionDateFormatted }} ({{ $dayOfWeek }})</p>
                                        @endif
                                        @if($isCancelled)
                                            <p class="history-cancelled-badge">キャンセル済み</p>
                                        @endif
                                    </div>
                                    <div class="history-card-right">
                                        <p class="history-payment-status">{{ $paymentMethodName }}</p>
                                        <p class="history-amount">{{ number_format($item->total_amount) }}円</p>
                                    </div>
                                </div>
                                @if(!$isCancelled)
                                    <div class="history-card-actions">
                                        <a href="{{ route('user.items.show', ['id' => $item->id]) }}" class="history-btn history-btn-change">品目を変更する</a>
                                        <button type="button" class="history-btn history-btn-cancel" data-item-id="{{ $item->id }}">キャンセルする</button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="history-empty">
                        <p>申込み履歴がありません。</p>
                    </div>
                @endif

                <div class="history-footer">
                    <a href="{{ route('user.mypage') }}" class="history-btn-back">マイページTOPに戻る</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // キャンセルボタンのクリックイベント
            $(document).on('click', '.history-btn-cancel', function(e) {
                e.preventDefault();

                const itemId = $(this).data('item-id');
                const $card = $(this).closest('.history-card');
                const receptionNumberText = $card.find('.history-reception-number').text();
                const receptionNumber = receptionNumberText ? receptionNumberText.replace('受付番号: ', '') : '';
                const collectionDateText = $card.find('.history-collection-date').text();
                const collectionDate = collectionDateText ? collectionDateText.replace('収集日: ', '') : '';

                let confirmText = 'この申込みをキャンセルしますか？';
                if (receptionNumber) {
                    confirmText = `受付番号: ${receptionNumber} の申込みをキャンセルしますか？`;
                } else if (collectionDate) {
                    confirmText = `収集日: ${collectionDate} の申込みをキャンセルしますか？`;
                }

                Swal.fire({
                    title: 'キャンセルしますか？',
                    text: confirmText + '\n\nキャンセル後は元に戻せません。',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ED4141',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'キャンセルする',
                    cancelButtonText: '戻る',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ローディング表示
                        Swal.fire({
                            title: '処理中...',
                            text: 'キャンセル処理を実行しています',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // キャンセル処理を実行
                        $.ajax({
                            url: '{{ route("user.items.cancel") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                item_id: itemId
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'キャンセル完了',
                                    text: response.message || '申込みをキャンセルしました。',
                                    icon: 'success',
                                    confirmButtonColor: '#416FED',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                let errorMessage = 'キャンセル処理に失敗しました。';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    title: 'エラー',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonColor: '#ED4141',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
