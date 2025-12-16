@extends('layouts.app')

@section('meta')
    <title>地図登録 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="地図登録 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="地図登録,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="地図登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="地図登録 | 名古屋市ゴミ収集サイト">
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
                    <span>地図登録</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">地図登録</h1>
                </div>
                <div class="form-description">
                    <p class="text-E20000">申込む前に必ずお読みください</p>
                    <br>
                    <p>※自宅位置と排出位置が地図上に表示されている方</p>
                    <br>
                    <p>・以前に電話またはインターネットで申し込んだ際の「氏名」「電話番号」「住所」を入力した場合は、申し込み当時の自宅位置と排出位置が地図上に表示されています。</p>
                    <p>間違いが無い場合は、そのまま品目入力に進むボタンをクリックしてください。</p>
                    <br>
                    <p>※自宅位置と排出位置が地図上に表示されていない方</p>
                    <p>・建物が建ってから初めて申し込む方は、ページ中央部の「建物が建ってから初めて申し込む」にチェックを入れてください。</p>
                    <br>
                    <p>・自宅位置を入力するボタンをクリックし、地図上で自宅の位置を指定してください。</p>
                    <p>・ボタンをクリックすると地図上に[家]マークが表示されるので、ドラッグして位置を指定してください。</p>
                    <br>
                    <p>・排出位置を入力するボタンをクリックし、地図上でごみの排出位置を指定してください。</p>
                    <p>ボタンをクリックすると地図上に[排出場所]マークが表示されるので、ドラッグして位置を指定してください。</p>
                    <br>
                    <p>・戸建てにお住まいの方・・・自宅の玄関前の道路に位置を指定してください。</p>
                    <p>・集合住宅にお住まいの方・・・各集合住宅で定められたごみの排出場所を指定してください。集合住宅排出場所がある集合住宅は集合住宅排出場所を指定してください。</p>
                    <br>
                    <p>・「地図選択」から表示する地図の種類を変更することができます。表示されている地図で場所が分かりにくい場合には、地図の種類を変更して確認してください。</p>
                    <p>・位置の指定方法が分からない場合や、地図上で位置を確認できない場合は、「場所の指定ができない」ボタンをクリックして申し込みを中止し、電話でお申し込みください。</p>
                    <p>・排出位置は選択していただいた場所から修正させていただくことがございますので、必ず前日お知らせメールで排出位置をご確認ください</p>
                </div>
                <form action="{{ route('user.register.confirm.store.map.save', ['token' => $tempUser->token]) }}"
                    method="POST" class="mt-10" id="map-form">
                    @csrf
                    <div class="form-group">
                        <div class="flex flex-wrap gap-2 items-center mb-4">
                            <button type="button" id="set-home-location" class="c-btn-blue">自宅位置を設定</button>
                            <button type="button" id="set-disposal-location" class="c-btn-blue">排出場所を設定</button>
                            <div class="flex items-center gap-2 ml-4">
                                <input type="checkbox" name="apply_after_building" id="apply_after_building"
                                    class="form-checkbox" value="1"
                                    {{ old('apply_after_building', $tempUser->userInfo?->apply_after_building) ? 'checked' : '' }}>
                                <label for="apply_after_building" class="form-label">建物が建ってから初めて申し込む</label>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mb-4">
                            <label for="map-type" class="form-label mr-2">地図選択:</label>
                            <select id="map-type" class="form-input max-w-[200px]">
                                <option value="roadmap">標準地図</option>
                                <option value="satellite">航空写真</option>
                                <option value="hybrid">航空写真+地図</option>
                                <option value="terrain">地形図</option>
                            </select>
                        </div>
                    </div>

                    <!-- 地図コンテナ -->
                    <div class="form-group">
                        <div id="map" style="width: 100%; height: 600px; border: 1px solid #ccc; border-radius: 5px;">
                        </div>
                    </div>

                    <!-- 位置情報の隠しフィールド -->
                    <input type="hidden" name="home_latitude" id="home_latitude"
                        value="{{ old('home_latitude', $tempUser->userInfo?->home_latitude) }}">
                    <input type="hidden" name="home_longitude" id="home_longitude"
                        value="{{ old('home_longitude', $tempUser->userInfo?->home_longitude) }}">
                    <input type="hidden" name="disposal_latitude" id="disposal_latitude"
                        value="{{ old('disposal_latitude', $tempUser->userInfo?->disposal_latitude) }}">
                    <input type="hidden" name="disposal_longitude" id="disposal_longitude"
                        value="{{ old('disposal_longitude', $tempUser->userInfo?->disposal_longitude) }}">

                    <!-- ステータス表示 -->
                    <div class="form-group">
                        <div id="location-status" class="text-sm text-gray-600 mb-4">
                            <p id="home-status">自宅位置: 未設定</p>
                            <p id="disposal-status">排出位置: 未設定</p>
                        </div>
                    </div>

                    <div class="mt-10">
                        <div class="">
                            <button type="submit" class="c-button btn-416FED" id="submit-btn">品目入力に進む</button>
                        </div>
                        <div class="mt-6">
                            <button type="button" class="c-button btn-ED4141" id="c-button ">場所の指定が出来ない</button>
                        </div>

                        <div class="md:mt-16 mt-10 flex justify-center">
                            <a href="{{ route('user.register.confirm', ['token' => $tempUser->token]) }}"
                                class="c-btn-black">戻る</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Google Maps API -->
    @if (config('services.google_maps.api_key'))
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&language=ja&region=JP&libraries=geometry&callback=initMap"
            async defer></script>
    @else
        <div class="alert alert-warning">
            <p>Google Maps APIキーが設定されていません。.envファイルにGOOGLE_MAPS_API_KEYを設定してください。</p>
        </div>
    @endif
    <script>
        // Google Maps初期化
        let map;
        let homeMarker = null;
        let disposalMarker = null;
        let currentMode = null; // 'home' or 'disposal'
        const NAGOYA_CENTER = {
            lat: 35.1815,
            lng: 136.9066
        }; // 名古屋市の中心座標
        const MIN_DISTANCE = 500; // 最小距離（メートル）

        function initMap() {
            // 既存の位置情報があればそれを使用、なければ名古屋市の中心
            const savedHomeLat = parseFloat(document.getElementById('home_latitude').value) || NAGOYA_CENTER.lat;
            const savedHomeLng = parseFloat(document.getElementById('home_longitude').value) || NAGOYA_CENTER.lng;

            map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: savedHomeLat,
                    lng: savedHomeLng
                },
                zoom: 15,
                mapTypeId: 'roadmap'
            });

            // 既存の位置情報があればマーカーを表示
            if (document.getElementById('home_latitude').value && document.getElementById('home_longitude').value) {
                setHomeMarker(parseFloat(document.getElementById('home_latitude').value), parseFloat(document
                    .getElementById('home_longitude').value));
            }

            if (document.getElementById('disposal_latitude').value && document.getElementById('disposal_longitude').value) {
                setDisposalMarker(parseFloat(document.getElementById('disposal_latitude').value), parseFloat(document
                    .getElementById('disposal_longitude').value));
            }

            // 地図クリックイベント
            map.addListener('click', function(event) {
                if (currentMode === 'home') {
                    setHomeMarker(event.latLng.lat(), event.latLng.lng());
                } else if (currentMode === 'disposal') {
                    setDisposalMarker(event.latLng.lat(), event.latLng.lng());
                }
            });
        }

        // 自宅位置マーカーを設定
        function setHomeMarker(lat, lng) {
            if (homeMarker) {
                homeMarker.setPosition({
                    lat: lat,
                    lng: lng
                });
            } else {
                homeMarker = new google.maps.Marker({
                    position: {
                        lat: lat,
                        lng: lng
                    },
                    map: map,
                    title: '自宅位置',
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/home.png',
                        scaledSize: new google.maps.Size(40, 40)
                    },
                    draggable: true
                });

                homeMarker.addListener('dragend', function(event) {
                    updateHomeLocation(event.latLng.lat(), event.latLng.lng());
                });
            }

            updateHomeLocation(lat, lng);
            currentMode = null;
        }

        // 排出位置マーカーを設定
        function setDisposalMarker(lat, lng) {
            if (disposalMarker) {
                disposalMarker.setPosition({
                    lat: lat,
                    lng: lng
                });
            } else {
                disposalMarker = new google.maps.Marker({
                    position: {
                        lat: lat,
                        lng: lng
                    },
                    map: map,
                    title: '排出位置',
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                        scaledSize: new google.maps.Size(40, 40)
                    },
                    draggable: true
                });

                disposalMarker.addListener('dragend', function(event) {
                    updateDisposalLocation(event.latLng.lat(), event.latLng.lng());
                });
            }

            updateDisposalLocation(lat, lng);
            currentMode = null;

            // 距離チェック
            checkDistance();
        }

        // 自宅位置を更新
        function updateHomeLocation(lat, lng) {
            document.getElementById('home_latitude').value = lat;
            document.getElementById('home_longitude').value = lng;
            document.getElementById('home-status').textContent = `自宅位置: 設定済み (${lat.toFixed(6)}, ${lng.toFixed(6)})`;
        }

        // 排出位置を更新
        function updateDisposalLocation(lat, lng) {
            document.getElementById('disposal_latitude').value = lat;
            document.getElementById('disposal_longitude').value = lng;
            document.getElementById('disposal-status').textContent = `排出位置: 設定済み (${lat.toFixed(6)}, ${lng.toFixed(6)})`;
        }

        // 距離をチェック（500m以上離れている場合アラート）
        function checkDistance() {
            if (homeMarker && disposalMarker) {
                const distance = google.maps.geometry.spherical.computeDistanceBetween(
                    homeMarker.getPosition(),
                    disposalMarker.getPosition()
                );

                if (distance > MIN_DISTANCE) {
                    Toastify({
                        text: `自宅と排出位置が${Math.round(distance)}m離れています。距離が500m以上の場合、確認が必要です。`,
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ffa500, #ff8c00)",
                        }
                    }).showToast();
                }
            }
        }

        // ボタンイベント
        document.getElementById('set-home-location').addEventListener('click', function() {
            currentMode = 'home';
            Toastify({
                text: '地図上をクリックして自宅位置を指定してください。',
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #416FED, #2277FF)",
                }
            }).showToast();
        });

        document.getElementById('set-disposal-location').addEventListener('click', function() {
            currentMode = 'disposal';
            Toastify({
                text: '地図上をクリックして排出位置を指定してください。',
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #416FED, #2277FF)",
                }
            }).showToast();
        });

        // 地図タイプ変更
        document.getElementById('map-type').addEventListener('change', function() {
            map.setMapTypeId(this.value);
        });

        // フォーム送信前の検証
        document.getElementById('map-form').addEventListener('submit', function(e) {
            const applyAfterBuilding = document.getElementById('apply_after_building').checked;

            if (!applyAfterBuilding) {
                if (!homeMarker) {
                    e.preventDefault();
                    Toastify({
                        text: '自宅位置を設定してください。',
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                        }
                    }).showToast();
                    return false;
                }
                if (!disposalMarker) {
                    e.preventDefault();
                    Toastify({
                        text: '排出位置を設定してください。',
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                        }
                    }).showToast();
                    return false;
                }
            }
        });

        // 場所の指定ができないボタン
        document.getElementById('cancel-btn').addEventListener('click', function() {
            if (confirm('場所の指定を中止して、電話でお申し込みしますか？')) {
                window.location.href = '{{ route('home') }}';
            }
        });

        // 地図初期化（APIキーが設定されている場合のみ）
        @if (config('services.google_maps.api_key'))
            // initMapはGoogle Maps APIのcallbackで呼ばれる
            window.initMap = initMap;
        @else
            document.getElementById('map').innerHTML =
                '<div style="padding: 20px; text-align: center; color: #666;">Google Maps APIキーが設定されていません。</div>';
        @endif
    </script>
@endsection
