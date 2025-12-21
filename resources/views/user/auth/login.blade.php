@extends('layouts.app')

@section('meta')
    <title>ログイン | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="ログイン | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="ログイン,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="ログイン | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="ログイン | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('user.login') }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection

@section('content')
    <div class="c-page">
        <div class="c-container">
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">ログイン</h1>
                </div>
            </div>
        </div>
    </div>
@endsection
