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
            <div class="breadcrumbs">
                <div class="breadcrumbs-item">
                    <a href="{{ route('home') }}">ホーム</a>
                </div>
                <div class="breadcrumbs-item">
                    <span>ログイン</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">ログイン</h1>
                </div>
                <div class="form-description">
                    <p class="text-E20000">申込む前に必ずお読みください</p>
                    <br>
                    <p>ログイン情報が正しければ、本人確認用コードを、入力したメールアドレスに送信します。</p>
                    <p>お知らせメールの発信元は<noreply@nagoya-sodaigomi.jp>です。</p>
                    <p>ドメイン等で受信拒否設定されている場合は解除してください。</p>
                    <p>メールの設定については、お使いのプロバイダなどのメールアドレス提供事業者にお問い合わせください。</p>
                </div>
                <form action="{{ route('user.login.store') }}" method="POST" class="mt-16 max-w-md mx-auto">
                    @csrf
                    @if(session('error'))
                        <div class="form-input-error mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-red-600 text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    @endif
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="email" class="form-label required">メールアドレス</label> <span
                                class="text-sm text-gray-500">※半角英数字</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}" required>
                            <div class="form-input-error">
                                @error('email')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="phone" class="form-label required">電話番号</label> <span
                                class="text-sm text-gray-500">※半角英数字</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="text" name="phone" id="phone" class="form-input" value="{{ old('phone') }}" required>
                            <div class="form-input-error">
                                @error('phone')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="password" class="form-label required">パスワード</label> <span
                                class="text-sm text-gray-500">※半角英数字</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="password" name="password" id="password" class="form-input" value="{{ old('password') }}" required>
                            <div class="form-input-error">
                                @error('password')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="c-button btn-416FED">ログイン</button>
                    </div>
                </form>
        </div>
    </div>
@endsection
