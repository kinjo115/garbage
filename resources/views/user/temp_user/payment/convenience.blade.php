@extends('layouts.app')

@section('meta')
    <title>コンビニ決済 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="コンビニ決済 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="コンビニ決済,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="コンビニ決済 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="コンビニ決済 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ $tempUser->token ? route('guest.payment.convenience', ['token' => $tempUser->token]) : route('user.payment.convenience', ['id' => $selected->id]) }}">
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
                        <span>コンビニ決済</span>
                    </div>
                @else
                    <div class="breadcrumbs-item">
                        <a href="{{ route('user.mypage') }}">マイページ</a>
                    </div>
                    <div class="breadcrumbs-item">
                        <span>コンビニ決済</span>
                    </div>
                @endif
            </div>

            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">コンビニ決済</h1>
                </div>
                <div class="form-notification">コンビニ決済は現在準備中です</div>
                <div class="form-description mt-16">
                    <p class="text-E20000 text-bold">【注意事項】</p>
                    <br>
                    <p>・コンビニ決済機能は現在準備中です。</p>
                    <p>・オンライン決済をご利用ください。</p>
                </div>
                <div class="form-submit mt-6">
                    <a href="{{ $tempUser->token ? route('guest.payment.index', ['token' => $tempUser->token]) : route('user.payment.index', ['id' => $selected->id]) }}" class="c-button btn-416FED">支払い方法選択に戻る</a>
                </div>
            </div>
        </div>
    </div>
@endsection


