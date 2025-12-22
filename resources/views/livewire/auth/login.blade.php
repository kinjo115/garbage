@extends('layouts.app')

@section('meta')
    <title>管理画面ログイン | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="管理画面ログイン | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="管理画面,ログイン,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="管理画面ログイン | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="管理画面ログイン | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('login') }}">
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
                    <span>管理画面ログイン</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">管理画面ログイン</h1>
                </div>
                <div class="form-description text-center">
                    <p class="text-E20000">管理者のみアクセス可能です</p>
                    <br>
                    <p>メールアドレスとパスワードを入力してログインしてください。</p>
                </div>
                <form method="POST" action="{{ route('login') }}" class="mt-16 max-w-md mx-auto">
                    @csrf

                    @if(session('status'))
                        <div class="form-input-error mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                            <p class="text-green-600 text-sm font-medium">{{ session('status') }}</p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="form-input-error mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                            @foreach($errors->all() as $error)
                                <p class="text-red-600 text-sm font-medium">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <!-- Email Address -->
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="email" class="form-label required">メールアドレス</label>
                            <span class="text-sm text-gray-500">※半角英数字</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="email@example.com">
                            <div class="form-input-error">
                                @error('email')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="password" class="form-label required">パスワード</label>
                            <span class="text-sm text-gray-500">※半角英数字</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="password" name="password" id="password" class="form-input" required autocomplete="current-password" placeholder="パスワード">
                            <div class="form-input-error">
                                @error('password')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="mr-2" {{ old('remember') ? 'checked' : '' }}>
                            <span class="text-sm text-gray-600">ログイン状態を保持する</span>
                        </label>
                    </div>

                    <div class="form-submit">
                        <button type="submit" class="c-button btn-416FED">ログイン</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
