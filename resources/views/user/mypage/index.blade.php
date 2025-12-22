@extends('layouts.app')

@section('meta')
    <title>マイページ | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="マイページ | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="マイページ,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="マイページ | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="マイページ | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.mypage') }}">
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
                    <span>マイページ</span>
                </div>
            </div>
            <div class="page-content mypage-content">
                <div class="page-header">
                    <h1 class="page-title">マイページ</h1>
                </div>

                <div class="mypage-menu">
                    <a href="{{ route('user.items.index') }}" class="mypage-menu-item">
                        <span class="mypage-menu-text">新規申込み</span>
                        <span class="mypage-menu-arrow">▶</span>
                    </a>

                    <a href="{{ route('user.history.index') }}" class="mypage-menu-item">
                        <span class="mypage-menu-text">申込み履歴</span>
                        <span class="mypage-menu-arrow">▶</span>
                    </a>

                    <p class="mypage-notice text-center">
                        過去の申込みの変更・キャンセルは、申込み履歴から行ってください。
                    </p>

                    <div class="mypage-separator"></div>

                    <a href="{{ route('user.info.edit') }}" class="mypage-menu-item">
                        <span class="mypage-menu-text">会員情報変更</span>
                        <span class="mypage-menu-arrow">▶</span>
                    </a>

                    <a href="{{ route('user.password.edit') }}" class="mypage-menu-item">
                        <span class="mypage-menu-text">パスワード変更</span>
                        <span class="mypage-menu-arrow">▶</span>
                    </a>

                    <a href="#" class="mypage-menu-item">
                        <span class="mypage-menu-text">退会する</span>
                        <span class="mypage-menu-arrow">▶</span>
                    </a>
                </div>

                <div class="mypage-logout">
                    <form method="POST" action="{{ route('user.logout') }}" id="logout-form" class="w-full flex justify-center">
                        @csrf
                        <button type="button" id="logout-button" class="mypage-logout-button">ログアウトする</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#logout-button').on('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'ログアウトしますか？',
                    text: 'ログアウトすると、再度ログインが必要になります。',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#005FBE',
                    cancelButtonColor: '#ED4141',
                    confirmButtonText: 'ログアウトする',
                    cancelButtonText: 'キャンセル',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#logout-form').submit();
                    }
                });
            });
        });
    </script>
@endsection
