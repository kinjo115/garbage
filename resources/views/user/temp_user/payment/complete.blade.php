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
    <meta property="og:url" content="{{ route('guest.payment.complete', ['token' => $tempUser->token]) }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection

@section('content')
    <div class="c-page">
        <div class="c-container">
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">決済完了</h1>
                </div>
            </div>
        </div>
    </div>
@endsection
