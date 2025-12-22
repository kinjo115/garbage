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


                    <div class="mt-16">
                        <form id="items-form" method="POST" action="{{ route('user.history.update-items', ['id' => $selectedItem->id]) }}"
                            data-initial-items='@json($initialSelectedItems ?? [])'>
                            @csrf
                            <input type="hidden" name="items_json" id="items-json">

                            <div class="selected-items mt-16">
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

                            @if($selectedItem->confirm_status !== \App\Models\SelectedItem::CONFIRM_STATUS_CANCELLED)
                                {{-- 品目一覧 --}}
                                <div class="mt-16">
                                    <div class="form-description">
                                        <p class="text-E20000 text-bold">【注意事項】</p>
                                        <br>
                                        <p>・品目名をクリックすると、一覧に追加されます。</p>
                                        <p>・追加された品目に対して個数を設定し、品目全体を削除できます。</p>
                                        <p>・複数の品目を追加した場合、合計金額が表示されます。</p>
                                    </div>

                                    <div class="item-list-wrapper mt-10" id="item-list-wrapper">
                                        @foreach ($items as $item)
                                            <div class="item-list-item" data-item-id="{{ $item->id }}"
                                                data-item-name="{{ $item->name }}" data-item-price="{{ $item->price }}">
                                                <div class="flex items-end justify-between">
                                                    <button type="button" class="item-list-item-name js-add-item">
                                                        {{ $item->name }}
                                                    </button>
                                                    <div class="item-list-item-price">{{ $item->price }}円</div>
                                                </div>
                                                <div class="category-wrapper">
                                                    <div class="category-item">{{ $item->itemCategory->name ?? '' }}</div>
                                                </div>
                                                <div class="description-wrapper">
                                                    <div class="label">説明表示</div>
                                                    <div class="description-item">{{ $item->description }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-submit mt-10">
                                    <button type="submit" class="c-button btn-416FED" id="update-items-btn" disabled>品目を更新する</button>
                                </div>
                                <div class="md:mt-16 mt-10 flex justify-center">
                                    <a href="{{ route('user.history.index') }}" class="c-btn-black">戻る</a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 品目選択機能（TempUserのitem/indexと同じロジック）
            const $itemsForm = $('#items-form');
            if ($itemsForm.length) {
                let selectedItems = [];
                let initialSelectedItems = []; // 初期状態を保存

                // 初期選択データを読み込む
                const initialItemsAttr = $itemsForm.attr('data-initial-items');
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
                            // 初期状態をディープコピーで保存
                            initialSelectedItems = JSON.parse(JSON.stringify(selectedItems));
                        }
                    } catch (e) {
                        console.warn('初期品目データのパースに失敗しました', e);
                    }
                }

                const $selectedWrapper = $('#selected-items-wrapper');
                const $totalAmount = $('#total-content-amount');
                const $updateBtn = $('#update-items-btn');

                /**
                 * 品目が変更されたかどうかをチェック
                 */
                function checkItemsChanged() {
                    if (selectedItems.length !== initialSelectedItems.length) {
                        return true;
                    }

                    // 各品目を比較
                    const currentMap = {};
                    selectedItems.forEach(function(item) {
                        currentMap[item.id] = item.quantity;
                    });

                    const initialMap = {};
                    initialSelectedItems.forEach(function(item) {
                        initialMap[item.id] = item.quantity;
                    });

                    // 品目IDのセットを比較
                    const currentIds = Object.keys(currentMap).sort();
                    const initialIds = Object.keys(initialMap).sort();

                    if (currentIds.join(',') !== initialIds.join(',')) {
                        return true;
                    }

                    // 各品目の数量を比較
                    for (let id in currentMap) {
                        if (currentMap[id] !== initialMap[id]) {
                            return true;
                        }
                    }

                    return false;
                }

                /**
                 * 更新ボタンの状態を更新
                 */
                function updateSubmitButton() {
                    const hasChanges = checkItemsChanged();
                    $updateBtn.prop('disabled', !hasChanges);
                }

                /**
                 * 選択済み一覧を再描画
                 */
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
                                        <button type="button" class="decrease-button">
                                            <img src="${window.assetUrls?.iconMinus || '/assets/images/icons/icon-minus.svg'}" alt="マイナス">
                                        </button>
                                        <input type="number" value="${item.quantity}" class="quantity-input" hidden>
                                        <div class="count">${item.quantity}</div>
                                        <button type="button" class="increase-button">
                                            <img src="${window.assetUrls?.iconPlus || '/assets/images/icons/icon-plus.svg'}" alt="プラス">
                                        </button>
                                    </div>
                                    <div class="delete-button">
                                        <button type="button" class="delete-button-icon">
                                            <img src="${window.assetUrls?.trash || '/assets/images/icons/trash.svg'}" alt="削除">
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `);

                        $selectedWrapper.append($itemEl);
                    });

                    $totalAmount.html(`${totalPrice.toLocaleString()}円 <span class="text-sm">(${totalCount}個)</span>`);

                    // ボタンの状態を更新
                    updateSubmitButton();
                }

                /**
                 * 品目カードクリックで選択リストに追加
                 */
                $(document).on('click', '.item-list-item', function(e) {
                    if ($(e.target).closest('.no-add').length) {
                        return;
                    }

                    const $parent = $(this);
                    const id = parseInt($parent.data('item-id'), 10);
                    const name = $parent.data('item-name');
                    const price = parseInt($parent.data('item-price'), 10);

                    if (!id || !name || isNaN(price)) {
                        console.warn('品目情報が不正です', { id, name, price });
                        return;
                    }

                    const existing = selectedItems.find(function(i) {
                        return i.id === id;
                    });

                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        selectedItems.push({
                            id: id,
                            name: name,
                            price: price,
                            quantity: 1,
                        });
                    }

                    renderSelectedItems();
                });

                /**
                 * 数量変更（+ / -）
                 */
                $selectedWrapper.on('click', '.increase-button', function() {
                    const $itemEl = $(this).closest('.selected-item');
                    const id = parseInt($itemEl.data('item-id'), 10);

                    const target = selectedItems.find(function(i) {
                        return i.id === id;
                    });
                    if (!target) return;

                    target.quantity += 1;
                    renderSelectedItems();
                });

                $selectedWrapper.on('click', '.decrease-button', function() {
                    const $itemEl = $(this).closest('.selected-item');
                    const id = parseInt($itemEl.data('item-id'), 10);

                    const target = selectedItems.find(function(i) {
                        return i.id === id;
                    });
                    if (!target) return;

                    if (target.quantity > 1) {
                        target.quantity -= 1;
                    } else {
                        return;
                    }
                    renderSelectedItems();
                });

                /**
                 * 品目削除
                 */
                $selectedWrapper.on('click', '.delete-button-icon', function() {
                    const $itemEl = $(this).closest('.selected-item');
                    const id = parseInt($itemEl.data('item-id'), 10);

                    selectedItems = selectedItems.filter(function(i) {
                        return i.id !== id;
                    });

                    renderSelectedItems();
                });

                /**
                 * 送信前にJSONとしてhiddenに詰める
                 */
                $itemsForm.on('submit', function(e) {
                    e.preventDefault();

                    if (selectedItems.length === 0) {
                        Swal.fire({
                            title: 'エラー',
                            text: '少なくとも1つの品目を選択してください。',
                            icon: 'error',
                            confirmButtonColor: '#ED4141'
                        });
                        return false;
                    }

                    $('#items-json').val(JSON.stringify(selectedItems));

                    // ローディング表示
                    Swal.fire({
                        title: '処理中...',
                        text: '品目を更新しています',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // AJAXで送信
                    $.ajax({
                        url: $itemsForm.attr('action'),
                        method: 'POST',
                        data: $itemsForm.serialize(),
                        success: function(response) {
                            // 確認ページにリダイレクト
                            window.location.href = '{{ route("user.history.confirmation", ["id" => $selectedItem->id]) }}';
                        },
                        error: function(xhr) {
                            let errorMessage = '品目の更新に失敗しました。';
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
                });

                // 初期データがあれば描画
                if (selectedItems.length > 0) {
                    renderSelectedItems();
                }

                // 初期状態でボタンの状態を更新
                updateSubmitButton();
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
