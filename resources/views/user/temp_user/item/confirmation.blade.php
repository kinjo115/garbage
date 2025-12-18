@extends('layouts.app')

@section('meta')
    <title>申込内容の確認 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="申込内容の確認 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="申込内容の確認,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="申込内容の確認 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="申込内容の確認 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.register.confirm.store.map', ['token' => $tempUser->token]) }}">
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
                    <span>申込内容の確認</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">申込内容の確認</h1>
                </div>
                <form action="">
                    <div class="form-notification">まだ申込みは完了していません</div>
                    <div class="collection-date-content">
                        <div class="collection-date-content-title">
                            <h2>収集日が確定しました</h2>
                        </div>
                        <div class="collection-date-content-date">
                            <p>10月25日(水)</p>
                        </div>
                        <input type="text" name="">
                    </div>

                    <div class="confirm-main-info mt-16">
                        <div class="confirm-main-info-title">
                            <h2 class="text-4xl font-bold text-center">基本情報</h2>
                        </div>
                        <div class="mt-10 grid grid-cols-1 gap-2 w-full max-w-xl mx-auto">
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
                                        value=" {{ $tempUser->userInfo->postal_code ?? '-' }} ">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">住所</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ $tempUser->userInfo->prefecture->name }} {{ $tempUser->userInfo->city }} {{ $tempUser->userInfo->town }} {{ $tempUser->userInfo->chome }} {{ $tempUser->userInfo->building_number }} {{ $tempUser->userInfo->house_number }} {{ $tempUser->userInfo->apartment_name }} {{ $tempUser->userInfo->apartment_number }}">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">電話番号</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ $tempUser->userInfo->phone_number ?? '-' }}">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">緊急連絡先</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ $tempUser->userInfo->emergency_contact ?? '-' }}">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                <div class="label w-full max-w-[120px] text-right">メールアドレス</div>
                                <div class="w-full flex-1">
                                    <input type="text" readonly class="form-input"
                                        value="{{ $tempUser->email ?? '-' }}">
                                </div>
                            </div>
                        </div>

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
                            value="{{ $tempUser->userInfo?->home_latitude ?? '' }}">
                        <input type="hidden" name="home_longitude" id="home_longitude"
                            value="{{ $tempUser->userInfo?->home_longitude ?? '' }}">
                        <input type="hidden" name="disposal_latitude" id="disposal_latitude"
                            value="{{ $tempUser->userInfo?->disposal_latitude ?? '' }}">
                        <input type="hidden" name="disposal_longitude" id="disposal_longitude"
                            value="{{ $tempUser->userInfo?->disposal_longitude ?? '' }}">

                        <!-- 住所情報（地図の初期表示用） -->
                        <input type="hidden" id="user_address"
                            value="{{ $tempUser->userInfo?->prefecture?->name ?? '' }} {{ $tempUser->userInfo?->city ?? '' }} {{ $tempUser->userInfo?->town ?? '' }}">
                        <input type="hidden" id="user_postal_code"
                            value="{{ $tempUser->userInfo?->postal_code ?? '' }}">

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
                        <div class="mt-10">
                            <p>・基本情報または排出位置を変更したい場合は「基本情報に戻る」ボタンをクリックしてください。</p>
                            <p>・入力した品目内容を変更したい場合は「品目選択に戻る」ボタンをクリックしてください。</p>
                            <p>・申込みを中止したい場合は「申込みを中止する」ボタンをクリックしてください。</p>
                            <p>・内容に間違いがなければ、同意欄にチェックのうえ、「支払い方法に進む」ボタンをクリックしてください。</p>
                        </div>
                    </div>

                </form>
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
            @if (config('services.google_maps.api_key'))
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
                document.getElementById('map').innerHTML =
                    '<div style="padding: 20px; text-align: center; color: #666;">Google Maps APIキーが設定されていません。</div>';
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
                if (homeLat && homeLng && !isNaN(homeLat) && !isNaN(homeLng) && homeLat !== 0 && homeLng !==
                    0) {
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
                if (disposalLat && disposalLng && !isNaN(disposalLat) && !isNaN(disposalLng) && disposalLat !==
                    0 && disposalLng !== 0) {
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
    @if (config('services.google_maps.api_key'))
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&language=ja&region=JP&libraries=geometry&callback=initConfirmationMap"
            async defer></script>
    @endif
@endsection
