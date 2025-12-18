@extends('layouts.app')

@section('meta')
    <title>支払い方法 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="支払い方法 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="支払い方法,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="支払い方法 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="支払い方法 | 名古屋市ゴミ収集サイト">
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
                    <span>支払い方法</span>
                </div>
            </div>

            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">支払い方法</h1>
                </div>
                <div class="form-notification">まだ申込みは完了していません</div>
                <div class="form-description mt-16">
                    <p class="text-E20000 text-bold">【注意事項】</p>
                    <br>
                    <p>・支払い方法が確定しなければ、申込みは完了しません。</p>
                    <p>・コンビニで排出券を購入する場合は、申込みが完了し、コンビニでの購入方法が表示されます。期日までに対応してください。</p>
                    <p>・オンライン支払いの場合は、支払いが確定しなければ、申し込みが完了しません。</p>
                </div>
                <div class="form-submit">
                    <button type="button" class="c-button btn-416FED">コンビニで排出券を購入する</button>
                </div>
                <div class="form-submit">
                    <button type="button" class="c-button btn-416FED">オンラインで支払いする</button>
                </div>

            </div>
        </div>
    </div>
@endsection
