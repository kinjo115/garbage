@extends('layouts.app')

@section('meta')
    <title>決済完了 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="決済完了 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="決済完了,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="決済完了 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="決済完了 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $tempUser->token ? route('guest.payment.complete', ['token' => $tempUser->token]) : route('user.payment.complete', ['id' => $selected->id]) }}">
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
                        <span>決済完了</span>
                    </div>
                @else
                    <div class="breadcrumbs-item">
                        <a href="{{ route('user.mypage') }}">マイページ</a>
                    </div>
                    <div class="breadcrumbs-item">
                        <span>決済完了</span>
                    </div>
                @endif
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">決済完了</h1>
                </div>
            </div>
        </div>
    </div>
@endsection
