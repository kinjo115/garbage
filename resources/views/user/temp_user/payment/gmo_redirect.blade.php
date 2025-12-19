@extends('layouts.app')

@section('meta')
    <title>決済処理中 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="決済処理中 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="決済処理中,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="決済処理中 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="決済処理中 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection

@section('content')
    <div class="c-page">
        <div class="c-container">
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">決済処理中</h1>
                </div>
                <div class="form-description mt-16">
                    <p>GMOペイメントの決済画面へリダイレクトしています。</p>
                    <p>しばらくお待ちください...</p>
                </div>

                <form id="gmo-payment-form" action="{{ $gmoUrl }}" method="POST">
                    @foreach ($params as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>

                <div class="form-submit mt-16">
                    <p class="text-center">自動的にリダイレクトされない場合は、下記のボタンをクリックしてください。</p>
                    <button type="button" onclick="document.getElementById('gmo-payment-form').submit();"
                        class="c-button btn-416FED mt-6">
                        決済画面へ進む
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ページ読み込み時に自動的にフォームを送信
        window.onload = function() {
            document.getElementById('gmo-payment-form').submit();
        };
    </script>
@endsection
