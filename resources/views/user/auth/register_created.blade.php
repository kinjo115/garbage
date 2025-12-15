@extends('layouts.app')

@section('meta')
    <title>新規申込みの受付 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="新規申込みの受付 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="新規登録,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="新規登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="新規登録 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.register') }}">
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
                    <span>メールの送信が完了しました</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">メールの送信が完了しました</h1>
                </div>
                <div class="form-description">
                    <p class="text-E20000">申込む前に必ずお読みください</p>
                    <br>
                    <p>・メールに記載されているURLに24時間以内にアクセスして、申込みをしてください。24時間以内にアクセスさ　れなかった場合には、URLは無効になります。その場合は、最初からお申し込みください。</p>
                    <br>
                    <p>・お知らせメールの発信元は<a href="mailto:noreply@nagoya-sodaigomi.jp" class="text-blue-500 underline"
                            target="_blank">noreply@nagoya-sodaigomi.jp</a>です。</p>
                    <p>・ドメイン等で受信拒否設定されている場合は解除してください。</p>
                    <p>・メールの設定については、お使いのプロバイダなどのメールアドレス提供事業者にお問い合わせください。</p>
                </div>
            </div>
        </div>
    </div>
@endsection
