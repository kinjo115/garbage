@extends('layouts.app')

@section('meta')
    <title>名古屋市ゴミ収集サイト</title>
    <meta name="description" content="名古屋市ゴミ収集サイト">
    <meta name="keywords" content="名古屋市,ゴミ収集,ゴミ収集サイト">
    <meta name="author" content="名古屋市ゴミ収集サイト">
    <meta property="og:title" content="名古屋市ゴミ収集サイト">
    <meta property="og:description" content="名古屋市ゴミ収集サイト">
    <meta property="og:image" content="{{ asset('assets/images/ogp.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="名古屋市ゴミ収集サイト">
@endsection

@section('content')
    <div class="c-page">
        <div class="c-container">
            {{-- 名古屋市からのお知らせ --}}
            <div class="announcement-header">
                <h2 class="announcement-title">名古屋市からのお知らせ</h2>
            </div>

            {{-- お知らせ内容 --}}
            <div class="announcement-content">
                <div class="announcement-item">
                    <h3 class="announcement-item-title">【年末年始について】</h3>
                    <p>インターネットでの申込みは24時間受付しております。</p>
                    <p>粗大ごみ受付センターは、令和7年12月27日（金）から令和8年1月4日（土）まで休業いたします。</p>
                    <p>令和8年1月7日（火）～9日（木）の収集分については、令和7年12月26日（木）17時までに申込みが必要です。</p>
                </div>

                <div class="announcement-item">
                    <h3 class="announcement-item-title">ジモティスポット名古屋について</h3>
                    <p>ジモティ株式会社との連携により、西区・守山区・港区に「ジモティスポット名古屋」を開設しました。</p>
                    <p>再利用可能な品目を無料でお持ちいただけます。詳しくは<a href="#" class="announcement-link">こちら</a>をご覧ください。</p>
                </div>
            </div>

            {{-- ナビゲーションブロック --}}
            <div class="navigation-blocks">
                <div class="nav-block">
                    <div class="nav-block-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H21V8H3V6ZM3 11H21V13H3V11ZM3 16H21V18H3V16Z" fill="white" />
                        </svg>
                    </div>
                    <p class="nav-block-text">家庭ごみ</p>
                    <p class="nav-block-subtext">資源の分け方・出し方</p>
                </div>

                <div class="nav-block">
                    <div class="nav-block-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H21V8H3V6ZM3 11H21V13H3V11ZM3 16H21V18H3V16Z" fill="white" />
                        </svg>
                    </div>
                    <p class="nav-block-text">各区の資源・ごみ収集日</p>
                </div>

                <div class="nav-block">
                    <div class="nav-block-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H21V8H3V6ZM3 11H21V13H3V11ZM3 16H21V18H3V16Z" fill="white" />
                        </svg>
                    </div>
                    <p class="nav-block-text">各区の環境事業所</p>
                </div>

                <div class="nav-block">
                    <div class="nav-block-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H21V8H3V6ZM3 11H21V13H3V11ZM3 16H21V18H3V16Z" fill="white" />
                        </svg>
                    </div>
                    <p class="nav-block-text">品目一覧表</p>
                </div>

                <div class="nav-block">
                    <div class="nav-block-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H21V8H3V6ZM3 11H21V13H3V11ZM3 16H21V18H3V16Z" fill="white" />
                        </svg>
                    </div>
                    <p class="nav-block-text">よくある質問</p>
                </div>
            </div>

            {{-- メインCTA --}}
            <div class="main-cta">
                <h2 class="main-cta-title">粗大ごみ収集申込みはこちらから</h2>
            </div>

            {{-- 注意書きバナー --}}
            <div class="warning-banner">
                <p class="warning-text">お申込みの前に必ずお読み下さい</p>
            </div>

            {{-- アコーディオン情報セクション --}}
            <div class="info-section">
                <p class="info-section-note">※各項目をクリックして、詳細を確認してください。</p>

                <div class="accordion-wrapper">
                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>粗大ごみの対象となるもの</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>粗大ごみの対象となるものの詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>市が収集しないごみ</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>市が収集しないごみの詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>インターネットで申込みができる品目</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>インターネットで申込みができる品目の詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>インターネット受付の締切日</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>インターネット受付の締切日の詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>粗大ごみの出し方</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>粗大ごみの出し方の詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>なごやか収集</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>なごやか収集の詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>減免制度</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>減免制度の詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>動作確認済みブラウザ</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>動作確認済みブラウザの詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>個人情報の保護</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>個人情報の保護の詳細説明がここに入ります。</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header" type="button">
                            <span>注意事項</span>
                            <span class="accordion-chevron">▶</span>
                        </button>
                        <div class="accordion-content">
                            <p>注意事項の詳細説明がここに入ります。</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 最終確認セクション --}}
            <div class="confirmation-section">
                <form id="agreement-form" method="GET" action="{{ route('guest.register') }}">
                    <div class="confirmation-text text-center">
                        <p>上記内容について確認しました。</p>
                        <p>申し込む粗大ごみは、家庭から出るものです。</p>
                        <p>※家庭から出る粗大ごみ以外は申込みできません。</p>
                    </div>

                    <div class="confirmation-submit mt-16">
                        <button type="submit" class="c-button-primary" id="agree-button">
                            同意して進む
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="application-modal" class="c-modal">
        <div class="c-modal-overlay"></div>
        <div class="c-modal-content modal-lg modal-about-reg">
            <button class="c-modal-close" id="modal-close">&times;</button>
            <div class="c-modal-header">
                <h2 class="c-modal-title c-title text-center">申込みについて</h2>
            </div>
            <div class="c-modal-body">
                <div class="mt-6">
                    <p class="text-center">はじめて申込む場合</p>

                    <div class="mt-5">
                        <a href="{{ route('guest.register') }}" class="c-button btn-416FED">新規登録</a>
                    </div>
                </div>
                <div class="mt-10">
                    <div class="text-center">
                        <p>すでに会員登録を行っている場合や</p>
                        <p>受付番号を用いて申込み内容に修正を行う方</p>
                    </div>
                    <div class="p-alert">
                        <p>・品目の追加は、収集日の７日前までに行ってください。</p>
                        <p>・品目の減少は、収集日の１日前までに行ってください。</p>
                        <p>・受付の取消は、収集日の１日前までに行ってください。</p>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('user.login') }}" class="c-button btn-ED4141">ログイン</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // アコーディオンの開閉（jQuery slideDown/slideUp使用、複数同時に開ける）
            $('.accordion-header').on('click', function() {
                const $item = $(this).closest('.accordion-item');
                const $content = $item.find('.accordion-content');
                const $chevron = $(this).find('.accordion-chevron');
                const isCurrentlyOpen = $item.hasClass('is-open');

                // クリックしたアコーディオンを開閉（jQuery slideDown/slideUp）
                if (isCurrentlyOpen) {
                    $item.removeClass('is-open');
                    $content.slideUp(400);
                    $chevron.removeClass('is-open');
                } else {
                    $item.addClass('is-open');
                    $content.slideDown(400);
                    $chevron.addClass('is-open');
                }
            });

            // フォーム送信時にモーダルを表示（デフォルトの送信を防ぐ）
            $('#agreement-form').on('submit', function(e) {
                e.preventDefault();
                $('#application-modal').addClass('is-active');
                $('body').css('overflow', 'hidden');
            });

            // モーダルを閉じる
            $('#modal-close, .c-modal-overlay').on('click', function() {
                $('#application-modal').removeClass('is-active');
                $('body').css('overflow', '');
            });

            // ESCキーでモーダルを閉じる
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#application-modal').hasClass('is-active')) {
                    $('#application-modal').removeClass('is-active');
                    $('body').css('overflow', '');
                }
            });
        });
    </script>
@endsection
