@extends('layouts.app')

@section('meta')
    <title>404 - ページが見つかりません | 名古屋市ゴミ収集サイト</title>
    <meta name="description" content="お探しのページが見つかりませんでした。名古屋市ゴミ収集サイト">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="404 - ページが見つかりません | 名古屋市ゴミ収集サイト">
    <meta property="og:description" content="お探しのページが見つかりませんでした。">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ja_JP">
@endsection

@section('content')
    <div class="c-page">
        <div class="c-container">
            <div class="breadcrumbs">
                <div class="breadcrumbs-item">
                    <a href="{{ route('home') }}">ホーム</a>
                </div>
                <div class="breadcrumbs-item">
                    <span>404 - ページが見つかりません</span>
                </div>
            </div>
            <div class="page-content">
                <div class="page-header">
                    <h1 class="page-title">404</h1>
                    <h2 class="page-subtitle"
                        style="font-size: 24px; font-weight: 600; color: #505050; margin-top: 20px; text-align: center;">
                        ページが見つかりません
                    </h2>
                </div>

                <div class="error-content" style="text-align: center; padding: 40px 20px;">
                    <div style="max-width: 600px; margin: 0 auto;">
                        <p style="font-size: 16px; line-height: 1.8; color: #505050; margin-bottom: 30px;">
                            申し訳ございませんが、お探しのページは見つかりませんでした。<br>
                            ページが移動または削除された可能性があります。
                        </p>

                        <div style="margin: 40px 0;">
                            <a href="{{ route('home') }}" class="c-button btn-416FED"
                                style="display: inline-block; background-color: #416FED; color: #fff; padding: 14px 40px; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 16px; font-family: 'Noto Sans JP', sans-serif;">
                                ホームに戻る
                            </a>
                        </div>

                        <div style="margin-top: 40px; padding: 20px; background-color: #f5f5f5; border-radius: 5px;">
                            <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                                <strong>よくある原因：</strong>
                            </p>
                            <ul
                                style="text-align: left; font-size: 14px; color: #666; line-height: 1.8; max-width: 400px; margin: 0 auto;">
                                <li>URLの入力ミス</li>
                                <li>ページが移動または削除された</li>
                                <li>リンクが古くなっている</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
