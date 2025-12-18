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
                </form>
            </div>
        </div>
    </div>
@endsection
