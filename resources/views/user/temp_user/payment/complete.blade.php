@extends('layouts.app')

@section('meta')
    <title>決済完了 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="決済完了 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="決済完了,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="決済完了 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="決済完了 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $tempUser->token ? route('guest.payment.complete', ['token' => $tempUser->token]) : route('user.payment.complete', ['id' => $selected->id]) }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection

@section('content')
    @php
        use Carbon\Carbon;
        
        // 受付番号を生成
        $receptionNumber = $selected->reception_number ?? null;
        
        // 決済日時をフォーマット
        $paymentDateFormatted = $selected->payment_date 
            ? Carbon::parse($selected->payment_date)->format('Y年n月j日 H:i')
            : null;
        
        // 収集日をフォーマット
        $collectionDateFormatted = null;
        $dayOfWeek = '';
        if ($selected->collection_date) {
            $collectionDate = Carbon::parse($selected->collection_date);
            $collectionDateFormatted = $collectionDate->format('Y年n月j日');
            $days = ['日', '月', '火', '水', '木', '金', '土'];
            $dayOfWeek = $days[$collectionDate->dayOfWeek];
        }
        
        // 決済方法の表示名
        $paymentMethodName = '未決済';
        if ($selected->payment_method === 'online') {
            $paymentMethodName = 'オンライン決済';
        } elseif ($selected->payment_method === 'convenience') {
            $paymentMethodName = 'コンビニ決済';
        }
        
        // 選択された品目
        $selectedItems = $selected->selected_items ?? [];
    @endphp

    <div class="c-page">
        <div class="c-container">
            <div class="breadcrumbs">
                <div class="breadcrumbs-item">
                    <a href="{{ route('home') }}">ホーム</a>
                </div>
                @if($tempUser->token)
                    <div class="breadcrumbs-item">
                        <span>決済完了</span>
                    </div>
                @else
                    <div class="breadcrumbs-item">
                        <a href="{{ route('user.mypage') }}">マイページ</a>
                    </div>
                    <div class="breadcrumbs-item">
                        <span>決済完了</span>
                    </div>
                @endif
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">決済完了</h1>
                </div>

                <div class="form-notification" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    決済が正常に完了いたしました。この度は、名古屋市ゴミ収集サイトをご利用いただき、誠にありがとうございます。
                </div>

                <div class="confirm-main-info mt-16">
                    <div class="confirm-main-info-title">
                        <h2 class="text-4xl font-bold text-center">決済情報</h2>
                    </div>
                    <div class="mt-10 grid grid-cols-1 gap-2 w-full max-w-xl mx-auto">
                        @if($receptionNumber)
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">受付番号</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input" value="{{ $receptionNumber }}" style="font-weight: 600; color: #416FED;">
                                </div>
                            </div>
                        @endif
                        @if($paymentDateFormatted)
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">決済日時</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input" value="{{ $paymentDateFormatted }}">
                                </div>
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">支払い方法</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input" value="{{ $paymentMethodName }}">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">決済金額</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input" value="{{ number_format($selected->total_amount) }}円">
                            </div>
                        </div>
                        @if($collectionDateFormatted)
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">収集日</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input" value="{{ $collectionDateFormatted }}（{{ $dayOfWeek }}）">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="confirm-main-info mt-16">
                    <div class="confirm-main-info-title">
                        <h2 class="text-4xl font-bold text-center">基本情報</h2>
                    </div>
                    <div class="mt-10 grid grid-cols-1 gap-2 w-full max-w-xl mx-auto">
                        @if($tempUser->userInfo)
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">名前</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ $tempUser->userInfo->last_name }} {{ $tempUser->userInfo->first_name }}">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">郵便番号</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ $tempUser->userInfo->postal_code ? (strlen($tempUser->userInfo->postal_code) === 7 ? substr($tempUser->userInfo->postal_code, 0, 3) . '-' . substr($tempUser->userInfo->postal_code, 3) : $tempUser->userInfo->postal_code) : '-' }}">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">住所</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ ($tempUser->userInfo->prefecture->name ?? '') }} {{ $tempUser->userInfo->city ?? '' }} {{ $tempUser->userInfo->town ?? '' }} {{ $tempUser->userInfo->chome ?? '' }} {{ $tempUser->userInfo->building_number ?? '' }} {{ $tempUser->userInfo->house_number ?? '' }} {{ $tempUser->userInfo->apartment_name ?? '' }} {{ $tempUser->userInfo->apartment_number ?? '' }}">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">電話番号</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ $tempUser->userInfo->phone_number ?? '-' }}">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if(count($selectedItems) > 0)
                    <div class="mt-16">
                        <div class="selected-items">
                            <div class="selected-items-header">
                                <h2 class="selected-items-title">選択済み品目</h2>
                            </div>
                            <div class="selected-items-body">
                                <div class="selected-items-wrapper">
                                    @foreach($selectedItems as $item)
                                        @php
                                            $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                                        @endphp
                                        <div class="selected-item" data-item-id="{{ $item['id'] ?? '' }}">
                                            <div class="flex items-end justify-between">
                                                <div class="selected-item-name">{{ $item['name'] ?? '' }}</div>
                                                <div class="selected-item-amount">{{ number_format($itemTotal) }}円</div>
                                            </div>
                                            <div class="flex">
                                                <div class="quantity-wrapper mt-10">
                                                    <div class="count">{{ $item['quantity'] ?? 0 }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="total-content mt-6">
                                    <div class="total-content-title">合計金額</div>
                                    <div class="total-content-amount">
                                        {{ number_format($selected->total_amount) }}円 
                                        <span class="text-sm">({{ $selected->total_quantity }}個)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-submit mt-16">
                    @if($tempUser->token)
                        <a href="{{ route('home') }}" class="c-button btn-416FED">ホームに戻る</a>
                    @else
                        <a href="{{ route('user.mypage') }}" class="c-button btn-416FED">マイページに戻る</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
