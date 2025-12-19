@php
    $housingTypes = \App\Models\HousingType::all();
    $prefectures = \App\Models\Prefecture::all();
@endphp

@extends('layouts.app')

@section('meta')
    <title>新規申込みの登録 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="新規申込みの登録 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="新規申込みの登録,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="新規申込みの登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="新規申込みの登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('guest.register.confirm', ['token' => $tempUser->token]) }}">
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
                    <span>新規申込みの登録</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">新規申込みの登録</h1>
                </div>
                <div class="form-description">
                    <p class="text-E20000">申込む前に必ずお読みください</p>
                    <br>
                    <p>・名古屋市に在住の方のみ、お申し込みができます。</p>
                    <p>・第三者によるお申し込みはできません。必ず粗大ごみの所有者本人、もしくは同居の家族の方が申し込んでください。</p>
                    <p>・(*)のある項目は必須項目です。</p>
                    <p>・町名は、「町丁目を選択する」ボタンを押して住所検索画面から選択します。（直接入力はできません）</p>
                    <p>・番地は、数字で入力してください。</p>
                    <p>・電話番号と緊急連絡先はハイフンなしで入力してください。</p>
                </div>
                <div class="form-description mt-16">
                    <div class="text-E20000 mb-4">注意事項</div>
                    <p>・お申し込みの途中に前の画面に戻る場合は、必ず各画面の「戻る」ボタンをクリックしてください。</p>
                    <p>※ブラウザの「戻る」機能は使用しないでください。</p>
                </div>
                <form action="{{ route('guest.register.confirm.store', ['token' => $tempUser->token]) }}" method="POST"
                    class="mt-16">
                    @csrf
                    <div class="grid md:grid-cols-2 grid-cols-1 gap-2">
                        <div class="form-group">
                            <div class="flex items-center gap-10">
                                <label for="last_name" class="form-label required">姓</label>
                            </div>
                            <div class="form-input-wrapper">
                                <input type="text" name="last_name" id="last_name" class="form-input"
                                    value="{{ old('last_name', $tempUser->userInfo ? $tempUser->userInfo->last_name : '') }}"
                                    required>
                            </div>
                            <div class="form-input-error">
                                @error('last_name')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="flex items-center gap-10">
                                <label for="first_name" class="form-label required">名</label>
                            </div>
                            <div class="form-input-wrapper">
                                <input type="text" name="first_name" id="first_name" class="form-input"
                                    value="{{ old('first_name', $tempUser->userInfo ? $tempUser->userInfo->first_name : '') }}"
                                    required>
                            </div>
                            <div class="form-input-error">
                                @error('first_name')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="housing_type_id" class="form-label required">住宅種別</label>
                        </div>
                        <div class="form-input-wrapper">
                            <select name="housing_type_id" id="housing_type_id" class="form-input max-w-1/2">
                                <option value="">住宅種別を選択してください</option>
                                @foreach ($housingTypes as $housingType)
                                    <option value="{{ $housingType->id }}"
                                        {{ old('housing_type_id', $tempUser->userInfo ? $tempUser->userInfo->housing_type_id : '') == $housingType->id ? 'selected' : '' }}>
                                        {{ $housingType->name }}</option>
                                @endforeach
                                <div class="form-input-error">
                                    @error('housing_type_id')
                                        <p class="text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="prefecture_id" class="form-label required">郵便番号</label>
                        </div>
                        <div class="form-input-wrapper">
                            <div class="flex flex-wrap gap-2">
                                <input type="text" name="postal_code" id="postal_code" class="form-input max-w-1/2"
                                    value="{{ old('postal_code', $tempUser->userInfo ? $tempUser->userInfo->postal_code : '') }}"
                                    required>
                                <button type="button" class="form-btn" id="fetch-address-btn">住所を反映する</button>
                            </div>
                            <div class="form-input-error">
                                @error('postal_code')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="prefecture_id" class="form-label required">都道府県</label>
                        </div>
                        <div class="form-input-wrapper">
                            <select name="prefecture_id" id="prefecture_id" class="form-input max-w-1/2">
                                <option value="">都道府県を選択してください</option>
                                @foreach ($prefectures as $prefecture)
                                    <option value="{{ $prefecture->id }}"
                                        {{ old('prefecture_id', $tempUser->userInfo ? $tempUser->userInfo->prefecture_id : '23') == $prefecture->id ? 'selected' : '' }}>
                                        {{ $prefecture->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-input-error">
                                @error('prefecture_id')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="city" class="form-label required">市区町村</label>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="text" name="city" id="city" class="form-input max-w-1/2"
                                value="{{ old('city', $tempUser->userInfo ? $tempUser->userInfo->city : '名古屋市') }}"
                                required>
                            <div class="form-input-error">
                                @error('city')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="town" class="form-label required">町名</label>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="text" name="town" id="town" class="form-input max-w-1/2"
                                value="{{ old('town', $tempUser->userInfo ? $tempUser->userInfo->town : '') }}" required>
                            <div class="form-input-error">
                                @error('town')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="town" class="form-label required">番地</label>
                        </div>
                        <div class="form-input-wrapper">
                            <div class="flex items-center gap-2">
                                <input type="text" name="chome" id="chome" class="form-input max-w-[100px]"
                                    value="{{ old('chome', $tempUser->userInfo ? $tempUser->userInfo->chome : '') }}">
                                <span class="mr-5">丁目</span>
                                <input type="text" name="building_number" id="building_number"
                                    class="form-input max-w-[100px]"
                                    value="{{ old('building_number', $tempUser->userInfo ? $tempUser->userInfo->building_number : '') }}">
                                <span class="mr-5">番</span>
                                <input type="text" name="house_number" id="house_number"
                                    class="form-input max-w-[100px]"
                                    value="{{ old('house_number', $tempUser->userInfo ? $tempUser->userInfo->house_number : '') }}">
                                <span>号</span>
                            </div>
                            <div class="text-sm mt-2 text-gray-500">
                                <p>（号に枝番がある場合は、「2-3」のように入力してください）</p>
                                <p>※部屋番号は番地欄に入力しないでください</p>
                            </div>
                            <div class="form-input-error">
                                @error('chome')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                                @error('building_number')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                                @error('house_number')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="town" class="form-label">マンション名</label>
                        </div>
                        <div class="form-input-wrapper">
                            <div class="flex items-end gap-2">
                                <input type="text" name="apartment_name" id="apartment_name"
                                    class="form-input max-w-1/2 mr-5"
                                    value="{{ old('apartment_name', $tempUser->userInfo ? $tempUser->userInfo->apartment_name : '') }}">
                                <span class="">部屋番号</span>
                                <input type="text" name="apartment_number" id="apartment_number"
                                    class="form-input max-w-[100px]"
                                    value="{{ old('apartment_number', $tempUser->userInfo ? $tempUser->userInfo->apartment_number : '') }}">
                            </div>
                            <div class="text-sm mt-2 text-gray-500">
                                <p>（部屋番号まで入力してください。）</p>
                                <p>例) コーポ名古屋　 １０３　※集合住宅にお住いの方は必ず入力してください。</p>
                            </div>
                            <div class="form-input-error">
                                @error('apartment_name')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                                @error('apartment_number')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="town" class="form-label required">電話番号</label>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="text" name="phone_number" id="phone_number" class="form-input max-w-1/2"
                                value="{{ old('phone_number', $tempUser->userInfo ? $tempUser->userInfo->phone_number : '') }}"
                                required>
                            <div class="text-sm mt-2 text-gray-500">
                                <p>※市外局番から入力して下さい。ハイフン不要(数字のみ)</p>
                            </div>
                            <div class="form-input-error">
                                @error('phone_number')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="emergency_contact" class="form-label required">緊急連絡先</label>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="text" name="emergency_contact" id="emergency_contact" class="form-input"
                                value="{{ old('emergency_contact', $tempUser->userInfo ? $tempUser->userInfo->emergency_contact : '') }}"
                                required>
                            <div class="text-sm mt-2 text-gray-500">
                                <p>※平日の9:00～17:00の間に繋がる番号を市外局番から入力して下さい。ハイフン不要(数字のみ)</p>
                                <p>※内容確認のため、お電話をさせて頂くことがあります。</p>
                            </div>
                        </div>
                        <div class="form-input-error">
                            @error('emergency_contact')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="email" class="form-label required">メールアドレス</label>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="text" name="email" id="email" class="form-input max-w-1/2"
                                value="{{ old('email', $tempUser->email) }}" required readonly>
                        </div>
                        <div class="form-input-error">
                            @error('email')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="text-center mt-10">
                        <p>上記の入力内容を確認し、内容に誤りがなければ</p>
                        <p>「地図の登録に進む」ボタンをクリックしてください。</p>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="c-button btn-416FED">地図の登録に進む</button>
                    </div>
                    <div class="md:mt-16 mt-10 flex justify-center">
                        <a href="{{ route('guest.register') }}" class="c-btn-black">戻る</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.japanPostApiUrl = '{{ route('api.japan-post.search') }}';
    </script>
@endsection
