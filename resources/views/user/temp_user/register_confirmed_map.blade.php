@extends('layouts.app')

@section('meta')
    <title>地図登録 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="地図登録 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="地図登録,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="地図登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="地図登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('guest.register.confirm.store.map', ['token' => $tempUser->token]) }}">
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
                <form action="{{ route('guest.register.confirm.store.map.save', ['token' => $tempUser->token]) }}"
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

                    <!-- 住所情報（地図の初期表示用） -->
                    <input type="hidden" id="user_address" value="{{ $address ?? '' }}">
                    <input type="hidden" id="user_postal_code" value="{{ $tempUser->userInfo?->postal_code ?? '' }}">

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
                            <button type="button" class="c-button btn-ED4141" id="cancel-btn">場所の指定が出来ない</button>
                        </div>

                        <div class="md:mt-16 mt-10 flex justify-center">
                            <a href="{{ route('guest.register.confirm', ['token' => $tempUser->token]) }}"
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
            console.log('Google Maps初期化開始');

            // 既存の位置情報があればそれを使用
            const savedHomeLat = parseFloat(document.getElementById('home_latitude').value);
            const savedHomeLng = parseFloat(document.getElementById('home_longitude').value);

            let initialCenter = NAGOYA_CENTER; // デフォルトは名古屋市の中心
            let initialZoom = 15;

            // 既存の位置情報があればそれを使用
            if (savedHomeLat && savedHomeLng && !isNaN(savedHomeLat) && !isNaN(savedHomeLng) && savedHomeLat !== 0 &&
                savedHomeLng !== 0) {
                initialCenter = {
                    lat: savedHomeLat,
                    lng: savedHomeLng
                };
                initialZoom = 17; // 位置情報がある場合はより拡大
                console.log('既存の位置情報を使用:', initialCenter);
            } else {
                // 位置情報がない場合は、住所から座標を取得
                const userAddress = document.getElementById('user_address').value;
                const userPostalCode = document.getElementById('user_postal_code').value;

                if (userAddress || userPostalCode) {
                    // Google Geocoding APIを使用して住所から座標を取得
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
                            console.log('住所から座標を取得:', initialCenter);

                            // 地図を再初期化
                            map.setCenter(initialCenter);
                            map.setZoom(initialZoom);
                        } else {
                            console.warn('住所から座標を取得できませんでした:', status);
                        }
                    });
                }
            }

            console.log('地図の中心座標:', initialCenter);

            map = new google.maps.Map(document.getElementById('map'), {
                center: initialCenter,
                zoom: initialZoom,
                mapTypeId: 'roadmap'
            });

            console.log('地図が初期化されました');

            // 地図の読み込み完了を待ってからマーカーを表示
            google.maps.event.addListenerOnce(map, 'idle', function() {
                console.log('地図の読み込みが完了しました。マーカーを表示します。');

                // 既存の位置情報があればマーカーを表示（保存済みの位置情報のみ表示）
                // 初期表示時にホームマーカーを自動表示
                const homeLatInput = document.getElementById('home_latitude').value;
                const homeLngInput = document.getElementById('home_longitude').value;

                if (homeLatInput && homeLngInput) {
                    const homeLat = parseFloat(homeLatInput);
                    const homeLng = parseFloat(homeLngInput);
                    console.log('既存の自宅位置情報:', homeLat, homeLng);

                    // 有効な座標の場合のみマーカーを表示
                    if (homeLat && homeLng && !isNaN(homeLat) && !isNaN(homeLng) && homeLat !== 0 && homeLng !==
                        0) {
                        console.log('初期表示: 自宅位置マーカーを作成します');
                        // 初期表示時は地図の中心を移動しない（既に設定済み）
                        setHomeMarker(homeLat, homeLng, false);
                        console.log('初期表示: 自宅位置マーカーが作成されました');
                    } else {
                        console.warn('自宅位置の座標が無効です:', homeLat, homeLng);
                    }
                } else {
                    console.log('既存の自宅位置情報がありません');
                }
            });

            // 地図の読み込み完了を待ってから排出位置マーカーを表示
            google.maps.event.addListenerOnce(map, 'idle', function() {
                const disposalLatInput = document.getElementById('disposal_latitude').value;
                const disposalLngInput = document.getElementById('disposal_longitude').value;

                if (disposalLatInput && disposalLngInput) {
                    const disposalLat = parseFloat(disposalLatInput);
                    const disposalLng = parseFloat(disposalLngInput);
                    console.log('既存の排出位置情報:', disposalLat, disposalLng);

                    // 有効な座標の場合のみマーカーを表示
                    if (disposalLat && disposalLng && !isNaN(disposalLat) && !isNaN(disposalLng) && disposalLat !==
                        0 &&
                        disposalLng !== 0) {
                        console.log('初期表示: 排出位置マーカーを作成します');
                        setDisposalMarker(disposalLat, disposalLng, false); // false = アニメーションなし、地図の中心移動なし
                        console.log('初期表示: 排出位置マーカーが作成されました');
                    } else {
                        console.warn('排出位置の座標が無効です:', disposalLat, disposalLng);
                    }
                } else {
                    console.log('既存の排出位置情報がありません');
                }
            });

            // 地図クリックイベント（currentModeが設定されている場合のみマーカーを作成）
            map.addListener('click', function(event) {
                const clickLat = event.latLng.lat();
                const clickLng = event.latLng.lng();
                console.log('地図がクリックされました。座標:', clickLat, clickLng, 'currentMode:', currentMode);

                if (currentMode === 'home') {
                    console.log('自宅位置マーカーを作成します:', clickLat, clickLng);
                    setHomeMarker(clickLat, clickLng);
                    console.log('setHomeMarker呼び出し完了');
                } else if (currentMode === 'disposal') {
                    console.log('排出位置マーカーを作成します:', clickLat, clickLng);
                    setDisposalMarker(clickLat, clickLng);
                    console.log('setDisposalMarker呼び出し完了');
                } else {
                    console.log('currentModeが設定されていません。ボタンをクリックしてください。currentMode:', currentMode);
                    Toastify({
                        text: 'まず「自宅位置を設定」または「排出場所を設定」ボタンをクリックしてください。',
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ffa500, #ff8c00)",
                        }
                    }).showToast();
                }
            });

            // ボタンイベントリスナーを設定（地図初期化後に設定）
            setupButtonListeners();

            console.log('地図の初期化が完了しました');
        }

        // ボタンイベントリスナーを設定する関数
        function setupButtonListeners() {
            console.log('ボタンイベントリスナーを設定します');

            // 自宅位置設定ボタン
            const setHomeBtn = document.getElementById('set-home-location');
            if (setHomeBtn) {
                // 既存のイベントリスナーを削除（重複を防ぐため）
                const newSetHomeBtn = setHomeBtn.cloneNode(true);
                setHomeBtn.parentNode.replaceChild(newSetHomeBtn, setHomeBtn);

                newSetHomeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('自宅位置設定ボタンがクリックされました');
                    currentMode = 'home';
                    console.log('currentModeを設定しました:', currentMode);
                    console.log('現在のcurrentModeの値:', currentMode);
                    console.log('typeof currentMode:', typeof currentMode);

                    // 既存のマーカーがある場合は強調表示
                    if (homeMarker) {
                        console.log('既存の自宅位置マーカーがあります');
                        homeMarker.setAnimation(google.maps.Animation.BOUNCE);
                        setTimeout(function() {
                            homeMarker.setAnimation(null);
                        }, 2000);
                        map.setCenter(homeMarker.getPosition());
                        map.setZoom(17);
                        Toastify({
                            text: '既存の自宅位置マーカーをドラッグして位置を調整できます。または地図上をクリックして新しい位置を設定してください。',
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #416FED, #2277FF)",
                            }
                        }).showToast();
                    } else {
                        // 既存のマーカーがない場合、TempUserで登録した位置情報があればそれを使用
                        const savedHomeLat = parseFloat(document.getElementById('home_latitude').value);
                        const savedHomeLng = parseFloat(document.getElementById('home_longitude').value);

                        if (savedHomeLat && savedHomeLng && !isNaN(savedHomeLat) && !isNaN(savedHomeLng) &&
                            savedHomeLat !== 0 && savedHomeLng !== 0) {
                            console.log('TempUserで登録した位置情報を使用してマーカーを作成します:', savedHomeLat, savedHomeLng);
                            // 登録済みの位置情報を使用してマーカーを作成
                            setHomeMarker(savedHomeLat, savedHomeLng, true); // true = アニメーションあり、地図の中心移動あり

                            Toastify({
                                text: '登録済みの自宅位置にマーカーを設定しました。ドラッグして位置を調整できます。',
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                                }
                            }).showToast();
                        } else {
                            console.log('既存の自宅位置マーカーがありません。地図をクリックしてマーカーを作成してください。');
                            Toastify({
                                text: '地図上をクリックして自宅位置を指定してください。マーカーはドラッグして位置を調整できます。',
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #416FED, #2277FF)",
                                }
                            }).showToast();
                        }
                    }
                });
                console.log('自宅位置設定ボタンのイベントリスナーを設定しました');
            } else {
                console.error('自宅位置設定ボタンが見つかりません');
            }

            // 排出位置設定ボタン
            const setDisposalBtn = document.getElementById('set-disposal-location');
            if (setDisposalBtn) {
                // 既存のイベントリスナーを削除（重複を防ぐため）
                const newSetDisposalBtn = setDisposalBtn.cloneNode(true);
                setDisposalBtn.parentNode.replaceChild(newSetDisposalBtn, setDisposalBtn);

                newSetDisposalBtn.addEventListener('click', function() {
                    console.log('排出位置設定ボタンがクリックされました');
                    currentMode = 'disposal';
                    console.log('currentModeを設定しました:', currentMode);

                    // 既存のマーカーがある場合は強調表示
                    if (disposalMarker) {
                        disposalMarker.setAnimation(google.maps.Animation.BOUNCE);
                        setTimeout(function() {
                            disposalMarker.setAnimation(null);
                        }, 2000);
                        map.setCenter(disposalMarker.getPosition());
                        map.setZoom(17);
                        Toastify({
                            text: '既存の排出位置マーカーをドラッグして位置を調整できます。または地図上をクリックして新しい位置を設定してください。',
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #416FED, #2277FF)",
                            }
                        }).showToast();
                    } else {
                        // 既存のマーカーがない場合、TempUserで登録した位置情報があればそれを使用
                        const savedDisposalLat = parseFloat(document.getElementById('disposal_latitude').value);
                        const savedDisposalLng = parseFloat(document.getElementById('disposal_longitude').value);

                        if (savedDisposalLat && savedDisposalLng && !isNaN(savedDisposalLat) && !isNaN(
                                savedDisposalLng) && savedDisposalLat !== 0 && savedDisposalLng !== 0) {
                            console.log('TempUserで登録した排出位置情報を使用してマーカーを作成します:', savedDisposalLat, savedDisposalLng);
                            // 登録済みの位置情報を使用してマーカーを作成
                            setDisposalMarker(savedDisposalLat, savedDisposalLng,
                                true); // true = アニメーションあり、地図の中心移動あり

                            Toastify({
                                text: '登録済みの排出位置にマーカーを設定しました。ドラッグして位置を調整できます。',
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                                }
                            }).showToast();
                        } else {
                            Toastify({
                                text: '地図上をクリックして排出位置を指定してください。マーカーはドラッグして位置を調整できます。',
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #416FED, #2277FF)",
                                }
                            }).showToast();
                        }
                    }
                });
                console.log('排出位置設定ボタンのイベントリスナーを設定しました');
            } else {
                console.error('排出位置設定ボタンが見つかりません');
            }

            // 地図タイプ変更
            const mapTypeSelect = document.getElementById('map-type');
            if (mapTypeSelect) {
                mapTypeSelect.addEventListener('change', function() {
                    if (map) {
                        map.setMapTypeId(this.value);
                    }
                });
                console.log('地図タイプ変更のイベントリスナーを設定しました');
            }

            // フォーム送信前の検証
            const mapForm = document.getElementById('map-form');
            if (mapForm) {
                mapForm.addEventListener('submit', function(e) {
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

                        // 距離チェック（500m以上の場合送信を防ぐ）
                        if (!checkDistance()) {
                            e.preventDefault();
                            Toastify({
                                text: '自宅と排出位置が500m以上離れているため、送信できません。位置を調整してください。',
                                duration: 5000,
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
                console.log('フォーム送信検証のイベントリスナーを設定しました');
            }

            // 場所の指定ができないボタン
            const cancelBtn = document.getElementById('cancel-btn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('場所の指定を中止して、電話でお申し込みしますか？')) {
                        // フォームを作成してPOSTリクエストを送信
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action =
                            '{{ route('guest.register.confirm.store.map.cancel', ['token' => $tempUser->token]) }}';

                        // CSRFトークンを追加
                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
                console.log('キャンセルボタンのイベントリスナーを設定しました');
            }
        }

        // 自宅位置マーカーを設定
        // animateAndCenter: true = アニメーションと地図の中心移動を行う, false = 行わない
        function setHomeMarker(lat, lng, animateAndCenter = true) {
            console.log('setHomeMarker呼び出し:', lat, lng, 'animateAndCenter:', animateAndCenter);

            // 座標の検証
            if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
                console.error('無効な座標です:', lat, lng);
                return;
            }

            // 地図が初期化されているか確認
            if (!map) {
                console.error('地図が初期化されていません');
                return;
            }

            if (homeMarker) {
                // 既存のマーカーがある場合は位置を更新
                console.log('既存の自宅位置マーカーを更新します');
                homeMarker.setPosition({
                    lat: lat,
                    lng: lng
                });
            } else {
                // 新しいマーカーを作成
                console.log('新しい自宅位置マーカーを作成します');
                try {
                    // 自宅位置用のhomeアイコン（カスタムSVGアイコン）
                    const homeIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#FF0000">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    `);

                    homeMarker = new google.maps.Marker({
                        position: {
                            lat: lat,
                            lng: lng
                        },
                        map: map, // 地図にマーカーを追加
                        title: '自宅位置（ドラッグして移動できます）',
                        icon: {
                            url: homeIconSvg,
                            scaledSize: new google.maps.Size(40, 40),
                            anchor: new google.maps.Point(20, 40)
                        },
                        draggable: true, // ドラッグ可能に設定
                        zIndex: 1000, // 他のマーカーより前面に表示
                        animation: animateAndCenter ? google.maps.Animation.DROP : null,
                        cursor: 'move', // マーカーにカーソルを合わせたときにmoveカーソルを表示
                        optimized: false // パフォーマンス最適化を無効化（確実に表示するため）
                    });

                    // マーカーが確実に地図に表示されることを確認
                    if (homeMarker.getMap() === null) {
                        console.error('マーカーが地図に追加されていません。手動で追加します。');
                        homeMarker.setMap(map);
                    }

                    // マーカーの表示を確認
                    const markerMap = homeMarker.getMap();
                    if (markerMap) {
                        console.log('自宅位置マーカーが作成され、地図に追加されました:', homeMarker);
                        console.log('マーカーの位置:', homeMarker.getPosition());
                        console.log('マーカーが地図に表示されているか:', homeMarker.getMap() !== null);
                    } else {
                        console.error('マーカーの追加に失敗しました。再度試行します。');
                        // 再度試行
                        setTimeout(function() {
                            if (homeMarker && homeMarker.getMap() === null) {
                                homeMarker.setMap(map);
                                console.log('マーカーを再度追加しました');
                            }
                        }, 100);
                    }
                } catch (error) {
                    console.error('マーカー作成エラー:', error);
                    return;
                }

                // マーカーにマウスオーバー時のイベント（視覚的フィードバック）
                homeMarker.addListener('mouseover', function() {
                    const homeIconSvgLarge = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="#FF0000">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    `);
                    homeMarker.setIcon({
                        url: homeIconSvgLarge,
                        scaledSize: new google.maps.Size(48, 48), // 少し大きくする
                        anchor: new google.maps.Point(24, 48)
                    });
                });

                homeMarker.addListener('mouseout', function() {
                    const homeIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#FF0000">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    `);
                    homeMarker.setIcon({
                        url: homeIconSvg,
                        scaledSize: new google.maps.Size(40, 40),
                        anchor: new google.maps.Point(20, 40)
                    });
                });

                // ドラッグ開始時のイベント
                homeMarker.addListener('dragstart', function() {
                    document.getElementById('home-status').textContent = '自宅位置: 移動中...';
                    // ドラッグ中はアイコンを少し大きくする
                    const homeIconSvgDrag = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="#FF0000">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    `);
                    homeMarker.setIcon({
                        url: homeIconSvgDrag,
                        scaledSize: new google.maps.Size(50, 50),
                        anchor: new google.maps.Point(25, 50)
                    });
                });

                // ドラッグ中のイベント（リアルタイムで位置を更新）
                homeMarker.addListener('drag', function(event) {
                    const currentLat = event.latLng.lat();
                    const currentLng = event.latLng.lng();
                    document.getElementById('home-status').textContent =
                        `自宅位置: 移動中... (${currentLat.toFixed(6)}, ${currentLng.toFixed(6)})`;
                });

                // ドラッグ終了時のイベント
                homeMarker.addListener('dragend', function(event) {
                    const newLat = event.latLng.lat();
                    const newLng = event.latLng.lng();
                    updateHomeLocation(newLat, newLng);

                    // アイコンサイズを元に戻す
                    const homeIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#FF0000">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    `);
                    homeMarker.setIcon({
                        url: homeIconSvg,
                        scaledSize: new google.maps.Size(40, 40),
                        anchor: new google.maps.Point(20, 40)
                    });

                    // 距離チェック（排出位置が設定されている場合）
                    if (disposalMarker) {
                        checkDistance(); // 距離チェックとボタンの有効/無効を更新
                    }

                    // 成功メッセージ
                    Toastify({
                        text: '自宅位置を更新しました',
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                });
            }

            // 既存のマーカーも確実にドラッグ可能にする（念のため）
            if (homeMarker) {
                homeMarker.setDraggable(true);
            }

            updateHomeLocation(lat, lng);
            currentMode = null;

            // アニメーションと地図の中心移動を行う場合のみ
            if (animateAndCenter) {
                map.setCenter({
                    lat: lat,
                    lng: lng
                });
            }
        }

        // 排出位置マーカーを設定
        // animateAndCenter: true = アニメーションと地図の中心移動を行う, false = 行わない
        function setDisposalMarker(lat, lng, animateAndCenter = true) {
            console.log('setDisposalMarker呼び出し:', lat, lng, 'animateAndCenter:', animateAndCenter);

            // 座標の検証
            if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
                console.error('無効な座標です:', lat, lng);
                return;
            }

            // 地図が初期化されているか確認
            if (!map) {
                console.error('地図が初期化されていません');
                return;
            }

            if (disposalMarker) {
                // 既存のマーカーがある場合は位置を更新
                console.log('既存の排出位置マーカーを更新します');
                disposalMarker.setPosition({
                    lat: lat,
                    lng: lng
                });
            } else {
                // 新しいマーカーを作成
                console.log('新しい排出位置マーカーを作成します');
                try {
                    // 排出場所用のアイコン（カスタムSVGアイコン - ゴミ箱）
                    const disposalIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#4169E1">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    `);

                    disposalMarker = new google.maps.Marker({
                        position: {
                            lat: lat,
                            lng: lng
                        },
                        map: map, // 地図にマーカーを追加
                        title: '排出位置（ドラッグして移動できます）',
                        icon: {
                            url: disposalIconSvg,
                            scaledSize: new google.maps.Size(40, 40),
                            anchor: new google.maps.Point(20, 40)
                        },
                        draggable: true, // ドラッグ可能に設定
                        zIndex: 1000, // 他のマーカーより前面に表示
                        animation: animateAndCenter ? google.maps.Animation.DROP : null,
                        cursor: 'move', // マーカーにカーソルを合わせたときにmoveカーソルを表示
                        optimized: false // パフォーマンス最適化を無効化（確実に表示するため）
                    });

                    // マーカーが確実に地図に表示されることを確認
                    if (disposalMarker.getMap() === null) {
                        console.error('マーカーが地図に追加されていません');
                        disposalMarker.setMap(map);
                    }
                    console.log('排出位置マーカーが作成されました:', disposalMarker);
                } catch (error) {
                    console.error('マーカー作成エラー:', error);
                    return;
                }

                // マーカーにマウスオーバー時のイベント（視覚的フィードバック）
                disposalMarker.addListener('mouseover', function() {
                    const disposalIconSvgLarge = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="#4169E1">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    `);
                    disposalMarker.setIcon({
                        url: disposalIconSvgLarge,
                        scaledSize: new google.maps.Size(48, 48), // 少し大きくする
                        anchor: new google.maps.Point(24, 48)
                    });
                });

                disposalMarker.addListener('mouseout', function() {
                    const disposalIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#4169E1">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    `);
                    disposalMarker.setIcon({
                        url: disposalIconSvg,
                        scaledSize: new google.maps.Size(40, 40),
                        anchor: new google.maps.Point(20, 40)
                    });
                });

                // ドラッグ開始時のイベント
                disposalMarker.addListener('dragstart', function() {
                    document.getElementById('disposal-status').textContent = '排出位置: 移動中...';
                    // ドラッグ中はアイコンを少し大きくする
                    const disposalIconSvgDrag = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="#4169E1">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    `);
                    disposalMarker.setIcon({
                        url: disposalIconSvgDrag,
                        scaledSize: new google.maps.Size(50, 50),
                        anchor: new google.maps.Point(25, 50)
                    });
                });

                // ドラッグ中のイベント（リアルタイムで位置を更新）
                disposalMarker.addListener('drag', function(event) {
                    const currentLat = event.latLng.lat();
                    const currentLng = event.latLng.lng();
                    document.getElementById('disposal-status').textContent =
                        `排出位置: 移動中... (${currentLat.toFixed(6)}, ${currentLng.toFixed(6)})`;
                });

                // ドラッグ終了時のイベント
                disposalMarker.addListener('dragend', function(event) {
                    const newLat = event.latLng.lat();
                    const newLng = event.latLng.lng();
                    updateDisposalLocation(newLat, newLng);

                    // アイコンサイズを元に戻す
                    const disposalIconSvg = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="#4169E1">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    `);
                    disposalMarker.setIcon({
                        url: disposalIconSvg,
                        scaledSize: new google.maps.Size(40, 40),
                        anchor: new google.maps.Point(20, 40)
                    });

                    // 距離チェック（自宅位置が設定されている場合）
                    if (homeMarker) {
                        checkDistance(); // 距離チェックとボタンの有効/無効を更新
                    }

                    // 成功メッセージ
                    Toastify({
                        text: '排出位置を更新しました',
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                });
            }

            // 既存のマーカーも確実にドラッグ可能にする（念のため）
            if (disposalMarker) {
                disposalMarker.setDraggable(true);
            }

            updateDisposalLocation(lat, lng);
            currentMode = null;

            // 距離チェック
            checkDistance(); // 距離チェックとボタンの有効/無効を更新

            // アニメーションと地図の中心移動を行う場合のみ
            if (animateAndCenter) {
                map.setCenter({
                    lat: lat,
                    lng: lng
                });
            }
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

        // 距離をチェック（500m以上離れている場合アラートとボタン無効化）
        function checkDistance() {
            const submitBtn = document.getElementById('submit-btn');

            if (homeMarker && disposalMarker) {
                const distance = google.maps.geometry.spherical.computeDistanceBetween(
                    homeMarker.getPosition(),
                    disposalMarker.getPosition()
                );

                if (distance > MIN_DISTANCE) {
                    // ボタンを無効化
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.5';
                        submitBtn.style.cursor = 'not-allowed';
                        submitBtn.title = '自宅と排出位置が500m以上離れているため、送信できません。';
                    }

                    Toastify({
                        text: `自宅と排出位置が${Math.round(distance)}m離れています。距離が500m以上の場合、送信できません。位置を調整してください。`,
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff6b6b, #ee5a6f)",
                        }
                    }).showToast();

                    return false; // 距離が500m以上
                } else {
                    // ボタンを有効化
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                        submitBtn.title = '';
                    }

                    return true; // 距離が500m未満
                }
            } else {
                // マーカーが設定されていない場合もボタンを無効化
                if (submitBtn) {
                    submitBtn.disabled = false; // マーカーが設定されていない場合は、フォーム送信検証で処理
                }
                return true;
            }
        }

        // ボタンイベントは setupButtonListeners() 関数内で設定されます（地図初期化後）

        // 地図タイプ変更とフォーム送信検証は setupButtonListeners() 関数内で設定されます（地図初期化後）

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
