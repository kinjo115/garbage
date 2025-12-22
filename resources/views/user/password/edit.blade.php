@extends('layouts.app')

@section('meta')
    <title>パスワード変更 | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="パスワード変更 | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="パスワード変更,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="パスワード変更 | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="パスワード変更 | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.password.edit') }}">
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
                    <a href="{{ route('user.mypage') }}">マイページ</a>
                </div>
                <div class="breadcrumbs-item">
                    <span>パスワード変更</span>
                </div>
            </div>
            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">パスワード変更</h1>
                </div>

                <div class="mt-6 text-center text-gray-600">
                    <p>パスワードは、半角英数で8文字以上入力してください。</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success mt-6" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 4px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error mt-6" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px;">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('user.password.update') }}" method="POST" class="mt-10 max-w-xl mx-auto">
                    @csrf
                    <div class="form-group">
                        <label for="password" class="form-label required">
                            パスワード
                        </label>
                        <div class="form-input-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input @error('password') border-red-500 @enderror"
                                required
                                autocomplete="new-password"
                            >
                            <div class="form-input-error">
                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-6">
                        <label for="password_confirmation" class="form-label required">
                            パスワード(確認)
                        </label>
                        <div class="form-input-wrapper">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-input @error('password_confirmation') border-red-500 @enderror"
                                required
                                autocomplete="new-password"
                            >
                            <div class="form-input-error">
                                @error('password_confirmation')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-submit mt-10">
                        <button type="submit" class="c-button btn-ED4141">
                            変更する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

