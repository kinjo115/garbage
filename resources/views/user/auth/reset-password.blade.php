@extends('layouts.app')

@section('meta')
    <title>パスワードリセット | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="パスワードリセット | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="パスワードリセット,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="パスワードリセット | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="パスワードリセット | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('user.reset-password', ['token' => $token]) }}">
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
                    <a href="{{ route('user.login') }}">ログイン</a>
                </div>
                <div class="breadcrumbs-item">
                    <span>パスワードリセット</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">パスワードリセット</h1>
                </div>
                <div class="form-description text-center">
                    <p>新しいパスワードを入力してください。</p>
                    <p class="text-E20000 mt-2">パスワードは8文字以上の半角英数字で入力してください。</p>
                </div>
                <form action="{{ route('user.reset-password.store', ['token' => $token]) }}" method="POST" class="mt-16 max-w-md mx-auto">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    @if(session('error'))
                        <div class="form-input-error mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-red-600 text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    @endif
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="password" class="form-label required">新しいパスワード</label> <span
                                class="text-sm text-gray-500">※半角英数字8文字以上</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="password" name="password" id="password" class="form-input" required>
                            <div class="form-input-error">
                                @error('password')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="flex items-center gap-10">
                            <label for="password_confirmation" class="form-label required">新しいパスワード（確認）</label> <span
                                class="text-sm text-gray-500">※半角英数字8文字以上</span>
                        </div>
                        <div class="form-input-wrapper">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required>
                            <div class="form-input-error">
                                @error('password_confirmation')
                                    <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="c-button btn-416FED">パスワードをリセット</button>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('user.login') }}" class="text-blue-600 hover:text-blue-800 underline">ログイン画面に戻る</a>
                    </div>
                </form>
        </div>
    </div>
@endsection

