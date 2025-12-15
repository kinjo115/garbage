@extends('layouts.app')

@section('meta')
    <title>新規申込みの受付 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="新規申込みの受付 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="新規申込みの受付,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="新規申込みの受付 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="新規申込みの受付 | 名古屋市ゴミ収集サイト">
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
                    <span>新規申込みの受付</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">新規申込みの受付</h1>
                </div>
                <div class="form-description">
                    <p class="text-E20000">申込む前に必ずお読みください</p>
                    <br>
                    <p>お申込み画面専用URLを、入力したメールアドレスに送信します。</p>
                    <p>お知らせメールの発信元は<noreply@nagoya-sodaigomi.jp>です。</p>
                    <p>ドメイン等で受信拒否設定されている場合は解除してください。</p>
                    <p>メールの設定については、お使いのプロバイダなどのメールアドレス提供事業者にお問い合わせください。</p>
                </div>
                <form action="{{ route('user.register.store') }}" method="POST" class="mt-16">
                    @csrf
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="email" class="form-label required">メールアドレス</label> <span
                                class="text-sm text-gray-500">※半角英数字</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="email" name="email" id="email" class="form-input"
                                value="{{ old('email') }}" required>
                            <div class="form-input-error">
                                @error('email')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="privacy-policy">
                        <div>
                            <input type="checkbox" name="privacy_policy" id="privacy_policy" class="privacy-policy-input">
                            <label for="privacy_policy">
                                <span>サイトの利用規約を読んで同意しました <span class="text-E20000">*</span></span>
                            </label>
                        </div>
                        <div class="form-input-error">
                            @error('privacy_policy')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="c-button btn-ED4141">申込み用メールを送信</button>
                    </div>
                </form>
                <div class="text-center mt-10">
                    既にアカウントをお持ちの方は<a href="{{ route('user.login') }}" class="text-ED4141 text-underline">こちら</a>
                </div>
            </div>
        </div>
    </div>
@endsection
