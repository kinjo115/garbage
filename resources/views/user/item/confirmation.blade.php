@extends('layouts.app')

@section('meta')
    <title>申込内容の確認 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="申込内容の確認 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="申込内容の確認,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="申込内容の確認 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="申込内容の確認 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ $tempUser->token ? route('guest.register.confirm.store.map', ['token' => $tempUser->token]) : route('user.items.confirmation') }}">
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
                @if($tempUser->token)
                    <div class="breadcrumbs-item">
                        <span>申込内容の確認</span>
                    </div>
                @else
                    <div class="breadcrumbs-item">
                        <a href="{{ route('user.mypage') }}">マイページ</a>
                    </div>
                    <div class="breadcrumbs-item">
                        <span>申込内容の確認</span>
                    </div>
                @endif
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">申込内容の確認</h1>
                </div>
                <form action="{{ $tempUser->token ? route('guest.confirmation.store', ['token' => $tempUser->token]) : route('user.items.confirmation.store', $id ? ['id' => $id] : []) }}" method="POST"
                    id="confirmation-form">
                    @csrf
                    <div class="form-notification">まだ申込みは完了していません</div>
                    <div class="collection-date-content">
                        <div class="collection-date-content-title">
                            <h2>収集日が確定しました</h2>
                        </div>
                        <div class="collection-date-content-date">
                            <p>{{ $nextSecondWednesday['formatted'] }}（{{ $nextSecondWednesday['day_of_week_jp'] }}）</p>
                        </div>
                        <input type="hidden" name="collection_date"
                            value="{{ $nextSecondWednesday['date']->format('Y-m-d') }}">
                    </div>

                    <div class="confirm-main-info mt-16">
                        <div class="confirm-main-info-title">
                            <h2 class="text-4xl font-bold text-center">基本情報</h2>
                        </div>
                        <div class="mt-10 grid grid-cols-1 gap-2 w-full max-w-xl mx-auto">
                            @if(isset($receptionNumber) && $receptionNumber)
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

                        @if($tempUser->userInfo->home_latitude && $tempUser->userInfo->home_longitude)
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
                        @endif

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

                        <div class="m-10">
                            <p>・基本情報または排出位置を変更したい場合は「基本情報に戻る」ボタンをクリックしてください。</p>
                            <p>・入力した品目内容を変更したい場合は「品目選択に戻る」ボタンをクリックしてください。</p>
                            <p>・申込みを中止したい場合は「申込みを中止する」ボタンをクリックしてください。</p>
                            <p>・内容に間違いがなければ、同意欄にチェックのうえ、「{{ $tempUser->token ? '支払い方法に進む' : '確認完了' }}」ボタンをクリックしてください。</p>
                        </div>
                    </div>

                    {{-- 同意事項セクション --}}
                    <div class="agreement-section mt-16">
                        <div class="agreement-content">
                            <h2 class="agreement-title">同意事項</h2>
                            <div class="agreement-text">
                                <p>・排出位置を確認しました。</p>
                                <p>(戸建て) 自宅前の道路上に排出します。</p>
                                <p>(集合住宅) 決められた場所に排出します。</p>
                                <p>※排出場所が不明な場合は、お住まいの区の環境事業所に確認をお願いします。</p>
                                <p>・申し込む粗大ごみは、家庭から排出するものです。事業活動に伴うものではありません。</p>
                            </div>
                            <div class="agreement-checkbox">
                                <label class="confirmation-checkbox">
                                    <input type="checkbox" name="agree_terms" id="agree_terms" required>
                                    <span>以上の内容に同意する</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ナビゲーションボタン --}}
                    <div class="navigation-buttons mt-16">
                        <div class="flex flex-wrap gap-4 justify-center">
                            @if($tempUser->token)
                                <a href="{{ route('guest.register.confirm.store.map', ['token' => $tempUser->token]) }}"
                                    class="c-btn-black">基本情報に戻る</a>
                                <a href="{{ route('guest.item.index', ['token' => $tempUser->token]) }}"
                                    class="c-btn-black">品目選択に戻る</a>
                            @else
                                @if($id)
                                    <a href="{{ route('user.info.edit') }}"
                                        class="c-btn-black">基本情報に戻る</a>
                                    <a href="{{ route('user.items.show', ['id' => $id]) }}"
                                        class="c-btn-black">品目選択に戻る</a>
                                @else
                                    <a href="{{ route('user.info.edit') }}"
                                        class="c-btn-black">基本情報に戻る</a>
                                    <a href="{{ route('user.items.index') }}"
                                        class="c-btn-black">品目選択に戻る</a>
                                @endif
                            @endif
                            <button type="button" class="c-btn-black" id="cancel-application-btn">申込みを中止する</button>
                        </div>
                    </div>

                    {{-- 送信ボタン --}}
                    <div class="form-submit mt-16">
                        <button type="submit" class="c-button btn-416FED" id="proceed-payment-btn" disabled>
                            {{ $tempUser->token ? '支払い方法に進む' : '確認完了' }}
                        </button>
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

            // 同意事項チェックボックスの確認
            $('#agree_terms').on('change', function() {
                updateProceedButton();
            });

            // 支払い方法に進むボタンの有効/無効を更新
            function updateProceedButton() {
                const agreeChecked = $('#agree_terms').is(':checked');
                $('#proceed-payment-btn').prop('disabled', !agreeChecked);
            }

            // フォーム送信時のバリデーション
            $('#confirmation-form').on('submit', function(e) {
                const agreeChecked = $('#agree_terms').is(':checked');

                if (!agreeChecked) {
                    e.preventDefault();
                    Toastify({
                        text: '「以上の内容に同意する」にチェックを入れてください。',
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                        }
                    }).showToast();
                    return false;
                }
            });

            // 申込みを中止するボタン
            $('#cancel-application-btn').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '申込みを中止しますか？',
                    text: '申込みを中止すると、変更内容が失われます。',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ED4141',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '中止する',
                    cancelButtonText: '戻る',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        @if($tempUser->token)
                            window.location.href = '{{ route('home') }}';
                        @else
                            window.location.href = '{{ route('user.mypage') }}';
                        @endif
                    }
                });
            });

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
