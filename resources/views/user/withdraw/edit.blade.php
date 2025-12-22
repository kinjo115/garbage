@extends('layouts.app')

@section('meta')
    <title>退会する | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="退会する | 名古屋市ゴミ収集サイト">
    <meta name="keywords" content="退会,名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="退会する | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="退会する | 名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ route('user.withdraw.edit') }}">
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
                    <span>退会する</span>
                </div>
            </div>

            <div class="page-content form-content">
                <div class="page-header">
                    <h1 class="page-title">退会する</h1>
                </div>

                <div class="withdraw-content">
                    @if($hasPendingApplications)
                        <div class="withdraw-error">
                            <p class="withdraw-error-title">未完了の申込みがあります</p>
                            <p class="withdraw-error-message">すべての申込みを完了またはキャンセルしてから退会してください。</p>
                        </div>
                    @else
                        <div class="withdraw-description">
                            <p>退会後はマイページヘアクセス出来なくなります。</p>
                            <p>申込み履歴なども破棄されます。</p>
                        </div>

                        <div class="withdraw-confirmation">
                            <p>本当に退会しますか?</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="withdraw-error mt-6">
                            <p class="withdraw-error-message">{{ session('error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('user.withdraw.update') }}" method="POST" id="withdraw-form">
                        @csrf
                        <div class="withdraw-submit">
                            <button type="button" class="withdraw-button" id="withdraw-button" {{ $hasPendingApplications ? 'disabled' : '' }}>退会する</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 退会確認モーダル -->
    <div id="withdraw-confirm-modal" class="c-modal">
        <div class="c-modal-overlay"></div>
        <div class="c-modal-content withdraw-modal-content">
            <div class="withdraw-modal-body">
                <h2 class="withdraw-modal-title">退会について</h2>
                <p class="withdraw-modal-description">全てのユーザー情報が削除されます</p>
                <div class="withdraw-modal-buttons">
                    <button type="button" class="withdraw-modal-button withdraw-modal-button-confirm" id="withdraw-confirm-button">完全に退会する</button>
                    <button type="button" class="withdraw-modal-button withdraw-modal-button-cancel" id="withdraw-cancel-button">退会をキャンセルする</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 退会ボタンのクリックイベント（無効化されていない場合のみ）
            $('#withdraw-button').on('click', function(e) {
                if ($(this).prop('disabled')) {
                    e.preventDefault();
                    return false;
                }

                e.preventDefault();

                // モーダルを表示
                $('#withdraw-confirm-modal').addClass('is-active');
                $('body').css('overflow', 'hidden');
            });

            // 完全に退会するボタン
            $('#withdraw-confirm-button').on('click', function() {
                // フォームを送信
                $('#withdraw-form').submit();
            });

            // 退会をキャンセルするボタン
            $('#withdraw-cancel-button, .c-modal-overlay').on('click', function() {
                // モーダルを閉じる
                $('#withdraw-confirm-modal').removeClass('is-active');
                $('body').css('overflow', '');
            });

            // ESCキーでモーダルを閉じる
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#withdraw-confirm-modal').hasClass('is-active')) {
                    $('#withdraw-confirm-modal').removeClass('is-active');
                    $('body').css('overflow', '');
                }
            });
        });
    </script>
@endsection

