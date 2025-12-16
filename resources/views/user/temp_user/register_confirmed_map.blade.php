@extends('layouts.app')

@section('meta')
    <title>新規申込みの登録 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="新規申込みの登録 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="新規申込みの登録,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="新規申込みの登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="新規申込みの登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.register.confirm', ['token' => $tempUser->token]) }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection
