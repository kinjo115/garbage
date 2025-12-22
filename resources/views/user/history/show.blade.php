@php
    $days = ['日', '月', '火', '水', '木', '金', '土'];
@endphp

@extends('layouts.app')

@section('meta')
    <title>申込み詳細 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="申込み詳細 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="申込み詳細,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="申込み詳細 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="申込み詳細 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.history.show', ['id' => $selectedItem->id]) }}">
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
                    <a href="{{ route('user.history.index') }}">申込み履歴</a>
                </div>
                <div class="breadcrumbs-item">
                    <span>申込み詳細</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">申込み詳細</h1>
                </div>

                @if($selectedItem->confirm_status === \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED)
                    <div class="form-notification" style="background-color: #f5f5f5; border-color: #d0d0d0; color: #999;">
                        この申込みはキャンセルされています
                    </div>
                @endif

                @if($collectionDateFormatted)
                    <div class="collection-date-content">
                        <div class="collection-date-content-title">
                            <h2>収集日</h2>
                        </div>
                        <div class="collection-date-content-date">
                            <p>{{ $collectionDateFormatted }}（{{ $dayOfWeek }}）</p>
                        </div>
                    </div>
                @endif

                <div class="confirm-main-info mt-16">
                    <div class="confirm-main-info-title">
                        <h2 class="text-4xl font-bold text-center">基本情報</h2>
                    </div>
                    <div class="mt-10 grid grid-cols-1 gap-2 w-full max-w-xl mx-auto">
                        @if($receptionNumber)
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">受付番号</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input" value="{{ $receptionNumber }}">
                                </div>
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">名前</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input"
                                    value="{{ $userInfo->last_name }} {{ $userInfo->first_name }}">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">郵便番号</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input"
                                    value="{{ $userInfo->postal_code ? substr($userInfo->postal_code, 0, 3) . '-' . substr($userInfo->postal_code, 3) : '-' }}">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">住所</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input"
                                    value="{{ ($userInfo->prefecture->name ?? '') }} {{ $userInfo->city ?? '' }} {{ $userInfo->town ?? '' }} {{ $userInfo->chome ?? '' }} {{ $userInfo->building_number ?? '' }} {{ $userInfo->house_number ?? '' }} {{ $userInfo->apartment_name ?? '' }} {{ $userInfo->apartment_number ?? '' }}">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">電話番号</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input"
                                    value="{{ $userInfo->phone_number ?? '-' }}">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">緊急連絡先</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input"
                                    value="{{ $userInfo->emergency_contact ?? '-' }}">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                            <div class="label w-full max-w-[120px] text-right">メールアドレス</div>
                            <div class="w-full flex-1">
                                <input type="text" readonly class="form-input"
                                    value="{{ $userInfo->user->email ?? '-' }}">
                            </div>
                        </div>
                        @if($paymentMethodName !== '未決済')
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">支払い方法</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input" value="{{ $paymentMethodName }}">
                                </div>
                            </div>
                        @endif
                        @if($selectedItem->payment_date)
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">決済日</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ \Carbon\Carbon::parse($selectedItem->payment_date)->format('Y年n月j日 H:i') }}">
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($userInfo->home_latitude && $userInfo->home_longitude)
                        <div class="mt-10">
                            <p class="text-center">ごみの排出位置は下の地図で確認してください。</p>
                            <p class="text-center">※排出位置が誤っていないかご確認ください。</p>
                        </div>

                        {{-- map confirmation --}}
                        <div class="form-group mt-10">
                            <div id="map"
                                style="width: 100%; height: 600px; border: 1px solid #ccc; border-radius: 5px;">
                            </div>
                        </div>

                        <!-- 位置情報の隠しフィールド -->
                        <input type="hidden" name="home_latitude" id="home_latitude"
                            value="{{ $userInfo->home_latitude ?? '' }}">
                        <input type="hidden" name="home_longitude" id="home_longitude"
                            value="{{ $userInfo->home_longitude ?? '' }}">
                        <input type="hidden" name="disposal_latitude" id="disposal_latitude"
                            value="{{ $userInfo->disposal_latitude ?? '' }}">
                        <input type="hidden" name="disposal_longitude" id="disposal_longitude"
                            value="{{ $userInfo->disposal_longitude ?? '' }}">

                        <!-- 住所情報（地図の初期表示用） -->
                        <input type="hidden" id="user_address"
                            value="{{ ($userInfo->prefecture->name ?? '') }} {{ $userInfo->city ?? '' }} {{ $userInfo->town ?? '' }}">
                        <input type="hidden" id="user_postal_code"
                            value="{{ $userInfo->postal_code ?? '' }}">
                    @endif

                    <div class="mt-16">
                        <div class="selected-items mt-16" data-initial-items='@json($initialSelectedItems ?? [])'>
                            <div class="selected-items-header">
                                <h2 class="selected-items-title">選択済み品目</h2>
                            </div>
                            <div class="selected-items-body">
                                <div class="selected-items-wrapper" id="selected-items-wrapper">
                                    {{-- JSで動的に挿入 --}}
                                </div>
                                <div class="total-content mt-6">
                                    <div class="total-content-title">合計金額</div>
                                    <div class="total-content-amount" id="total-content-amount">0円 <span
                                            class="text-sm">(0個)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ナビゲーションボタン --}}
                <div class="navigation-buttons mt-16">
                    <div class="flex flex-wrap gap-4 justify-center">
                        <a href="{{ route('user.history.index') }}" class="c-btn-black">申込み履歴に戻る</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 確認ページ用：選択済み品目を表示（読み取り専用）
            const $selectedItemsSection = $('.selected-items[data-initial-items]');
            if ($selectedItemsSection.length) {
                let selectedItems = [];

                // 初期選択データを読み込む
                const initialItemsAttr = $selectedItemsSection.attr('data-initial-items');
                if (initialItemsAttr) {
                    try {
                        const parsed = JSON.parse(initialItemsAttr);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            selectedItems = parsed.map(function(item) {
                                return {
                                    id: item.id,
                                    name: item.name,
                                    price: item.price,
                                    quantity: item.quantity,
                                };
                            });
                        }
                    } catch (e) {
                        console.warn('初期品目データのパースに失敗しました', e);
                    }
                }

                const $selectedWrapper = $('#selected-items-wrapper');
                const $totalAmount = $('#total-content-amount');

                // 選択済み一覧を描画（読み取り専用、編集機能なし）
                function renderSelectedItems() {
                    $selectedWrapper.empty();

                    let totalPrice = 0;
                    let totalCount = 0;

                    selectedItems.forEach(function(item) {
                        const itemTotal = item.price * item.quantity;
                        totalPrice += itemTotal;
                        totalCount += item.quantity;

                        const $itemEl = $(`
                            <div class="selected-item" data-item-id="${item.id}">
                                <div class="flex items-end justify-between">
                                    <div class="selected-item-name">${item.name}</div>
                                    <div class="selected-item-amount">${itemTotal.toLocaleString()}円</div>
                                </div>
                                <div class="flex">
                                    <div class="quantity-wrapper mt-10">
                                        <div class="count">${item.quantity}</div>
                                    </div>
                                </div>
                            </div>
                        `);

                        $selectedWrapper.append($itemEl);
                    });

                    $totalAmount.html(
                        `${totalPrice.toLocaleString()}円 <span class="text-sm">(${totalCount}個)</span>`);
                }

                // 初期データがあれば描画
                if (selectedItems.length > 0) {
                    renderSelectedItems();
                }
            }

            // 地図表示ロジック（確認ページ用、読み取り専用）
            @if (config('services.google_maps.api_key') && $userInfo->home_latitude && $userInfo->home_longitude)
                // Google Maps APIが読み込まれるまで待つ
                if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                    window.initConfirmationMap();
                } else {
                    // APIが読み込まれるまで待機
                    const checkGoogleMaps = setInterval(function() {
                        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                            clearInterval(checkGoogleMaps);
                            window.initConfirmationMap();
                        }
                    }, 100);
                }
            @else
                @if($userInfo->home_latitude && $userInfo->home_longitude)
                    document.getElementById('map').innerHTML =
                        '<div style="padding: 20px; text-align: center; color: #666;">Google Maps APIキーが設定されていません。</div>';
                @endif
            @endif
        });

        // 確認ページ用の地図初期化（読み取り専用）- グローバルスコープに配置
        window.initConfirmationMap = function() {
            let map;
            let homeMarker = null;
            let disposalMarker = null;
            const NAGOYA_CENTER = {
                lat: 35.1815,
                lng: 136.9066
            };

            // 既存の位置情報を取得
            const homeLat = parseFloat(document.getElementById('home_latitude').value);
            const homeLng = parseFloat(document.getElementById('home_longitude').value);
            const disposalLat = parseFloat(document.getElementById('disposal_latitude').value);
            const disposalLng = parseFloat(document.getElementById('disposal_longitude').value);

            let initialCenter = NAGOYA_CENTER;
            let initialZoom = 15;

            // 既存の位置情報があればそれを使用
            if (homeLat && homeLng && !isNaN(homeLat) && !isNaN(homeLng) && homeLat !== 0 && homeLng !== 0) {
                initialCenter = {
                    lat: homeLat,
                    lng: homeLng
                };
                initialZoom = 17;
            } else {
                // 位置情報がない場合は、住所から座標を取得
                const userAddress = document.getElementById('user_address').value;
                const userPostalCode = document.getElementById('user_postal_code').value;

                if (userAddress || userPostalCode) {
                    const geocoder = new google.maps.Geocoder();
                    const addressToGeocode = userAddress || userPostalCode;

                    geocoder.geocode({
                        address: addressToGeocode
                    }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            const location = results[0].geometry.location;
                            initialCenter = {
                                lat: location.lat(),
                                lng: location.lng()
                            };
                            initialZoom = 16;
                            map.setCenter(initialCenter);
                            map.setZoom(initialZoom);
                        }
                    });
                }
            }

            // 地図を初期化
            map = new google.maps.Map(document.getElementById('map'), {
                center: initialCenter,
                zoom: initialZoom,
                mapTypeId: 'roadmap',
                zoomControl: true,
                mapTypeControl: true,
                scaleControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            // 地図の読み込み完了を待ってからマーカーを表示
            google.maps.event.addListenerOnce(map, 'idle', function() {
                // 自宅位置マーカーを表示
                if (homeLat && homeLng && !isNaN(homeLat) && !isNaN(homeLng) && homeLat !== 0 && homeLng !== 0) {
                    const homeIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#FF0000">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    `);

                    homeMarker = new google.maps.Marker({
                        position: {
                            lat: homeLat,
                            lng: homeLng
                        },
                        map: map,
                        title: '自宅位置',
                        icon: {
                            url: homeIconSvg,
                            scaledSize: new google.maps.Size(40, 40),
                            anchor: new google.maps.Point(20, 40)
                        },
                        draggable: false, // 確認ページなのでドラッグ不可
                        zIndex: 1000
                    });
                }

                // 排出位置マーカーを表示
                if (disposalLat && disposalLng && !isNaN(disposalLat) && !isNaN(disposalLng) && disposalLat !== 0 && disposalLng !== 0) {
                    const disposalIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#4169E1">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    `);

                    disposalMarker = new google.maps.Marker({
                        position: {
                            lat: disposalLat,
                            lng: disposalLng
                        },
                        map: map,
                        title: '排出位置',
                        icon: {
                            url: disposalIconSvg,
                            scaledSize: new google.maps.Size(40, 40),
                            anchor: new google.maps.Point(20, 40)
                        },
                        draggable: false, // 確認ページなのでドラッグ不可
                        zIndex: 1000
                    });
                }

                // 両方のマーカーがある場合、地図の範囲を調整
                if (homeMarker && disposalMarker) {
                    const bounds = new google.maps.LatLngBounds();
                    bounds.extend(homeMarker.getPosition());
                    bounds.extend(disposalMarker.getPosition());
                    map.fitBounds(bounds);
                } else if (homeMarker) {
                    map.setCenter(homeMarker.getPosition());
                    map.setZoom(17);
                } else if (disposalMarker) {
                    map.setCenter(disposalMarker.getPosition());
                    map.setZoom(17);
                }
            });
        }
    </script>

    <!-- Google Maps API -->
    @if (config('services.google_maps.api_key') && $userInfo->home_latitude && $userInfo->home_longitude)
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&language=ja&region=JP&libraries=geometry&callback=initConfirmationMap"
            async defer></script>
    @endif
@endsection
